<?php
  include("include/bittorrent.php");
  loggedinorreturn();
  $messagesperpage = 25;

  stdhead(__('Notificari'));

  echo '<div align=center><h1>Notificări</h1></div>';

  list($pagertop, $pagerbottom, $limit) = pager($messagesperpage, 1000, "");
  $posts = fetchAll("
    SELECT SQL_CALC_FOUND_ROWS id, added, msg, unread
    FROM notifications
    WHERE user_id = :user_id
    ORDER BY id DESC $limit",
    array('user_id' => $CURUSER['id'])
  );

  list($pagertop, $pagerbottom, $limit) = pager($messagesperpage, fetchOne('SELECT FOUND_ROWS()'), "/notifs.php?");

  echo $pagertop;
?>
<div id="messages">
<?php
  $at_least_one_unread = false;
  foreach ($posts as $arr):
    $elapsed = get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]));
    $new = "";
    if ($arr["unread"] == "yes") {
      $at_least_one_unread = true;
      $new = "<b>(<font color=red>Unread!</font>)</b>";
    }
?>
  <table width=100% border=1 cellspacing=0 cellpadding=10 class=pmMessage style="margin: 20px 0 20px 0">
    <tr>
      <td class=text><?=__('la')?> <?=$arr["added"]?> (<?=$elapsed?>) GMT <?=$new?>
        <p>
          <table class=main width=100% border=1 cellspacing=0 cellpadding=10>
            <tr>
              <td class=text>
                <?php print(postProcessingRemoveNewPageLink(format_comment($arr["msg"]))); ?>
              </td>
            </tr>
          </table>
        </p>
      </td>
    </tr>
  </table>
<?php
  endforeach;
?>
</div>
<?php
echo $pagerbottom;
if ($at_least_one_unread) {
  CachedUserData::resetNotifications($CURUSER['id']);
  q("UPDATE notifications SET unread='no' WHERE user_id = :user_id", array('user_id' => $CURUSER['id']));
}
stdfoot();