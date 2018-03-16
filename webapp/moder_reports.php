<?php
require "include/bittorrent.php";
require $WWW_ROOT."comments_inc.php";
require $WWW_ROOT."forum_inc.php";

loggedinorreturn();

if ( !(isForumModer())) {
  die();
}

if (ispost() && isset($_POST['action'])) {
	$action = $_POST['action'];
	$type = $_POST['type'];
	$id = post('id');
	$topicId = post('topicId');

	if ($action == 'ok' && ($type == 'forum' || $type == 'comment') ) {
		Q('UPDATE raportedmsg SET status="reviewed" WHERE type=:type AND postId=:postId',
				array( 'postId'=>$id, 'type'=>$type ) );
	}

	//if ($action == 'cenz' && ( ($type == 'forum' && isForumModer() && allow_censoring(array(), (int)$topicId) ) || ($type == 'comment' && isTorrentModer()) ) )
	if ($action == 'cenz' && ($type == 'forum' || $type == 'comment') ) //lasam securitata
	{
		Q('UPDATE raportedmsg SET status="reviewed" WHERE type=:type AND postId=:postId',
			array( 'postId'=>$id, 'type'=>$type ) );

		if ($type == 'forum') {
			Q('UPDATE posts SET censored="y" WHERE id=:id', array('id'=>$id) );
			write_moders_log("Postul $id cenzurat de " .  $CURUSER["username"]);
		}

		if ($type == 'comment') {
			Q('UPDATE comments SET censored="y" WHERE id=:id', array('id'=>$id) );
			write_moders_log("Comentariu $id cenzurat de " .  $CURUSER["username"]);

			postCleanCache($id);
			// Clean the cache
			//censoreComment($id);
		}
	}
	//stdfoot();
	die();
}

stdhead("User's reports");
?>
<h1>Raportarile utilizatorilor din ultimile 24 ore, cel putin 2 raportari</h1>
	<center><a href="?all_reports=1">afişează şi cele cu 1 raportare</a></center><br>
<?php

$hours = isset($_GET['all_reports']) ? 240 : 72;

$sql = 'CREATE temporary TABLE raportedMsgMax
SELECT postId,forumid,count(id) AS reports,type,MAX(date) AS date
FROM raportedmsg
WHERE date > now() - interval ' . $hours .  ' hour AND status="waiting"
GROUP BY postId HAVING reports >= 1
ORDER BY reports, date DESC';

q($sql);

$queryWhere='';

if (!isModerator() && isForumModer()) {
		$user_forums_moderator = fetchColumn('SELECT forum_category_id FROM `forum_moderators` WHERE `user_id`= '. $CURUSER['id']);
		$user_forums_moderator = implode(',', $user_forums_moderator);
		$queryWhere .= 'AND (topics.forumid IN ('. $user_forums_moderator .') )';
}

