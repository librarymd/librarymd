<?php
require "include/bittorrent.php";
loggedinorreturn();

$userid = (int)$_GET["id"];

$userlang = get_lang();

if (!is_valid_id($userid)) stderr(__('Eroare'), "Invalid ID");

$page = isset($_GET["page"])?$_GET["page"]:'';

$action = isset($_GET["action"])?$_GET["action"]:'';
$action = esc_html($action);

//-------- Global variables

$perpage = 25;

//-------- Action: View posts
$userIsMe = ($userid == $CURUSER["id"]);

if ($action == "viewposts") {
	redirect('/userhistory_posts.php?id='.$userid);
}

if ($action == "viewposts")
{
	$select_is = "COUNT(DISTINCT p.id)";

	$from_is = "posts AS p JOIN topics as t ON p.topicid = t.id JOIN forums AS f ON t.forumid = f.id";

	$where_is = "p.userid = $userid AND f.minclassread <= " . $CURUSER['class'];

	$order_is = "p.id DESC";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is";

	$res = q($query);

	$arr = mysql_fetch_row($res) or stderr(__('Eroare'), "No posts found");

	$postcount = $arr[0];

	//------ Make page menu

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $postcount, "userhistory.php?action=viewposts&id=$userid&");

	//------ Get user data

	$res = q("SELECT username, donor, warned, enabled, user_opt FROM users WHERE id=:id", array('id' => $userid));

	if (mysql_num_rows($res) == 1)
	{
  	$arr = mysql_fetch_assoc($res);

	  $subject = "<a href=userdetails.php?id=$userid><b>$arr[username]</b></a>" . get_user_icons($arr, true);
	}
	else
	    $subject = "unknown[$userid]";

	//------ Get posts

	// For myself show topics where new posts has been appear from last visit
	if ($userIsMe) {
		$from_is = "posts AS p JOIN topics as t ON p.topicid = t.id JOIN forums AS f ON t.forumid = f.id LEFT JOIN readposts as r ON p.topicid = r.topicid AND p.userid = r.userid";
		$select_is = "f.id AS f_id, f.name_$userlang as name, t.id AS t_id, t.subject, t.lastpost, r.lastpostread, p.*";
	} else {
 		$from_is = "posts AS p JOIN topics as t ON p.topicid = t.id JOIN forums AS f ON t.forumid = f.id";
 		$select_is = "f.id AS f_id, f.name_$userlang as name, t.id AS t_id, t.subject, t.lastpost, p.*";
 	}


	$query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";

	$res = q($query);

	if (mysql_num_rows($res) == 0) stderr(__('Eroare'), "No posts found");

	stdhead("Posts history");

	print("<h1>Post history for $subject</h1>\n");

	if ($postcount > $perpage) echo $pagertop;

	//------ Print table

	begin_main_frame();

	begin_frame();

	while ($arr = mysql_fetch_assoc($res))
	{
	    $postid = $arr["id"];

	    $posterid = $arr["userid"];

	    $topicid = $arr["t_id"];

	    $topicname = esc_html($arr["subject"]);

	    $forumid = $arr["f_id"];

	    $forumname = $arr["name"];

		if ($userIsMe) {
	    	$newposts = ($arr["lastpostread"] < $arr["lastpost"]) && $CURUSER["id"] == $userid;
	    } else {
	    	$newposts = false;
	    }

	    $added = $arr["added"] . " GMT (" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . ')';

	    print("<p class=sub><table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>
	    $added&nbsp;--&nbsp;<b>Forum:&nbsp;</b>
	    <a href=./forum.php?action=viewforum&forumid=$forumid>$forumname</a>
	    &nbsp;--&nbsp;<b>Topic:&nbsp;</b>
	    <a href=./forum.php?action=viewtopic&topicid=$topicid>$topicname</a>
      &nbsp;--&nbsp;<b>Post:&nbsp;</b>
      #<a href=./forum.php?action=viewtopic&topicid=$topicid&page=p$postid#$postid>$postid</a>" .
      ($newposts ? " &nbsp;<b>(<font color=red>NEW!</font>)</b>" : "") .
	    "</td></tr></table></p>\n");

	    begin_table(true);

	    $body = format_comment($arr["body"]);

	    if (is_valid_id($arr['editedby']))
	    {
        	$subres = q("SELECT username FROM users WHERE id=$arr[editedby]");
	        if (mysql_num_rows($subres) == 1)
	        {
	            $subrow = mysql_fetch_assoc($subres);
	            $body .= "<p><font size=1 class=small>Last edited by <a href=userdetails.php?id=$arr[editedby]><b>$subrow[username]</b></a> at $arr[editedat] GMT</font></p>\n";
	        }
	    }

	    print("<tr valign=top><td class=comment>$body</td></tr>\n");

	    end_table();
	}

	end_frame();

	end_main_frame();

	if ($postcount > $perpage) echo $pagerbottom;

	stdfoot();

	die;
}

