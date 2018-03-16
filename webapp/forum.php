<?php

require "./include/bittorrent.php";
require $WWW_ROOT . "./forum_inc.php";
require_once($INCLUDE . 'classes/users.php');
require_once($INCLUDE . 'classes/forum.php');
require_once($INCLUDE . 'classes/watches.php');

require_once($WWW_ROOT . 'forum/forum_globals.php');
require_once($WWW_ROOT . 'forum/forum_action_viewtopic.php');
require_once($WWW_ROOT . 'forum/forum_action_newpost.php');

//Becnhmark
$time_start = microtime_float();

$user_logged = @$CURUSER?true:false;

$userlang = get_lang();
$action = (isset($_REQUEST['action']))?$_REQUEST['action']:'';
$action = esc_html($action);

$clean = array();
$sql = array();
$forum_img_dir = $WWW_ROOT . 'forum_img';
$forum_img_dir_www = './forum_img';

/** Check the action **/
if (!$user_logged) {
  $CURUSER = array('id' => 0,'class' => 0,'avatars' => 'yes');
    // Deny post requests
  if (isPost() || isset($_GET["catchup"]) ) {
    header("Location: ./forum.php");
    exit();
  }

  if (strlen($action)) {
    $allowed_action_anonym = array('viewforum', 'viewtopic', 'search');

    if (!in_array($action, $allowed_action_anonym) ) {
      header("Location: ./forum.php");
      exit();
    }
  }
}

/**
 * Add like, remove like. If there is unlike, delete unlike first.
 * Add unlike, remove unlike. If there is a like, delete the like first.
 */

$like_action   = $action == 'like';
$unlike_action = $action == 'unlike';