$posts = fetchAll('
SELECT raportedMsgMax.postId, raportedMsgMax.type, posts.userid, posts.body, topics.id topic_id,topics.subject, forums.name_'.get_lang().' AS forum_name,
		raportedMsgMax.reports, users.username, users.class, users_additional.comments, users_additional.posts
	FROM raportedMsgMax

	LEFT JOIN posts ON raportedMsgMax.postId = posts.id AND raportedMsgMax.forumid = posts.forumid
	LEFT JOIN topics ON posts.topicid = topics.id
	LEFT JOIN forums ON topics.forumid = forums.id
	LEFT JOIN users ON posts.userid = users.id
    LEFT JOIN users_additional ON users.id = users_additional.id
	WHERE raportedMsgMax.type="forum" '. $queryWhere .'
	ORDER BY reports DESC, date DESC
');


//TODO: query se executa, indiferent are acces or nu
$comments = fetchAll(

'SELECT raportedMsgMax.postId, raportedMsgMax.type, comments.user AS userid, torrents.name AS torrentName, comments.text,
		raportedMsgMax.reports, users.username, users.class, comments.torrent, users_additional.comments, users_additional.posts
	FROM raportedMsgMax

	LEFT JOIN comments ON raportedMsgMax.postId = comments.id
	LEFT JOIN users ON comments.user = users.id
    LEFT JOIN users_additional ON users.id = users_additional.id
	LEFT JOIN torrents ON comments.torrent = torrents.id

	WHERE raportedMsgMax.type="comment"
	ORDER BY reports DESC, date DESC
	'
);


if(isModerator())
    $posts = array_merge($posts,$comments);
else {
	$com = $pos = array();

	if(isTorrentModer())
		$com = $comments;

	if(isForumModer())
		$pos = $posts;

	$posts = array_merge($pos,$com);
}

?>


<table width="100%" cellpadding="10">
<tr>
	<td width="10">Reports</td> <td>Topic, nick, post</td> <td width="100">Action</td>
</tr>

<?php foreach($posts AS $post) : ?>

		<?php //if ( $post['type'] == 'forum' && isForumModer() && allow_censoring(array(), $post['topic_id']) ):
		if ( $post['type'] == 'forum'):
	?>
<tr>
	<td><?=$post['reports']?></td>
	<td>
			<?=$post['forum_name']?> ->
			<a href="./forum.php?action=viewtopic&topicid=<?=$post['topic_id']?>&page=p<?=$post['postId']?>#<?=$post['postId']?>"><?=esc_html($post['subject'])?></a>
	<br>
			<a href="./userdetails.php?id=<?=$post['userid']?>" target="_blank"><?=$post['username']?></a> (<?=$post['posts']?>) (<?=get_user_class_name($post['class'])?>)
			<br>
			<br>
	<?=format_comment($post['body'])?>
	</td>
	<td valign="top">
		<div class="lnk doAction" customAction="ok" customType="forum" topicId="<?=$post['topic_id']?>" customId="<?=$post['postId']?>" style="color:green;">În ordine</div>
		<br>

		<div class="lnk doAction" customAction="cenz" customType="forum" topicId="<?=$post['topic_id']?>"  customId="<?=$post['postId']?>" style="color:#CC3333;">Cenzurează</div>
	</td>
	</tr>
		<?php //elseif ($post['type'] == 'comment' && isTorrentModer()):
			elseif ($post['type'] == 'comment'):
			?>
	<tr>
	<td><?=$post['reports']?></td>
	<td>
			<a href="./details.php?id=<?=$post['torrent']?>&viewcomm=<?=$post['postId']?>#comm<?=$post['postId']?>"><?=esc_html($post['torrentName'])?></a> <br>
			<a href="./userdetails.php?id=<?=$post['userid']?>" target="_blank"><?=$post['username']?></a> (<?=get_user_class_name($post['class'])?>)
	<br>
			<br>
	<?=format_comment($post['text'])?>
	</td>
	<td valign="top">
		<div class="lnk doAction" customAction="ok" customType="comment" topicId="<?=$post['torrent']?>"  customId="<?=$post['postId']?>" style="color:green;">În ordine</div>
		<br>

		<div class="lnk doAction" customAction="cenz" customType="comment" topicId="<?=$post['torrent']?>"  customId="<?=$post['postId']?>" style="color:#CC3333;">Cenzurează</div>
	</td>
	</tr>
		<?php endif; ?>

<?php endforeach; ?>

</table>


<script>
	(function($) {
		$('.doAction').click( function() {

				var action = this.getAttribute('customAction');
				var type = this.getAttribute('customType');
				var id = this.getAttribute('customId');
				var topicId = this.getAttribute('topicId');

				console.log(this, action, type, id);
				jQuery.ajax( {
					url: '/moder_reports.php',
					type: 'POST',
					data: {action:action, type: type, id:id, topicId:topicId}
				} );


				$(this).parents('tr').remove();
			} );
	})(jQuery);
</script>

<?php
stdfoot();