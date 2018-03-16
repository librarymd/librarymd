<?php
  require "include/bittorrent.php";
  //require $WWW_ROOT . "./forum_inc.php";

  if ($_SERVER["REQUEST_METHOD"] != "POST")
    stderr(__('Eroare'), "Method");


  loggedinorreturn();
  $n_pms = @$_POST["n_pms"];

  if (User::signedUpRecently()) {
    stderr(__('Eroare'), "La momentul dat nu puteti inca scrie mesaje pentru ca utilizatorul dvs. este prea nou. Aceasta masura este luata pentru a discuraja utilizatorii banati sa-si creeze clone.");
  }

  if (isset($_POST["receiver_name"])) {
    $res = q("SELECT id FROM users WHERE username=" . _esc($_POST["receiver_name"]) );
    $user = mysql_fetch_assoc($res);
    if (!$user)
      showErr(__('Eroare'), __('Numele destinatarului este greşit.'));
    else
      $receiver = $user['id'];
  } else {
    $receiver = $_POST["receiver"] + 0;
  }
  $origmsg = $_POST["origmsg"];
  //$save = (isset($_POST['save']) && $_POST['save'] == 'yes')?'yes':'';
  $save = 'yes';
  $returnto = @$_POST["returnto"];

  if (!is_valid_id($receiver) || ($origmsg && !is_valid_id($origmsg)))
    showErr(__('Eroare'),"Invalid ID");

  $msg = trim($_POST["msg"]);
  if (!$msg)
    showErr(__('Eroare'),"Please enter something!");

  $location = ($save == 'yes') ? "both" : "in";

  $res = q("SELECT acceptpms, email, notifs, UNIX_TIMESTAMP(last_access) as la FROM users WHERE id=$receiver");
  $user = mysql_fetch_assoc($res);
  if (!$user)
    showErr(__('Eroare'),  __('Nu exista utilizator cu ID') . " $receiver.");

  //Make sure recipient wants this message
  if (get_user_class() < UC_MODERATOR && get_user_class() != UC_SANITAR) {

    if ($user["acceptpms"] == "yes") {
      $res2 = q("SELECT * FROM blocks WHERE userid=$receiver AND blockid=" . $CURUSER["id"]);
      if (mysql_num_rows($res2) == 1)
        showErr(__('Respins'), __('Acest utilizator a blocat recepţionarea mesajelor private de la tine.'));
    }
    elseif ($user["acceptpms"] == "friends") {
      $res2 = q("SELECT * FROM friends WHERE userid=$receiver AND friendid=" . $CURUSER["id"]);
      if (mysql_num_rows($res2) != 1)
        showErr(__('Respins'), __('Acest utilizator primeşte mesaje doar de la lista lui de prieteni.'));
    }
    elseif ($user["acceptpms"] == "no")
      showErr(__('Respins'), __('Acest utilizator nu acceapta mesaje private.'));
  }

  $ip = $CURUSER['id'];

  $antiflood_banned_key = $ip.'_banned';

  if (mem_get($antiflood_banned_key) != null) {
      showErr(__('Ai temporar interdicție de a trimite mesaje'), __('Nu poți trimite mesaje deoarece de pe ip-ul tau au fost trimise prea multe mesaje intr-un interval scurt de timp.'));
  }

  //Anti flood
  $antiflood_key = $ip . '_pm';
  $antiflood_time_key = $ip . '_pm_time';
  $antiflood = mem_get($antiflood_key);
  $antiflood_ftime = mem_get($antiflood_time_key); //ftime first time

  if (get_user_class() < UC_VIP && $antiflood != false && $antiflood_ftime != false) {
      $antiflood_seconds_ago = time() - $antiflood_ftime; //first message nseconds ago

      $ban_the_user = false;

      if ( $antiflood >= 10 && $antiflood_seconds_ago <= 2*60) {
          $ban_the_user = true;
      } elseif ( $antiflood >= 30 && $antiflood_seconds_ago <= 20*60) {
          $ban_the_user = true;
      } elseif ( $antiflood >= 40) {
          $ban_the_user = true;
      } else {
          mem_set($antiflood_key, $antiflood + 1, 6000 - $antiflood_seconds_ago);
      }

      if ($ban_the_user) {
          $log = "User [url=/userdetails.php?id=$CURUSER[id]]$CURUSER[username][/url] was banned for pm flood while sending to user_id: $receiver.
          First message seconds ago: $antiflood_seconds_ago, total messages: $antiflood
          message: " . esc_html($msg);
          write_user_modcomment($CURUSER['id'], "The user was banned for PM flood for 12 hours");
          mem_set($antiflood_banned_key,1,43200);//43200 = 12h
          topic_post(88153349, $log, 3);
          die('Ai trimis prea multe mesaje');
          exit();
      }
  } else {
      mem_set($antiflood_key, 1, 6000);
      mem_set($antiflood_time_key, time(), 6000);
  }

  $text_parsed = format_comment($msg);
  bbcode_check_permission($msg,$text_parsed);

  messages_die_if_url_blacklist($msg,$text_parsed);

  newPM($CURUSER["id"], $receiver, $msg, $location);

  newPMEmailNotif($user['notifs'], $user['email'], $CURUSER['username']);

  $delete = 'no';

  if ($origmsg)
  {
    if ($delete == "yes") {
      // Make sure receiver of $origmsg is current user
      $res = q("SELECT * FROM messages WHERE id=$origmsg");
      if (mysql_num_rows($res) == 1) {
        $arr = mysql_fetch_assoc($res);
        if ($arr["receiver"] != $CURUSER["id"])
          stderr("w00t","This shouldn't happen.");
        if ($arr["location"] == "in")
          q("DELETE FROM messages WHERE id=$origmsg AND location = 'in'");
        elseif ($arr["location"] == "both")
          q("UPDATE messages SET location = 'out' WHERE id=$origmsg AND location = 'both'");
      }
      expirePmCache($arr["receiver"]);
      expirePmCache($CURUSER["id"]);
    }
    if (!$returnto)
      $returnto = "http://{$DEFAULTBASEURL}/inbox.php";
  }

  if (@$_POST['ajax']==1) {
    echo __("Răspuns cu succes!");
    die;
  }

  if ($returnto) {
    header("Location: $returnto");
    die;
  }

  stdhead();
  stdmsg(__('Succes'), __('Mesajul a fost'). __(' trimis cu succes!'));
  stdfoot();

  function showErr($errName, $errBody) {
    if (@$_POST['ajax']==1) {
      echo "Eroare, $errBody";
      die;
    } else
      stderr($errName, $errBody);
  }
?>
