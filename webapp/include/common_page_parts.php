<?php

function stdheadDisplayCommonBars() {
  global $CURUSER;

  $unread               = $CURUSER['unread'];
  $unread_notifications = $CURUSER['unread_notifications'];

  if (isset($unread) && $unread) {
      print("<table border=0 cellspacing=0 cellpadding=10 bgcolor=\"red\" align=\"center\" id=boxNewMessage><tr><td style='padding: 10px; background: red'>\n");
      print("<a href=/inbox.php>You have $unread new message" . ($unread > 1 ? "s" : "") . "!</a>");
      print("</td></tr></table>\n");
  }

  if (isset($unread_notifications) && $unread_notifications) {
      print("<table border=0 cellspacing=0 cellpadding=10 align=\"center\" id=boxNewMessage style=\"background-color: green\"><tr><td style='padding: 10px; background-color: #7777ce'>\n");
      print("<a href=/notifs.php>$unread_notifications " . __('notificări noi') . " !</a>");
      print("</td></tr></table>\n");
  }

  $last_user_read_global_notification = $CURUSER['last_read_global_notification'];
  $last_global_notification           = fetchOne_memcache_with_key('SELECT MAX(id) FROM global_notifications', getMemcacheGlobalKey('global_notifications_last_id'));

  $need_to_display_global_notif = $last_user_read_global_notification < $last_global_notification;

  if ($need_to_display_global_notif && false) {
?>
      <table border=0 cellspacing=0 cellpadding=10 align="center" id=boxNewMessage>
        <tr>
          <td style='padding: 10px; background-color: #4f8015;' class="wavesBackgroundBackground">
            <a href=/notifs_global.php><?=__('Notificare globală')?> !</a>
          </td>
        </tr>
        </table>
<?php
  }

  $flash_success = get_flash_success();
  if ($flash_success !== false): ?>
    <table border=0 cellspacing=0 cellpadding=10 align="center" id=boxNewMessage>
        <tr>
            <td style='padding: 10px; background-color: #398640; color: white;'>
                <?=nl2br(esc_html($flash_success))?>
            </td>
        </tr>
    </table>
  <?php
  endif;
}

