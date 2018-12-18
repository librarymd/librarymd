<?php

function showActiveUsersOnWebsite() {
  global $CURUSER;

  $nowActiveLastHours = 24;
  $dt = time() - (60 * 60 * $nowActiveLastHours);
  $dt = sqlesc(get_date_time($dt));

  $onTrackerUsers = mem2_get('ontracker_users_40');
  $activeusers_total = mem2_get('ontracker_users_count');

  if (!$onTrackerUsers) {
      $onTrackerUsers = fetchAll("SELECT users.id, users.username, users.class, users.ip
              FROM users_down_up
              RIGHT JOIN users ON users_down_up.id = users.id
              WHERE users_down_up.last_access >= $dt
              ORDER BY users.username");

      $onTrackerUsers40 = array_slice($onTrackerUsers,0,40);
      $activeusers_total = count($onTrackerUsers);
      mem2_set('ontracker_users',$onTrackerUsers, 60);
      mem2_set('ontracker_users_40',$onTrackerUsers40, 60);
      mem2_set('ontracker_users_count',$activeusers_total, 60);
      $onTrackerUsers = $onTrackerUsers40;
  }

  if (isset($_GET['show_all_online_users'])) {
      $onTrackerUsers = mem2_get('ontracker_users');
  }

  $activeusers = '';
  $activeusers_i = 0;
  $me_in_list = false;

  foreach($onTrackerUsers AS $arr) {
      if ($activeusers) $activeusers .= ", ";

      $withLink = isset($CURUSER);
      $link = linkForUser($arr, $withLink);

      //When logged, show links to online user details
      $activeusers .= $link;
      $activeusers_i++;
      if (isset($CURUSER) && $CURUSER['id'] == $arr["id"]) $me_in_list = true;
  }
  // Add myself to the end if not present
  if (isset($CURUSER) && !$me_in_list) {
      if ($activeusers) $activeusers .= ", ";

      $link = linkForUser($CURUSER, true);
      $activeusers .= $link;
      $activeusers_i++;
  }

  $mostEverStr = recordAndGetMaxUsersOnWebsite($activeusers_total);

  if (!isset($activeusers)) $activeusers = "There have been no active users in the last 15 minutes.";
  ?>

  <h2 align="left">
      <span class="style1 style7"><?=$GLOBALS['lang']['index_active_users']?> <?php echo ' (',$activeusers_total,') '; ?></span>
      <span style="color:#F5F4EA;"><?=$mostEverStr?></span>
  </h2>
  <table width=100% border=1 cellspacing=0 cellpadding=10><tr><td id="users_online">
  <?php echo $activeusers ?>, ...
  <?php if (!isset($_GET['show_all_online_users'])): ?>
      <br><br>
      <a href="?show_all_online_users=1" style="color:#0A50A1;"><?=__("arată toată lista de"),' ',$activeusers_total,' ',__("utilizatori")?>...</a>
  <?php endif; ?>
  </td></tr></table>

<?php
}

function recordAndGetMaxUsersOnWebsite($activeusers_total) {
  /**
      Most users ever code
  */
  $mostEver = mem_get('stat_most_online');
  if (!$mostEver) {
      $mostEver = fetchOne('SELECT value FROM avps WHERE arg="most_online"');
      $mostEver_date = fetchOne('SELECT value FROM avps WHERE arg="most_online_date"');
      // That mean no rows found
      if ($mostEver === NULL) {
          Q('INSERT INTO avps VALUES ("most_online",0)');
          Q('INSERT INTO avps VALUES ("most_online_date",:time)',array('time'=>time() ) );
      }
      $mostEver = serialize(array($mostEver,$mostEver_date));
      mem_set('stat_most_online', $mostEver );
  }
  list($mostEver,$mostEver_date) = unserialize($mostEver);

  if ($activeusers_total > $mostEver) {
      Q('UPDATE avps SET value=:now WHERE arg="most_online"', array('now'=>$activeusers_total) );
      Q('UPDATE avps SET value=:time WHERE arg="most_online_date"', array('time'=>time() ) );
      // purge
      mem_delete('stat_most_online');
      // update vars for current user
      $mostEver = $activeusers_total;
      $mostEver_date = time();
  }

  return " (" . __('cel mai mulţi').": $mostEver" . __(' la ') . date('d-F-Y G:i',$mostEver_date) . ")";
}

function showMostActiveUploaders() {
  global $CURUSER;

  $cacheDurationMinutes = 60;
  $cacheDurationInSec = 60 * $cacheDurationMinutes;

  $mostActiveUploaders = fetchAll_memcache(
    'SELECT users.id as id, COUNT(*) as total_torrents, users.username, users.class
    FROM torrents
    LEFT JOIN users ON users.id = torrents.owner
    WHERE owner > 0
    GROUP BY owner
    ORDER BY total_torrents DESC', $cacheDurationInSec);

?>


<h2><?=__('Top utilizatori care au incarcat cele mai multe torrente, <b><a href="/upload.php">incarcă un torrent</a></b>')?></h2>
<table width="100%" border="1" cellspacing="0" cellpadding="10">
  <tr>
    <td id="users_online">
      <?php
      $withLink = isset($CURUSER);
      $firstIteration = true;

      foreach ($mostActiveUploaders as $user) {
        if ($firstIteration) {
          $firstIteration = false;
        } else {
          echo ", ";
        }
        echo linkForUser($user, $withLink, ' (' . $user['total_torrents'] . ')');
      }
      ?>
    </td>
  </tr>
</table>

<?php

}