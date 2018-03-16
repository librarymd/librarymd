<?php

require "include/bittorrent.php";

loggedinorreturn();

if (get_user_class() < UC_MODERATOR) {
	stderr("Error", "Permission denied.");
}

if (isset($_GET['turnannonce'])) {
	if ($_GET['turnannonce'] == 'on') {
		set_config_var('anunt',1);
		write_admins_log('Annonce turned ON by ' . $CURUSER['username']);
	} else {
		set_config_var('anunt',0);
		write_admins_log('Annonce turned OFF by ' . $CURUSER['username']);
	}
	header('Location: ./index.php');
	exit();
}

$action = esc_html($_REQUEST["action"]);

//   Delete News Item    //////////////////////////////////////////////////////

if ($action == 'delete')
{
	$newsid = $_REQUEST["newsid"];
  if (!is_valid_id($newsid))
  	stderr("Error","Invalid news item ID - Code 1.");

  $returnto = $_REQUEST["returnto"];

  $sure = $_POST["sure"];
  if (!$sure)
    stderr("Delete news item","Do you really want to delete a news item? Click\n" .
					'<form action="firstpage_annonce.php" method="post" style="display:inline">
      				  <input type="hidden" name="action" value="delete">
      				  <input type="hidden" name="newsid" value="'.$newsid.'">
      				  <input type="hidden" name="returnto" value="'.$returnto.'">
      				  <input type="submit" name="sure" value="here">
      			    </form>' .
          	"if you are sure.");

  q("DELETE FROM annonces WHERE id=$newsid");
  
  mem_delete('annonce_body_ro');
  mem_delete('annonce_body_ru');
  
  write_admins_log('Annonce deleted by ' . $CURUSER['username']);

	if ($returnto != "")
		header("Location: $returnto");
	else
		$warning = "News item was deleted successfully.";
}

//   Add News Item    /////////////////////////////////////////////////////////

if ($action == 'add')
{

	$body_ro = $_POST["body_ro"];
	$body_ru = $_POST["body_ru"];
	if (!$body_ro || !$body_ru)	stderr("Error","The news item cannot be empty!");

	$added = $_POST["added"];
	$until = sql_timestamp_to_unix_timestamp($_POST["until"]);
	if (!is_numeric($until)) stderr("Error",'Bad expire data! ');
	if (!$added)
		$added = sqlesc(get_date_time());
   $sql = "INSERT INTO annonces (userid, added, body_ro, body_ru, until) VALUES (
              {$CURUSER['id']}, $added, "._esc($body_ro).", "._esc($body_ru).", ".$until.")";
   write_admins_log('Annonce added ' . $CURUSER['username']);
	
  q($sql);
	if (mysql_affected_rows() == 1)
		$warning = "News item was added successfully.";
	else
		stderr("Error","Something weird just happened.");
	
	global $conf_user_opt;

	q("UPDATE users SET user_opt=user_opt & ~" . $conf_user_opt['have_seen_annonce']); //Unset have seen annonce
	echo mem_delete('annonce_body_ro');
	echo mem_delete('annonce_body_ru');

}

//   Edit News Item    ////////////////////////////////////////////////////////

