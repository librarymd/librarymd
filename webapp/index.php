<?php
require "include/bittorrent.php";

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
    if ($activeusers) $activeusers .= ",\n";

    switch ($arr["class"]) {
        case UC_SYSOP:
            $arr["username"] = "<span class='sysop'>" . $arr["username"] . "</span>";
            break;
        case UC_ADMINISTRATOR:
            $arr["username"] = "<span class='admin'>" . $arr["username"] . "</span>";
            break;
        case UC_MODERATOR:
            $arr["username"] = "<span class='moder'>" . $arr["username"] . "</span>";
            break;
        case UC_SANITAR:
            $arr["username"] = "<span class='sanitar'>" . $arr["username"] . "</span>";
            break;
        case UC_VIP:
            $arr["username"] = "<span class='vip'>" . $arr["username"] . "</span>";
            break;
        case UC_RELEASER:
            $arr["username"] = "<span class='releaser'>" . $arr["username"] . "</span>";
            break;
        case UC_KNIGHT:
            $arr["username"] = "<span class='faithful'>" . $arr["username"] . "</span>";
            break;
        case UC_UPLOADER:
            $arr["username"] = "<span class='uploader'>" . $arr["username"] . "</span>";
            break;
        case UC_POWER_USER:
                $arr["username"] = "<span class='p_user'>" . $arr["username"] . "</span>";
            break;
        case UC_USER:
    }
    $donator = false;

    //When logged, show links to online user details
    if (isset($CURUSER)) $activeusers .= "<a href=userdetails.php?id=" . $arr["id"] . ">" . $arr["username"] . "</a>";
    else $activeusers .= "$arr[username]";
    $activeusers_i++;
    if (isset($CURUSER) && $CURUSER['id'] == $arr["id"]) $me_in_list = true;
}
// Add myself to the end if not present
if (isset($CURUSER) && !$me_in_list) {
    if ($activeusers) $activeusers .= ",\n";
    $arr["username"] = $CURUSER['username'];
    switch ($CURUSER["class"]) {
        case UC_SYSOP:
            $arr["username"] = "<span class='sysop'>" . $arr["username"] . "</span>";
            break;
        case UC_ADMINISTRATOR:
            $arr["username"] = "<span class='admin'>" . $arr["username"] . "</span>";
            break;
        case UC_MODERATOR:
            $arr["username"] = "<span class='moder'>" . $arr["username"] . "</span>";
            break;
        case UC_SANITAR:
            $arr["username"] = "<span class='sanitar'>" . $arr["username"] . "</span>";
            break;
        case UC_VIP:
            $arr["username"] = "<span class='vip'>" . $arr["username"] . "</span>";
            break;
        case UC_RELEASER:
            $arr["username"] = "<span class='releaser'>" . $arr["username"] . "</span>";
            break;
        case UC_KNIGHT:
            $arr["username"] = "<span class='faithful'>" . $arr["username"] . "</span>";
            break;
        case UC_UPLOADER:
            $arr["username"] = "<span class='uploader'>" . $arr["username"] . "</span>";
            break;
        case UC_POWER_USER:
            $arr["username"] = "<span class='p_user'>" . $arr["username"] . "</span>";
            break;
        case UC_USER:
    }
    $activeusers .= "<a href=userdetails.php?id=" . $CURUSER["id"] . ">" . $arr["username"] . "</a>";
    $activeusers_i++;
}

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

if ($activeusers_total > $mostEver && date('H') > 10 ) {
    Q('UPDATE avps SET value=:now WHERE arg="most_online"', array('now'=>$activeusers_total) );
    Q('UPDATE avps SET value=:time WHERE arg="most_online_date"', array('time'=>time() ) );
    // purge
    mem_delete('stat_most_online');
    // update vars for current user
    $mostEver = $activeusers_total;
    $mostEver_date = time();
}

$mostEverStr = " (" . __('cel mai mulţi').": $mostEver" . __(' la ') . date('d-F-Y G:i',$mostEver_date) . ")";

if (!isset($activeusers)) $activeusers = "There have been no active users in the last 15 minutes.";

stdhead();

$strLang = (get_lang()=='ro')?'ro':'ru';

/* News */

$limit = 'LIMIT 3';
if (isset($_GET['newsShowAll'])) $limit = '';

$column_body_name = (get_lang()=='ro')?'body_ro':'body_ru';

$news = mem_get('news4_'.$limit.get_lang());

if (!$news) {
    $news = fetchAll("SELECT id,userid,added,$column_body_name AS body FROM news ORDER BY added DESC ".$limit);
    mem_set('news4_'.$limit.get_lang(),serialize($news),86400);
} else {
     $news = unserialize($news);
}

if ($limit == '') {
    $news = fetchAll("SELECT id,userid,added,$column_body_name AS body FROM news ORDER BY added DESC ".$limit);
}
/* End news */

?>

