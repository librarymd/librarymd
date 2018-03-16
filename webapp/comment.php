<?php
require_once("include/bittorrent.php");
require_once($INCLUDE . 'torrent_opt.php');
require $WWW_ROOT . "./comments_inc.php";

/**
tid: torrent id
cid: comment id
action: action name
**/

loggedinorreturn();

$action = esc_html($_REQUEST["action"]);

if (have_flag('postingban')) barkk("You have posting ban and can't write/edit/delete any comment.");

$moderator = (isset($CURUSER) && get_user_class() >= UC_MODERATOR);

// If cid is available the id is required
if (isset($_REQUEST['cid']) && !isset($_REQUEST['tid']) ) barkk(__('Adresa este incorectă, parametrul id este obligatoriu dar lipsește'));

// Give to Releasers special rights when on own torrent
$torrent_id = intval($_REQUEST['tid']);
$torrent_id_post = intval(post('tid'));

// If postid is set, confirm that torrentid is the same as assigned to this comment
$comment_id = intval($_REQUEST['cid']);
$comment_id_post = intval(post('cid'));
if (isset($_REQUEST['cid'])) {
	$t_torrent = fetchOne('SELECT torrent FROM comments WHERE id=:id AND torrent=:torrent',array('id'=>$comment_id,'torrent'=>$torrent_id));
	if ($t_torrent != $torrent_id) {
		barkk(__('Adresa este incorectă, comentariul nu a putut fi găsit.'));
	}
}

function commentValidateBody($text) {
	$text_parsed = format_comment($text);

	messages_die_if_spam($text,$text_parsed);
	bbcode_check_permission($text,$text_parsed);
}
function torrentGetOpt($torrentid) {
	return fetchOne('SELECT torrent_opt FROM torrents WHERE id=:id',array('id'=>$torrentid));
}
/**
@param $set boolean
@param $flag int
**/
function torrentSetFlag($torrentid,$flag,$set) {
	$torrent_opt = setflag(torrentGetOpt($torrentid), $flag, $set);
	q('UPDATE torrents SET torrent_opt=:opt WHERE id=:torrent',array('opt'=>$torrent_opt,'torrent'=>$torrentid));
	cleanTorrentCache($torrentid);
}