if ($action == 'edit')
{

	$newsid = $_GET["newsid"];

  if (!is_valid_id($newsid))
  	stderr("Error","Invalid news item ID - Code 2.");

  $res = q("SELECT * FROM annonces WHERE id=$newsid");

	if (mysql_num_rows($res) != 1)
	  stderr("Error", "No news item with ID $newsid.");

	$arr = mysql_fetch_array($res);

  if ($_SERVER['REQUEST_METHOD'] == 'POST')
  {
  	$body_ro = $_POST['body_ro'];
  	$body_ru = $_POST['body_ru'];

    if ($body_ro == "" || $body_ru == "")
    	stderr("Error", "Body cannot be empty!");

    //$body = sqlesc($body);

    $editedat = sqlesc(get_date_time());
    
	$until = sql_timestamp_to_unix_timestamp($_POST["until"]);
	if (!is_numeric($until)) stderr("Error",'Bad expire data! ');

    q("UPDATE annonces SET body_ro="._esc($body_ro).", body_ru="._esc($body_ru).", until=".$until. " WHERE id=$newsid");
    
    mem_delete('annonce_body_ro');
	mem_delete('annonce_body_ru');
	
	write_admins_log('Annonce updated ' . $CURUSER['username']);

    $returnto = $_POST['returnto'];

		if ($returnto != "")
			header("Location: $returnto");
		else
			$warning = "News item was edited successfully.";
  }
  else
  {
 	  $returnto = $_GET['returnto'];
	  stdhead();
	  print("<h1>Edit News Item</h1>\n");
	  print("<form method=post action=?action=edit&newsid=$newsid>\n");
	  print("<table border=1 cellspacing=0 cellpadding=5>\n");
	  print("<tr><td><input type=hidden name=returnto value=$returnto></td></tr>\n");
      print("<tr><td style='padding: 10px'>Romanian<br><textarea name=body_ro cols=141 rows=5 style='border: 0px'>" . esc_html($arr["body_ro"]) . "</textarea>\n");
	  print("<br>Russian<br><textarea name=body_ru cols=141 rows=5 style='border: 0px'>" . esc_html($arr["body_ru"]) . "</textarea><br><br>
	  	    Arata pina pe data <input type=text name=until value='".get_date_time($arr["until"])."'> (format strict: an-luna-zi ora:minute:sec exemplu: 2005-06-10 08:44:47)
	  		<br><br><div align=center><input type=submit value='Okay' class=btn></div></td></tr>\n");
	  print("</table>\n");
	  print("</form>\n");
	  stdfoot();
	  die;
  }
}

//   Other Actions and followup    ////////////////////////////////////////////

stdhead("Site annonces");
print("<h1>Anunţ pe prima pagină</h1>\n");
if ($warning)
	print("<p><font size=-3>($warning)</font></p>");
print("<form method=post action=?action=add>\n");
print("<table border=1 cellspacing=0 cellpadding=5>\n");
print("<tr><td style='padding: 10px'>Romanian<br><textarea name=body_ro cols=141 rows=5 style='border: 0px'></textarea>\n");
print("<br>Russian<br><textarea name=body_ru cols=141 rows=5 style='border: 0px'></textarea><br><br>
Arata pina pe data <input type=text name=until size=22 value='".get_date_time()."'> (format strict: an-luna-zi ora:minute:sec exemplu: 2005-06-10 08:44:47)
<br><br><div align=center><input type=submit value='Okay' class=btn></div></td></tr>\n");
print("</table></form><br><br>\n");

$res = q("SELECT * FROM annonces ORDER BY added DESC");

if (mysql_num_rows($res) > 0)
{


 	begin_main_frame();
	begin_frame();

	while ($arr = mysql_fetch_array($res))
	{
	  $newsid = $arr["id"];
	  $body_ro = $arr["body_ro"];
	  $body_ru = $arr["body_ru"];
	  $userid = $arr["userid"];
	  $expire_date = get_date_time($arr["until"]);
	  $expire_remain = 'expirat';
	  if ($arr["until"] > time()) {
	  	  $expire_remain_hours = floor( ($arr["until"] - time()) / 60 / 60 );
	  	  $expire_remain_min = floor ( ( ($arr["until"] - time()) - ($expire_remain_hours * 60 * 60) ) / 60 );
	  	  $expire_remain = 'va mai fi aratat ' . $expire_remain_hours  . ' ore ' . $expire_remain_min . ' minute';
	  }
	  	  
	  $added = $arr["added"] . " GMT (" . (get_elapsed_time(sql_timestamp_to_unix_timestamp($arr["added"]))) . ')';

    $res2 = q("SELECT username, donor FROM users WHERE id = $userid");
    $arr2 = mysql_fetch_array($res2);

    $postername = $arr2["username"];

    if ($postername == "")
    	$by = "unknown[$userid]";
    else
    	$by = "<a href=userdetails.php?id=$userid><b>$postername</b></a>" .
    		($arr2["donor"] == "yes" ? "<img src=pic/user_state/star.gif alt='Donor'>" : "");

	print("<p class=sub><table border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>");
    print("$added&nbsp;---&nbsp;by&nbsp$by");
    print(" - [<a href=?action=edit&newsid=$newsid><b>Edit</b></a>]");
    print(" - [<a href=?action=delete&newsid=$newsid><b>Delete</b></a>]");
    print("</td></tr></table></p>\n");

	  begin_table(true);
	  print("<tr valign=top><td class=comment>Romanian<br>$body_ro</td></tr>\n");
	  print("<tr valign=top><td class=comment>Russian<br>$body_ru</td></tr>\n");
	  print("<tr valign=top><td class=comment>Expire<br>$expire_date ($expire_remain)</td></tr>\n");
	  end_table();
	}
	end_frame();
	end_main_frame();
}
else
  stdmsg("Sorry", "No news available!");
stdfoot();
die;
?>