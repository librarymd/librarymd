<?php

function forum_action_post($maxsubjectlength) {
  global $CURUSER, $INCLUDE;

  $forumid = (int) isset($_POST["forumid"])?$_POST["forumid"]:'';
  $topicid = (int) isset($_POST["topicid"])?$_POST["topicid"]:'';

  if (!is_valid_id($forumid) && !is_valid_id($topicid))
    stderr(__('Eroare'), __('ID-ul forumului sau al temei este greşit.'));

  if (have_flag('postingban'))
    stderr(__('Eroare'), __('Dvs. aveţi post ban şi nu puteţi scrie pe forum.'));

  if (User::signedUpRecently())
    stderr(__('Eroare'), "La momentul dat nu puteti inca scrie mesaje pentru ca utilizatorul dvs. este prea nou. Aceasta masura este luata pentru a discuraja utilizatorii banati sa-si creeze clone.");

  $newtopic = $forumid > 0;

  $subject = isset($_POST["subject"])?$_POST["subject"]:'';
  $replyAsSystem = ( isset($_POST["replyAsSystem"]) && ($_POST["replyAsSystem"]=='yes') );

  if ($newtopic) {
    $subject = trim($subject);

    if (!$subject)
      stderr(__('Eroare'), __('Este necesar să introduceţi denumirea temei.'));

    if ((mb_strlen($subject) > $maxsubjectlength) && (get_user_class() < UC_MODERATOR))
      stderr(__('Eroare'), __('Subiectul/Mesajul este limitat la ') . $maxsubjectlength . __(' caractere.'));
  }
  else
    $forumid = get_topic_forum($topicid) or die(__('ID-ul temei este greşit.'));

  //------ Make sure sure user has write access in forum

  $arr = get_forum_access_levels($forumid) or die(__('ID-ul forumului este greşit.'));

  if (get_user_class() < $arr["write"] || ($newtopic && get_user_class() < $arr["create"]))
    stderr(__('Eroare'), __('Accesul este interzis.'));

  $body = trim($_POST["body"]);
  $body = str_replace('[color=red]', '[color=#CC3333]', $body);

  if ($body == "")
    stderr(__('Eroare'), __('Conţinutul este gol.'));

  $userid = $CURUSER["id"];
  $subcat = (int)post('subcategory');

  if ($newtopic) {
    //---- Create topic
    $subject = sqlesc($subject);

    Q("INSERT INTO topics SET userid=$userid, forumid=$forumid, ". ($subcat?"subcat=$subcat, ":'') ."
                              subject=$subject, created=UNIX_TIMESTAMP(NOW())");
    $topicid = q_mysql_insert_id() or stderr(__('Eroare'), "No topic ID returned");
    Q('UPDATE forums_tags SET total=total+1 WHERE id='.$subcat);
    subcat_number_changed($forumid);
  }

  /**
   * Fetch topic
   */
  $topic_arr = fetchRow(
    "SELECT *
     FROM topics
     WHERE id=:topicid", array('topicid'=>$topicid)) or stderror("Topic id n/a");

  $forumid                 = $topic_arr["forumid"];
  $postRequiresModApproval = $topic_arr['mod_approval'] == 'yes';

  if ($topic_arr["locked"] == 'yes' && get_user_class() < UC_MODERATOR)
    stderr(__('Eroare'), __('Această temă este închisă.'));

  /**
    Check if last message was not posted by the same user in last hour
    True - Append to the last message
    False - Insert new
  */

  $isForumIdChat = $forumid == 18;
  $isMsgTooShort = strlen($body) < 5 && $isForumIdChat;
  if ($isMsgTooShort)
    stderr(
      __('Eroare'),
      __('Mesajul e prea scurt, te rugam sa scrii mesaje cu mai mult continut, cel putin 5 caractere.')
    );

  bbcode_check_permission($body);

  if (!$newtopic) {
    $lastComment = fetchRow(
      'SELECT posts.id, posts.userid, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(posts.added) AS elapsed,
              posts.body, posts.censored
        FROM topics
        LEFT JOIN posts ON topics.lastpost = posts.id AND posts.forumid=:forumid
        WHERE topics.id =:id',
        array('id'=>$topicid,'forumid'=>$forumid));
  }

  $is_flood_banned = AntiFloodMessages::check_flood($CURUSER["id"], $body);
  if ($is_flood_banned !== false) {
    barkk($is_flood_banned);
  }

  $first_post = mem_get('forum_first_post'.$topicid);
  if ($first_post !== false)
    $first_post = unserialize($first_post);
  else
    $first_post = array();

  $disableAppendViaBody = false;
  if (substr($body, -2) == '--') {
    $body = substr($body, 0, -2);
    $disableAppendViaBody = true;
  }

  $shouldAppendToPreviousMessage = (
      isset($lastComment) && $lastComment['userid'] == $CURUSER['id'] &&
      $lastComment['elapsed'] < 3600 &&
      $lastComment['censored'] != 'y' &&
      $lastComment['id'] != $first_post['id'] &&
      !$postRequiresModApproval &&
      !$disableAppendViaBody
  );

  if ($shouldAppendToPreviousMessage) {
    $lastCommentText = $lastComment['body'] . "\n\n" . $body;
    $lastCommentId   = $lastComment['id'];

    sendNotifications($CURUSER['username'], $topic_arr, $lastCommentId, $body);
    sendNotificationsToAdmins($CURUSER['username'], $topic_arr, $lastCommentId, $body);

    Q('UPDATE posts
       SET body = :text
       WHERE id = :id AND forumid = :forumid',
      array(
        'text'    => $lastCommentText,
        'id'      => $lastCommentId,
        'forumid' => $forumid) );

    if (isset($_POST['ajax'])) {
        require $INCLUDE . 'JSON.php';
        $json = new Services_JSON();
        echo $json->encode( array('state'=>1) );
        exit();
    }

    header("Location: ./forum.php?action=viewtopic&topicid=$topicid&page=last");
    die();
  } else {
    $newPostModApproved = $postRequiresModApproval && !isAdmin() ? 'awaiting' : 'not_needed';

    q('INSERT INTO posts
       (topicid,  userid,  added, body,  forumid,  mod_approved)
       VALUES
       (:topicid, :userid, NOW(), :body, :forumid, :modApproved)',
      array(
        'topicid'     => $topicid,
        'userid'      => $userid,
        'body'        => $body,
        'forumid'     => $forumid,
        'modApproved' => $newPostModApproved
      )
    );

    $postid = q_mysql_insert_id();

    sendNotifications($CURUSER['username'], $topic_arr, $postid, $body);
    sendNotificationsToAdmins($CURUSER['username'], $topic_arr, $postid, $body);

    Watches::startForTopic($CURUSER['id'], $topic_arr['id']);

    if ($newPostModApproved == 'awaiting') {
      q('INSERT INTO `posts_for_review`
         (post_id, forum_id)
         VALUES
         (:postId, :forumId)',
        array('postId'         => $postid,
              'forumId'        => $forumid
        )
      );
    }

    if($replyAsSystem && isModerator()) {
      q('INSERT INTO forum_admin_answer (topicid, postid) '.
                            "VALUES ('{$topicid}', '{$postid}') ");

      //------ Get first post
      $first_post = getFirstPostInTopic($topicid, $forumid);

      $pmMsg = __('Ați primit un răspuns la mesajul adresat echipei TMD.') ."[quote={$first_post['username']}]{$first_post['body']}[/quote]\r\n{$body}";

      newPM(0, $first_post['userid'], $pmMsg);

      //lock topic
      q("UPDATE topics SET locked='yes' WHERE id=$topicid");
    }

    q('UPDATE users_additional SET posts=posts+1 WHERE id = :userid', array('userid' => $userid));

    after_topic_post($postid, $topicid, true, $forumid);
  }

  //------ All done, redirect user to the post

  $headerstr = "Location: ./forum.php?action=viewtopic&topicid=$topicid&page=last";

  if (isset($_POST['ajax'])) {
      require $INCLUDE . 'JSON.php';
      $json = new Services_JSON();
      //ob_clean();
      echo $json->encode( array('state'=>1) );
      exit();
  }

  if ($newtopic)
    header($headerstr);
  else
    header("$headerstr&goto=$postid");
  die;

}