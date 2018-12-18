<?php
require "include/bittorrent.php";
require "include/torrent_opt.php";
require $INCLUDE . "/classes/forum.php";

loggedinorreturn();

function bark($msg) {
  stdhead();
  stdmsg(__('Eroare'), $msg);
  stdfoot();
  exit;
}

function maketable($res,$count) {
    global $torrents_all, $id;
  $ret = "<table class=main border=1 cellspacing=0 cellpadding=5>" .
    "<tr><td class=colhead align=center>". __('Tip') ."</td><td class=colhead>". __('Nume') ."</td><td class=colhead align=center>". __('Mărime') ."</td><td class=colhead align=center><img src=pic/arrowup.gif></td><td class=colhead align=center><img src=pic/arrowdown.gif></td><td class=colhead align=center>". __('Încărcat') ."</td>\n" .
    "<td class=colhead align=center>". __('Descărcat') ."</td><td class=colhead align=center>". __('Raport') ."</td></tr>\n";
  while ($arr = mysql_fetch_assoc($res))
  {
    if ($arr["downloaded"] > 0)
    {
      $ratio = number_format($arr["uploaded"] / $arr["downloaded"], 3);
      $ratio = "<font color=" . get_ratio_color($ratio) . ">$ratio</font>";
    }
    else
      if ($arr["uploaded"] > 0)
        $ratio = "Inf.";
      else
        $ratio = "---";
	$catimage = esc_html($arr["image"]);
	$catname = esc_html($arr["catname"]);

	$size = str_replace(" ", "<br>", mksize($arr["size"]));
	$uploaded = str_replace(" ", "<br>", mksize($arr["uploaded"]));
	$downloaded = str_replace(" ", "<br>", mksize($arr["downloaded"]));
	$seeders = number_format($arr["seeders"]);
	$leechers = number_format($arr["leechers"]);
    $ret .= "<tr><td style='padding: 0px' align=center><img src=\"pic/categs/$catimage\" alt=\"$catname\" width=32></td>\n" .
		"<td><a href=details.php?id=$arr[torrent]><b>" . $arr["torrentname"] .
		"</b></a></td><td align=center>$size</td><td align=right>$seeders</td><td align=right>$leechers</td><td align=center>$uploaded</td>\n" .
		"<td align=center>$downloaded</td><td align=center>$ratio</td></tr>\n";
  }
  if ($count > 3 && !$torrents_all) {
    $ret .= '<tr><td colspan="8"><a href="?id='.$id.'&torrents_all=1">'.sprintf(__('Arată toate %s torrente'),$count).'</a></td></tr>';
  }
  $ret .= "</table>\n";
  return $ret;
}

$id = isset($_GET["id"])?$_GET["id"]:'';

if (!is_valid_id($id)) $id = $CURUSER["id"];