<div class="section">
  <div class="group">
    <div class="col full_with_for_small" style="width: 100%;">
      <h2 style="margin:0;">
          <span id="last_news" style="cursor:pointer"><?=$GLOBALS['lang']['index_recent_news']?></span>
          <?php if (!isset($_GET['newsShowAll'])): ?>
            [<a href="./index.php?newsShowAll=1" style="font-size:11px;"><?=$lang['index_news_show_all']?></a>]
          <?php endif; ?>
      </h2>
      <br/>

        <div class="generic_box_default">
        <?php
            $firstLi = true;
            foreach($news AS $array) {
                $firstLiStr = '';
                if ($firstLi) $firstLiStr = ' style="margin-top:4px;" ';
                print('<li class=newslistli '.$firstLiStr.'>' . date("Y-m-d",strtotime($array['added'])) . " - " . $array['body']);
                $firstLi = false;
                    if (get_user_class() >= UC_ADMINISTRATOR)
                    {
                  print(" <font size=\"-2\">[<a class=altlink href=news.php?action=edit&newsid=" . $array['id'] . "&returnto=index.php><b>E</b></a>]</font>");
                  print(" <font size=\"-2\">[<a class=altlink href=news.php?action=delete&newsid=" . $array['id'] . "&returnto=index.php><b>D</b></a>]</font>");
                    }
                print("</li>");
            }
        ?>
        </div>

    </div>
  </div>
</div>

<div style="max-width:990px; width:100%;">
<?php
//Anunt
$anunt = get_config_var('anunt');
if ($anunt || get_user_class() >= UC_MODERATOR) {
    echo '<h2 align=left>',$lang['index_anunt_label'], ' ';
    if (get_user_class() >= UC_MODERATOR) {
        echo '[<a href="./firstpage_annonce.php">Annonce page</a>] ';
        if ($anunt) echo '[<a href=./firstpage_annonce.php?turnannonce=off>Turn Off</a>] (acum e <font color=green>On</font>, se arata tuturor)';
        else echo '[<a href=./firstpage_annonce.php?turnannonce=on>Turn On</a>] (acum e <font color=#CC3200>Off</font>, se arata doar la staff)';
    }
    echo '</h2>';
?>
<table width="100%" align=center border=0>
<tr><td align=center>

<?php
    $column_body_name = (get_lang()=='ro')?'body_ro':'body_ru';
    $annonce = mem_get('annonce_'.$column_body_name);
    if ($annonce == null) {
        $annonce = q_firstrow("SELECT $column_body_name AS body, until FROM annonces ORDER BY added DESC LIMIT 1");
        mem_set('annonce_'.$column_body_name, serialize($annonce), 0);
    } else {
        $annonce = unserialize($annonce);
    }
    //Check if annonce is not expired
    if ($anunt && $annonce['until'] < time()) {
        set_config_var('anunt',0);
        write_admins_log('Pagina de anunt s-a facut oFf deoarece ' . $annonce['until'] . ' < ' . time());
    }
    if ($anunt) echo $annonce['body'];
?>

</td></tr></table><br>

<?php
}

?>
<h2 align="left"><span class="style1"><span class="style7"> Utilizatorii activi din ultimele 24 ore <?php echo ' (',$activeusers_total,') '; ?> </span></span><span style="color:#F5F4EA;"><?=$mostEverStr?></span></h2>
<table width=100% border=1 cellspacing=0 cellpadding=10><tr><td id="users_online">
<?php echo $activeusers ?>, ...
<?php if (!isset($_GET['show_all_online_users'])): ?>
    <br><br>
    <a href="?show_all_online_users=1" style="color:#0A50A1;"><?=__("arată toată lista de"),' ',$activeusers_total,' ',__("utilizatori")?>...</a>
<?php endif; ?>
</td></tr></table>

<?php include('include/index_stats.php'); ?>

<?php include('include/index_top_forum.php'); ?>

<br />
<div align="center">
  <p>
    <a href="./forum.php?action=viewtopic&topicid=1"><?=__('Ultimile modificări pe tracker');?></a>
  </p>
</div>
</div>
<?php
stdfoot();
?>
<noscript>
      <div style="position: absolute; top:150px; left:2px; width:260px; height:auto; background-color:#FFFFCC; border: solid 2px black; z-index: 100; padding: 10px; font-family: Arial, Helvetica, sans-serif; font-size: 12pt; line-height: 18pt;"><center><span style="font-family: Arial, Helvetica, sans-serif; font-size: 18pt; line-height: 22pt; font-weight: bold; color: #AC2D0D"><i>WHOAH!</i></span></center>Vede&#355;i aceasta din cauza c&#259; <u>javascript din browserul vostru este dezactivat</u>. Aceast&#259; pagin&#259; &#351;i &icirc;ntregul site folose&#351;te activ javascript. Situl nu va putea fi vizualizat adecvat far&#259; javascript, noi v&#259; rug&#259;m s&#259;-l activa&#355;i. Ap&#259;sa&#355;i <a href="http://www.tookaa.com/js_turn/on.htm">&nbsp;AICI&nbsp;</a> pentru a g&#259;si cum s&#259; activa&#355;i pe browserul vostru. V&#259; mul&#355;umim.</div>
</noscript>
