<?php

require "include/bittorrent.php";
require $WWW_ROOT . "./forum_inc.php";


loggedinorreturn();


function puke($text = "w00t") {
  stderr("w00t", $text);
}

if (get_user_class() < UC_MODERATOR)
  puke();

$action = $_POST["action"];

if ($action == "edituser") {
  $userid = (int)$_POST["userid"];
  $title = $_POST["title"];
  $avatar = $_POST["avatar"];


  $uploaded = $_POST["uploaded"];
  $downloaded = $_POST["downloaded"];

  $enabled = $_POST["enabled"];

  //User ip bind
  if (isset($_POST["ipbind"])) {
  	$ipbind = $_POST["ipbind"];
  } else {
  	$ipbind = false;
  }
  $ipsBindList = $_POST["ipsBindList"];

  $warned = $_POST["warned"];
  $warnlength = 0 + $_POST["warnlength"];
  $warnpm = $_POST["warnpm"];
  $donor = $_POST["donor"];
  $fotbalist = $_POST["fotbalist"];
  $usr_comment = $_POST['usr_comment'];
  // Posting ban
  $postingban = $_POST['postingban'];
  $postingbanlength = 0 + $_POST['postingbanlength'];
  $postingbanpm = $_POST['postingbanpm'];
  // Torrents upload ban
  $torrentsUploadBan = $_POST['torrentsUploadBan'];
  $torrentsUploadBanlength = 0 + $_POST['torrentsUploadBanLength'];
  $torrentsUploadBanPm = $_POST['torrentsUploadBanPm'];
  // Torrents download ban
  $torrentsDownloadBan = $_POST['torrentsDownloadBan'];
  $torrentsDownloadBanlength = 0 + $_POST['torrentsDownloadBanLength'];
  $torrentsDownloadBanPm = $_POST['torrentsDownloadBanPm'];
  // Invite disabling
  $invite_disabled = $_POST['invite_disabled'];
  $invitedisablepm = $_POST['invitedisablepm'];
  // Moderator pe temele sale
  $moderator_pe_temele_sale = $_POST['moderator_pe_temele_sale'];
  // Velo club & chess club flag
  $velo_club = $_POST['velo_club'];
  $chess_club = $_POST['chess_club'];
  $spanked = $_POST['spanked'];

  $class = 0 + $_POST["class"];
  if (!is_valid_id($userid) || !is_valid_user_class($class))
    stderr("Error", "Bad user ID or class ID.");
  // check target user class
  $res = q("SELECT users.*, u_du.uploaded, u_du.downloaded
  	FROM users
  	LEFT JOIN users_down_up AS u_du ON users.id = u_du.id
  	WHERE users.id=$userid");
  $arr = mysql_fetch_assoc($res) or puke();
  $curenabled = $arr["enabled"];
  $curclass = $arr["class"];
  $curwarned = $arr["warned"];
  $modcomment =  fetchOne('SELECT modcomment FROM users_rare WHERE id=:id', array('id'=>$userid) );
  $curip = $arr['ip'];
  $curmodcomment = $modcomment;
  $curavatar = $arr['avatar'];
  $curdonor = $arr['donor'];

  $curIpBind = (have_flag('ipbind', $arr['user_opt']))?'yes':'no';
  if ($curIpBind == 'yes') {
  	  $curIpsBindList = q_singleval('SELECT ips FROM users_acl WHERE id='.$arr['id']);
  }

  $curtitle = $arr['title'];
  $user_username = $arr['username']; //Numele Userului cu care operam
  $curuploaded = $arr['uploaded'];
  $curdownloaded = $arr['downloaded'];

  $curuser_opt = $arr['user_opt'];
  $curpostingban = ($curuser_opt & $conf_user_opt['postingban'])?'yes':'no';

  $curTorrentsUploadBan = ($curuser_opt & $conf_user_opt['torrentsUploadBan'])?'yes':'no';
  $curTorrentsDownloadBan = ($curuser_opt & $conf_user_opt['torrentsDownloadBan'])?'yes':'no';
  $curInvite_disabled = ($curuser_opt & $conf_user_opt['invite_disabled'])?'yes':'no';
  $curmoderator_pe_temele_sale = ($curuser_opt & $conf_user_opt['moderator_pe_tema_sa'])?'yes':'no';

  // User may not edit someone with same or higher class than himself!
  if ($curclass >= get_user_class())
    puke();


  function hoursToDays($hours) {
  	  if ($hours == 1) return '1 hour';
  	  if ($hours <= 24) return "$hours hours";
  	  if ($hours >= 744) return floor($hours / 744) . ' months';
  	  if ($hours > 24) return floor($hours / 24) . ' days';
  }

  function addModcomment($message) {
    global $modcomment;
    $modcomment = date("Y-m-d H:i") . " - $message de " . User::currentUserName() . ".\n" . $modcomment;
  }

  function handleUserIconChange($userid, $newIcons) {
    global $modcomment;

    $userJson = Users::fetchUserJson($userid);
    $currentIcons = $userJson->getUserIcons();

    $iconDiffs = array_merge( array_diff($currentIcons, $newIcons),  array_diff($newIcons, $currentIcons) );

    if (count($iconDiffs) > 0) {

      foreach ($iconDiffs as $diffIconId) {
        $iconName = User_Icons::getNameById($diffIconId);


        if (in_array($diffIconId, $newIcons)) {
          addModcomment("Iconița $iconName a fost adaugată");
          newPM(0, $userid, "Felicitări ! Acum ai iconița cu titlul $iconName !");
        } else {
          addModcomment("Iconița $iconName a fost eliminată");
          newPM(0, $userid, "Ai fost lipsit de iconița $iconName.");
        }
      }

      $userJson->setUserIcons($newIcons);
      $userJson->save();
    }

  }

  //if user_icons
  if (isset($_POST['user_icons_available']) && isAdmin()) {
    $userIconsParam = is_array($_POST['user_icons']) ? $_POST['user_icons'] : array();
    handleUserIconChange($userid, $userIconsParam);
  }

  if ($class >= get_user_class()) puke();
  if ($curclass != $class && $class < get_user_class())
  {
    // Notify user
    $what = ($class > $curclass ? "promoted" : "demoted");
    $msg = "You have been $what to '" . get_user_class_name($class) . "'";
    $added = sqlesc(get_date_time());
    newPM(0, $userid, $msg);
    $updateset[] = "class = $class";
    $what = ($class > $curclass ? "Promoted" : "Demoted");
 		$modcomment = date("Y-m-d H:i") . " - $what to '" . get_user_class_name($class) . "' by $CURUSER[username].\n". $modcomment;
  }

  if ($warned && $curwarned != $warned)
  {
		$updateset[] = "warned = " . sqlesc($warned);
		$updateset[] = "warneduntil = '0000-00-00 00:00:00'";
    if ($warned == 'no')
    {
			$modcomment = date("Y-m-d H:i") . " - Warning removed by " . $CURUSER['username'] . ".\n". $modcomment;
      $msg = "Your warning has been removed by System.";
    }
		$added = sqlesc(get_date_time());
		newPM(0, $userid, $msg);
  }
	elseif ($warnlength) {
		if ($warnlength == 255) {
			$modcomment = date("Y-m-d H:i") . " - Warned by " . $CURUSER['username'] . ". Reason: $warnpm\n" . $modcomment;
			$msg = "You have received a [url=./rules.php]warning[/url] from System." . ($warnpm ? "\n\nReason: $warnpm" : "");
			$updateset[] = "warneduntil = '0000-00-00 00:00:00'";
		} else {
			$warneduntil = get_date_time(time() + $warnlength * 604800);
			$dur = $warnlength . " week" . ($warnlength > 1 ? "s" : "");
			$msg = "You have received a $dur [url=./rules.php]warning[/url] from System." . ($warnpm ? "\n\nReason: $warnpm" : "");
			$modcomment = date("Y-m-d H:i") . " - Warned for $dur by " . $CURUSER['username'] .  ". Reason: $warnpm\n" . $modcomment;
			$updateset[] = "warneduntil = '$warneduntil'";
		}
 		$added = sqlesc(get_date_time());
		newPM(0, $userid, $msg);

		$updateset[] = "warned = 'yes'";
	}
/**
	Posting Ban
**/
  if ($postingban == 'no') {
		$curuser_opt = setflag($curuser_opt,$conf_user_opt['postingban'],false);
		$updateset[] = "postingbanuntil = '0000-00-00 00:00:00'";
		$modcomment = date("Y-m-d H:i") . " - Posting ban has been removed by " . $CURUSER['username'] . ".\n". $modcomment;
		$msg = "Your posting ban has been removed by System.";
		$added = sqlesc(get_date_time());
		newPM(0, $userid, $msg);
    AntiFloodMessages::clean_all_counters($userid);
  }	elseif ($postingbanlength) {
		if ($postingbanlength == 255) {
			$modcomment = date("Y-m-d H:i") . " - Posting ban unlimited by " . $CURUSER['username'] . ". Reason: $postingbanpm\n" . $modcomment;
			$msg = "You have received a [url=./rules.php]posting ban[/url] from System." . ($postingbanpm ? "\n\nReason: $postingbanpm" : "");
			$updateset[] = "postingbanuntil = '0000-00-00 00:00:00'";
		} else {
			$baneduntil = get_date_time(time() + $postingbanlength * 3600);
			$dur = hoursToDays($postingbanlength);
			$msg = "You have received a $dur [url=./rules.php]posting ban[/url] from System" . "." . ($postingbanpm ? "\n\nReason: $postingbanpm" : "");
			$modcomment = date("Y-m-d H:i") . " - Posting ban $dur by " . $CURUSER['username'] .  ". Reason: $postingbanpm\n" . $modcomment;
			$updateset[] = "postingbanuntil = '$baneduntil'";
		}
 		$added = sqlesc(get_date_time());
		newPM(0, $userid, $msg);
		$curuser_opt = setflag($curuser_opt,$conf_user_opt['postingban'],true);
	}

/**
	Torrent Upload Ban
**/
  if ($torrentsUploadBan == 'no') {
		$curuser_opt = setflag($curuser_opt,$conf_user_opt['torrentsUploadBan'],false);
		$updateset[] = "uploadbanuntil = '0000-00-00 00:00:00'";
		$modcomment = date("Y-m-d H:i") . " - Torrents upload ban has been removed by " . $CURUSER['username'] . ".\n". $modcomment;
		$msg = "Your Torrents upload ban has been removed by System.";
		$added = sqlesc(get_date_time());
		newPM(0, $userid, $msg);
  }	elseif ($torrentsUploadBanlength) {
		if ($torrentsUploadBanlength == 255) {
			$modcomment = date("Y-m-d H:i") . " - Torrents upload ban by " . $CURUSER['username'] . ". Reason: $torrentsUploadBanPm\n" . $modcomment;
			$msg = "You have received a [url=./rules.php]Torrents upload ban[/url] from System." . ($torrentsUploadBanPm ? "\n\nReason: $torrentsUploadBanPm" : "");
			$updateset[] = "uploadbanuntil = '0000-00-00 00:00:00'";
		} else {
			$baneduntil = get_date_time(time() + $torrentsUploadBanlength * 3600);
			$dur = hoursToDays($torrentsUploadBanlength);
			$msg = "You have received a $dur [url=./rules.php]torrents upload ban[/url] from System" . "." . ($torrentsUploadBanPm ? "\n\nReason: $torrentsUploadBanPm" : "");
			$modcomment = date("Y-m-d H:i") . " - Torrents upload ban $dur by " . $CURUSER['username'] .  ". Reason: $torrentsUploadBanPm\n" . $modcomment;
			$updateset[] = "uploadbanuntil = '$baneduntil'";
		}
 		$added = sqlesc(get_date_time());
		newPM(0, $userid, $msg);
		$curuser_opt = setflag($curuser_opt,$conf_user_opt['torrentsUploadBan'],true);
	}


/**
	Torrent Download Ban
**/
  if ($torrentsDownloadBan == 'no') {
		$curuser_opt = setflag($curuser_opt,$conf_user_opt['torrentsDownloadBan'],false);
		$updateset[] = "downloadbanuntil = '0000-00-00 00:00:00'";
		$modcomment = date("Y-m-d H:i") . " - Torrents download ban has been removed by " . $CURUSER['username'] . ".\n". $modcomment;
		$msg = "Your Torrents download ban has been removed by System.";
		$added = sqlesc(get_date_time());
		newPM(0, $userid, $msg);
  }	elseif ($torrentsDownloadBanlength) {
		if ($torrentsDownloadBanlength == 255) {
			$modcomment = date("Y-m-d H:i") . " - Torrents download ban by " . $CURUSER['username'] . ". Reason: $torrentsDownloadBanPm\n" . $modcomment;
			$msg = "You have received a [url=./rules.php]Torrents download ban[/url] from System." . ($torrentsDownloadBanPm ? "\n\nReason: $torrentsDownloadBanPm" : "");
			$updateset[] = "downloadbanuntil = '0000-00-00 00:00:00'";
		} else {
			$baneduntil = get_date_time(time() + $torrentsDownloadBanlength * 3600);
			$dur = hoursToDays($torrentsDownloadBanlength);
			$msg = "You have received a $dur [url=./rules.php]torrents download ban[/url] from System." . ($torrentsDownloadBanPm ? "\n\nReason: $torrentsDownloadBanPm" : "");
			$modcomment = date("Y-m-d H:i") . " - Torrents download ban $dur by " . $CURUSER['username'] .  ". Reason: $torrentsDownloadBanPm\n" . $modcomment;
			$updateset[] = "downloadbanuntil = '$baneduntil'";
		}
 		$added = sqlesc(get_date_time());
		newPM(0, $userid, $msg);
		$curuser_opt = setflag($curuser_opt,$conf_user_opt['torrentsDownloadBan'],true);
	}


	if ($ipbind !== FALSE && $curIpBind != $ipbind && accessToIpBind()) {
		if ($ipbind == 'yes') {
			$curuser_opt = setflag($curuser_opt,$conf_user_opt['ipbind'],true);
			$modcomment = date("Y-m-d H:i") . " - Bind Ip Enable By " . $CURUSER['username'] .  ".\n" . $modcomment;
			write_sysop_log("$user_username bind ip has been enableds");
		} else {
			$curuser_opt = setflag($curuser_opt,$conf_user_opt['ipbind'],false);
			$modcomment = date("Y-m-d H:i") . " - Bind Ip Disabled By " . $CURUSER['username'] .  ".\n" . $modcomment;
			write_sysop_log("$user_username bind ip has been disabled");
		}
	}

	if ($ipbind !== FALSE && $ipbind == 'yes' && accessToIpBind()) {

		//Remove spaces what can persist between ips
		$ipsBindList = str_replace(' ','',$ipsBindList);
		//Make array
		$bindips_arr = explode(',',$ipsBindList);
		//Serialize
		$bindips_arr = serialize($bindips_arr);

		if ($curIpsBindList != $bindips_arr) {
			$modcomment = date("Y-m-d H:i") . " - Bind Ips has been updated by " . $CURUSER['username'] .  ".\n" . $modcomment;
			write_sysop_log("$user_username bind ip list has been updated to $ipsBindList");
		}

		$bindips_arr = _esc($bindips_arr);

		q("REPLACE INTO users_acl VALUES ($userid,$bindips_arr)");
	}

/*
	Invite disabling
*/
  if ($invite_disabled != $curInvite_disabled) {
		if ($invite_disabled == 'yes') {
			$curuser_opt = setflag($curuser_opt,$conf_user_opt['invite_disabled'],true);
			$modcomment = date("Y-m-d H:i") . " - Invite disabled by " . $CURUSER['username'] . " Reason: $invitedisablepm.\n" . $modcomment;
			newPM(0, $userid, __('Ați primit interdicția de a mai invita pe cineva.')."\n\n".__('Motiv: ').$invitedisablepm);
		} else {
			$curuser_opt = setflag($curuser_opt,$conf_user_opt['invite_disabled'],false);
			$modcomment = date("Y-m-d H:i") . " - Invite enabled by " . $CURUSER['username'] . ".\n" . $modcomment;
			newPM(0, $userid, __('Dreptul de a acorda invitații ți-a fost restabilit.'));
		}
  }

  if ($moderator_pe_temele_sale != $curmoderator_pe_temele_sale) {
		if ($moderator_pe_temele_sale == 'yes') {
			$curuser_opt = setflag($curuser_opt,$conf_user_opt['moderator_pe_tema_sa'],true);
			$modcomment = date("Y-m-d H:i") . ' - Modul "moderator pe temele sale" a fost activat de ' . $CURUSER['username'] . ".\n" . $modcomment;
			newPM(0, $userid, __('Modul "moderator pe temele mele" a fost activat pentru dvs.'));
		} else {
			$curuser_opt = setflag($curuser_opt,$conf_user_opt['moderator_pe_tema_sa'],false);
			$modcomment = date("Y-m-d H:i") . ' - Modul "moderator pe temele sale" a fost dezactivat de ' . $CURUSER['username'] . ".\n" . $modcomment;
			newPM(0, $userid, __('Modul "moderator pe temele mele" a fost dezactivat pentru dvs.'));
		}
  }


  if ($enabled != $curenabled) {
  	if ($enabled == 'yes') {
  		$modcomment = date("Y-m-d H:i") . " - Enabled by " . $CURUSER['username'] . ".\n" . $modcomment;

  		// Send a message to the user about his enable..
  		email_to($arr['email'],$arr['username'] . ' account enabled','Felicitari ! Accountul tau, '.$arr['username'].', a fost restabilit.', false);
  	} else {

  		$modcomment = date("Y-m-d H:i") . " - Disabled by " . $CURUSER['username'] . ".\n" . $modcomment;

  		topic_post(59277, '[b]'. $CURUSER['username'] . '[/b] a facut disable userului [url=/userdetails.php?id=' . $arr['id'] . ']' . $arr['username'] . '[/url]' .
  							' motiv ' . $usr_comment, 3 );

  		email_to($arr['email'],$arr['username'] . ' account disabled','Ne pare rau dar accountul dvs., '.$arr['username'].', a fost inchis. Motiv: '.$usr_comment, false);
  	}
  }

if ($curavatar != $avatar) {
    if ($avatar == 'yes') $modcomment = date("Y-m-d H:i") . " - Avatar enabled by " . $CURUSER['username'] . ".\n" . $modcomment;

    else
    {
	$modcomment = date("Y-m-d H:i") . " - Avatar disabled by " . $CURUSER['username'] . ".\n" . $modcomment;
	$pm_msg = "Avatarul Dvs. a fost dezactivat de către Administrație.";
	newPM(0, $userid, $pm_msg);
    }
}

  if ($curdonor != $donor && get_user_class() == UC_ADMINISTRATOR) {
  	  if ($donor == 'yes') $modcomment = date("Y-m-d H:i") . ' - Donor status added by ' . $CURUSER['username'] . ".\n" . $modcomment;
  	  else $modcomment = date("Y-m-d H:i") . ' - Donor status removed by ' . $CURUSER['username'] . ".\n" . $modcomment;
  }

	if ( $CURUSER["class"] == UC_SYSOP )
	{
		foreach( $user_icons AS $flag => $flag_data )
		{
			if ( !$flag_data['canSet'] || !$flag_data['award'] )
				continue;

			$haveCurrentFlag = ( have_flag($flag, $curuser_opt) )?true:false;
			$flagToUpdate = ( $_POST[$flag_data['name']]=='yes' )?true:false;

			if ($haveCurrentFlag != $flagToUpdate)
			{
				if ($flagToUpdate)
				{
					$modcomment = date("Y-m-d H:i") . ' - Flagul '. $flag_data['name'] .' a fost adăugat de către ' . $CURUSER['username'] . ".\n" . $modcomment;
					$curuser_opt = setflag($curuser_opt,$conf_user_opt[$flag],true);
				}
				else
				{
					$modcomment = date("Y-m-d H:i") . ' - Flagul '. $flag_data['name'] .' a fost revocat de către ' . $CURUSER['username'] . ".\n" . $modcomment;
					$curuser_opt = setflag($curuser_opt,$conf_user_opt[$flag],false);
				}
			}
		}
	}


  if (isset($_POST['spanked']) &&  $_POST['spanked'] == 'no' && get_user_class() >= UC_ADMINISTRATOR) {
	$modcomment = date("Y-m-d H:i") . ' - Bătaie dezactivată de către ' . $CURUSER['username'] . ".\n" . $modcomment;
	$curuser_opt = setflag($curuser_opt,$conf_user_opt['spanked'],false);
  }

  if ($title != $curtitle) {
  	  $modcomment = date("Y-m-d H:i") . ' - Title was changed to "' . $title . '" by ' . $CURUSER['username'] . ".\n" . $modcomment;
  }

  if (strlen($usr_comment) > 0) {
  	  $modcomment = date("Y-m-d H:i") . ' - Comment ' . $CURUSER['username'] . ': <br>' . str_replace("\n","<br>",$usr_comment) . ".\n" . $modcomment;
  }

  function toPositive($number) {
  	  if ($number < 0) {
  	  	  return $number * -1;
  	  }
  	  return $number;
  }

  //Convert xx GB to xxxxxx bytes
  // @input - size - ex. "100 GB" with a space
  // @input - onerror - on error, return this
  function size_tobytes(&$size, $onerror) {
  	    $arr_uploaded = explode(' ', $size); //[0] nr [1] mb/gb/tb
  	    if (count($arr_uploaded) != 2 || !is_numeric($arr_uploaded[0]) || $arr_uploaded[0] <= 0 || strlen($arr_uploaded[1]) != 2) return $onerror;
  	    $amount = $arr_uploaded[0];
  	    $masure = $arr_uploaded[1];
  	    if ($masure == 'MB') return ceil($amount * 1048576);
  	    if ($masure == 'GB') return ceil($amount * 1073741824);
  	    if ($masure == 'TB') return ceil($amount * 1099511627776);
  	    return $onerror;
  }

  $uploadedHuman = $uploaded;
  $curuploadedHuman = mksize($curuploaded);
  $downloadedHuman = $downloaded;
  $curdownloadedHuman = mksize($curdownloaded);

  $uploaded = size_tobytes($uploaded, $curuploaded);
  $downloaded = size_tobytes($downloaded, $curdownloaded);

  if ( ($uploaded < $curuploaded || get_user_class() == UC_SYSOP || get_user_class() == UC_ADMINISTRATOR) && ( $uploadedHuman != $curuploadedHuman ) && ( toPositive($uploaded - $curuploaded) > 5368709119) ) {
  	  $modcomment = date("Y-m-d H:i") . ' - Uploaded('.mksize($curuploaded).'->'.mksize($uploaded).') by ' . $CURUSER['username'] . ".\n" . $modcomment;
  	  $pm_msg = 'Your upload was modified ' . mksize($curuploaded).'->'.mksize($uploaded);
  	  newPM(0, $userid, $pm_msg);
  } else $uploaded = $curuploaded;


  if ( ($downloaded < $curdownloaded || get_user_class() == UC_SYSOP || get_user_class() == UC_ADMINISTRATOR) && ( $downloadedHuman != $curdownloadedHuman ) && ( toPositive($downloaded - $curdownloaded) > 5368709119) ) {
  	  $modcomment = date("Y-m-d H:i") . ' - Downloaded('.mksize($curdownloaded).'->'.mksize($downloaded).') by ' . $CURUSER['username'] . ".\n" . $modcomment;
  	  $pm_msg = 'Your download was modified ' . mksize($curdownloaded).'->'.mksize($downloaded);
  	  newPM(0, $userid, $pm_msg);
  } else $downloaded = $curdownloaded;


  if ($downloaded < 0) $downloaded = 0;
  if ($uploaded < 0) $uploaded = 0;

  $modcomment_changes = str_replace($curmodcomment,'',$modcomment); //Get only changed part
  $modcomment_changes_arr = split("\n", trim($modcomment_changes));

  $modcomment_insert_values = '';
  foreach($modcomment_changes_arr as $modcomment_line) {
  	  list(,$comment_only) = split(' - ', $modcomment_line); //Remove time
  	  //$comment_only = $user_username . ' - ' . $comment_only . ' (' . getip() . ')'; //Add moder ip to the end
	  $comment_only = $user_username . ' - ' . $comment_only;
  	  $modcomment_insert_values .= '(0, NOW(),' . _esc($comment_only) . '), ';
  }
  if ($modcomment_insert_values != '') {
  	  unset($comment_only);
  	  $modcomment_insert_values = rtrim($modcomment_insert_values,', ');
  	  q('INSERT INTO `moderslog` VALUES ' . $modcomment_insert_values);
  }




  $updatesetDownUp = array();

  $updateset[] = "enabled = " . sqlesc($enabled);
  $updateset[] = "donor = " . sqlesc($donor);
  $updateset[] = "avatar = " . sqlesc($avatar);



  //$updateset[] = "uploaded = " . sqlesc($uploaded);
  //$updateset[] = "downloaded = " . sqlesc($downloaded);

  $updatesetDownUp[] = "uploaded = " . sqlesc($uploaded);
  $updatesetDownUp[] = "downloaded = " . sqlesc($downloaded);

  $updateset[] = "title = " . sqlesc($title);
  $updateset[] = "user_opt = " . sqlesc($curuser_opt);
  //$updateset[] = "modcomment = " . sqlesc($modcomment);
  q("UPDATE users SET  " . implode(", ", $updateset) . " WHERE id=$userid");

  q('UPDATE users_rare SET modcomment=:modcomment WHERE id=:id', array('id'=>$userid, 'modcomment'=>$modcomment) );

  q("UPDATE users_down_up SET  " . implode(", ", $updatesetDownUp) . " WHERE id=$userid");

  cache_user_expire($userid);

  // Clean the cache
  mem_delete('user_'.$arr['passkey']);

  $returnto = $_POST["returnto"];

  header("Location: ./$returnto");
  die;
}

puke();

?>