//-------- Action: View comments

if ($action == "viewcomments")
{
	$select_is = "COUNT(*)";

	// LEFT due to orphan comments
	$from_is = "comments AS c LEFT JOIN torrents as t
	            ON c.torrent = t.id";

	$where_is = "c.user = $userid";
	$order_is = "id DESC";

	$res = q("SELECT comments FROM users_additional WHERE id=:id", array('id' => (int)$userid));

	$arr = mysql_fetch_row($res) or stderr(__('Eroare'), "No comments found");

	$commentcount = $arr[0];

	//------ Make page menu

	list($pagertop, $pagerbottom, $limit) = pager($perpage, $commentcount, "userhistory.php?action=viewcomments&id=$userid&");

	//------ Get user data

	$res = q("SELECT username, donor, warned, enabled, user_opt FROM users WHERE id=$userid");

	if (mysql_num_rows($res) == 1)
	{
		$arr = mysql_fetch_assoc($res);

	  $subject = "<a href=userdetails.php?id=$userid><b>$arr[username]</b></a>" . get_user_icons($arr, true);
	}
	else
	  $subject = "unknown[$userid]";

	//------ Get comments

	$select_is = "t.name, c.torrent AS t_id, c.id, c.added, c.text";

	$query = "SELECT $select_is FROM $from_is WHERE $where_is ORDER BY $order_is $limit";

	$res = q($query);

	if (mysql_num_rows($res) == 0) stderr(__('Eroare'), __('Nici un comentariu găsit'));

	stdhead(__('Istoria comentariilor'));

	print("<h1>". __('Istoria comentariilor pentru') ." $subject</h1>\n");

	if ($commentcount > $perpage) echo $pagertop;

	//------ Print table

	begin_main_frame();

	begin_frame();

	while ($arr = mysql_fetch_assoc($res))
	{

		$commentid = $arr["id"];

	  $torrent = $arr["name"];

    // make sure the line doesn't wrap
	  if (strlen($torrent) > 55) $torrent = substr($torrent,0,52) . "...";

	  $torrentid = $arr["t_id"];

	  $added = $arr["added"] . " GMT (" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . ')';

		print("<p class=sub><table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>".
			"$added&nbsp;---&nbsp;<b>". __('Torrent') .":&nbsp;</b>".
			($torrent?("<a href=/details.php?id={$torrentid}>$torrent</a>"):" [". __('Şters') ."] ").
			"&nbsp;---&nbsp;<b>". __('Comentariul') .":&nbsp;</b>#<a href=/details.php?id={$torrentid}&viewcomm={$commentid}#comm{$commentid}>{$commentid}</a>
	  </td></tr></table></p>\n");

	  begin_table(true);

	  $body = format_comment($arr["text"]);

	  print("<tr valign=top><td class=comment>$body</td></tr>\n");

	  end_table();
	}

	end_frame();

	end_main_frame();

	if ($commentcount > $perpage) echo $pagerbottom;

	stdfoot();

	die;
}

//-------- Handle unknown action

if ($action != "")
	stderr("History Error", "Unknown action '$action'.");

//-------- Any other case

stderr("History Error", "Invalid or no query.");

?>
