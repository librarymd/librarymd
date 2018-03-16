<?php
require "../include/bittorrent.php";
require $WWW_ROOT."forum_inc.php";

loggedinorreturn();

if (!isAdmin()) die();

if (isPost()) {
  $approved = $_POST['approve'] == 'yes' ? 'yes' : 'no';
  $postId = (int)$_POST['post_id'];
  $reason = isset($_POST['reason']) ? $_POST['reason'] : '';

  $postForReview = fetchRow('
    SELECT posts_for_review.forum_id, posts_for_review.post_id, posts.userid, posts.topicid, topics.subject
    FROM posts_for_review
    LEFT JOIN posts ON posts_for_review.post_id = posts.id AND posts_for_review.forum_id = posts.forumid
    LEFT JOIN topics ON posts.topicid = topics.id
    WHERE post_id = :postid AND approved = "todo"', array('postid' => $postId));

  q('UPDATE posts
    SET mod_approved = :approved
    WHERE id = :postid AND forumid = :forumid',
    array('approved' => $approved, 'postid' => $postId, 'forumid' => $postForReview['forum_id'])
  );

  q('UPDATE posts_for_review
    SET approved = :approved, reviewer_user_id = :moderator, reason = :reason
    WHERE post_id = :postid',
    array('postid' => $postId, 'approved' => $approved, 'moderator' => $CURUSER['id'], 'reason' => $reason)
  );

  update_topic_last_post($postForReview['topicid'], true);

  // Send notification to the user about his message being approved or not
  $topicBit = "[b][url=/forum.php?action=viewtopic&topicid=$postForReview[topicid]&page=p$postId#$postId]$postForReview[subject][/url][/b]";
  $approvalText = "a fost acceptat cu succes. Mesajul a devenit vizibil in topic. \n\nVă mulțumim pentru așteptare. Moderarea are loc pentru a crea un spațiu pozitiv și constructiv de discuții pentru toți.";
  $refusalText = "respins din motivul urmator: \n[quote]".$reason."[/quote]\n\nVă rugăm să consultați regulile forumului. \n\nVă mulțumim pentru înțelegere și ne pare rău pentru incomodotitățile provocate.";
  $acceptOrRefuseBit = $approved == 'yes' ? $approvalText : $refusalText;
  newNotification(
    $postForReview['userid'],
    'Mesajul scris in topicul '.$topicBit . ' a fost ' . $acceptOrRefuseBit
  );
}

stdhead("Forum messages to moderate");

$postsForReview = fetchAll('
  SELECT posts_for_review.reviewer_user_id, posts_for_review.post_id,
         posts.userid, posts.body, posts.added, posts.topicid,
         users.username,
         topics.subject, forums.name_ro as forumName
  FROM posts_for_review
  LEFT JOIN posts ON posts_for_review.post_id = posts.id AND posts_for_review.forum_id = posts.forumid
  LEFT JOIN users ON posts.userid = users.id
  LEFT JOIN topics ON posts.topicid = topics.id
  LEFT JOIN forums ON forums.id = topics.forumid
  WHERE approved = "todo"
  ORDER BY post_id ASC
  LIMIT 100');
?>

<h1>Mesaje de pe forum din topicurile de moderare</h1>

<table width="100%" cellpadding="10">
<tr>
  <td width="100">Utilizator</td><td>Mesaj spre aprobare</td><td width="300">Action</td>
</tr>

<?php foreach ($postsForReview as $postForReview):
  $postId     = $postForReview['post_id'];
  $addedUnix  = sql_timestamp_to_unix_timestamp($postForReview["added"]);
  $added      = $postForReview["added"] . ' (' . (get_elapsed_time($addedUnix)) . ')';
?>

<tr>
  <td><?=esc_html($postForReview['username'])?></td>
  <td>
    <?php
      $topicUrl = "/forum.php?action=viewtopic&topicid=$postForReview[topicid]&page=p$postId#$postId";
    ?>
    <?=esc_html($postForReview['forumName'])?> / <a href="<?=$topicUrl?>"><?=esc_html($postForReview['subject'])?></a><br/>
    <?=$added?>
    <br/><br/>
    <?=esc_html($postForReview['body'])?>
  </td>
  <td>
    <form method="POST">
      <input type="hidden" name="post_id" value="<?=$postForReview['post_id']?>">
      <input type="hidden" name="approve" value="yes">
      <input type="submit" name="submit" value="Approve">
    </form>
    <hr/>
    or<br/>
    <br/>
    <form method="POST">
      <input type="hidden" name="post_id" value="<?=$postForReview['post_id']?>">
      <input type="hidden" name="approve" value="no">
      <textarea name="reason" placeholder="Reason of refusal" style="width: 100%; height: 50px;"></textarea>
      <br/>
      <input type="submit" name="submit" value="Disapprove">
    </form>
  </td>
</tr>

<?php endforeach; ?>

</table>
<br/>
<br/>

<?php
stdfoot();
?>