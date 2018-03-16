<?php
require "include/bittorrent.php";
loggedinorreturn();
allow_only_local_referer_domain();

$userid = (isset($_GET['id']))     ? (int)$_GET['id'] : '';
$action = (isset($_GET['action'])) ? $_GET['action']  : '';

if (!$userid)
	$userid = $CURUSER['id'];

if (!is_valid_id($userid))
	stderr(__('Eroare'), __('ID incorect') ." $userid.");

if ($userid != $CURUSER["id"]) stderr(__('Eroare'), __('Acces interzis.'));

$res = q("SELECT * FROM users WHERE id=:userid", array('userid' => $userid));
$user = mysql_fetch_array($res) or stderr(__('Eroare'), __('Nu există utilizator cu ID') ." $userid.");

// action: add -------------------------------------------------------------

if ($action == 'add') {
  $targetid = (int)$_GET['targetid'];
  $type = esc_html($_GET['type']);

  if ($type == 'block')
    q("INSERT IGNORE INTO blocks VALUES (0,:userid, :targetid)", array('userid'=>$userid, 'targetid'=>$targetid));
  else
    q("INSERT IGNORE INTO friends VALUES (0,:userid, :targetid)", array('userid'=>$userid, 'targetid'=>$targetid));
  redirect($_SERVER['HTTP_REFERER']);
  die;
}

// action: delete ----------------------------------------------------------

if ($action == 'delete') {
	$targetid = $_GET['targetid'];
	$sure = $_GET['sure'];
	$type = esc_html($_GET['type']);

  if ($type == 'friend') $cine = 'prieten';
  if ($type == 'block') $cine = 'utilizator blocat';

  if (!is_valid_id($targetid))
		stderr(__('Eroare'), __('ID incorect'));

  if (!$sure)
    stderr(__('Şterge ') . __($cine),__('Chiar doriţi să ştergeţi un ') . __($cine)."? ". __('Apasă') ."\n" .
    	"<a href=?id=$userid&action=delete&type=$type&targetid=$targetid&sure=1>". __('aici') ."</a>". __(' dacă sunteţi sigur.'));

  if ($type == 'friend') {
    q("DELETE FROM friends WHERE userid=$userid AND friendid=$targetid");
    q("DELETE FROM friends WHERE userid=$targetid AND friendid=$userid");
    $frag = "friends";
  }
  elseif ($type == 'block') {
    q("DELETE FROM blocks WHERE userid=$userid AND blockid=$targetid");
    if (mysql_affected_rows() == 0)
      stderr(__('Eroare'), __('Nu există utilizator blocat cu aşa ID'));
    $frag = "blocks";
  }
  else
    stderr(__('Eroare'), 'Unknown type');

  header("Location: /friends.php?id=$userid#$frag");
  die;
}

// main body  -----------------------------------------------------------------

stdhead(__('Lista personală pentru ') . $user['username']);

print("<br><p><table class='main mCenter' border=0 cellspacing=0 cellpadding=0>".
"<tr><td class=embedded><h1 style='margin:0px'>". __('Lista personală pentru ') ."{$user['username']}". get_user_icons($CURUSER, true) ."</h1></td></tr></table></p>\n");

print("<table class='main mCenter' width=860 border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>");

/**
 * Friend list ----------------------------------------------------------------
 */

print("<h2 align=left><a name=\"friends\">". __('Lista prietenilor') ."</a></h2>\n");

print("<table width=860 border=1 cellspacing=0 cellpadding=5><tr><td>");

