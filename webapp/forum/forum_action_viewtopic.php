<?php
function post_format_by($arr) {
  $title      = $arr["title"];
  $posterid   = $arr["userid"];
  $postername = $arr["username"];

  if ($posterid == 0 || $postername == "")
    return "System";

  if (!$title)
    $title = get_user_class_name($arr["class"]);

  $postername = $arr['username'];

  return "<a href=userdetails.php?id=$posterid".
                (($arr["gender"]=='fem')?' style="color:#F93EA0;"':'')
                  ."><b>$postername</b></a>".
               '<span class="userIcons">' . get_user_icons($arr) . "</span> ($title) ";
}

function display_post_link($arr) {
  $post_link = get_post_link($arr);
  return ' - [<a href="'.$post_link.'">' . __('Link') . "</a>]";
}

function get_post_link($arr, $more_query_params = '') {
  $topicid = $arr['topicid'];
  $postid = $arr['id'];

  return "?action=viewtopic&topicid=$topicid&page=p$postid$more_query_params#$postid";
}

function display_post_avatar($arr) {
  list($width, $height, $avatar) = forum_getAvatar($arr);

  if (!$avatar) {
    $avatar = "./pic/forum/default_avatar.gif";
    $width = 150;
    $height = 75;
  }
  return '<img src="'.$avatar.'" width="'.$width.'" height="'.$height.'">';
}

function display_post_format_date($added) {
  $addedUnix = sql_timestamp_to_unix_timestamp($added);
  return $added . ' (' . (get_elapsed_time($addedUnix)) . ')';
}

function display_useful_yes_no($arr) {
  global $CURUSER;
  $topicid = $arr['topicid'];
  $postid = $arr['id'];

        ob_start();
?>
        <br/><br/><br/>
        <div style="text-align: right; position: absolute; bottom: 0px; right: 0; margin: 10px; color: #8e8e8e;">
          <?=getPostLikeUnlikeActionHtml($topicid, $postid, $arr); ?>
        </div>
<?php

  $usefulYesNo = ob_get_contents();
  ob_end_clean();
  return $usefulYesNo;
}

