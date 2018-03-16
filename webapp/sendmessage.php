<?php
require "include/bittorrent.php";
loggedinorreturn();

if ( isset($_POST['ajax']) && $_POST['ajax'] ) {
	if ($_POST['action']=='getMess') {
		if ( !isset($_POST['replyto']) )
			die;

		$replyto = (int)$_POST['replyto'];

		$msg = fetchRow("SELECT * FROM messages WHERE id=$replyto");
		if ($msg["receiver"] != $CURUSER["id"])
			die;

		$user = fetchRow("SELECT username FROM users WHERE id={$msg["sender"]}");
		$message = "\n\n\n-------- {$user["username"]} wrote: --------\n" . esc_html($msg['msg']);
		echo '<tr><td><h2 class="messageTo">'. __('Mesaj către') .' <a href=userdetails.php?id='. $msg["sender"] .'>'. $user["username"] .'</a></h2><div><div class="sendmessage_loader"></div><textarea class="message">'. $message .'</textarea></div><div class="center"><input type="submit" id="send" value="'. __('Trimite!') .'"> <input type="submit" id="abort" value="'. __('Anulează!') .'"><span class="error"></span></div></td></tr>';
		die;
	}
}

// Standard Administrative PM Replies
$pm_std_reply[1] = "Read the bloody [url=http://torrentbits.org/faq.php]FAQ[/url] and stop bothering me!";
$pm_std_reply[2] = "Die! Die! Die!";