function stdhead($title = "", $noescape = false, $page_id = null, $canonical_url = null) {
    global $CURUSER, $_SERVER, $SITENAME, $STDHEAD_CALL;

    $STDHEAD_CALL = true;

    header('Content-type: text/html; charset=utf-8');
    header('Pragma: No-cache');
    header('X-Frame-Options: SAMEORIGIN');

    if ($title == "")
        $title = $SITENAME;
    else
        $title = (($noescape == false)?esc_html($title):$title) . " :: {$SITENAME}";

    $logo = mem2_get('site_logo');
    if ($logo == false) {
        $logo = fetchOne('SELECT value FROM avps WHERE arg="logo"');
        if ($logo) {
            $logo = explode('|', $logo);
        }
        mem2_set('site_logo',$logo);
    }
    // Structure, 0 logo_ro | 1 logo_ru | 2 width_ro | 3 width_ru | 4 title_ro | 5 title_ru | 6 logo_map_title | 7 logo_map | 8 logo_map_link | 9 logo_map_link_ru
    if (!empty($logo)) {
        if (get_lang() == 'ro') {
            $logo = array($logo[0],$logo[2],$logo[4],$logo[6],$logo[7],$logo[8]);
        } else $logo = array($logo[1],$logo[3],$logo[5],$logo[6],$logo[7],@$logo[9]);
        $logo[0] = "";
    }

    if (strlen($logo[0]) == 0)
        $logo = array( 0 => 'open_library_logo.png', 1 => '280', 2=>'',3=>'',4=>'',5=>'');

    require $GLOBALS['INCLUDE'].'header.php';

    //HttpsSupportDetection::https_detector_iframe();

    show_fulgi();
    if (is_logged()) {show_jucarii(); }

    global $lang;

    //This is for switch guest/logged user menu
    $guest = '';
    if (!is_logged()) {
        $guest = 1;
    }

    echo '<script type="text/javascript">ShowMenu(',  $guest, ');</script>';
    if (!is_logged()) {
        echo '<script type="text/javascript" src="/js/login.js?v=7"></script>'; //login.0.1.js must be called after ShowMenu()
    }


if ($CURUSER && isset($CURUSER['username']) ) {
    //If user is autentificated, execute..
    $uped = mksize($CURUSER['uploaded']);
    $downed = mksize($CURUSER['downloaded']);
    if ($CURUSER["downloaded"] > 0) {
        $ratio = $CURUSER['uploaded'] / $CURUSER['downloaded'];
        if ($CURUSER["class"] < UC_VIP && $CURUSER["donor"] == 'no') {
            $gigs = $CURUSER["uploaded"] / 1073741824; //1024*1024*1024
            $ratio = (($CURUSER["downloaded"] > 0) ? ($CURUSER["uploaded"] / $CURUSER["downloaded"]) : 0);


            if ($ratio < 0.1) $limit = 1;
            elseif ($ratio < 0.2) $limit = 2;
            elseif ($ratio < 0.3) $limit = 3;
            elseif ($ratio < 0.4) $limit = 4;
            elseif ($ratio < 0.6) $limit = 6;
            elseif ($ratio < 0.8) $limit = 8;
            else $limit = 0;

        } else $limit = 0;

        $ratio = number_format($ratio, 3);

        $color = get_ratio_color($ratio);

        if ($color) {
            $ratio = "<font color=$color>$ratio</font>";
        }
    } else {
        if ($CURUSER["uploaded"] > 0) {
            $ratio = "Inf.";
        } else {
            $ratio = "---";
        }
    }

    $user_icons = '';

    $messages = $CURUSER['received'];
    $unread = $CURUSER['unread'];

    if ($unread) $inboxpic = '<img width=14 height=14 alt="inbox" title="inbox (new messages)" src="/pic/icons/post-unread-ico.png" style="vertical-align:middle">';
    else $inboxpic = '<img width=14 height=14 alt="inbox" title="inbox (no new messages)" src="/pic/icons/post-ico.png" style="vertical-align:middle">';

    // Prepare data in one request from the cache (this must save some resources?)
    mem_get_multi_prepare(
      array(
        'user_peers'.$CURUSER['id'],
         'appreciate.'.$CURUSER['id'],
         'user_watch_'.$CURUSER['id'],
         'user_new_torrents_'.$CURUSER['id'],
         User_Icons::uniqueKeyName(),
         getMemcacheGlobalKey('global_notifications_last_id')
      )
    );

    //Counting activeseed&activeleech
    $peers = mem_get_multi_get('user_peers'.$CURUSER['id']);

    if (!$peers) {
        $peers = fetchRow("SELECT SUM(if(seeder='yes',1,0)), SUM(if(seeder='no',1,0)) FROM peers WHERE userid={$CURUSER['id']}");
        mem_set('user_peers'.$CURUSER['id'], serialize($peers), 1800);
    } else {
        $peers = unserialize($peers);
    }


    if (is_null($peers[0])) $activeseed=0; else $activeseed = $peers[0];
    if (is_null($peers[1])) $activeleech=0; else $activeleech = $peers[1];

    //Get watch count
    //id type topicId/torrentId lastSeeMsg/lastSeeComment lastMsg/lastComment


    if (!isset($GLOBALS['user_watch_ignore_cach'])) { // Trick the user, this will be set in watcher.php ;p
        $to_watch = mem_get_multi_get('user_watch_'.$CURUSER['id']);
    } else {
        $to_watch = false;
    }

    if ($to_watch == false) {
        $to_watch = q_singleval('SELECT count(id) FROM watches WHERE user = '.$CURUSER['id'].' AND lastThreadMsg > lastSeenMsg');
        if ($to_watch > 3) {
            mem_set('user_watch_'.$CURUSER['id'],$to_watch,300);
        }
    }


    $to_watch = (($to_watch > 0)?" ($to_watch)":'');


    if (isTorrentModer() || isForumModer()) {
        $queryWhere='';

        if (!isModerator()) {
            $queryWhere = 'AND (';

            if(isTorrentModer()) $queryWhere .= '(type="comment")';

            if(isForumModer()) {
                if(isTorrentModer()) $queryWhere .= 'OR';

                $user_forums_moderator = mem_get('user'.$CURUSER['id'].'_forums_moderator');
                if(!$user_forums_moderator) {
                    $user_forums_moderator = fetchColumn('SELECT forum_category_id FROM `forum_moderators` WHERE `user_id`= '. $CURUSER['id']);
                    $user_forums_moderator = implode(',', $user_forums_moderator);

                    mem_set('user'.$CURUSER['id'].'_forums_moderator', $user_forums_moderator, 3600); //o data pe ora
                }

                $queryWhere .= '( type="forum" AND forumid IN ('. $user_forums_moderator .') )';
            }

            $queryWhere .= ')';
        }

        $total_raports = fetchOne('
            SELECT COUNT(*) FROM (
                SELECT postId,forumid,count(id) AS reports,type
                FROM raportedmsg
                WHERE date > now() - interval 72 hour AND status="waiting" '. $queryWhere .'
                GROUP BY postId
                HAVING reports >= 1) e
            ');

    }

    if (isModerator()) {
        $total_reports_torrents = Torrents_Reports::countNonSolved();
    }

?>

<table id="user_box" style="background-color:#E9F5FC;" border="0" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td colspan="2" style="padding-top:5px; max-width:990px; width: 100%;" align="left">
    <div style="float:left" class="full_with_for_small">
      <?php
        echo '&nbsp;&nbsp;<b><a href="/userdetails.php">',$CURUSER['username'],'</a></b><span class="userIcons">',get_user_icons($CURUSER), '</span>,';
      ?>
    <a href="/my.php"><?=$lang['quick_bar_profile']?></a> |
    <a href="/mytorrents.php"><?=$lang['quick_bar_my_torrents']?></a> |
    <a href="/friends.php"><?=$lang['quick_bar_my_friends']?></a> |
    <a href="/bookmarks.php"><?=$lang['quick_bar_bookmarks']?></a> |
    <a href="/watcher.php"><?=$lang['quick_bar_watcher'],'</a>',$to_watch?>

<?php if (isForumModer() ): ?>
     | <a href="/moder_reports.php"><?=__('Mesaje semn.')?></a> (<?=$total_raports?>)
<?php endif; ?>

<?php if (isModerator() ): ?>
     | <a href="/details_report_admin.php"><?=__('Torrente semn.')?></a> (<?=$total_reports_torrents?>)
<?php endif; ?>

<?php if (isSysop() ):
    $postsToReview = fetchOne('select count(*) from posts_for_review where approved = "todo"');
?>
     | <a href="/forum/forum_mod_approving.php"><?=__('Mesaje de moderat')?></a> (<?=$postsToReview?>)
<?php endif; ?>


    </div>
<?
        if (!$CURUSER['last_browse_see']) $CURUSER['last_browse_see'] = time();

        $new_torrents = mem_get_multi_get('user_new_torrents_'.$CURUSER['id']);

        if ($new_torrents == FALSE) {
            $new_torrents = q_singleval("SELECT count(id) FROM torrents_added WHERE addedUnix > " . $CURUSER['last_browse_see']);
            if ($new_torrents > 15) {
                mem_set('user_new_torrents_'.$CURUSER['id'],$new_torrents,3600);
            }
        }


?>
    <div style="float:right; text-align: right;" class="full_with_for_small">
        <a href="/browse.php?unseen=1"><?=$GLOBALS['lang']['quick_bar_lastest_torrents'],'</a> (',$new_torrents?>)&nbsp;&nbsp;
      <div style="padding:2px 0px 5px 0">
        <a href="/inbox.php" class="no_hover"><?=$inboxpic?></a>
        <a href="/notifs.php" class="no_hover">
            <img src="/pic/icons/notif-ico.png" height="14" width="14" title="Notificări" style="vertical-align:middle">
        </a>
        &nbsp;&nbsp;
      </div>
    </div>
    <div style="clear:both;"></div>
    </td>
  </tr>
</table>
<br/>

<?php

    stdheadDisplayCommonBars();

    global $conf_user_opt;
    if ( !($CURUSER['user_opt'] & $conf_user_opt['have_seen_news']) && basename($_SERVER['SCRIPT_FILENAME']) != 'index.php') {
            print("<p><table border=0 cellspacing=0 cellpadding=10 bgcolor=\"red\" align=\"center\"><tr><td style='padding: 10px; background: #2E8B57'>\n");
            print("<b><a href=/index.php><font color=white>".$GLOBALS['lang']['new_news_label']."</font></a></b>");
            print("</td></tr></table></p>\n");
    }

} //End of if CURUSER
?>
</div> <!--End Of no_td_border-->
<div class="pageContainer" <?php
  if ($page_id) echo formatHtmlSafe('id=":id"', array('id' => $page_id));
?> >

<?php

} // stdhead