function forum_action_viewtopic($maxsubjectlength, $trashTag) {
  global $CURUSER, $siteVariables, $BASEURL, $lang;

  $user_logged = Users::isLogged();
  $userlang = get_lang();
  $maypost = false;

  $topicid = (int) $_GET["topicid"];

  if (isset($_GET["page"])) $page = $_GET["page"];
  else $page = 1;

  if (!is_valid_id($topicid))
    die;

  $userid = $CURUSER["id"];

  //------ Get topic info

  $topic_data = fetchRow("SELECT * FROM topics WHERE id = :id" ,array('id'=>$topicid))
                or stderr(__('Eroare'), __('Tema nu a fost găsită.'));

  $locked       = ($topic_data["locked"] == 'yes');
  $subject      = esc_html($topic_data["subject"]);
  $subjectRaw   = $topic_data["subject"];
  $sticky       = $topic_data["sticky"] == "yes";
  $modApproval  = $topic_data["mod_approval"] == "yes";
  $forumid      = $topic_data["forumid"];
  $postcount    = $topic_data['posts'];
  $subcat_id    = $topic_data['subcat'];

  forbidIfAnonymous($topic_data["forumid"]);
  //------ Update hits column

  q_delayed("UPDATE topics SET views = views + 1 WHERE id=$topicid");

  //------ Get forum

  $forum_arr = mem_get('forum_forums_'.$userlang.$forumid);

  if (!$forum_arr) {
      $forum_arr = fetchRow("
        SELECT sort, id, name_$userlang AS name, minclassread, minclasswrite, postcount, topiccount, minclasscreate
        FROM forums
        WHERE id=$forumid"
      );
      mem_set('forum_forums_'.$userlang.$forumid,serialize($forum_arr), 3600);
  } else {
      $forum_arr = unserialize($forum_arr);
  }

  $forum = $forum_arr["name"];

  forbidIfNotEnoughRights($forum_arr);

  //------ Get post count
  $postcount = $postcount - 1;

  //------ Make page menu

  $pagemenu = "<p class='center'>\n";

  $perpage = 25;

  $pages = ceil($postcount / $perpage);


  if ($page == 'lastseen') {
      $lastpostread = fetchOne("SELECT lastpostread FROM readposts WHERE topicid=$topicid AND userid=".$CURUSER["id"]);
      if ($lastpostread) {
          $page = "p".$lastpostread;
      }
  }

  if ($page[0] == "p") {
      $findpost = substr($page, 1);
      $page = fetchOne('SELECT page FROM posts WHERE id=:id AND forumid=:forumid',array('id'=>$findpost, 'forumid'=>$forumid) );
  }

  if ($page == "last") $page = $pages;
  else {
      if ($page > $pages) {
          $page = $pages;
      }
  }
  if($page < 1) $page = 1;


  $page = (int) $page;

  $offset = $page * $perpage - $perpage + 1;

  $tooMuchPages = false;
  $putSomePoints = false;
  for ($i = 1; $i <= $pages; ++$i) {
    if ($i == $page)
      $pagemenu .= "<font class=gray><b>$i</b></font>\n";

    else {
        if ($i > 10 && ($pages - 10) > 20 && ($pages - $i) >= 10 && (($i - $page) >= 10 || ($page - $i) >= 10) ) {
            if ($putSomePoints) {
                $pagemenu .= ' ... ';
                $putSomePoints = false;
            }
            if ($tooMuchPages) continue;
            $tooMuchPages = true;
        } else {
          $pagemenu .= "<a href=?action=viewtopic&topicid=$topicid&page=$i><b>$i</b></a>\n";
          $putSomePoints = true;
        }
    }
  }

  if ($page == 1)
    $pagemenu .= "<br/><font class=gray><b>&lt;&lt; " . __('Precedenta') . "</b></font>";
  else
    $pagemenu .= "<br/><a href=?action=viewtopic&topicid=$topicid&page=" . ($page - 1) .
      "><b>&lt;&lt; " . __('Precedenta') . "</b></a>";

  $pagemenu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

  if ($page == $pages || $pages == 0)
    $pagemenu .= "<font class=gray><b>" . __('Următoarea') . " &gt;&gt;</b></font></p>\n";
  else
    $pagemenu .= "<a href=?action=viewtopic&topicid=$topicid&page=" . ($page + 1) .
      "><b>" . __('Următoarea') . " &gt;&gt;</b></a></p>\n";

    //------ Get first post
  $first_post = getFirstPostInTopic($topicid, $forumid);

  if (empty($first_post))
    stderr(__('Eroare'), __('Tema nu a fost găsită.'));

  //------ Get posts

  // Skip first post for first page in the topic
  $forumLimit = '';
  if ($page == 1) {
      $forumLimit = "LIMIT 1,28";
  }

  $checkForSystemAns = $siteVariables['forum']['toStaffID'] == $forumid;

  $res = q('SELECT posts.*,
                    users.username, users.class,   users.avatar, users.avatar_version, users.donor,
                    users.title,    users.enabled, users.warned, users.user_opt,       users.gender,
                    UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(posts.added) AS added_seconds_ago,
                    posts_likes.added AS likeAdded, posts_likes.type as like_sign,
                    posts_for_review.reason AS refusal_reason,
                    users_additional.total_wall_posts
                   '.($checkForSystemAns?',forum_admin_answer.postid as replyAsSystem':'').'
            FROM posts
            LEFT JOIN users ON users.id = posts.userid
            LEFT JOIN users_additional ON users_additional.id = users.id
            LEFT JOIN posts_likes ON posts_likes.postid = posts.id AND posts_likes.userid = :curuserid
            LEFT JOIN posts_for_review ON posts_for_review.post_id = posts.id
            '.($checkForSystemAns?'LEFT JOIN forum_admin_answer ON forum_admin_answer.postid = posts.id':'').'
            WHERE posts.forumid = :forumid AND posts.topicid=:topicid AND page=:page
            ORDER BY id '.
            $forumLimit,
          array('curuserid' => $CURUSER['id'],
                'forumid'   => $forumid,
                'topicid'   => $topicid,
                'page'      => $page));

   if ($subcat_id) $subcat_data = get_subcat($subcat_id);

   $pageTitle = $subjectRaw . ($page > 1 ? " :: Page $page" : "");
   stdhead($pageTitle);

  if ($subcat_id) {
      print("<a name=top></a><h1><a href=\"./forum.php\" style='font-size:13px'>" . __('Forum Index') . "</a> &gt; <a href=?action=viewforum&forumid=$forumid style='font-size:13px'>$forum</a> &gt; <a style='font-size:13px' href=?action=viewforum&forumid=$forumid&subcat=$subcat_data[id]>".esc_html($subcat_data['name'])."</a> &gt; $subject</h1>\n");
  } else {
      print("<a name=top></a><h1><a href=\"./forum.php\"  style='font-size:13px'>" . __('Forum Index') . "</a> &gt; <a href=?action=viewforum&forumid=$forumid style='font-size:13px'>$forum</a> &gt; $subject</h1>\n");
  }

  //------ Print table

  begin_main_frame();

  begin_frame('',false,1,'forumPosts_first');

  echo '<div style="margin: 0pt 11px 11px; max-width: 966px; width: 100%;">';
  $resultNumRows = mysql_num_rows($res);

  $postNumber = 0;

  $lastPostReadResult = q("SELECT lastpostread FROM readposts WHERE userid=" . $CURUSER["id"] . " AND topicid=$topicid");
  $lastPostReadResultRow = mysql_fetch_row($lastPostReadResult);

  $lastPostRead = $lastPostReadResultRow[0];

  if (mysql_num_rows($lastPostReadResult) == 0 && $userid != 0)
    q("INSERT INTO readposts (userid, topicid) VALUES($userid, $topicid)");

  $msg_order_id = $offset; // Pentru numerotarea mesajelor
  $messagesAwaitingReview = 0;
  while (1) {
    // Inject first post on top
    if (isset($first_post)) {
      $arr = $first_post;
      unset($first_post); //cleaning
      $is_first_post = true;
    } else {
      if ($is_first_post) $is_first_post = false; //Flag, mean we list now the posts
      if ( !($arr = mysql_fetch_assoc($res)) ) break;
    }

    /** All post related vars */

    $postid         = $arr["id"];
    $posterid       = $arr["userid"];
    $added          = display_post_format_date($arr["added"]);
    $postername     = $arr["username"];
    $censored       = $arr['censored'] == 'y';
    $revealCensored = @$_GET['reveal_censored'] == $postid;
    $replyAsSystem  = isset($arr['replyAsSystem']) && $arr['replyAsSystem'] !== null;
    $awaitsReview   = $arr['mod_approved'] == 'awaiting';
    $messageNotApproved = $arr['mod_approved'] == 'no';
    $refusalReason  = @$arr['refusal_reason'];
    $postLink       = get_post_link($arr);

    if ($awaitsReview || $messageNotApproved) {
      $messagesAwaitingReview++;
      $shouldNotDisplay = !isAdmin() && $posterid != $CURUSER['id'];
      if ($shouldNotDisplay) {
        $msg_order_id++;
        continue;
      }
    }

    if ($arr['image'] != '') {
      $upl_img = '<a href="'.$forum_img_dir_www.'/'.$arr['id'].'_'.$arr['image'].'" target="_blank"><img src="./pic/upl_pic.gif" border="0"></a>';
    } else unset($upl_img);

    print("<a name=$postid></a>\n");

    if ($postNumber == $resultNumRows) {
      print("<a name=last></a>\n");
    }

    if ($is_first_post) { //The first post always have order id 0.. This is smth. like multi page workarround
        $msg_order_id_temp = $msg_order_id;
        $msg_order_id = 0;
    }

    echo '<table border=0 cellspacing=0 cellpadding=0 width="100%" class="forumPostName"><tr><td class=embedded width=99%>
             <span class="lnk" onmousedown="citeaza(this);">#',$msg_order_id,'</span>',' by ',post_format_by($arr)," at $added";

    if ($is_first_post) {
        $msg_order_id = $msg_order_id_temp;
        unset($msg_order_id_temp);
    }

    $isMyPost = $CURUSER["id"] == $posterid;

    if (!$isMyPost && !$censored && Users::isLogged()) {
        echo " - [<a href=?action=quotepost&topicid=$topicid&postid=$postid>" . __('Citează') . "</a>]";
    }

    $wasMessageEditedBySomebodyElse = $arr['editedby'] > 0 && $arr['editedby'] != $CURUSER['id'];
    $myPostIsFirstInTopic = $is_first_post && $isMyPost && !$locked;

    $addedLessThan1Day = $arr['added_seconds_ago'] < 86400;
    $canEditCondAnd = $isMyPost && !$locked && !$censored &&
                      !$wasMessageEditedBySomebodyElse && $addedLessThan1Day;
    $canEditCondOr  = get_user_class() >= UC_MODERATOR || $myPostIsFirstInTopic;

    $canEditAllCond = $canEditCondAnd || $canEditCondOr;

    if ($canEditAllCond) {
      echo ' - [<a class="post_edit" href="?action=editpost&postid='.$postid.'">'. __('Editează') .'</a>]';
    }

    if (get_user_class() >= UC_MODERATOR) {
        print(" - [<a class=post_delete href=?action=deletepost&postid=$postid>" . __('Şterge') . "</a>]");
    }

    if (allow_censoring($topic_data)) {
        if (!$censored) {
            echo " - [<span class='lnk censlnk' customId='$postid'>" . __('Cenzurează') . "</span>]";
        } else {
            echo " - [<a href=?action=uncensore&postid=$postid&topicid=$topicid>" . __('Decenzurează') . "</a>]";
        }
    }

    if ($arr['editedby'] && get_user_class() >= UC_MODERATOR) {
         echo " - [<a href=forum.php?action=vieworiginal&amp;cid=$arr[id]>" . __('Post original') . "</a>]";
    }

    if ($postername != '' && !$isMyPost && Users::isLogged()) echo " - [<a href=sendmessage.php?receiver=$posterid>PM</a>]";

    if (Users::isLogged() && $arr['class'] < UC_MODERATOR &&  !$censored && !$isMyPost && $CURUSER['class'] < UC_MODERATOR ) {
      echo ' - [<span class="lnk raportareLnk" customforumid="'.$arr['forumid'].'" customId="'.$arr['id'].'">'.__('Raportează').'</span>]';
    }

    echo display_post_link($arr);

    print('</td><td class=embedded width="1%"><a href=#top><img src="./pic/forum/top.gif" border=0 alt="Top"></a></td></tr>');

    print("</table>\n");

    echo '<table class=main width=100% border=1 cellspacing=0 cellpadding=5>';

    $body = format_comment($arr["body"],true,true);

    if (is_valid_id($arr['editedby'])) {
      $res2 = q("SELECT username FROM users WHERE id=$arr[editedby]");
      if (mysql_num_rows($res2) == 1) {
        $arr2 = mysql_fetch_assoc($res2);
        $body .= "<p><font size=1 class=small>" . __('Editat de către ') . "<a href=userdetails.php?id=$arr[editedby]><b>$arr2[username]</b></a> " . __('la') . " $arr[editedat] </font></p>\n";
      }
    }

    $userCanCensore = allow_censoring($topic_data);

    $showUserAvatar = true;
    $showCustomMessage = false;
    $customMessage = '';
    $customTrHtml  = '';
    $alignBodyToCenter = false;
    $additionalBodyMessage = false;

    $censoredBackgroundStyle = 'style="background-color:#FAEBE2"';

    if ($censored) {
      if ($userCanCensore) {
        $customTrHtml = $censoredBackgroundStyle;
      } else {
        $showUserAvatar = false;

        if ($revealCensored) {
          $customTrHtml = $censoredBackgroundStyle;
          $additionalBodyMessage = __('Acest mesaj încalcă eticheta de comunicare și a fost ascuns. Te rog să nu răspunzi la el, discuțiile trebuie să fie strict la subiect.');
        } else {
          $showUserAvatar = false;
          $showCustomMessage = true;
          $customMessage = 'Acest mesaj nu respecta eticheta de comunicare și a fost ascuns. <a href="'.get_post_link($arr,'&reveal_censored=' . $postid).'" class="show_reveal_warning" rel="nofollow">Click</a> dacă oricum dorești să-l vezi.';
          $alignBodyToCenter = true;
        }
      }
    } else if ($awaitsReview) {
      $customTrHtml = 'style="background-color:#e6ecb3"';
      $additionalBodyMessage = __('În așteptarea moderării.');
    } else if ($messageNotApproved) {
      $customTrHtml = 'style="background-color:#f9d0c7"';
      $additionalBodyMessage = __('Mesajul n-a fost acceptat.') . '<br/>' . format_comment($refusalReason);
    }

    $customTrHtmlAll  = ( $replyAsSystem?'class="replyAsSystem"':'') . $customTrHtml;
    $postImageHtml    = (isset($upl_img)?'<br/><br/>'.$upl_img.'<br/><br/>':'');
    $userAvatarHtml   = display_post_avatar($arr);
    $bodyHtml         = $showCustomMessage ? $customMessage : $body;
    $alignBodyToCenterHtml = $alignBodyToCenter ? 'align="center"' : '';

    if ($additionalBodyMessage) {
      $bodyHtml .= '<br/><br/><div style="text-align: right; font-style: italic; font-weight: bold;">(' . $additionalBodyMessage . ')</div>';
    }

    $usefulYesNo = !$censored ? display_useful_yes_no($arr) : '';

    echo'<tr valign="top" '.$customTrHtmlAll.'>
          <td width=150 align=center style="padding: 0px">'.
            ($showUserAvatar ? $userAvatarHtml : '') . $postImageHtml .
          '</td>
           <td class="comment" ' . $alignBodyToCenterHtml . ' style="position: relative;">'.$bodyHtml.'
           '.$usefulYesNo.'
           </td>
        </tr>';

    end_table();

    if ($is_first_post) {
        echo '</div>';
        end_frame();

        if ($topic_data['posts'] > 3) {
          display_top_message($forumid, $topicid);
        }


        echo '<center>',$pagemenu,'</center>';
        echo '<table width=100% border=1 cellspacing=0 cellpadding=0><tr><td id="forumPosts">';
        echo '<div style="margin: 0pt 11px 11px; max-width: 966px; width: 100%;">';
        //Now the normal order
    } else {
        $msg_order_id++;
    }
    $postNumber++;
  }

  if ($postid > $lastPostRead) {
    q_delayed("UPDATE readposts SET lastpostread=$postid WHERE userid=$userid AND topicid=$topicid");

    // Update watch index
    q("UPDATE watches SET lastSeenMsg=$postid WHERE user=$userid AND thread=$topicid AND type='topic'");

    mem_delete('user_watch_'.$CURUSER['id']);
  }


  //------ Mod options

    if (get_user_class() >= UC_MODERATOR || $topic_data['userid'] == $CURUSER['id']) {
        ?>
<br/><br/>
  <?php if ($user_logged): ?>
<form method="POST" action="forum.php">
<input type="hidden" name="topicid" value="<?=$topicid?>">
<input type="hidden" name="action" value="changeSubcat">
<table border=0 cellspacing=0 cellpadding=10>
  <tr>
      <td width="100"><?=__('Subcategorie')?>:</td>
      <td>
<select name="subcategory" style="display:inline;">
  <option value="0"><?=__('-- nici una')?></option>
  <?php foreach( getSubcategories($forumid,$userlang) AS $subcat_item ) : ?>
      <option value="<?=$subcat_item['id']?>" <?=($subcat_item['id'] == $topic_data['subcat'])?'SELECTED':''?> ><?=esc_html($subcat_item['name'])?></option>
  <?php endforeach; ?>
</select>
          <input type="submit" value="<?=__('Schimbă')?>">

      </td>
  </tr>
</table>
</form>
      <?php endif; /**  if $user_logged **/ ?>
<br/>
        <?php
    }

  function getAllForums($userlang) {
    $forums_id_name_key = 'forums_id_name'.$userlang;

    $all_forums = mem_get($forums_id_name_key);
    if ($all_forums == false) {
        $all_forums = fetchAll("SELECT id,name_$userlang AS name,minclasswrite FROM forums ORDER BY sort,id");
        mem_set($forums_id_name_key,$all_forums,43200);
    }
    return $all_forums;
  }

  if (allow_censoring($topic_data)) print("<table border=0 cellspacing=0 cellpadding=0>\n");

    if (get_user_class() >= UC_MODERATOR) {
      print("<form method=post action=?action=setsticky>\n");
      print("<input type=hidden name=topicid value=$topicid>\n");
      print("<input type=hidden name=returnto value=$BASEURL$_SERVER[REQUEST_URI]>\n");
      print("<tr><td class=embedded align=right>". __('Important: ') ."</td>\n");
      print("<td class=embedded><input type=radio name=sticky value='yes' " . ($sticky ? " checked" : "") . "> ". __('Da') ." <input type=radio name=sticky value='no' " . (!$sticky ? " checked" : "") . "> ". __('Nu') ."\n");
      print("<input type=submit value='". __('Ok') ."'></td></tr>");
      print("</form>\n");
    }

   if (allow_censoring($topic_data)) {
      print("<form method=post action=?action=setlocked>\n");
      print("<input type=hidden name=topicid value=$topicid>\n");
      print("<input type=hidden name=returnto value=$BASEURL$_SERVER[REQUEST_URI]>\n");
      print("<tr><td class=embedded align=right>". __('Închis:') ."</td>\n");
      print("<td class=embedded><input type=radio name=locked value='yes' " . ($locked ? " checked" : "") . "> ". __('Da') ." <input type=radio name=locked value='no' " . (!$locked ? " checked" : "") . "> ". __('Nu') ."\n");
      print("<input type=submit value='". __('Ok') ."'></td></tr>");
      print("</form>\n");

      if (get_user_class() >= UC_MODERATOR) {
        print("<form method=post action=?action=setmodapproval>\n");
        print("<input type=hidden name=topicid value=$topicid>\n");
        print("<input type=hidden name=returnto value=$BASEURL$_SERVER[REQUEST_URI]>\n");
        print("<tr><td class=embedded align=right>". __('Aprobarea individuala a mesajelor: ') ."</td>\n");
        print("<td class=embedded><input type=radio name=modapproval value='yes' " . ($modApproval ? " checked" : "") . "> ". __('Da') ." <input type=radio name=modapproval value='no' " . (!$modApproval ? " checked" : "") . "> ". __('Nu') ."\n");
        print("<input type=submit value='". __('Ok') ."'></td></tr>");
        print("</form>\n");
      }

      print("<form method=post action=?action=renametopic>\n");
      print("<input type=hidden name=topicid value=$topicid>\n");
      print("<input type=hidden name=returnto value=$BASEURL$_SERVER[REQUEST_URI]>\n");
      print("<tr><td class=embedded align=right>". __('Redenumeşte tema:') ."</td><td class=embedded><input type=text name=subject class=textarea_mobile_long maxlength=$maxsubjectlength value=\"" . $subject . "\">\n");
      print("<input type=submit value='". __('Ok') ."'></td></tr>");
      print("</form>\n");



      if (@$trashTag[$forumid]) {
          printf('<form method="POST" action="?action=totrash"><input type="hidden" name="topicid" value="%s">', $topicid);
          printf('<tr><td class="embedded" align="right">%s</td>', __('Aruncă la gunoi: ') );
          printf('<td class="embedded"><input type="submit" value="%s"></td></tr></form>', __('Ok'));
      }
   }
   if (get_user_class() >= UC_MODERATOR) {

      $all_forums = getAllForums($userlang);

      print("<form method=post action=?action=movetopic&topicid=$topicid>\n");
      print("<tr><td class=embedded>". __('Mută tema în:') ."&nbsp;</td><td class=embedded><select name=forumid>");

      foreach($all_forums AS $arr) {
        if ($arr["id"] != $forumid && get_user_class() >= $arr["minclasswrite"])
          print("<option value=" . $arr["id"] . ">" . $arr["name"] . "\n");
      }

      print("</select> <input type=submit value='". __('Ok') ."'></form></td></tr>\n");
      print("<tr><td class=embedded>". __('Şterge tema:') ."</td><td class=embedded>\n");
      print("<form method=post action=./forum.php>\n");
      print("<input type=hidden name=action value=deletetopic>\n");
      print("<input type=hidden name=topicid value=$topicid>\n");
      print("<input type=hidden name=forumid value=$forumid>\n");
      print("<input type=checkbox name=sure value=1>". __('sunt sigur') ."\n");
      print("<input type=submit value='". __('Ok') ."'>\n");
      print("</form>\n");
      print("</td></tr>\n");
    }

  if (allow_censoring($topic_data)) print("</table>\n");
  echo '</div>';
  end_frame();

  end_main_frame();



  /*
      3in3 sec section
  */

  echo '<script>var lang_raport_ok="'.__('Raportat, mulţumim').'";
              var langOameni="',__('oameni'),'";
  </script>';

      if ($user_logged):


  // If this is the last topic page AND topic is not locked
  if (($page == $pages || $pages == 0) && !$locked && !$modApproval) {
      echo '<br/><br/>';
      echo '<script type="text/javascript">
          var langPostsChecking="',$lang['check_each_3_sec_checking'],'";
          var topicId=',$topicid,';var lastMsg=',$postid,';
          </script>';
      echo '<table class="mCenter" border=1 cellspacing=5 cellpadding=5 onclick="TurnAutoCheckTimer();" style="cursor:pointer;"><tr><td id="activateTimerText">',$lang['check_each_3_sec'],'</td></tr></table>';
  }

  /*
      Watcher section
  */

  // Check if this topic is in watch list
  $watchId = q_singleval("SELECT id FROM watches WHERE user=$userid AND thread=$topicid AND type='topic'");
  $watchOn = ($watchId > 0)?true:false;
  echo '<br/><br/>';
  echo '<script type="text/javascript">
      var langWatchOn="',$lang['watch_on'],'";
      var langWatchOff="',$lang['watch_off'],'";
      var watchStatut=',(($watchOn)?'true':'false'),';
      var topicId=',$topicid,';
      </script>';
  echo '<table class="mCenter" border=1 cellspacing=5 cellpadding=5 onclick="Watcher();" style="cursor:pointer;"><tr><td id="watcherText"',(($watchOn)?' bgcolor="#D3F1E2"':''),'>', (($watchOn)?$lang['watch_off']:$lang['watch_on']) ,'</td></tr></table>';

      endif; /** if $user_logged **/


  print($pagemenu);

  if ($subcat_id) {
      print("<a name=top></a><h1><a href=\"./forum.php\" style='font-size:13px'>" . __('Forum Index') . "</a> &gt; <a href=?action=viewforum&forumid=$forumid style='font-size:13px'>$forum</a> &gt; <a style='font-size:13px' href=?action=viewforum&forumid=$forumid&subcat=$subcat_data[id]>".esc_html($subcat_data['name'])."</a> &gt; $subject</h1>\n");
  } else {
      print("<a name=top></a><h1><a href=\"./forum.php\" style='font-size:13px'>" . __('Forum Index') . "</a> &gt; <a href=?action=viewforum&forumid=$forumid style='font-size:13px'>$forum</a> &gt; $subject</h1>\n");
  }

  if ($locked && get_user_class() < UC_MODERATOR)
      print("<p>". __('Această temă este închisă. Nu puteţi posta mesaje noi.') ."</p>\n");

  else
  {
      $arr = get_forum_access_levels($forumid) or die;

      if (get_user_class() < $arr["write"])
        print("<p><i>You are not permitted to post in this forum.</i></p>\n");

      else
        $maypost = true;
    }

    //------ "View unread" / "Add reply" buttons


echo '<script type="text/javascript" src="./js/forum.js?v=17"></script>';
?>
<br/>

<?php
if ($maypost) {
    if ($user_logged): ?>

<?php if ($modApproval): ?>
  <div class="generic_box_default">
    <h2>Acest topic este setat cu aprobarea individuală a mesajelor.</h2>
    Mesajul nou nu va fi vizibil imediat în topic ci doar după aprobarea lui individuală.<br/>
    <br/>
    Mesajul nu va fi acceptat dacă:

    <ul>
      <li>Nu este în strictă concordanță cu regulile în vigoare.</li>
      <li>Exprimă orice formă de insultă sau agresivitate.</li>
      <li>Nu este la subiect sau nu contribuie cu nimic constructiv la discuție.</li>
    </ul>

    Dorim un spațiu constructiv și pozitiv de discuții unde ne ajutăm reciproc într-un mod respectuos. Contăm pe suportul dumneavoastra. Vă mulțumim.
  </div>
  <br/><br/>
<?php endif; ?>

<div class="generic_box_default">
Scriind un mesaj, te rog să te asiguri că corespunde <b><a href="/forum.php?action=viewtopic&topicid=88154400" target="_blank">etichetei de comunicare</a></b>.
<br/>
<ul>
<li><b>Scrie la subiectul discuției definit in #0.</b> Mesajul tău ajută cu ceva alți membri ai comunității ?</li>
<li><b>La o intrebare serioasă se așteaptă un răspuns serios.</b> O întrebare simplă pentru tine, poate fi complicată pentru altcineva. Dacă crezi că ai răspunsul la întrebarea pusă, te rugăm să răspunzi într-un mod cît mai respectuos. Ajutandu-ne reciproc avem de câștigat cu toții.</li>
<li><b>Te rugăm să aderi la aceleași standarte de comportare in spațiul online la care aderi și în viața reala.</b></li>
<li><b>Ține minte că vorbești cu alți oameni.</b> Când comunici online, totul ce vezi este un ecran de computer. Scriind, poți să te întrebi "I-aș fi spus acestei persoane în față același lucru ?" sau "S-ar fi supărat un prieten dacă i-aș fi răspuns așa ?".<br/>
</li>
<li><b>Nu practica trollingul</b>. Un troll este un provocator fără cauză, cineva care lansează o discuție cu scopul de a recrea un conflict de idei. Uneori, conflictul devine amuzant pentru troll, dar aproape niciodată pentru participanții inocenți.
</li>

<li><b>Mulțumește persoana care te ajută</b>. Când pui o întrebare și cineva investește timp să-ți răspundă printr-un mesaj gândit și desfășurat, nu uita să mulțumești. Nimeni nu este obligat să-ți răspundă la întrebarea pusă. Dar mulțumind, arăți că timpul său a fost apreciat și recunoscut. Altfel, la următorul mesajul posibil nimeni să nu-ți răspundă. Marcheză mesajul său ca fiind util prin butoanele din colțul drept jos al mesajului.
</li>

</ul>


<a href="/forum.php?action=viewtopic&topicid=88154400" target="_blank">Intreg text al etichetei de comunicare pe forum.</a>
</div>

<div style="width:740;text-align:center"><h1><? echo $GLOBALS['lang']['forum_reply'];?></h1></div>
<div style="width:740;text-align:left">
<form method="post" action="forum.php" name="topic_msg" enctype="multipart/form-data" onsubmit="return submitPostAjax();">
<input type="hidden" name="action" value="post">
<input type="hidden" name="topicid" value="<? echo $topicid;?>">
<?=$GLOBALS['lang']['forum_reply_message'];?>:<br/>

<textarea name="body" id="posttext" style="width:100%; height: 300px;"></textarea>

<p align=right><a href=tags.php target=_blank><?=$GLOBALS['lang']['forum_tags']?></a> | <a class="pointer" onclick="PopSmilies('posttext','<?php echo get_lang();?>');"><?=$GLOBALS['lang']['forum_smiles']?></a>
</p>
</div>
<br/>
<div id="after_comment_box" class=""></div>

<div style="width:740;text-align:left">

<?php if ($forumid == $siteVariables['forum']['toStaffID']) :?>
    <label><input type="checkbox" name="replyAsSystem" value="yes" /> <b><?=__('Răspunde userului ca system (mesaj trimis în PM)')?></b></label><br />
<?php endif; ?>

  <input type="submit" value="<?=$modApproval ? $GLOBALS['lang']['forum_reply_send_approval'] : $GLOBALS['lang']['forum_reply_send'];?>"> (<b>asigură-te că respecți eticheta de comunicare</b>)
</form>

</div>
<?php
endif; /** if $user_logged **/

}


    //------ Forum quick jump drop-down

    insert_quick_jump_menu($forumid);


    if (isset($_GET['goto']) && is_numeric($_GET['goto'])) {
        echo "\n",'<script type="text/javascript">location.href=\'#',$_GET['goto'],'\';</script>',"\n";
    }

    stdfoot();

    die;
}