$i = 0;
$res = q("SELECT f.friendid as id, u.username AS name, u.gender, u.class, u.avatar, u.user_opt, u.avatar_version, u.title, u.donor, u.warned, u.enabled, u_du.last_access
	FROM friends AS f
	LEFT JOIN users as u ON f.friendid = u.id
	LEFT JOIN users_down_up AS u_du ON f.friendid = u_du.id
	WHERE userid=$userid ORDER BY name");
if(mysql_num_rows($res) == 0)
	$friends = "<em>". __('Nu ai nici un prieten în lista personală') ."</em>";
else
	while ($friend = mysql_fetch_array($res)) {
    $title = $friend["title"];
		if (!$title)
	    $title = get_user_class_name($friend["class"]);
    $body1 = "<a href=userdetails.php?id=" . $friend['id'] . (getUserGenderColor($friend['gender']))."><b>" . $friend['name'] . "</b></a>" .
    	get_user_icons($friend) . " ($title)<br><br>". __('ultima vizită la') . " " . $friend['last_access'] .
    	'<br>(' . get_elapsed_time(sql_timestamp_to_unix_timestamp($friend['last_access'])) . ')';
		$body2 = "<br><a href=friends.php?id=$userid&action=delete&type=friend&targetid=" . $friend['id'] . ">". __('Şterge') ."</a>" .
			"<br><br><a href=sendmessage.php?receiver=" . $friend['id'] . ">". __('Trimite Mesaj') ."</a>";

		if ($CURUSER["avatars"] == 'yes' && $friend["avatar"] == 'yes') {
			$avatar = avatarWww($friend['id'],$friend['avatar_version']);
		} else $avatar = "/pic/default_avatar.gif";

     if ($i % 2 == 0)
    	print("<table width=100% style='padding: 0px'><tr><td class=bottom style='padding: 5px' width=50% align=center>");
    else
    	print("<td class=bottom style='padding: 5px' width=50% align=center>");
    print("<table class=main width=100% height=75px>");
    print("<tr valign=top><td width=75 align=center style='padding: 0px'>" .
			($avatar ? "<div style='width:75px;height:75px;overflow: hidden'><img width=75px src=\"$avatar\"></div>" : ""). "</td><td>\n");
    print("<table class=main>");
    print("<tr><td class=embedded style='padding: 5px' width=80%>$body1</td>\n");
    print("<td class=embedded style='padding: 5px' width=20%>$body2</td></tr>\n");
    print("</table>");
		print("</td></tr>");
		print("</td></tr></table>\n");
    if ($i % 2 == 1)
			print("</td></tr></table>\n");
		else
			print("</td>\n");
		$i++;
	}
if ($i % 2 == 1) print("<td class=bottom width=50%>&nbsp;</td></tr></table>\n");

if (isset($friends)) print($friends);
print("</td></tr></table>\n");

/**
 * Black list -----------------------------------------------------------------
 */

$res = q("SELECT b.blockid as id, u.username AS name, u.donor, u.warned, u.enabled, u.last_access, u.user_opt FROM blocks AS b LEFT JOIN users as u ON b.blockid = u.id WHERE userid=$userid ORDER BY name");
if(mysql_num_rows($res) == 0)
	$blocks = "<em>". __('Nu ai nici un utilizator în lista neagră.') ."</em>";
else
{
	$i = 0;
	$blocks = "<table width=100% cellspacing=0 cellpadding=0>";
	while ($block = mysql_fetch_array($res))
	{
		if ($i % 6 == 0)
			$blocks .= "<tr>";
    	$blocks .= "<td style='border: none; padding: 4px; spacing: 0px;'>[<font class=small><a href=friends.php?id=$userid&action=delete&type=block&targetid=" .
				$block['id'] . ">D</a></font>] <a href=userdetails.php?id=" . $block['id'] . "><b>" . $block['name'] . "</b></a>" .
				get_user_icons($block) . "</td>";
		if ($i % 6 == 5)
			$blocks .= "</tr>";
		$i++;
	}
	print("</table>\n");
}
print("<br><br>");
print("<table class='main mCenter' width=860 border=0 cellspacing=0 cellpadding=10><tr><td class=embedded>");
print("<h2 align=left><a name=\"blocks\">". __('Lista neagră') ."</a></h2></td></tr>");
print("<tr><td style='padding: 10px;background-color: #ECE9D8'>");
print("$blocks\n");
print("</td></tr></table>\n");
print("</td></tr></table>\n");
print("<p class='center'><a href=users.php><b>". __('Caută utilizatori') ."</b></a></p>");
stdfoot();
?>
