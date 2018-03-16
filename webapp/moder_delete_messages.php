<?php
require_once("include/bittorrent.php");

loggedinorreturn();

if (get_user_class() < UC_MODERATOR) {
  die();
}

stdhead("Spam deleter");

if (ispost()) {

	$type = post('type');
	$username = post('username');

	list($userid,$user_class) = fetchRow('SELECT id,class FROM users WHERE username=:username', array('username'=>$username) );

	if (!is_numeric($userid)) {
		stderr("Error", "Utilizator inexistent.", true);
		die();
	}

	if ($user_class >= UC_RELEASER) {
		stderr("Error", "Nu se poate de sters comentariile releaserilor si mai sus.", true);
		die();
	}

	if ($type == 'comments') {
		require $WWW_ROOT . "./comments_inc.php";

		$torrents = fetchAll('SELECT torrent,id FROM comments WHERE user=:userid AND added >= NOW() - INTERVAL 24 hour', array('userid'=>$userid) );

		foreach($torrents AS $torrent) {
			event_delete_comment($torrent['id']);
		}

		q('DELETE FROM comments WHERE user=:userid AND added >= NOW() - INTERVAL 24 hour', array('userid'=>$userid) );
		$total = mysql_affected_rows();

		foreach($torrents AS $torrent) {
 			$last_id = torrent_updatelastcomment( $torrent['torrent'] );
			torrentRegenerateCommentsCount($torrent['torrent']);
			cleanTorrentCache($torrent['torrent']);
			$_page = getCommentPage($torrent['torrent'],$last_id);

			cleanCommentsCache($torrent['torrent'],$_page);
		}

		if ($total) {
			write_user_modcomment($userid,"Comments($total) from the last 24 hours has been deleted");
		}

		$type_human = 'Comentarii';
	}

	if ($type == 'posts') {
		require $WWW_ROOT . "./forum_inc.php";
		$topics = fetchAll('SELECT topicid, forumid FROM posts WHERE userid=:userid AND added >= NOW() - INTERVAL 24 hour GROUP BY forumid', array('userid'=>$userid) );

		q('DELETE FROM posts WHERE userid=:userid AND added >= NOW() - INTERVAL 24 hour', array('userid'=>$userid));

		$total = mysql_affected_rows();
		foreach($topics AS $topic) {
			update_topic_last_post( $topic['topicid'] );
			update_topic_posts_count( $topic['topicid'], $topic['forumid'] );
		}
		q('DELETE FROM topics WHERE userid=:userid AND created >= UNIX_TIMESTAMP(NOW() - INTERVAL 24 hour)', array('userid'=>$userid));
		$totalTopics = mysql_affected_rows();
		if ($total) {
			write_user_modcomment($userid,"Posts($total) from the last 24 hours has been deleted");
		}
		if ($totalTopics) {
			write_user_modcomment($userid,"Topics($total) from the last 24 hours has been deleted");
		}
		$type_human = 'Posturi';
	}
	if (!isset($type_human)) die('Unknow type');

	write_moders_log("$type_human ($total) din ultimile 24 ore a utilizatorului [url=/userdetails.php?id={$userid}]{$username}[/url] au fost sterse de catre [url=/userdetails.php?id={$CURUSER['id']}]" . $CURUSER['username'] .'[/url]');
	if ($totalTopics)
		write_moders_log("Topics ($total) din ultimile 24 ore a utilizatorului [url=/userdetails.php?id={$userid}]{$username}[/url] au fost sterse de catre [url=/userdetails.php?id={$CURUSER['id']}]" . $CURUSER['username'] .'[/url]');
?>
	<font color=green>
		<?="$total  ".strtolower($type_human) . " a utilizatorului $username au fost sterse"?></br>
		<?php
			if ($totalTopics)
				echo "$totalTopics  ".strtolower($type_human) . " a utilizatorului $username au fost sterse";
		?>

	</font>
	<br><br>
<?php
}
?>
<h2>Sterge toate comentariile din torrente scrise de un utilizator in ultimile 24 ore.</h2>
<form method="POST" action="moder_delete_messages.php">
<input type="hidden" name="type" value="comments">
<table cellpadding="10">
<tr>
	<td>Username:</td>
	<td><input type="text" name="username" value=<?=esc_html(get('username'))?>></td>
</tr>
<tr>
	<td colspan="2" align="center">
		<input type="submit" value="Sterge comentariile">
	</td>
</tr>
</table>
</form>
<br>
<h2>Sterge toate posturile de pe forum scrise de un utilizator in ultimile 24 ore.</h2>
<form method="POST" action="moder_delete_messages.php">
<input type="hidden" name="type" value="posts">
<table cellpadding="10">
<tr>
	<td>Username:</td>
	<td><input type="text" name="username" value=<?=esc_html(get('username'))?>></td>
</tr>
<tr>
	<td colspan="2" align="center">
		<input type="submit" value="Sterge posturile">
	</td>
</tr>
</table>
</form>

<?php
	stdfoot();
?>