if ( ($like_action || $unlike_action) && isset($_POST['ajax']) ) {
  loggedinorreturn();

  $postid     = (int) $_POST['postid'];
  $topicid    = (int) $_POST['topicid'];

  $postAuthorUserId =
    fetchOne('SELECT userid FROM posts WHERE id = :postid AND topicid = :topicid',
      array('postid' => $postid, 'topicid' => $topicid)
    ) OR stderror('Mesajul nu exista');

  $userTriesToLikeHimSelf = $postAuthorUserId == $CURUSER['id'];
  if ($userTriesToLikeHimSelf) {
    stderror('You cannot like your own messages.');
  }

  processForumLikeAction($like_action, $postid, $topicid);

  Watches::startForTopic($CURUSER['id'], $topicid);

  $result = fetchRow("SELECT
    posts.*, likes, unlikes, posts_likes.type as like_sign
    FROM posts
    LEFT JOIN posts_likes ON posts_likes.postid = posts.id AND posts_likes.userid = :curuserid
    WHERE id=:postid", array('postid' => $postid, 'curuserid' => $CURUSER['id']));

  $html = getPostLikeUnlikeActionHtml($topicid, $postid, $result);

  echoJson(
    array("likes"=>$result['likes'], 'unlikes' =>$result['unlikes'], 'html' => $html)
  );
}

  //-------- Action: New topic

  if ($action == "newtopic") {
    $forumid = (int) $_GET["forumid"];

    if (!is_valid_id($forumid))
      die;

    stdhead(__('Temă Nouă'));

    begin_main_frame();

    insert_compose_frame($forumid);

    end_main_frame();

    stdfoot();

    die;
  }

  //-------- Action: Post
  if ($action == "post") {
    forum_action_post($maxsubjectlength);
  }

  //-------- Action: View topic

  if ($action == "viewtopic") {
    forum_action_viewtopic($maxsubjectlength, $trashTag);
  }

  //-------- Action: Quote

    if ($action == "quotepost")
    {
        $topicid = (int) $_GET["topicid"];

        if (!is_valid_id($topicid))
            stderr(__('Eroare'), "Invalid topic ID $topicid.");

    stdhead(__('Răspuns'));

    begin_main_frame();

    insert_compose_frame($topicid, false, true);

    end_main_frame();

    stdfoot();

    die;
  }

  //-------- Action: Reply

  if ($action == "reply")
  {
    $topicid = (int) $_GET["topicid"];

    if (!is_valid_id($topicid))
      die;

    stdhead(__('Răspuns'));

    begin_main_frame();

    insert_compose_frame($topicid, false);

    end_main_frame();

    stdfoot();

    die;
  }

  //-------- Action: Move topic

  if ($action == "movetopic")
  {
    $forumid = (int) $_POST["forumid"];

    $topicid = (int) $_REQUEST["topicid"];

    if (!is_valid_id($forumid) || !is_valid_id($topicid) || get_user_class() < UC_MODERATOR)
      die;

    // Make sure topic and forum is valid

    $res = q("SELECT minclasswrite FROM forums WHERE id=$forumid");

    if (mysql_num_rows($res) != 1)
      stderr(__('Eroare'), "Forum not found.");

    $arr = mysql_fetch_row($res);

    if (get_user_class() < $arr[0])
      die;

    $res = q("SELECT subject,forumid FROM topics WHERE id=$topicid");

    if (mysql_num_rows($res) != 1)
      stderr(__('Eroare'), __('Tema nu a fost găsită'));

    $arr = mysql_fetch_assoc($res);

    if ($arr["forumid"] != $forumid) {
        write_moders_log("Topicul $topicid a fost mutat de $CURUSER[id]");
    $subcat=q_singleval("SELECT subcat FROM topics WHERE id=$topicid");
    if ($subcat!=0) q("UPDATE forums_tags SET total=total-1 WHERE id=$subcat");
    q("UPDATE topics SET forumid=$forumid, subcat=0 WHERE id=$topicid");

        // Here is some mysql partition bug.. Need to this multiple times
        q("UPDATE posts SET forumid=$forumid WHERE topicid=$topicid AND forumid=".$arr["forumid"]);

        // Force clean cache
    subcat_number_changed($forumid);
        update_forum_last_post($forumid);
        update_forum_last_post($arr["forumid"]);
    }
    update_forums_posts_count();
    // Redirect to forum page

    header("Location: ./forum.php?action=viewforum&forumid=$forumid");

    die;
  }



  //-------- Action: Move topic to trash

    if ($action == "totrash")
    {
        $topicid = (int) $_REQUEST["topicid"];

        if ( !is_valid_id($topicid) || !allow_censoring(array(), $topicid) )
            die;

        $topicrow = fetchRow( "SELECT * FROM topics WHERE id=:id", array('id' => $topicid) );

        if ( !$trashTag[$topicrow['forumid']] ) die; //nu avem subcategorie in trash pentru aceasta categorie

        q( "UPDATE topics SET forumid=:forumid, subcat=:subcat WHERE id=:topicid",
            array('forumid'=>33, 'subcat'=>$trashTag[$topicrow['forumid']], 'topicid'=>$topicid) );

        updateSubcatCount($trashTag[$topicrow['forumid']], 33); // Actualizam numarul de teme din subcategoria data din trash
        updateSubcatCount($topicrow['subcat'], $topicrow['forumid']); // Actualizam numarul de teme din vechea subcategorie

        q( "UPDATE posts SET forumid=33 WHERE topicid=:topicid", array('topicid'=>$topicid) );

        update_topic_last_post( fetchOne('SELECT MAX(id) FROM topics WHERE forumid=:forumid',array('forumid'=>$topicrow['forumid'])) );
        update_forum_last_post( $topicrow['forumid'] );

    write_to_forumslog($CURUSER['username'], $CURUSER['id'], $action, $topicid, $topicrow['forumid']);

        header("Location: ./forum.php?action=viewforum&forumid={$topicrow['forumid']}");
        die;
    }

  //-------- Action: Delete topic

  if ($action == "deletetopic")
  {
    $topicid = (int) $_REQUEST["topicid"];
    $forumid = (int) $_REQUEST["forumid"];

    if (!is_valid_id($topicid) || get_user_class() < UC_MODERATOR)
      die;

    $sure = $_POST["sure"];

    if (!$sure)
    {
      stderr("Delete topic", 'Sanity check: You are about to delete a topic. Click
                    <form action="forum.php" method="post" style="display:inline">
                      <input type="hidden" name="action" value="deletetopic">
                      <input type="hidden" name="topicid" value="'.$topicid.'">
                      <input type="submit" name="sure" value="here">
                    </form> if you are sure.');
    }

    write_moders_log('Forum topic ' . q_singleval("SELECT subject FROM topics WHERE id=$topicid") . ' was deleted by ' . $CURUSER["username"]);

    $forum_id = q_singleval("SELECT forumid FROM topics WHERE id=$topicid");
    if ( !($forum_id > 0) ) die('Topic inexistent');
    $forum_posts = fetchRow("SELECT posts,forumid FROM topics WHERE id=$topicid");
    if ($forum_posts['posts'] > 50 && $forum_posts['forumid'] != 2) stderr("Eroare","Nu puteti sterge tema deoarece contine multe mesaje potential valoroase");

    $subcat=q_singleval("SELECT subcat FROM topics WHERE id=$topicid");
    if ($subcat!=0) q("UPDATE forums_tags SET total=total-1 WHERE id=$subcat");

    q("DELETE FROM topics WHERE id=$topicid");

    //Check if the post don't have a image attached
    $upl_imgs = q("SELECT id,image FROM posts WHERE topicid=$topicid AND forumid=$forum_id");
    while($upl_img = mysql_fetch_assoc($upl_imgs)) {
        $upl_img_id = $upl_img['id'];
        $upl_img_file = $upl_img['image'];
        if ($upl_img_file != '') @unlink($forum_img_dir.'/'.$upl_img_id.'_'.$upl_img_file);
    }

    q("DELETE FROM posts WHERE topicid=$topicid AND forumid=$forum_id");
    //q("DELETE FROM readposts WHERE topicid=$topicid");
    q("DELETE FROM watches WHERE thread=$topicid AND type='topic'");

    update_topic_last_post( fetchOne('SELECT MAX(id) FROM topics WHERE forumid=:forumid',array('forumid'=>$forum_id)) );
    update_forum_last_post( $forum_id );

    header("Location: ./forum.php?action=viewforum&forumid=$forumid");
    die;
  }

  //-------- Action: Edit post

  if ($action == "editpost") {
    $postid = (int) $_GET["postid"];
      $ajax = (int)get('ajax') === 1;

    if (!is_valid_id($postid)) die;

    $res = q("
      SELECT *, UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(added) AS added_seconds_ago
      FROM posts
      WHERE id=$postid");

    if (mysql_num_rows($res) != 1)
        stderr(__('Eroare'), __('Post inexistent. ID ') .$postid. '.', false, false, $ajax);

    $arr = mysql_fetch_assoc($res);

    $topicid             = $arr['topicid'];
    $messageWasModerated = $arr['mod_approved'] == 'yes' || $arr['mod_approved'] == 'no';

    if ($arr['censored'] == 'y') {
        stderr(__('Eroare'), __('Mesajul este cenzurat.'), false, false, $ajax);
    }

    $res2 = q("SELECT locked, lastpost, forumid, mod_approval FROM topics WHERE id = " . $topicid);
    $arr2 = mysql_fetch_assoc($res2);
    //Lastest post id from topic
    $lastest_topic_post_id = $arr2['lastpost'];
    $forumid = $arr2['forumid'];

    if (mysql_num_rows($res) != 1)
      stderr(__('Eroare'), "No topic associated with post ID $postid.", false, false, $ajax);

    $locked = ($arr2["locked"] == 'yes');
    $topicWithModApproval = $arr2['mod_approval'] == 'yes';

    if (($CURUSER["id"] != $arr["userid"] || $locked) && get_user_class() < UC_MODERATOR)
      stderr(__('Eroare'), "Denied!", false, false, $ajax);

    $edited_by_moderator = false;
    if ($CURUSER["id"] != $arr["userid"] && get_user_class() >= UC_MODERATOR) {
        $edited_by_moderator = true;
    }

    /**
      Don't allow editing messages older than 1 day
    */
    $firstComment = fetchRow('
      SELECT id
      FROM posts
      WHERE topicid =:id AND page=1 AND forumid = :forumid
      ORDER BY id ASC LIMIT 1',
      array('id'=>$topicid,'forumid'=>$forumid)
    );
    $firstComment = $firstComment['id'];
    $isFirstCommentBeingEdited = $firstComment == $postid;

    if (!isAdmin() && !$isFirstCommentBeingEdited && $messageWasModerated) {
        stderr(__('Eroare'), __('Dvs. nu puteţi edita un mesaj ce a fost deja moderat de un moderator.'), false, false, $ajax);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $body = $_POST['body'];

      if ($edited_by_moderator) {
          write_moders_log("[url=./forum.php?action=viewtopic&topicid=$topicid&page=$postid#$postid]Mesaj[/url] editat pe forum de " .  $CURUSER["username"]);
      }

      if ($body == "")
        stderr(__('Eroare'), __('Conţinutul nu poate fi gol!'), false, false, $ajax);

      $body_unescaped = $body;
      $body = sqlesc($body);

      $editedat = sqlesc(get_date_time());

      if (!isAdmin() && $arr['added_seconds_ago'] > 86400) {
        // Check if this message is not #0 in topic
        if (!$isFirstCommentBeingEdited) {
            stderr(__('Eroare'), __('Dvs. nu puteţi edita un mesaj mai vechi de 1 zi.'), false, false, $ajax);
        }
      }

      if ($firstComment == $postid) {
        mem_delete('forum_first_post'.$arr['topicid']);
      }

      /**
        Don't allow editing of message already edited by a moderator
      */
      if (!isAdmin() && $arr['editedby'] && !$isFirstCommentBeingEdited) {
        if ($arr['editedby'] != $CURUSER['id'] && $firstComment != $postid) {
          stderr(__('Eroare'), __('Dvs. nu puteţi edita un mesaj ce a fost deja editat de un moderator.'), false, false, $ajax);
        }
      }

  // If added time is smaller than 5 minutes then don't show any edited notice..
  // If the edited comment is lastes and was added less than 15 minutes ago then don't show any edited notice..
  // If the torrent is not edited by a moderator

      bbcode_check_permission($body);
      if (strpos($body,'colegi.md') !== false) {
          stderr(__('Eroare'), __('Publicitatea pentru alte resurse este interzisă.'), false, false, $ajax);
      }

      if ($arr['added_seconds_ago'] > 300 && ($lastest_topic_post_id != $postid || $arr['added_seconds_ago'] > 900) || $edited_by_moderator == true) {
        // Get original body, to store as ori_text, only if this is first edit
        $editedby_body = q("SELECT editedby, body FROM posts WHERE id=$postid AND forumid=$forumid");
        $editedby_body = mysql_fetch_assoc($editedby_body);
        if ($editedby_body['editedby'] == 0) {
            $oldbody = sqlesc($editedby_body['body']);
            q("UPDATE posts SET ori_text=$oldbody WHERE id=$postid AND forumid=$forumid");
        }
        q("UPDATE posts SET body=$body, editedat=$editedat, editedby=$CURUSER[id] WHERE id=$postid AND forumid=$forumid");
      } else {
        q("UPDATE posts SET body=$body WHERE id=$postid AND forumid=$forumid");
      }

        if($ajax)
        {
            die( format_comment($body_unescaped,true,true));
        }

        $returnto = $_POST["returnto"];

            if ($returnto != "")
            {
                $returnto .= "&page=p$postid#$postid";
                header("Location: $returnto");
            }
            else
                stderr(__('Succes'), __('Postul a fost editat cu succes.'));
    }

    // This JSON can contain unescaped HTML, so handle it properly on server-side.
    if($ajax)
        echoJson($arr["body"]);

    stdhead();

    print("<h1>". __('Editează mesajul') ."</h1>\n");

    print("<form method=post action=?action=editpost&postid=$postid>\n");
    print("<input type=hidden name=returnto value=\"" . esc_html($_SERVER["HTTP_REFERER"]) . "\">\n");

    print("<table style='width:100%' border=1 cellspacing=0 cellpadding=5>\n");

    print("<tr><td style='padding: 0px'><textarea name=body rows=20 style='border: 0px; width:876px'>" . esc_html($arr["body"]) . "</textarea></td></tr>\n");

    print("<tr><td align=center><input type=submit value='Okay' class=btn></td></tr>\n");

    print("</table>\n");

    print("</form>\n");

    stdfoot();

    die;
  }

  //-------- Action: Delete post

  if ($action == "deletepost")
  {
    $postid = (int) $_REQUEST["postid"];

    $sure = post('sure');

    if (get_user_class() < UC_MODERATOR || !is_valid_id($postid))
      die;


      //------- Make sure we know what we do :-)
      if (!$sure)
      {
          stderr("Delete post", 'Sanity check: You are about to delete a post. Click
                    <form action="forum.php" method="post" style="display:inline">
                      <input type="hidden" name="action" value="deletepost">
                      <input type="hidden" name="postid" value="'.$postid.'">
                      <input type="submit" name="sure" value="here">
                    </form> if you are sure.');
      }

    //------- Get topic id

    $topicid_forumid = fetchRow("SELECT topicid, forumid, userid FROM posts WHERE id=$postid");

    if (!isset($topicid_forumid['topicid'])) stderr(__('Eroare'), "Post not found");

    $topicid = $topicid_forumid['topicid'];
    $forumid = $topicid_forumid['forumid'];
        $userid = $topicid_forumid['userid'];
    //------- We can not delete the post if it is the only one of the topic

    $res = q("SELECT COUNT(*) FROM posts WHERE topicid=$topicid AND forumid=$forumid");

    $arr = mysql_fetch_row($res);

    if ($arr[0] < 2)
      stderr(__('Eroare'), "Can't delete post; it is the only post of the topic. You should\n" .
      "<a href=?action=deletetopic&topicid=$topicid&sure=1>delete the topic</a> instead.\n");

    // Is first topic
    //------ Get first post
    $first_post = mem_get('forum_first_post'.$topicid);
    if (!$first_post) {
        $first_post = fetchRow("SELECT posts.*,users.username,users.class,users.avatar,users.avatar_version,users.donor,users.title,users.enabled,users.warned,users.user_opt,users.gender
              FROM posts
              LEFT JOIN users ON users.id = posts.userid
              WHERE posts.forumid = $forumid AND topicid=$topicid AND page=1
              ORDER BY id LIMIT 1");
        mem_set('forum_first_post'.$topicid, serialize($first_post), 3600);
    } else {
        $first_post = unserialize($first_post);
    }

    if ($first_post['id'] == $postid) {
        barkk(__('Pentru a şterge tema, utilizaţi opţiunea "Şterge Tema", ce o găsiţi jos, în opţiunile ei.'));
    }


    //------- Get the id of the last post before the one we're deleting

    $postBeforeDeleted = fetchOne("SELECT id FROM posts WHERE topicid=$topicid AND forumid=$forumid AND id < $postid ORDER BY id DESC LIMIT 1");

    //------- Delete post

    $subject = q_singleval('SELECT subject FROM topics WHERE id=:id',array('id'=>$topicid));

    write_moders_log("Mesaj $postid din tema [url=/forum.php?action=viewtopic&topicid={$topicid}]{$subject}[/url] a fost sters de ".$CURUSER["username"] );

    q("DELETE FROM posts WHERE id=$postid AND forumid=$forumid");
    q("UPDATE users_additional SET posts = posts - 1 WHERE id=$userid");
    q("UPDATE readposts SET lastpostread=$postBeforeDeleted WHERE lastpostread=$postid"); // Dupa ce stergem mesaj vazut, care sta in readposts, si intram in tema, ne duce la mesajul precedent celui sters ;D

    //------- Update topic

      //Dupa idee, noi nu mai stergem nimic, si nu mai avem nevoie de apelarea urmatoarelor functii
    update_topic_last_post($topicid);
    update_topic_posts_count($topicid,$forumid,true);
    update_posts_page_repage($topicid,$forumid);

    header("Location: ./forum.php?action=viewtopic&topicid={$topicid}&page=p{$postid}#{$postid}");

    die;
  }

  //-------- Action: Lock topic

  if ($action == "locktopic")
  {
    $forumid = (int) $_GET["forumid"];
    $topicid = (int) $_GET["topicid"];
    $page = (int) $_GET["page"];

    if (!is_valid_id($topicid) || get_user_class() < UC_MODERATOR)
      die;

    q("UPDATE topics SET locked='yes' WHERE id=$topicid");

    write_moders_log("Topicul $topicid a fost blocat de $CURUSER[id]");

    header("Location: ./forum.php?action=viewforum&forumid=$forumid&page=$page");

    die;
  }


    if ($action == 'changeSubcat') {
        $topicid = (int) post("topicid");
        $new_subcat = (int) post("subcategory");

        $topic_data = fetchRow('SELECT * FROM topics WHERE id=:id', array('id'=>$topicid) );

        if (!is_valid_id($topicid) || (get_user_class() < UC_MODERATOR && $topic_data['userid'] != $CURUSER['id'] ) ) {
            die;
        }

        if ($new_subcat != 0) {
            $subcat_data = get_subcat($new_subcat);
            if (!is_array($subcat_data) || !rightForumSubcat($topic_data['forumid'],$new_subcat)) {
                header("Location: ./forum.php?action=viewtopic&topicid=".$topicid);
                die();
            }
        }
        q('UPDATE topics SET subcat=:subcat WHERE id=:id', array('subcat'=>$new_subcat, 'id'=>$topicid) );
        if ($topic_data['subcat'] != 0) Q('UPDATE forums_tags SET total=total-1 WHERE id=:id', array('id'=>$topic_data['subcat']) );
        if ($new_subcat != 0) Q('UPDATE forums_tags SET total=total+1 WHERE id=:id', array('id'=>$new_subcat) );

        subcat_number_changed($topic_data['forumid']);

        header("Location: ./forum.php?action=viewtopic&topicid=".$topicid);
        die();
    }

  //-------- Action: Unlock topic

  if ($action == "unlocktopic")
  {
    $forumid = (int) $_GET["forumid"];

    $topicid = (int) $_GET["topicid"];

    $page = (int) $_GET["page"];

    if (!is_valid_id($topicid) || get_user_class() < UC_MODERATOR)
      die;

    q("UPDATE topics SET locked='no' WHERE id=$topicid");

    header("Location: ./forum.php?action=viewforum&forumid=$forumid&page=$page");

    die;
  }

  //-------- Action: Set locked on/off

  if ($action == "setlocked")
  {
    $topicid = (int) $_POST["topicid"];

    if (!$topicid || !allow_censoring('',$topicid))
      die;

    $locked = sqlesc($_POST["locked"]);
    q("UPDATE topics SET locked=$locked WHERE id=:id", array('id'=>$topicid));
    $forumid = fetchOne("SELECT forumid FROM topics WHERE id=:id", array('id'=>$topicid));

    write_moders_log("Topicul $topicid a fost locked: $locked de $CURUSER[id]");
    write_to_forumslog($CURUSER['username'], $CURUSER['id'], $action.'_'.$_POST["locked"], $topicid, $forumid);

    header("Location: $_POST[returnto]");

    die;
  }

  //-------- Action: Set sticky on/off

  if ($action == "setsticky") {
    $topicid = (int) $_POST["topicid"];

    if (!$topicid || get_user_class() < UC_MODERATOR)
      die;

    $sticky = $_POST["sticky"] == 'yes' ? 'yes' : 'no';
    q("UPDATE topics SET sticky=:sticky WHERE id=:topicid", array('sticky' => $sticky, 'topicid' => $topicid));
    write_moders_log("Topicul $topicid a fost facut sticky: $sticky de catre $CURUSER[id]");
    header("Location: $_POST[returnto]");

    die;
  }

  //-------- Action: Message moderator approval on/off

  if ($action == "setmodapproval") {
    $topicid = (int) $_POST["topicid"];

    if (!$topicid || get_user_class() < UC_MODERATOR) die;

    $modapproval = $_POST["modapproval"] == 'yes' ? 'yes' : 'no';
    q("UPDATE topics SET mod_approval=:modapproval WHERE id=:topicid",
      array('modapproval' => $modapproval, 'topicid' => $topicid));

    write_moders_log("Topicul $topicid a fost schimbat moderarea individuală a mesajelor: $modapproval de catre $CURUSER[id]");
    header("Location: $_POST[returnto]");

    die;
  }

  //-------- Action: Rename topic

  if ($action == 'renametopic')
  {
      $topicid = (int)$_POST['topicid'];
    if (!allow_censoring('',$topicid))
      die;



    if (!is_valid_id($topicid))
      die;

    $subject = $_POST['subject'];

    if ($subject == '')
      stderr(__('Eroare'), __('Trebuie să introduceţi un titlu nou.'));

    $subject = sqlesc($subject);

    $forum_data = fetchRow("SELECT forumid, subject FROM topics WHERE id=:id", array('id'=>$topicid));

    if($_POST['subject'] != $forum_data['subject'])
    {
      q("UPDATE topics SET subject=$subject WHERE id=$topicid");
      write_moders_log("Topicul $topicid a fost redenumit in: $subject de catre $CURUSER[id]");
      write_to_forumslog($CURUSER['username'], $CURUSER['id'], $action, $topicid, $forum_data['forumid'], null, $forum_data['subject'], $_POST['subject']);
    }
    $returnto = $_POST['returnto'];

    if ($returnto)
      header("Location: $returnto");

    die;
  }

  //-------- Action: View forum

  if ($action == "viewforum") {
    $forumid = (int) $_GET["forumid"];

    if (!is_valid_id($forumid)) die;

    forbidIfAnonymous($forumid);

    $page = @$_GET["page"];

    $userid = $CURUSER["id"];

    //------ Get forum name

    $arr = get_forum_data($forumid);

    $forumname = esc_html($arr["name"]);

    forbidIfNotEnoughRights($arr);

    //------ Page links

    //------ Get topic count

    $perpage = $CURUSER["topicsperpage"];

    if (!$perpage) $perpage = 40;

    $subcat = (int)get('subcat');
    if ($subcat) {
        $subcat_data = get_subcat($subcat);
        $topicsCount = $subcat_data['total'];
    } else {
        $topiccount_key = 'countTopics_'.$forumid;
        $topicsCount = mem_get($topiccount_key);

        if ($topicsCount == NULL) {
           $topicsCount = fetchFirst("SELECT COUNT(id) FROM topics WHERE forumid=$forumid");
           mem_set('countTopics_'.$forumid,$topicsCount,43200);
        }
    }




    //$arr = mysql_fetch_row($res);

    $num = $topicsCount;

    if ($page == 0)
      $page = 1;

    $first = ($page * $perpage) - $perpage + 1;

    $last = $first + $perpage - 1;

    if ($last > $num)
      $last = $num;

    $pages = ceil($num / $perpage);

    //------ Build menu

    $menu = "<p align=center><b>\n";

    $lastspace = false;

    for ($i = 1; $i <= $pages; ++$i)
    {
        if ($i == $page)
        $menu .= "<font class=gray>$i</font>\n";

      elseif ($i > 3 && ($i < $pages - 2) && ($page - $i > 3 || $i - $page > 3))
        {
            if ($lastspace)
              continue;

          $menu .= "... \n";

            $lastspace = true;
        }

      else
      {
        $menu .= "<a href=?action=viewforum&forumid=$forumid".($subcat?"&subcat=$subcat":'')."&page=$i>$i</a>\n";

        $lastspace = false;
      }
      if ($i < $pages)
        $menu .= "</b>|<b>\n";
    }

    $menu .= "<br/>\n";

    if ($page == 1)
      $menu .= "<font class=gray>&lt;&lt; " . __('Precedenta') . "</font>";

    else
      $menu .= "<a href=?action=viewforum&forumid=$forumid".($subcat?"&subcat=$subcat":'')."&page=" . ($page - 1) . ">&lt;&lt; " . __('Precedenta') . "</a>";

    $menu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    if ($last == $num)
      $menu .= "<font class=gray>" . __('Următoarea') . " &gt;&gt;</font>";

    else
      $menu .= "<a href=?action=viewforum&forumid=$forumid".($subcat?"&subcat=$subcat":'')."&page=" . ($page + 1) . ">" . __('Următoarea') . " &gt;&gt;</a>";

    $menu .= "</b></p>\n";

    $offset = $first - 1;

    //------ Get topics data
    // readposts.lastpostread column will be added later with _fill_arr_topics_lastpostread() function
    $q_sql = "SELECT topics.*, users.username AS topicAuthor, users.gender AS autothorGender,
                           posts.id AS postId, posts.userid AS lpuserId, posts.added AS lpAdded, lastUsers.username AS lpusername, lastUsers.gender AS lpuserGender
                   FROM topics %s
                   LEFT JOIN users ON users.id = topics.userid
                   LEFT JOIN posts ON posts.forumid = $forumid AND topics.lastpost = posts.id
                   LEFT JOIN users lastUsers ON posts.userid = lastUsers.id";
    // Stickey
    $topics_stickey_sql = sprintf($q_sql,"") ."
                   WHERE sticky='yes' AND topics.forumid=$forumid ".($subcat?"AND subcat=$subcat":'')." ORDER BY sticky, lastpost DESC";
    $topics_stickey = array();
    if ($offset == 0) {
        $topics_stickey = fetchAll_memcache($topics_stickey_sql,10);
        _fill_arr_topics_lastpostread($topics_stickey);
        if (mem2_get("forum:sticky:count:$forumid:$subcat") != count($topics_stickey)) mem2_set("forum:sticky:count:$forumid:$subcat",count($topics_stickey),60000);
    }

    // AND sticky='no' is applyed later
    if ($offset == 0) $perpage += count($topics_stickey);
    else $offset += mem2_get("forum:sticky:count:$forumid:$subcat");
    $topics_sql = sprintf($q_sql,((!($subcat))?"use index(forum_lastpost)":'')) ."
                   WHERE topics.forumid=$forumid ".($subcat?"AND subcat=$subcat":'')." ORDER BY lastpost DESC LIMIT $offset,$perpage";

    $topics = fetchAll_memcache($topics_sql,6);

    foreach($topics AS $topics_k=>$topics_v) {
        if ($topics_v['sticky'] != 'no') unset($topics[$topics_k]);
    }

    _fill_arr_topics_lastpostread($topics);
    $topicarr_all = array_merge($topics_stickey,$topics);

   if ($subcat && !rightForumSubcat($forumid,$subcat)) stderr("Eroare", "Sbcategorie gresita");

    stdhead(__('Forum'));

    $numtopics = count($topicarr_all);
    $link_log = '';
    if ( (get_user_class() >= UC_MODERATOR)) {
      $link_log = '<span class="view_log">[<a href="log_forums.php?forumid='.$forumid.'">'. __('vezi log') .'</a>]</span> (vizibil doar adminilor)';
    }
    if ($subcat) {
        print("<h1><a href=\"./forum.php\">" . __('Forum') . "</a> &gt; ".'<a href="./forum.php?action=viewforum&forumid='.$forumid.'">'.$forumname.'</a> &gt; '.$subcat_data['name'] . $link_log . ' </h1>');
    } else {
        print("<h1><a href=\"./forum.php\">" . __('Forum') . "</a> &gt; $forumname\n". $link_log .'</h1>');
        // Show all subcats if any
        $subcategories = getSubcategories($forumid,$userlang);
        if (count($subcategories)) {
            $subcategories_html = '<div class="subcategories">';
            foreach($subcategories AS $subcategorie) {
                $subcategories_html .= '<a href="forum.php?action=viewforum&forumid='.$forumid.'&subcat='.$subcategorie['id'].'">'.esc_html($subcategorie['name']).'</a> ('.$subcategorie['total'].'), ';
            }
            // Remove last 2 chars
            $subcategories_html = substr($subcategories_html,0,-2);
        $subcategories_html .= '</div>';
        }
        echo $subcategories_html;
        // For futher use..
        $subcategories_id_key = new_array_name_as_index('id',$subcategories);
    }

    if ($numtopics > 0)
    {
      print($menu);

      print("<table border=1 cellspacing=0 cellpadding=5 width=100%>");

      print("<tr><td class=colhead align=left>" . __('Teme') . "</td><td class=colhead>" . __('Replici') . "</td><td class=colhead>" . __('Vizualizări') . "</td>\n" .
        "<td class=colhead align=left>" . __('Autor') . "</td><td class=colhead align=left>" . __('Ultimul mesaj') . "</td>\n");

      print("</tr>\n");

      foreach ($topicarr_all AS $topicarr)
      {
        $topicid = $topicarr["id"];

        $topic_userid = $topicarr["userid"];

        $topic_views = $topicarr["views"];

        $topic_author = $topicarr['topicAuthor'];

        $topic_author_gender = $topicarr['autothorGender'];

        $views = number_format($topic_views);

        $locked = $topicarr["locked"] == "yes";

        $sticky = $topicarr["sticky"] == "yes";

        //---- Get reply count
        $posts = $topicarr['posts'] - 1;

        $replies = max(0, $posts);

        $tpages = floor($posts / $postsperpage);

        if ($tpages * $postsperpage != $posts)
          ++$tpages;

        if ($tpages > 1)
        {
          $topicpages = " (<img src=./pic/forum/multipage.gif>";

          $pauseTopicPages = false;
          for ($i = 1; $i <= $tpages; $i++) {
              //echo "$i > 3 && ($i < $tpages - 2) && ($tpages - $i > 3 || $i - $tpages > 3)<br/><br/>";
              if ($i > 3 && ($i < $tpages - 2) && ($tpages - $i >= 3 || $i - $tpages > 3)) {
                  if ($pauseTopicPages) continue;

                  $topicpages .= " ... ";
                  $pauseTopicPages = true;

               } else {
                  $topicpages .= " <a href=?action=viewtopic&topicid=$topicid&page=$i>$i</a>";
               }
          }

          $topicpages .= ")";
        }
        else
          $topicpages = "";

        //---- Get userID and date of last post

        $lppostid = $topicarr['postId'];

        $lpuserid = $topicarr['lpuserId'];

        $lpadded = "<nobr>" . $topicarr["lpAdded"] . "</nobr>";

        $lpusername = $topicarr['lpusername'];

        $lpuserGender = $topicarr['lpuserGender'];


        //------ Get name of last poster

        if ($lpusername != '') {
           $lpusername = "<a href=userdetails.php?id=$lpuserid".(($lpuserGender=='fem')?' style="color:#F93EA0;"':'')."><b>$lpusername</b></a>";
        }
        else if( $lpuserid == 0 )
               $lpusername = "System";
             else
               $lpusername = "unknown[$lpuserid]";

        //------ Get author

        if ($topic_author != '')
        {
          $lpauthor = "<a href=userdetails.php?id=$topic_userid".(($topic_author_gender=='fem')?' style="color:#F93EA0;"':'')."><b>$topic_author</b></a>";
        }
        else if( $topic_userid == 0 )
               $lpauthor = "System";
             else
               $lpauthor = "unknown[$topic_userid]";

        //---- Print row

        //$r = q("SELECT lastpostread FROM readposts WHERE userid=$userid AND topicid=$topicid");

        //$a = mysql_fetch_row($r);
        $a = $topicarr['lastpostread'];

        $new = !$a || $lppostid > $a;

        $topicpic = ($locked ? ($new ? "lockednew" : "locked") : ($new ? "unlockednew" : "unlocked"));

        if ($a === null) { //User have never read this topic
            $url = "<a href=?action=viewtopic&topicid=$topicid>";
        } else {
            $url = "<a href=?action=viewtopic&topicid=$topicid&page=p$a#$a>";
        }

        $subcat_name = '';
        if (!$subcat && $topicarr['subcat']) {
            $topic_subcat_id = $topicarr['subcat'];
            $topic_subcat = $subcategories_id_key[$topic_subcat_id];
            $subcat_name = "[<a href=forum.php?action=viewforum&forumid=$forumid&subcat=$topic_subcat_id>$topic_subcat[name]</a>]";
        }

        $subject = ($sticky ? __('Important: ') : "") . $url . '<b>' .
        esc_html($topicarr["subject"]) . "</b></a> &nbsp;$subcat_name $topicpages";




        print("<tr><td align=left><table border=0 cellspacing=0 cellpadding=0><tr>" .
        "<td class=embedded style='padding-right: 5px'><img src=./pic/forum/$topicpic.gif>" .
        "</td><td class=embedded align=left>\n" .
        "$subject</td></tr></table></td><td align=right>$replies</td>\n" .
        "<td align=right>$views</td><td align=left>$lpauthor</td>\n" .
        "<td align=left>$lpadded<br/>by&nbsp;$lpusername</td>\n");

        print("</tr>\n");
      } // while

      print("</table>\n");

      print($menu);

    } // if
    else
      print("<p align=center>". __('Nici o temă găsită.') ."</p>\n");

    print("<table class='main mCenter' border=0 cellspacing=0 cellpadding=0><tr valing=center>\n");

    print("<td class=embedded><img src=./pic/forum/unlockednew.gif style='margin-right: 5px'></td><td class=embedded>". __('Mesaje noi') ."</td>\n");

    print("<td class=embedded><img src=./pic/forum/locked.gif style='margin-left: 10px; margin-right: 5px'>" .
    "</td><td class=embedded>". __('Teme închise') ."</td>\n");

    print("</tr></table>\n");

    $arr = get_forum_access_levels($forumid) or die;

    $maypost = get_user_class() >= $arr["write"] && get_user_class() >= $arr["create"];

    if (!$maypost)
      print("<p><i>". __('Dvs nu aveţi dreptul de a crea teme noi în acest forum.') ."</i></p>\n");

    if ($maypost) {
?>
<br/>
    <?php if ($user_logged): ?>
<div style="width:740px;text-align:center"><h1><? echo $GLOBALS['lang']['forum_new_topic'];?></h1></div>

<div style="width:740px;text-align:left">
<form method="post" action="forum.php" enctype="multipart/form-data">
<input type="hidden" name="action" value="post">
<input type="hidden" name="forumid" value="<? echo $forumid;?>">
<? echo $GLOBALS['lang']['forum_new_topic_title'];?>:<br/>
<input name="subject" type="text" maxlength="70" size="100" style="margin-bottom:5px;width:100%"><br/>
<? echo $GLOBALS['lang']['forum_new_topic_message'];?>:<br/>
<textarea name="body" rows="15" cols="150" style="margin-bottom:5px;width:100%;"></textarea><br/>

<div style="padding-bottom:5px;"><?=__('Subcategorie')?>:</div>

<select name="subcategory" style="display:inline;">
    <option><?=__('-- nici una')?></option>
    <?php foreach( getSubcategories($forumid,$userlang) AS $subcat_item ) : ?>
        <option value="<?=$subcat_item['id']?>"  <?=($subcat_item['id'] == $subcat)?'SELECTED':''?> ><?=esc_html($subcat_item['name'])?></option>
    <?php endforeach; ?>
</select>

<?php /*<div style="padding-bottom:5px;padding-top:7px;"><?=$GLOBALS['lang']['forum_about_upload_img'];:</div>
<input name="file_image" size="77" type="file"><br/><br/> */ ?>
<input type="submit" value="<? echo $GLOBALS['lang']['forum_new_topic_send'];?>">
</form>

</div>
    <?php endif; /** $user_logged **/ ?>
<?php
    }



    insert_quick_jump_menu($forumid);

    stdfoot();

    die;
  }

  //-------- Action: View unread posts

  if ($action == "viewunread")
  {
    //die("This feature is currently unavailable.");
    $userid = $CURUSER['id'];

    //$maxresults = 25;

    //Get the post id, what was added one month ago

    $monthAgoPostId = mem_get('post_id_1_month_ago');

    if ($monthAgoPostId == null) {
        $monthAgoPostId = q_singleval("SELECT id FROM posts WHERE added < NOW() - INTERVAL 1 month ORDER BY id DESC LIMIT 1");
        mem_set('post_id_1_month_ago',$monthAgoPostId,864000);
    }

    $res = q("SELECT t.id, t.forumid, t.subject, t.lastpost, readposts.lastpostread
            FROM topics t
            LEFT JOIN readposts ON t.id = readposts.topicid AND readposts.userid=$userid
            WHERE t.lastpost > $monthAgoPostId AND (t.lastpost > readposts.lastpostread OR readposts.lastpostread IS NULL)
                AND (t.userid != 0 AND t.userid != 124425)
            ORDER BY t.lastpost DESC");
    stdhead();

    print("<h1>Topics with unread posts</h1>\n");

    $n = 0;

    $uc = get_user_class();

    $forums_rows = Forum::getForums($userlang);
    $forums = array();
    foreach ($forums_rows AS $forum) {
        $forums[$forum['id']] = $forum;
    }

    while ($arr = mysql_fetch_assoc($res))
    {
      $topicid = $arr['id'];

      $forumid = $arr['forumid'];

      //---- Check if post is read
      /*$a = q_singleval("SELECT lastpostread FROM readposts WHERE userid=$userid AND topicid=$topicid");

      if ($a && $a >= $arr['lastpost']) //> because sometime the last post is deleted
        continue;*/
      $last_read = $arr['lastpostread'];

      //---- Check access & get forum name
      $a = $forums[$forumid];

      if ($uc < $a['minclassread']) {
        continue;
      }

      ++$n;

      /*if ($n > $maxresults)
        break;*/

      $forumname = $a['name'];

      if ($n == 1)
      {
        print("<p><a href=?catchup><b>$lang[forum_mark_forums_read]</b></a></p>\n");
        print("<table border=1 cellspacing=0 cellpadding=5 width=100%>\n");

        print("<tr><td class=colhead align=left>" . __('Tema') . "</td><td class=colhead align=left>" . __('Forum') . "</td></tr>\n");
      }

      print("<tr><td align=left><table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>" .
      '<img src="./pic/forum/unlockednew.gif" style="margin-right:5px"></td><td class=embedded>' .
      "<a href=?action=viewtopic&topicid=$topicid&page=$last_read#$last_read><b>" . esc_html($arr["subject"]) .
      "</b></a></td></tr></table></td><td align=left><a href=?action=viewforum&amp;forumid=$forumid><b>$forumname</b></a></td></tr>\n");
    }
    if ($n > 0)
    {
      print("</table>\n");
      print("<p><a href=?catchup><b>$lang[forum_mark_forums_read]</b></a></p>\n");

      /*if ($n > $maxresults)
        print("<p>More than $maxresults items found, displaying first $maxresults.</p>\n");*/

    }
    else
      print("<b>Nothing found</b>");

    stdfoot();

    die;
  }

if ($action == "search")
{
    stdhead($lang['search_header']);
    print("<h1>$lang[search_header]</h1>\n");

    $keywords = trim(@$_GET["keywords"]);
    $category = @$_GET['category'];
    $search_username = trim(@$_GET['username']);

    if ($keywords != "")
    {
        $boolean_search_str = $keywords;

        if ( strpos( $boolean_search_str, '+') === false && strpos( $boolean_search_str, '-') === false && strpos( $boolean_search_str, '"') === false ) {
            // Remove multiple consecutive spaces
            $boolean_search_str = preg_replace('/\s\s+/', ' ', $boolean_search_str);
            $boolean_search_str = trim($boolean_search_str);
            $boolean_search_str = str_replace(' ', ' +', $boolean_search_str);
            // In front also put a +
            $boolean_search_str = '+'.$boolean_search_str;
        }

        $search_userid = '';
        if ($search_username != '') {
            $search_userid = fetchOne('SELECT id FROM users WHERE username=:username', array('username'=>$search_username) );
        }

        /**
            Spinx part
        **/
        include('./sphinx/sphinxapi.php');
        $sphinx_mode = 'SPH_MATCH_BOOLEAN';
        $sphinx_index = 'forum_search';
        $cl = new SphinxClient();
        $cl->SetServer ( $GLOBALS['sphinx_host'], $GLOBALS['sphinx_port'] );
        $cl->SetWeights ( array ( 100, 1 ) );
        $cl->SetMatchMode ( $sphinx_mode );
        $cl->SetLimits ( 0, 1000 );
        $cl->SetSortMode ( SPH_SORT_EXTENDED, '@id DESC' );
        if ($category > 0) {
            $cl->SetFilter ( "forumid", array ( $category ) );
        }
        if ($search_userid > 0) {
            $cl->SetFilter ( "userid", array ( $search_userid ) );
        }


        $sphinx_res = $cl->Query ( $boolean_search_str, $sphinx_index );



        if ( $sphinx_res===false && $CURUSER['id'] == 1)
        {
            print "Query failed: " . $cl->GetLastError() . ".\n";
        }

        if (is_array($sphinx_res) && isset($sphinx_res["matches"]) && count($sphinx_res["matches"]) ) {
            $matched_ids = array();



            foreach ( $sphinx_res["matches"] as $doc => $docinfo )
            {
                if (!is_numeric($doc)) continue;
                $matched_ids[] = $doc;
                $t_forumid = $docinfo['attrs']['forumid'];
                $matched_ids_with_forumid[] = "(posts.id = $doc AND posts.forumid = $t_forumid)";
            }
        }


        $perpage = 50;
        $page = max(1, (int) $_GET["page"]);
        $ekeywords = sqlesc($keywords);
        ?>

        <p class="center" style="font-size:140%;"><?=__('Caut mesajele ce conţin')?> <b><?=esc_html($keywords)?></b>
            <?=($search_userid > 0)? __('scrise de') .' <b>'. esc_html($search_username) .'</b> ' : '' ?>
            <?=($category > 0)? __('în categoria') . ' <b>' . fetchOne("SELECT name_$userlang FROM forums WHERE id=:cat", array('cat'=>$category) ) . '</b>' : '' ?>
        </p>

        <?php
        $hits = count($sphinx_res["matches"]);

        if ($hits == 0)
            print("<p class='center'><b>". __('Nimic nu a fost găsit!') ."</b></p>");
        else
        {
            $pages = ceil($hits / $perpage);

            $next_prev_link = './forum.php?action=search&amp;keywords=' . esc_html($keywords) .
                             (($category > 0) ? '&amp;category=' . esc_html($category):'' )    .
                             (($search_userid > 0) ? '&amp;username=' . esc_html($search_username):'' );
            if ($page > $pages) $page = $pages;
            for ($i = 1; $i <= $pages; ++$i)
                if ($page == $i)
                    $pagemenu1 .= "<font class=gray><b>$i</b></font>\n";
                else
                    $pagemenu1 .= "<a href=\"{$next_prev_link}&amp;page=$i\"><b>$i</b></a>\n";
            if ($page == 1)
                $pagemenu2 = "<font class=gray><b>&lt;&lt; " . __('Precedenta') . "</b></font>\n";
            else
                $pagemenu2 = "<a href=\"{$next_prev_link}&amp;page=" . ($page - 1) . "\"><b>&lt;&lt; " . __('Precedenta') . "</b></a>\n";
            $pagemenu2 .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
            if ($page == $pages)
                $pagemenu2 .= "<font class=gray><b>" . __('Următoarea') . " &gt;&gt;</b></font>\n";
            else
                $pagemenu2 .= "<a href=\"{$next_prev_link}&amp;page=" . ($page + 1) . "\"><b>" . __('Următoarea') . " &gt;&gt;</b></a>\n";
            $offset = ($page * $perpage) - $perpage;
            //$res = q("SELECT id, topicid,userid,added FROM posts_forsearch WHERE MATCH (body) AGAINST ($ekeywords) ORDER BY added DESC LIMIT $offset,$perpage");
            //$num = mysql_num_rows($res);

            $sql_where = join(' OR ', array_slice($matched_ids_with_forumid, $offset, $perpage ) );
            $res = q("SELECT posts.id, posts.topicid, posts.userid, posts.added, posts.forumid,
                             users.username, topics.subject, forums.name_$userlang AS forum_name
                      FROM posts
                      LEFT JOIN users ON posts.userid = users.id
                      LEFT JOIN topics ON posts.topicid = topics.id
                      LEFT JOIN forums ON posts.forumid = forums.id
                      WHERE minclassread<=:min AND ($sql_where)
                      ORDER BY id DESC
                ", array('min'=>$CURUSER["class"]) );


            print("<p>$pagemenu1<br/>$pagemenu2</p>");
            print("<table border=1 cellspacing=0 cellpadding=5 width=100%>\n");
            print("<tr><td class=colhead>Post</td><td class=colhead align=left>" . __('Tema') . "</td><td class=colhead align=left>" . __('Forum') . "</td><td class=colhead align=left>Posted by</td></tr>\n");
            while ($post = mysql_fetch_assoc($res)) {
                $username = $post['username'];
                if ($username == "") $user["username"] = "[$post[userid]]";
                print("<tr><td>$post[id]</td><td align=left><a href=?action=viewtopic&amp;topicid=$post[topicid]&amp;page=p$post[id]#$post[id]><b>" . esc_html($post["subject"]) . "</b></a></td><td align=left><a href=?action=viewforum&amp;forumid=$post[forumid]><b>" . esc_html($post["forum_name"]) . "</b></a><td align=left><a href=userdetails.php?id=$post[userid]><b>$username</b></a><br/>at $post[added]</tr>\n");
            }
            print("</table>\n");
            print("<p>$pagemenu2<br/>$pagemenu1</p>");
            print("<p>". __('Au fost găsite') ." $hits ". __('mesaje') ."</p>");
            print("<p><b>". __('Caută din nou') ."</b></p>\n");
        }
    }
?>
    <form method=get action=./forum.php>
    <input type=hidden name=action value=search>
    <table class="mCenter" border=1 cellspacing=0 cellpadding=5>
    <tr>
        <td><?=__('Cuvîntele de căutare')?></td>
        <td align=left>
            <input type=text size=55 name=keywords value="<?=esc_html($keywords)?>"><br/>
            <font class=small size=-1><?=__('Puteţi introduce unul sau mai multe cuvinte')?>.</font>
        </td>
    </tr>
    <tr>
        <td><?=__('Categoria')?></td>
        <td>
            <select name="category">
            <option> - <?=__('toate categoriile')?></option>
            <?php
            $categs = fetchAll("SELECT id,name_$userlang AS name,minclasswrite
                             FROM forums
                             WHERE minclasswrite <= :class
                             ORDER BY sort", array( 'class' => get_user_class() ) );
            foreach ($categs AS $categ) : ?>
                <option value="<?=$categ['id']?>" <?=($category == $categ['id'])?'selected':''?> ><?=$categ['name']?></option>
      <?php endforeach; ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><?=__('Nume de utilizator')?></td>
        <td>
            <input type="text" name="username" value="<?=esc_html($search_username)?>"><br/>
            <?=__('Nu introdu nimic ca să cauţi pentru toţi')?>
        </td>
    </tr>
    <tr>
        <td align=center colspan=2><input type=submit value="<?=__('Caută')?>" class=btn></td>
    </tr>
    </table>
    </form>
<?php
    stdfoot();
    die;
}

if ($action == "vieworiginal") {
    if (get_user_class() < UC_MODERATOR)
        stderr(__('Eroare'), __('Accesul este interzis.'));

  $commentid = 0 + $_GET["cid"];

  if (!is_valid_id($commentid))
        stderr(__('Eroare'), __('ID incorect:') . " $commentid.");

  $res = q("SELECT * FROM posts WHERE id='".$commentid."'");
  $arr = mysql_fetch_array($res);
  if (!$arr)
    stderr(__('Eroare'), __('ID incorect:') . " $commentid.");

  stdhead("Original comment");
  print("<h1>Original contents of comment #$commentid</h1><p>\n");
    print("<table width=500 border=1 cellspacing=0 cellpadding=5>");
  print("<tr><td class=comment>\n");
    echo esc_html($arr["ori_text"]);
  print("</td></tr></table>\n");

  $returnto = $_SERVER["HTTP_REFERER"];

//  $returnto = "details.php?id=$torrentid&amp;viewcomm=$commentid#$commentid";

    if ($returnto)
        print("<p><font size=small>(<a href=$returnto>back</a>)</font></p>\n");

    stdfoot();
    die;
}

// Censore
    if ($action == 'censor') {

        $id = post('postId');

        $row = fetchRow("SELECT forumid,topicid FROM posts WHERE id=:id",array('id'=>$id));
        $topicid = $row['topicid'];
        $forumid = $row['forumid'];

        if (!allow_censoring(array(),$topicid)) stderr(__('Eroare'), __('Accesul este interzis.'));

        Q('UPDATE posts SET censored="y" WHERE id=:id AND forumid=:forumid',array('id'=>$id,'forumid'=>$forumid));

        write_moders_log("[url=./forum.php?action=viewtopic&topicid=$topicid&page=p$id#$id]Mesaj[/url] cenzurat pe forum de " .  $CURUSER["username"]);

    write_to_forumslog($CURUSER['username'], $CURUSER['id'], $action, $topicid, $forumid, $id);
        die(1);
    }

    // Uncensore
    if ($action == 'uncensore') {
        $id = get('postid');

        $row = fetchRow("SELECT forumid,topicid FROM posts WHERE id=:id",array('id'=>$id));
        $topicid = $row['topicid'];
        $forumid = $row['forumid'];

        if (!allow_censoring(array(),$topicid)) stderr(__('Eroare'), __('Accesul este interzis.'));

        Q('UPDATE posts SET censored="n" WHERE id=:id AND forumid=:forumid',array('id'=>$id,'forumid'=>$forumid));
        mem_delete('forum_first_post'.$topicid);

        $topicid = get('topicid');

        write_moders_log("[url=./forum.php?action=viewtopic&topicid=$topicid&page=p$id#$id]Mesaj[/url] decenzurat pe forum de " .  $CURUSER["username"]);

        write_to_forumslog($CURUSER['username'], $CURUSER['id'], $action, $topicid, $forumid, $id);

        header("Location: ./forum.php?action=viewtopic&topicid=$topicid&page=p$id#$id");
        exit();
    }

// Report
    if ($action == 'postRaport') {
        $id = post('postId');
        $forumid = post('forumid');
        $userid = $CURUSER['id'];

        $post = fetchRow('SELECT * FROM posts WHERE id=:id AND forumid=:forumid',
                    array('id'=>$id,'forumid'=>$forumid) );

        // Deny reporting of posts older than 2 weeks, censored
        if ( !$post || !$post['id'] || $post['censored'] == 'y' ) {
            return;
        }
        /**
            Check if this user have not raported this message already
        */
        $already = q_singleval('SELECT id FROM raportedmsg WHERE postId=:postId AND userId=:userId AND type="forum"',
            array('postId'=>$id,'userId'=>$userid));
        if ($already) {
            die($lang['raported_already']);
        }
        /**
            Max 10 raports per day for users
        */
        if (get_user_class() <= UC_POWER_USER) {
            $limit = 30;
        } else {
            $limit = 100;
        }

        $totalToday = q_singleval('SELECT COUNT(id) FROM raportedmsg WHERE userId=:userid AND date=CURDATE()',array('userid'=>$userid));
        if ($limit < $totalToday) {
            die("Max raports per day reached");
        }

        Q('INSERT INTO raportedmsg VALUES (0,NOW(),"forum",:postid,:userid,"waiting",:forumid)',
            array('postid'=>$id,'userid'=>$userid,'forumid'=>$forumid)
        );
        Q('UPDATE raportedmsg SET status="waiting" WHERE type="forum" AND postId=:postid', array('postid'=>$id) );
        die($lang['raported_ok']);
    }

  //-------- Handle unknown action

  if ($action != "")
    stderr(__('Eroare'), __('Acţiune necunoscută.'));

  //-------- Default action: View forums

  if (isset($_GET["catchup"]))
    catch_up();

  //-------- Get forums

  $forums_rows = mem2_get('forum_forums_list');
  if (!$forums_rows) {
      // name_$userlang AS name, description_$userlang AS description
      $forums_rows = fetchAll(
        "SELECT sort,forums.id,name_ro,name_ru, description_ro, description_ru,minclassread,
           minclasswrite,postcount,topiccount,minclasscreate, forums.lastPost,
           posts.added AS postAdded, posts.topicid AS lastTopicId, users.username AS lastUsername,
           users.id AS lastUsernameId, users.gender AS lastGender, topics.subject AS lastSubject
         FROM forums
         LEFT JOIN posts ON forums.lastPost = posts.id AND posts.forumid = forums.id
         LEFT JOIN users ON posts.userid = users.id
         LEFT JOIN topics ON posts.topicid = topics.id
         ORDER BY forums.sort
      ");
      mem2_set('forum_forums_list', serialize($forums_rows), 60 );
  } else {
      $forums_rows = unserialize($forums_rows);
  }

  // Prepare a bulk SELECT on readposts table
  $forum_topics_ids = array();
  foreach($forums_rows AS $forums_row_k=>$forums_row_v) {
      if (is_numeric($forums_row_v['lastTopicId'])) $forum_topics_ids[] = $forums_row_v['lastTopicId'];
      // Translate name_lang and description_lang..
      $forums_rows[$forums_row_k]['name'] = $forums_row_v["name_$userlang"];
      $forums_rows[$forums_row_k]['description'] = $forums_row_v["description_$userlang"];
  }
  $forum_topics_ids = join(',',$forum_topics_ids);
  $lastpostread_list = array();
  $lastpostread_key = array();
  if (strlen($forum_topics_ids)) {
    $lastpostread_list = fetchAll("SELECT lastpostread,topicid FROM readposts WHERE readposts.userid=$CURUSER[id] AND readposts.topicid IN ($forum_topics_ids)");
    if (count($lastpostread_list)) {
        foreach($lastpostread_list AS $lastpostread_item) {
            $lastpostread_key[$lastpostread_item['topicid']] = $lastpostread_item['lastpostread'];
        }
    }
  }
  // Fil original answer with readposts results
  foreach($forums_rows AS $forums_row_k=>$forums_row_v) {
      $t_lastTopicId = $forums_row_v['lastTopicId'];
      $t_lastpostread = "";
      if (isset($lastpostread_key[$t_lastTopicId])) {
          $t_lastpostread = $lastpostread_key[$t_lastTopicId];
      }
      $forums_rows[$forums_row_k]['lastpostread'] = $t_lastpostread;
  }


  stdhead(__('Forum'));

/**
    Index forum
**/

  print("<h1>" . __('Forum') . "</h1>");
  //echo '<p><b>Anunţ!</b> Către autorii de teme, <a href="./forum.php?action=viewtopic&topicid=62597&page=lastseen">includeţi-le în subcategorii</a></p>';
  $forum_moderators_link = ' | <a href="/forum_moderators.php"><b>Moderatori</b></a>';
  if (get_config_variable('forum', 'moderators_activated') == false) {
    $forum_moderators_link = '';
  }
  print("<p align=center><a href=?action=viewunread><b>". $GLOBALS['lang']['forum_new_posts'] ."</b></a> | <a href=?catchup><b>" . $GLOBALS['lang']['forum_mark_forums_read'] . "</b></a> | <a href=./forum.php?action=search style=color:green><b>" . $GLOBALS['lang']['search'] . "</b></a> $forum_moderators_link </p>");

  print("<table border=1 cellspacing=0 cellpadding=5 width=100%>\n");
  echo '<colgroup><col width="567"><col width="36"><col width="36"><col width="196"></colgroup>';

  print("<tr><td class=colhead align=left>" . __('Forum') . "</td><td class=colhead align=right>" . __('Teme') . "</td>" .
  "<td class=colhead align=right>" . __('Replici') . "</td>" .
  "<td class=colhead align=left>" . __('Ultimul mesaj') . "</td></tr>\n");

  $tmp_user_class = get_user_class();

  $all_subcategories = getAllSubcategories($userlang);

  foreach($forums_rows AS $forums_arr) {
    if ($tmp_user_class < $forums_arr["minclassread"]) continue;

    if (!userCanSeeForum($forums_arr)) continue;

    $forumid = $forums_arr["id"];

    $forumname = $forums_arr["name"];

    $forumdescription = $forums_arr["description"];

    $topiccount = $forums_arr["topiccount"];

    $postcount = $forums_arr["postcount"];

    $sort = $forums_arr['sort'];

    if ($forumid == 9) echo '<tr><td colspan="4" align="left" style="border:0px;background-color:#F5F4EA;"><img src="./pic/forum/home.gif">&nbsp; &nbsp;<font size="2"><b>Community</b></font></td></tr>';
    if ($forumid == 33) echo '<tr><td colspan="4" align="left" style="border:0px;background-color:#F5F4EA;">&nbsp; &nbsp;<font size="2"><b>De sistem</b></font></td></tr>';
    // Find last post ID

    $lastpostid = $forums_arr['lastPost'];

    $postAdded = $forums_arr['postAdded'];
    $lastUsernameId = $forums_arr['lastUsernameId'];
    $lastUsername = $lastUsernameId == 0 ? 'System' : $forums_arr['lastUsername'];
    $lastGender = $forums_arr['lastGender'];
    $lastSubject = $forums_arr['lastSubject'];
    $lastTopicId = $forums_arr['lastTopicId'];

    // Subcats
    $subcategories = @$all_subcategories[$forumid];
    $subcategories_html = '';
    if (count($subcategories)) {
        foreach($subcategories AS $subcategorie) {
            $subcategories_html .= '<a href="forum.php?action=viewforum&forumid='.$forumid.'&subcat='.$subcategorie['id'].'">'.esc_html($subcategorie['name']).'</a> ('.$subcategorie['total'].'), ';
        }
        // Remove last 2 chars
        $subcategories_html = substr($subcategories_html,0,-2);
    }

    // Get last post info

    if ($lastTopicId > 0) {
      $lastposter = $lastUsername;
      if (strlen($lastSubject) > 80) $lastSubject = mb_substr($lastSubject,0,80) . '...';
      $lasttopic = esc_html($lastSubject);
      $a = $forums_arr['lastpostread'];
      $posted_by_link = "<a href=userdetails.php?id=$lastUsernameId".(($lastGender=='fem')?' style="color:#F93EA0;"':'')."><b>$lastUsername</b></a>";

      if ($lastUsernameId == 0) $posted_by_link = "System";

      $lastpost = "<nobr>$postAdded<br/>" .
      "by $posted_by_link</nobr><br/>" .
      "in <a href=?action=viewtopic&topicid=$lastTopicId&amp;page=lastseen><b>$lasttopic</b></a>";

      if ($a && $a >= $lastpostid)
        $img = "unlocked";
      else
        $img = "unlockednew";
    }
    else
    {
      $lastpost = "N/A";
      $img = "unlocked";
    }

    $topicTransparency = '';
    if ($lastTopicId > 0) {
        $postAddedTime = strtotime($postAdded);
        if ( $postAddedTime > 0 && (time() - $postAddedTime) > 345600  ) { // 345600 = 4 days
            $topicTransparency = ' class="semi_transparent"';
        }
    }


    print("<tr{$topicTransparency}><td align=left><table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded style='padding-right: 5px'><img src=".
    "./pic/forum/$img.gif class=forum_topic_image></td><td class=embedded><a href=?action=viewforum&forumid=$forumid><b>$forumname</b></a><br/>\n" .
    "$forumdescription<br/>$subcategories_html</td></tr></table></td><td align=right>$topiccount</td></td><td align=right>$postcount</td>" .
    "<td align=left>$lastpost</td></tr>\n");
  }

  print("</table>\n");


    $time = microtime_float() - $time_start;
    echo "<p style='color:#F5F4EA;'>$time</p>\n";

  stdfoot();
?>