$r = q('SELECT users.*, teams.name AS teamName, u_du.uploaded, u_du.downloaded, u_du.last_access,
               users_additional.comments, users_additional.posts, users_additional.total_censored,
               users_additional.author_of_reported_posts_total,
               users_additional.author_of_reported_posts_total_censored
		FROM users
		LEFT JOIN teams ON (users.team > 0 AND users.team = teams.id)
		LEFT JOIN users_down_up AS u_du ON users.id = u_du.id
    LEFT JOIN users_additional ON users.id = users_additional.id
    WHERE users.id=:id',array('id'=>$id));

$user = mysql_fetch_array($r) or bark(__('Nu există utilizator cu ID-ul') ." $id.");
if ($user["status"] == "pending") die;
$user['fotbalist'] = ( have_flag('fotbalist',$user['user_opt']) )?'yes':'no';

$torrents_limit = '';
$torrents_all = isset($_GET['torrents_all'])?true:false;
if (!$torrents_all) $torrents_limit = 'LIMIT 3';

$torrents_uploaded_where = '';
if ($user["id"] != $CURUSER["id"] && !isSysop()) {
  $torrents_uploaded_where = "AND NOT(torrent_opt & ".$conf_torrent_opt['anonim']." OR torrent_opt & ".$conf_torrent_opt['anonim_unverified'].') ';
}

$r = q("SELECT SQL_CALC_FOUND_ROWS torrents.id, torrents.name, seeders, leechers, category, categories.name AS catName, categories.image AS catImg
	   FROM torrents
	   LEFT JOIN categories ON torrents.category = categories.id
       WHERE owner=:id $torrents_uploaded_where
       ORDER BY added DESC $torrents_limit",array('id'=>$id));
$count = q_singleval('SELECT FOUND_ROWS()');
if (mysql_num_rows($r) > 0) {
  $torrents = "<table class=main border=1 cellspacing=0 cellpadding=5>\n" .
    "<tr><td class=colhead>". __('Tip') ."</td><td class=colhead>". __('Nume') ."</td><td class=colhead align=center><img src=pic/arrowup.gif></td><td class=colhead align=center><img src=pic/arrowdown.gif></td></tr>\n";
  while ($a = mysql_fetch_assoc($r))
  {
		$cat = "<img src=\"pic/categs/$a[catImg]\" alt=\"$a[catName]\">";
      $torrents .= "<tr><td style='padding: 0px' align=center>$cat</td><td><a href=details.php?id=" . $a["id"] . "><b>" . $a["name"] . "</b></a></td>" .
        "<td align=right>$a[seeders]</td><td align=right>$a[leechers]</td></tr>\n";
  }
  if ($count > 3 && !$torrents_all) {
    $torrents .= '<tr><td colspan="4"><a href="?id='.$id.'&torrents_all=1">'.sprintf(__('Arată toate %s torrente'),$count).'</a></td></tr>';
  }
  $torrents .= "</table>";
}

if (get_user_class() >= UC_MODERATOR) {
  $ip = $user["ip"];

  // Get nr of users with this ip
  if ($user["ip"] && get_user_class() >= UC_MODERATOR) {
      $addr = $ip;
  	  $addr .= ' (<a href="./users.php?ip='.$addr.'">' . q_singleval("SELECT count(id) FROM users WHERE ip='$addr'") . '</a>)';
  }

  if (get_user_class() >= UC_SYSOP && strlen($user['browserHash']) ) {
  	  $browserHashEsc = esc_html($user['browserHash']);
  	  $browserHash = '<a href="./browser_hash.php?hash='.$browserHashEsc.'">'.$browserHashEsc.'</a>';
  	  $browserHash .= ' (<a href="./users.php?browser='.$browserHashEsc.'">' .
  	  	  q_singleval("SELECT COUNT(id) FROM users WHERE browserHash=:bsession", array('bsession'=>$browserHashEsc) )
  	  	  . '</a>)';
  }

  $numberOfInvited = q_singleval(
    'SELECT COUNT(id) FROM users WHERE inviter=:user',
    array('user' => $id)
  );


}
if ($user['added'] == "0000-00-00 00:00:00") {
  $joindate = 'N/A';
}
else {
  $joindate = "$user[added] (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($user["added"])) . ')';
}

$lastseen = $user["last_access"];
if ($user['id'] == $CURUSER["id"]) $lastseen = get_date_time();


if ($lastseen == "0000-00-00 00:00:00")
  $lastseen = "never";
else
{
  $lastseen .= " (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($lastseen)) . ')';
}

//Sex
if ($user['gender'] == 'masc') $gender = $lang['ud_gender_m'];
elseif ($user['gender'] == 'fem') $gender = $lang['ud_gender_f'];
else $gender = '-';

//Region
$region = '-';
if ($user['region'] > 0) $region = q_singleval('SELECT name FROM regions WHERE id='.$user['region']);
$torrentcomments = $user['comments'];
$forumposts = $user['posts'];
$totalcensored = $user['total_censored'];
$authorOfReportedPosts = $user['author_of_reported_posts_total'];
$authorOfReportedPostsCensored = $user['author_of_reported_posts_total_censored'];

mainWithId("Details for " . $user["username"], "userdetails");
$enabled = $user["enabled"] == 'yes';


$templateArgs = array(
  "username" => $user['username'],
  "iconsHtml" => get_user_icons($user, true)
);

echo renderTemplateToString("userdetails/userdetails.html.php", $templateArgs);

if (!$enabled)
  print("<p class='center'><b>". __('This account has been disabled') ."</b></p>\n");


echo '<div class="center" style="font-size:12px">';

if ($CURUSER["id"] != $user["id"]) {
  $r = q("SELECT id FROM friends WHERE userid=$CURUSER[id] AND friendid=$id");
  $friend = mysql_num_rows($r);
  $r = q("SELECT id FROM blocks WHERE userid=$CURUSER[id] AND blockid=$id");
  $block = mysql_num_rows($r);

  echo (($CURUSER["id"] != $user['id'])? ' (<a href="./sendmessage.php?receiver='.$user['id'].'">'. __('Trimite mesaj') .'</a>)':'');

  echo '<br/><br/>';

  if ($friend)
    print("(<a href=/friends.php?action=delete&type=friend&targetid=$id>". __('şterge din prieteni') ."</a>)\n");
  elseif ($block)
    print("(<a href=/friends.php?action=delete&type=block&targetid=$id>". __('şterge din lista neagră') ."</a>)\n");
  else
  {
    print("(<a href=/friends.php?action=add&type=friend&targetid=$id>". __('adaugă în prieteni') ."</a>)");
    print(" - (<a href=/friends.php?action=add&type=block&targetid=$id>". __('adaugă în lista neagră') ."</a>)\n");
  }
  echo '</p>';
  if (get_user_class() >= UC_MODERATOR) {
      echo '<p class="center">';
      printf(" (<a href=users.php?inviter=%d>%s</a>) \n",$id,__('arată utilizatorii invitați'));
      printf(" - (<a href=moder_delete_messages.php?username=%s>%s</a>) \n",$user['username'],__('șterge comentarii/posturi 24h'));
      echo '</p>';
  }

}


begin_main_frame();
?>
<br/>
<table border=1 cellspacing=0 cellpadding=5 align="center" class=userdetails_userinfo>
<?php
if (get_user_class() >= UC_MODERATOR) {
	if ($user["user_opt"] & $conf_user_opt['invite_disabled']) {
		printf("<tr><td class=rowhead>%s</td><td align=left><b>%s<b></td></tr>\n",__('Stare'),__('Interdicție de a invita'));
	}
}
	if ($user["user_opt"] & $conf_user_opt['moderator_pe_tema_sa'] && get_config_variable('forum', 'moderators_activated')) {
		printf("<tr><td class=rowhead>%s</td><td align=left><b>%s<b></td></tr>\n",__('Stare'),__('Moderator pe temele sale din forum'));
	}
?>
<tr><td class=rowhead width="15%"><?=$lang['ud_join_date']?></td><td align=left><?=$joindate?></td></tr>
<tr><td class=rowhead><?=$lang['ud_last_seen']?></td><td align=left><?=$lastseen?></td></tr>
<?php
if (get_user_class() >= UC_MODERATOR) {
	if ($user["inviter"] > 0) {
		printf('<tr><td class=rowhead>%s</td><td align=left><a href="userdetails.php?id=%d">%s</a></td></tr>',__('Invitat de'),$user["inviter"],fetchOne('SELECT username FROM users_username WHERE id=:id',array('id'=>$user["inviter"])));
	}
}
if (isset($numberOfInvited) && $numberOfInvited > 0) {
  print(sprintf('<tr>
                    <td class=rowhead>Utilizatori invitati</td>
                    <td align=left><a href="/users.php?inviter=%s">%s utilizatori</a></td>
                </tr>', $id, $numberOfInvited));
}
  // Team
  if ($user['team']) {
?>
<tr><td class=rowhead><?=$lang['ud_team']?></td><td align=left>
    <a href="./team.php?name=<?php echo str_replace(' ','_',$user['teamName']); ?>"><?=$user['teamName']?></a>
   </td></tr>
<?php
  }
?>


<tr><td class=rowhead><?=$lang['ud_region']?></td><td align=left><?=$region?></td></tr>
<tr><td class=rowhead><?=$lang['ud_gender']?></td><td align=left><?=$gender?></td></tr>
<?php
if (get_user_class() >= UC_MODERATOR) {
   print("<tr><td class=rowhead>Email</td><td align=left>".email_cruncher($user['email'])."</td></tr>\n");
}
if ($addr) print("<tr><td class=rowhead>$lang[ud_address]</td><td align=left>$addr</td></tr>\n");

if ($browserHash) print("<tr><td class=rowhead>$lang[ud_br_hash]</td><td align=left>$browserHash</td></tr>\n");

?>
  <tr><td class=rowhead><?=$lang['ud_uploaded']?></td><td align=left><?=mksize($user["uploaded"])?></td></tr>
  <tr><td class=rowhead><?=$lang['ud_downloaded']?></td><td align=left><?=mksize($user["downloaded"])?></td></tr>
  <?php
  if ($user["downloaded"] > 0)
  {
    $sr = $user["uploaded"] / $user["downloaded"];
    if ($sr >= 4)
      $s = "w00t";
    else if ($sr >= 2)
      $s = "grin";
    else if ($sr >= 1)
      $s = "smile";
    else if ($sr >= 0.5)
      $s = "noexpression";
    else if ($sr >= 0.25)
      $s = "sad";
    else
      $s = "cry";
    $sr = "<table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded><font color=" . get_ratio_color($sr) . ">" . number_format($sr, 3) . "</font></td><td class=embedded>&nbsp;&nbsp;<img src=pic/smilies/$s.gif></td></tr></table>";
    print("<tr><td class=rowhead style='vertical-align: middle'>". __('Ratio') ."</td><td align=left valign=center style='padding-top: 1px; padding-bottom: 0px'>$sr</td></tr>\n");
  }

  /*
  	Thank
  */
  $thanks = $user['thanks'];

  // Check if language have different forms
  if (isset($lang['ud_was_thanked_f'])) {
  	if ($user['gender'] == 'fem') $lang['ud_was_thanked'] = $lang['ud_was_thanked_f'];
  	else $lang['ud_was_thanked'] = $lang['ud_was_thanked_m'];
  }
  echo '<tr><td class=rowhead>', $lang['ud_was_thanked'], '</td><td align=left>', $thanks, ' ', $lang['ud_was_thanked_times'], '</td></tr>';

$UC = array("SysOp" => "pic/class/sysop.gif",
  "Administrator" => "pic/class/admin.gif",
  "Moderator" => "pic/class/mod.gif",
  "VIP" => "pic/class/vip2.gif",
	"Knight of TMD" => "pic/class/knight.gif",
  "Moderator pe torrente" => "pic/class/moder_of_torrents.gif",
  "Releaser" => "pic/class/releaser.gif",
  "Uploader" => "pic/class/uploader.gif",
  "Power User" => "pic/class/power.gif",
  "User" => "pic/class/user.gif");

if ($user["avatar"] == 'yes') {
	$avatar = avatarWww($user['id'],$user['avatar_version'],true);
	print("<tr><td class=rowhead>". __('Avatar') ."</td><td align=left><img src=\"" .  $avatar . "\"></td></tr>\n");
}

    if(isDeveloper($user["id"]))
		$uclass = 'pic/class/developer.gif';
    elseif ((int)$user["id"]==260 && $user["class"]==UC_SYSOP) //BNQ custom icon ^_^
        $uclass = 'pic/class/sysop2.gif';
    else
    	$uclass = $UC[get_user_class_name($user["class"])];

    print("<tr><td class=rowhead>$lang[ud_class]</td><td align=left><img src=$uclass></td></tr>\n");

	print("<tr id=ud_t_commnets><td class=rowhead>$lang[ud_torrent_comments]</td>");
	if($torrentcomments && (int)$torrentcomments >0)
		print("<td align=left><a href=userhistory.php?action=viewcomments&id=$id>$torrentcomments</a></td></tr>\n");
	else
		print("<td align=left>0</td></tr>\n");


  print("<tr><td class=rowhead>$lang[ud_forum_posts]</td>");
  if ($forumposts && (int)$forumposts >0)
    print("<td align=left><a href=userhistory_posts.php?action=viewposts&id=$id>$forumposts</a></td></tr>\n");
  else
    print("<td align=left>0</td></tr>\n");

  if (isModerator()) {
    print("<tr><td class=rowhead>Dintre care cenzurate</td>");
  	if ($totalcensored && (int)$totalcensored >0)
  		print("<td align=left>$totalcensored</td></tr>\n");
  	else
  		print("<td align=left>0</td></tr>\n");
  }

  if (isModerator()) {
    print("<tr><td class=rowhead>Dintre care raportate</td>");
    if ($authorOfReportedPosts && (int)$authorOfReportedPosts >0)
      print("<td align=left>$authorOfReportedPosts</td></tr>\n");
    else
      print("<td align=left>0</td></tr>\n");
  }

  if (isModerator()) {
    print("<tr><td class=rowhead>Dintre care raportate si cenzurate</td>");
    if ($authorOfReportedPostsCensored && (int)$authorOfReportedPostsCensored >0)
      print("<td align=left>$authorOfReportedPostsCensored</td></tr>\n");
    else
      print("<td align=left>0</td></tr>\n");
  }


if ( have_flag('forum_moderator', $user['user_opt']) && get_config_variable('forum', 'moderators_activated') ) {
	$forum_moderator_data = Forum::getForumsAsModerator($user['id']);
	print '<tr><td class=rowhead>'.__('Moderatorul categoriilor').':</td><td align=left>';

	foreach($forum_moderator_data AS $forum_data) {
		$boss = '';
		if ( $forum_data['statut']=='moderator_primar' )
			$boss = ' style="color: #cc0000"';
		printf('<a %shref="/forum.php?action=viewforum&forumid=%d">%s</a><br>',
			$boss, $forum_data['id'], $forum_data['name_'.get_lang()]);
	}

	print '</td></tr>';
}

if ( get_user_class() >= UC_MODERATOR && $user["class"] < get_user_class() ) {
	echo '</table>'; // super cul patch, doar daca userul ce vizioneaza pagina e >=moder, tabela se taie si se baga in acel loc modformul... la userii simpli totul e intr'o bucata :]
	include($WWW_ROOT.'userdetails_modform.php');
	echo '<table border=1 cellspacing=0 cellpadding=5 align="center" class="userdetails_userinfo">';
}

if ( $user['id'] != $CURUSER["id"] ) {
  $curuser_total_uploaded_torrents = fetchOne("SELECT count(torrents.id)
	   FROM torrents
       WHERE owner=:id",array('id'=>$CURUSER["id"]));
}

if ($torrents)
  print("<tr valign=top id=userdetails_up_t><td class=rowhead width=15%>". __('Torrente încărcate') ."</td><td align=left>$torrents</td></tr>\n");

if ($user["info"]) {
 print('<tr valign=top><td align=left colspan=2 class=text bgcolor=#F4F4F0><div>' . format_comment($user["info"]) . "</td></tr>\n");
}

if ($CURUSER["id"] != $user["id"]) {
	if (get_user_class() >= UC_MODERATOR) {
		$showpmbutton = 1;
	} elseif ($user["acceptpms"] == "yes") {
		$r = q("SELECT id FROM blocks WHERE userid=$user[id] AND blockid=$CURUSER[id]") or sqlerr(__FILE__,__LINE__);
		$showpmbutton = (mysql_num_rows($r) == 1 ? 0 : 1);
	} elseif ($user["acceptpms"] == "friends") {
		$r = q("SELECT id FROM friends WHERE userid=$user[id] AND friendid=$CURUSER[id]") or sqlerr(__FILE__,__LINE__);
		$showpmbutton = (mysql_num_rows($r) == 1 ? 1 : 0);
	}
}
if (isset($showpmbutton) && $showpmbutton) {
	print("<tr><td colspan=2 align=center><form method=get action=sendmessage.php><input type=hidden name=receiver value=" .
		$user["id"] . "><input type=submit value=\"". __('Trimite mesaj') ."\" style='height: 23px'></form></td></tr>");
}

print("</table>\n");


end_main_frame();
stdfoot();

   function br2nl($text)
   {
       return str_replace("<br>","\n",$text);
   }
?>
<style type="text/css">
  body.topicmd #userdetails_up_t {
    display: none;
  }
  body.topicmd #ud_t_commnets {
    display: none;
  }
</style>