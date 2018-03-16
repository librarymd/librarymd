<?php
  require "include/bittorrent.php";
  
  loggedinorreturn();
  
  //Check for delete all
  if(isset($_GET['deleteall']) && isset($_GET['type'])) {
  	  $type = $_GET['type'];
  	  if (!isset($_POST['deleteall_conf'] )) { //Show the confirm form
  	  	  stdhead('Confirm messages deletion');
  	  	  if ($type == 'in') {
  	  	  	  echo '<h2 class="center">'.$lang['deleteallmessage_inbox_confirm'].'<h2>';
  	  	  } elseif ($type == 'out') {
  	  	  	  echo '<h2 class="center">'.$lang['deleteallmessage_sentbox_confirm'].'<h2>';
  	  	  } else {
							echo '<h2>Eroare, vă rugăm să încercați din nou!</h2>';
							stdfoot();
							exit();
					}
  	  	  //The confirm button form
  	  	  echo '<div class="center">';
		  echo '<form method="post" action="deletemessage.php">'."\n";
		  echo '<input type="hidden" name="deleteall_conf" value="'.$type.'">'."\n";
		  echo '<input type="submit" value="' . $lang['deleteallmessage_yes'] . '" class=btn>'."\n";
		  echo '</form>'."\n";
		  echo '</div>';
  	  	  stdfoot();
  	  	  exit();
  	  }
  }
  if (isset($_POST['deleteall_conf'])) {
  	  $type = $_POST['deleteall_conf'];
  	  if ($type == 'in') {
  	  	  q("DELETE FROM messages WHERE location='in' AND receiver=" . $CURUSER["id"] );
  	  	  q("UPDATE messages SET location='out' WHERE location='both' AND receiver=" . $CURUSER["id"] );
  	  	  userPmsCountRegenerate($CURUSER['id']);
  	  	  expirePmCache($CURUSER['id']);
			
  	  	  header("Location: http://".$DEFAULTBASEURL."/inbox.php".($type == 'out'?"?out=1":""));
  	  	  exit();
  	  }
  	  if ($type == 'out') {
  	  	  q("DELETE FROM messages WHERE location='out' AND sender=" . $CURUSER["id"] );
  	  	  q("UPDATE messages SET location='in' WHERE location='both' AND sender=" . $CURUSER["id"] );
  	  	  userPmsCountRegenerate($CURUSER['id']);
  	  	  expirePmCache($CURUSER['id']);
  	  	  
  	  	  header("Location: http://".$DEFAULTBASEURL."/inbox.php".($type == 'out'?"?out=1":""));
  	  	  exit();
  	  }
  }
  
  if(!isset($_GET["id"])) die('Id');
  $id = $_GET["id"];
  if (!is_numeric($id) || $id < 1 || floor($id) != $id)
    die;

  $type = $_GET["type"];

  if ($type == 'in')
  {
  	// make sure message is in CURUSER's Inbox
	  $res = q("SELECT receiver, location FROM messages WHERE id=" . sqlesc($id));
	  $arr = mysql_fetch_array($res) or die("Bad message ID");
	  if ($arr["receiver"] != $CURUSER["id"])
	    die("I wouldn't do that if i were you...");
    if ($arr["location"] == 'in') {
	  	q("DELETE FROM messages WHERE id=" . sqlesc($id));
	  	//expirePmCache($arr['receiver']);
	  	expirePmCache($CURUSER["id"]);
	  	
	  	userPmsCount($CURUSER["id"],'inbox','dec');
	  	
    } else if ($arr["location"] == 'both') {
		q("UPDATE messages SET location = 'out' WHERE id=" . sqlesc($id));
		//expirePmCache($arr['receiver']);
	  	expirePmCache($CURUSER["id"]);
	  	userPmsCount($CURUSER["id"],'inbox','dec');
    } else
    	die('The message is not in your Inbox.');
  }
	elseif ($type == 'out')
  {
   	// make sure message is in CURUSER's Sentbox
	  $res = q("SELECT sender, location FROM messages WHERE id=" . sqlesc($id)) or die("barf");
	  $arr = mysql_fetch_array($res) or die("Bad message ID");
	  if ($arr["sender"] != $CURUSER["id"])
	    die("I wouldn't do that if i were you...");
    if ($arr["location"] == 'out') {
	  	q("DELETE FROM messages WHERE id=" . sqlesc($id)) or die('delete failed (error code 3).. this should never happen, contact an admin.');
		userPmsCount($CURUSER["id"],'sentbox','dec');
    } else if ($arr["location"] == 'both') {
		q("UPDATE messages SET location = 'in' WHERE id=" . sqlesc($id)) or die('delete failed (error code 4).. this should never happen, contact an admin.');
		userPmsCount($CURUSER["id"],'sentbox','dec');
    } else {
    	die('The message is not in your Sentbox.');
    }
  }
  else
  	die('Unknown PM type.');
  if (isset($_GET['aj'])) {
  	  echo 1; //That mean message was deleted successfully
  	  exit();
  }
  expirePmCache($arr['receiver']);
  expirePmCache($CURUSER["id"]);
  $additional = isset($_GET['page'])&&is_numeric($_GET['page'])&&$_GET['page']>=0?'&page='.$_GET['page']:'';
  header("Location: ./inbox.php".($type == 'out'?"?out=1":"?in=1").$additional);
?>