// Add new comment
if ($action == "add" && isPost())
{
	$torrentid = intval($_POST["tid"]);
	if (!is_valid_id($torrentid))
		stderr("Error", "Invalid ID $torrentid.");

	$torrent = fetchRow("SELECT name, torrent_opt, added, moder_status FROM torrents WHERE id = :tid",
						array('tid'=>$torrentid));

	if (!$torrent) stderr("Error", "No torrent with ID $torrentid.");

	if ( (torrent_have_flag('is_comment_locked',$torrent['torrent_opt']) || torrent_status_downloadable($torrent) !== true ) && !isTorrentModer()) {
		barkk('Adaugarea comentariilor la acest torrent a fost interzisă.');
	}

	$text = trim($_POST["text"]);
	if (!$text) stderr("Error", "Comment body cannot be empty!");

	$is_flood_banned = AntiFloodMessages::check_flood($CURUSER['id'], $text);
	if ($is_flood_banned !== false) {
		barkk($is_flood_banned);
	}

	commentValidateBody($text);

	/**
	Check if last message was not posted by the same user in last hour
	True - Append to the last message
	False - Insert new
	*/
	$lastComment = fetchRow("SELECT id, user, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(added) AS elapsed, text
						   FROM comments WHERE torrent =:torrent ORDER BY id DESC LIMIT 1",array('torrent'=>$torrentid));
	if ($lastComment['user'] == $CURUSER['id'] && $lastComment['elapsed'] < 3600) {
		$lastCommentText = $lastComment['text'] . "\n\n" . $text;
		$lastCommentId = $lastComment['id'];

		Q("UPDATE comments SET text=:text WHERE id=:id AND torrent=:tid",
		array('id'=>$lastCommentId, 'text'=>$lastCommentText, 'tid'=>$torrentid) );

		postCleanCache($lastCommentId,$torrentid);

		header("Refresh: 0; url=details.php?id=$torrentid&viewcomm=$lastCommentId#comm$lastCommentId");
		die();
	} else {
		q("INSERT INTO comments (user, torrent, added, text) VALUES (:userid,:torrentid,NOW(),:text)",
			array('userid'=>$CURUSER["id"], 'torrentid'=>$torrentid, 'text'=>$text ));
		$newid = q_mysql_insert_id();
		event_insert_comment($newid);

		postCleanCache($newid,$torrentid);

		q("UPDATE torrents SET comments = comments + 1, lastcomment = :newid WHERE id = :torrentid",
			array('newid'=>$newid, 'torrentid'=> $torrentid) );

		cleanTorrentCache($torrentid);

		// Update watch index
		q("UPDATE watches SET lastThreadMsg=:newid WHERE thread=:torrentid AND type='torrent'",
			array('newid'=>$newid, 'torrentid'=> $torrentid));

		header("Refresh: 0; url=details.php?id=$torrentid&viewcomm=$newid#comm$newid");

		die;
	}
}

/**
	Edit a comment
*/
if ($action == "edit" && is_numeric($_GET["cid"]) && is_numeric($_GET["tid"])) {
	$commentid = $_GET["cid"];
	$torrentid = $_GET["tid"];

	$arr = fetchRow("SELECT c.*,UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(c.added) AS added_seconds_ago, t.name
			  FROM comments AS c
			  LEFT JOIN torrents AS t ON c.torrent = t.id
			  WHERE c.id=:id AND c.torrent = :torrent",array('id'=>$commentid,'torrent'=>$torrentid));

	if (!$arr) stderr("Error", "Invalid ID $commentid.");

	if ($arr['user'] != $CURUSER['id'] && get_user_class() < UC_MODERATOR) {
		stderr("Error", "Permission denied.");
	}
	//86400 = 1 days
	if (!isAdmin() && $arr['added_seconds_ago'] > 86400) {
		stderr("Error", __("Nu poți edita un mesaj mai vechi de 1 zi.") );
	}

	// Deny editing if it was already edited by other user(it must moder)
	if ($arr['user'] == $CURUSER['id'] && ($arr['editedby'] != 0 && $arr['editedby'] != $CURUSER['id']) ) {
		stderr("Error", "Permission denied.");
	}

	if (isPost()) {
		$text = $_POST["text"];
		$returnto = $_POST["returnto"];

		if ($text == "")
		stderr("Error", "Comment body cannot be empty!");

		commentValidateBody($text);

		// Get lastest comment ID from current torrent
		$lastest_comment_id = fetchOne("SELECT id FROM comments WHERE torrent=:torrent ORDER BY added DESC LIMIT 1",array('torrent'=>$arr['torrent']));

		// If added time is smaller than 5 minutes then don't show any edited notice..
		// If the edited comment is lastes and was added less than 3 hours ago then don't show any edited notice..
		if ($arr['added_seconds_ago'] > 300 && ($lastest_comment_id['id'] != $arr['id'] || $arr['added_seconds_ago'] > 21600)) {
			q("UPDATE comments SET text=:text, editedat=NOW(), editedby=:userid WHERE id=:id AND torrent=:torrent",
				array('text'=>$text, 'userid'=>$CURUSER['id'], 'id'=>$commentid, 'torrent'=>$arr['torrent'] ) );
		} else {
			q("UPDATE comments SET text=:text WHERE id=:id AND torrent=:torrent",
				array('text'=>$text, 'id'=>$commentid, 'torrent'=>$arr['torrent'] ) );
		}

		postCleanCache($commentid,$arr['torrent']);

		if ($returnto) redirect($returnto);
		else {
			redirect('./');
		}
	}

 	stdhead("Edit comment to \"" . $arr["name"] . "\"");

	print("<h1>Edit comment to \"" . esc_html($arr["name"]) . "\"</h1><p>\n");
        print("<table border=0 align=center><tr><td align=center>\n");
	print('<form method="post" action="comment.php?action=edit&amp;cid='.$commentid.'&amp;tid='.$torrentid.'" name="comment">');
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . esc_html($_SERVER["HTTP_REFERER"]) . "\" />\n");
	print("<input type=\"hidden\" name=\"cid\" value=\"$commentid\" />\n");
	print('<input type="hidden" name="tid" value="'.$torrentid.'" />');
	print("<textarea name=\"text\" rows=\"10\" cols=\"60\">" . esc_html($arr["text"]) . "</textarea></p>\n");
	print("<script type='text/javascript' src='js/torrent.js'></script>".
	  	  "<script type='text/javascript'>".
          "//Attach onKeyPress\n".
	  	  "LoadEvent();".
	      "</script>");

	print("<p><input type=\"submit\" class=btn value=\"Do it!\" id='abc'/></p></form>\n");
        print("</td><td>");
        draw_smiles();
	print("</td></tr></table>");


	stdfoot();
  	die;
}
// Stergem un coment
elseif ($action == "delete" && is_numeric($_GET["cid"]) && is_numeric($_GET["tid"]))
{
	if (get_user_class() < UC_MODERATOR)
		stderr("Error", "Permission denied.");

	$commentid = intval($_GET["cid"]);
	$torrentid = intval($_GET["tid"]);

	$sure = $_GET["sure"];

	if (!$sure)	{
		$referer = $_SERVER["HTTP_REFERER"];
		stderr("Delete comment", "You are about to delete a comment. Click\n" .
			"<a href=?action=delete&cid=$commentid&tid=$torrentid&sure=1" .
			($referer ? "&returnto=" . esc_html($referer) : "") .
			">here</a> if you are sure.");
	}

	event_delete_comment($commentid);
	q("DELETE FROM comments WHERE id=:id AND torrent=:torrent",
		array('id'=>$commentid, 'torrent'=>$torrentid) );

	torrentRegenerateCommentsCount($torrentid);
	torrent_updatelastcomment($torrentid);
	postCleanCache($commentid, $torrentid);

	$returnto = $_GET["returnto"];

	if ($returnto) redirect($returnto);
	else redirect("$DEFAULTBASEURL/");
}
elseif ( $action == "lockcomments" && ($moderator) && is_numeric($_POST["tid"]) ) {
	torrentSetFlag($torrent_id_post,$conf_torrent_opt['is_comment_locked'], true);

	write_moders_log("Scrierea comentariile la [url=./details.php?id=$torrentid]acest[/url] torrent a fost blocat de " .  $CURUSER["username"]);

	redirect('details.php?id='.$torrent_id);
}
elseif ($action == "unlockcomments" && ($moderator) && is_numeric($_POST["tid"]) ) {
	torrentSetFlag($torrent_id_post,$conf_torrent_opt['is_comment_locked'], false);

	write_moders_log("Scrierea comentariile la [url=./details.php?id=$torrentid]acest[/url] torrent a fost deblocat de " .  $CURUSER["username"]);

	redirect('details.php?id='.$torrent_id);
}
elseif ($action == "hiddecomments" && $moderator && is_numeric($_POST["tid"]) ) {
	torrentSetFlag($torrent_id_post,$conf_torrent_opt['is_comments_hidden'], true);

	write_moders_log("Comentariile la [url=./details.php?id=$torrentid]acest[/url] torrent au fost ascunse de " .  $CURUSER["username"]);

	redirect('details.php?id='.$torrent_id);
}
elseif ($action == "unhiddecomments" && $moderator && is_numeric($_POST["tid"]) ) {
	torrentSetFlag($torrent_id_post,$conf_torrent_opt['is_comments_hidden'], false);

	write_moders_log("Comentariile la [url=./details.php?id=$torrentid]acest[/url] torrent au fost scoase din ascunzis de " .  $CURUSER["username"]);

	redirect('details.php?id='.$torrent_id);
}

// Censore
if ($action == 'censor' && ($moderator) && $torrent_id_post && $comment_id_post ) {
	Q("UPDATE comments SET censored='y' WHERE id=:id AND torrent=:tid",
		array('id'=>$comment_id_post, 'tid'=>$torrent_id_post));

	if (q_mysql_affected_rows() == 0) die('Comment not found');

	postCleanCache($comment_id_post,$torrent_id_post);

	write_moders_log("[url=./details.php?id=$torrent_id_post&viewcomm=$comment_id_post#comm$comment_id_post]Comentariu[/url] cenzurat de " .  $CURUSER["username"]);

	die(1);
}


// Uncensore
if ($action == 'uncensore' && ($moderator) && $torrent_id && $comment_id ) {
	// todo
	Q("UPDATE comments SET censored='n' WHERE id=:id AND torrent=:tid",
		array('id'=>$comment_id, 'tid'=>$torrent_id));

	if (q_mysql_affected_rows() == 0) die('Comment not found');

	postCleanCache($comment_id,$torrent_id);

	write_moders_log("[url=./details.php?id=$torrent_id&viewcomm=$comment_id#comm$comment_id]Comentariu[/url] decenzurat de " .  $CURUSER["username"]);

	redirect("./details.php?id=$torrent_id&viewcomm=$comment_id#comm$comment_id");
}

if ($action == 'postRaport' && $torrent_id && $comment_id) {
		$userid = $CURUSER['id'];
		$post = fetchRow("SELECT * FROM comments WHERE id=:id AND torrent=:tid",
					array('id'=>$comment_id, 'tid'=>$torrent_id) );

		if ( !$post || !$post['id'] || $post['censored'] == 'y' ) {
			die('already censored or non existent');
			return;
		}
		/**
			Check if this user have not raported this message already
		*/
		$already = q_singleval('SELECT id FROM raportedmsg WHERE postId=:postId AND userId=:userId AND type="comment"',
			array('postId'=>$comment_id,'userId'=>$userid));
		if ($already) {
			die('already');
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

		Q('INSERT INTO raportedmsg SET date=NOW(), type="comment", postId=:postid, userId=:userid, status="waiting"',
			array('postid'=>$comment_id,'userid'=>$userid)
		);
		Q('UPDATE raportedmsg SET status="waiting" WHERE type="comment" AND postId=:postid', array('postid'=>$comment_id) );
		die('ok');
}

stderr("Error", "Unknown action $action");
die;
?>