// Standard Administrative PMs
$pm_template['1'] = array("Ratio warning","Hi,\n
You may have noticed, if you have visited the forum, that TB is disabling the accounts of all users with low share ratios.\n
I am sorry to say that your ratio is a little too low to be acceptable.\n
If you would like your account to remain open, you must ensure that your ratio increases dramatically in the next day or two, to get as close to 1.0 as possible.\n
I am sure that you will appreciate the importance of sharing your downloads.
You may PM any Moderator, if you believe that you are being treated unfairly.\n
Thank you for your cooperation.");
$pm_template['2'] = array("Avatar warning", "Hi,\n
You may not be aware that there are new guidelines on avatar sizes in the [url=http://torrentbits.org/rules.php]rules[/url], in particular \"Resize
your images to a width of 150 px and a size of [b]no more than 150 KB[/b].\"\n
I'm sorry to say your avatar doesn't conform to them. Please change it as soon as possible.\n
We understand this may be an inconvenience to some users but feel it is in the community's best interest.\n
Thanks for the cooperation.");

// Standard Administrative MMs
$mm_template['1'] = $pm_template['1'];
$mm_template['2'] = array("Downtime warning","We'll be down for a few hours");
$mm_template['3'] = array("Change warning","The tracker has been updated. Read
the forums for details.");

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{						          ////////  MM  //
	if (get_user_class() < UC_MODERATOR)
		stderr("Error", "Permission denied");
die();
  $n_pms = $_POST['n_pms'];
  $pmees = $_POST['pmees'];
  $auto = $_POST['auto'];

  if ($auto)
  	$body=$mm_template[$auto][1];

  stdhead("Send message");
	?>
  <table class="main mCenter" width=860 border=0 cellspacing=0 cellpadding=0>
	<tr><td class=embedded><div align=center>
	<h1>Mass Message to <?=$n_pms?> user<?=($n_pms>1?"s":"")?>!</h1>
	<form method=post action=takemessage.php>
	<? if ($_SERVER["HTTP_REFERER"]) { ?>
	<input type=hidden name=returnto value=<?=esc_html($_SERVER["HTTP_REFERER"])?>>
	<? } ?>
	<table border=1 cellspacing=0 cellpadding=5>
	<tr><td colspan="2"><div align="center">
	<textarea name=msg cols=80 rows=15><?=$body?></textarea>
	</div></td></tr>
	<tr><td colspan="2"><div align="center"><b>Comment:&nbsp;&nbsp;</b>
  <input name="comment" type="text" size="70">
	</div></td></tr>
  <tr><td><div align="center"><b>From:&nbsp;&nbsp;</b>
	<?=$CURUSER['username']?>
	<input name="sender" type="radio" value="self" checked>
	&nbsp; System
	<input name="sender" type="radio" value="system">
	</div></td>
  <td><div align="center"><b>Take snapshot:</b>&nbsp;<input name="snap" type="checkbox" value="1">
  </div></td></tr>
	<tr><td colspan="2" align=center><input type=submit value="Send it!" class=btn>
	</td></tr></table>
	<input type=hidden name=pmees value="<?=$pmees?>">
	<input type=hidden name=n_pms value=<?=$n_pms?>>
	</form><br><br>
	<form method=post action="sendmessage.php">
	<table border=1 cellspacing=0 cellpadding=5>
	<tr><td>
	<b>Templates:</b>
	<select name="auto">
	<?php
	for ($i = 1; $i <= count($mm_template); $i++)	{
		echo "<option value=$i ".($auto == $i?"selected":"").
    		">".$mm_template[$i][0]."</option>\n";}
  ?>
	</select>
	<input type=submit value="Use" class=btn>
	</td></tr></table>
	<input type=hidden name=pmees value="<?=$pmees?>">
	<input type=hidden name=n_pms value=<?=$n_pms?>>
	</form></div></td></tr></table>
  <?php
} else {                                                        ////////  PM  //

    if (!isset($_GET["receiver"]) && !isset($_GET["receiver"])) {
      stdhead($lang['inbox_compose']);
      $body = file_get_contents("templates/pm_new.tpl");

      $tpl_vars = array("{lang_inbox_compose}", "{lang_inbox_save_sentbox}", "{lang_inbox_send_message}","{lang_inbox_recipient_name}", "{lang_inbox_see_all_users}", "{lang_inbox_message}");
      $tpl_values = array($lang['inbox_compose'], $lang['inbox_save_sentbox'], $lang['inbox_send_message'], $lang['inbox_recipient_name'], $lang['inbox_see_all_users'], $lang['inbox_message']);
      $body = str_replace($tpl_vars, $tpl_values, $body);
	  print($body);

      stdfoot();
      exit();
    }

	$receiver = $_GET["receiver"];
	if (!is_valid_id($receiver)) die;

	$replyto = $_GET["replyto"];
	if ($replyto && !is_valid_id($replyto)) die;

	$auto = $_GET["auto"];
	$std = $_GET["std"];

	if (($auto || $std ) && get_user_class() < UC_MODERATOR)
	  die("Permission denied.");

	$res = q("SELECT * FROM users WHERE id=$receiver");
	$user = mysql_fetch_assoc($res);
	if (!$user)
	  die(__('Nu există utilizator cu aşa ID.'));

  if ($auto)
 		$body = $pm_std_reply[$auto];
  if ($std)
		$body = $pm_template[$std][1];

	if ($replyto)
	{
	  $res = q("SELECT * FROM messages WHERE id=$replyto");
	  $msga = mysql_fetch_assoc($res);
	  if ($msga["receiver"] != $CURUSER["id"])
	    die;
	  $res = q("SELECT username FROM users WHERE id=" . $msga["sender"]);
	  $usra = mysql_fetch_assoc($res);
	  $body .= "\n\n\n-------- $usra[username] wrote: --------\n$msga[msg]\n";
	}
	stdhead("Send message");
	?>
	<table class="main mCenter" width=860 border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>
	<h1><?=__('Mesaj către')?> <a href=userdetails.php?id=<?=$receiver?>><?=$user["username"]?></a></h1>
	<form method=post action=takemessage.php>
	<? if (isset($_GET["returnto"]) || $_SERVER["HTTP_REFERER"]) { ?>
	<input type=hidden name=returnto value="<?php echo isset($_GET["returnto"]) ? esc_html($_GET["returnto"]) : esc_html($_SERVER["HTTP_REFERER"])?>">
	<? } ?>
	<table class="mCenter" border=1 cellspacing=0 cellpadding=5>
	<tr><td<?=$replyto?" colspan=2":""?>><textarea name=msg cols=80 rows=15><?=$body?></textarea></td></tr>
	<tr>
	<? if ($replyto) { ?>
	<input type=hidden name=origmsg value=<?=$replyto?>></td>
	<? } ?>
	</tr>
	<tr><td<?=$replyto?" colspan=2":""?> align=center>
	<tr><td<?=$replyto?" colspan=2":""?> align=center>
		  <input type=submit value="<?=__('Trimite!')?>">
		</td></tr>
	</table>
	<input type=hidden name=receiver value=<?=$receiver?>>
	</form>
	</td></tr></table>
	<?php
}
stdfoot();
?>