function display_topic_message($arr) {
  $postBy         = post_format_by($arr);
  $added          = display_post_format_date($arr["added"]);
  $usefulYesNo    = display_useful_yes_no($arr);
  $bodyHtml       = format_comment($arr["body"],true,true);
  $userAvatarHtml = display_post_avatar($arr);

  begin_main_frame();
  begin_frame('',false,1,'forumPosts_top');
?>

<div style="margin: 0pt 11px 11px; max-width: 966px; width: 100%;">
<table border="0" cellspacing="0" cellpadding="0" width="100%" class="forumPostName"><tbody><tr><td class="embedded" width="99%">
             By <?=$postBy?> at <?=$added?> <?=display_post_link($arr)?></td></tr></tbody></table>

  <table class="main" width="100%" border="1" cellspacing="0" cellpadding="5"><tbody><tr valign="top">
          <td width="150" align="center" style="padding: 0px"><?=$userAvatarHtml?></td>
           <td class="comment" style="position: relative;">
            <?=$bodyHtml?>
          <br><br>
          <?=$usefulYesNo?>
           </td>
        </tr></tbody></table>
</div>

<?php
  end_frame();
  end_main_frame();
  echo "<br/>";
}

function display_top_message($forumid, $topicid) {
  global $CURUSER;

  $queryTemplate = 'SELECT posts.*,
                    users.username, users.class,   users.avatar, users.avatar_version, users.donor,
                    users.title,    users.enabled, users.warned, users.user_opt,       users.gender,
                    UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(posts.added) AS added_seconds_ago,
                    posts_likes.added AS likeAdded, posts_likes.type as like_sign,
                    users_additional.total_wall_posts
            FROM posts
            LEFT JOIN users ON users.id = posts.userid
            LEFT JOIN users_additional ON users_additional.id = users.id
            LEFT JOIN topics ON topics.id = posts.topicid
            LEFT JOIN posts_likes ON posts_likes.postid = posts.id AND posts_likes.userid = :curuserid
            WHERE posts.forumid = :forumid AND posts.topicid=:topicid
                  AND (censored = "n" OR censored IS NULL)
                  AND posts.added > (NOW() - INTERVAL :days day)
                  AND likes > 0 AND topics.created !=  UNIX_TIMESTAMP(posts.added)
            ORDER BY (likes - unlikes) DESC LIMIT 1';

  $queryParams = array(
                'curuserid' => $CURUSER['id'],
                'forumid'   => $forumid,
                'topicid'   => $topicid);

  $queryParams365Days = $queryParams;
  $queryParams7Days   = $queryParams;

  $queryParams7Days['days']   = 7;
  $queryParams365Days['days'] = 365;

  $arr365Days = fetchRow_memcache(sqlEscapeBind($queryTemplate, $queryParams365Days), 1200);
  $arr7Days   = fetchRow_memcache(sqlEscapeBind($queryTemplate, $queryParams7Days  ), 1200);

  if (empty($arr365Days)) {
    return;
  }
?>
<center><p><b><?=__('Top mesaje')?></b></p></center>
<?php
  if ($arr365Days['id'] != $arr7Days['id'] && !empty($arr7Days)) {
    display_topic_message($arr7Days);
  }
  display_topic_message($arr365Days);
}

