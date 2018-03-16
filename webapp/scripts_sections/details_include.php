<?php

function getagent($httpagent, $peer_id="") {
	if (preg_match("/^Azureus ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]\_B([0-9][0-9|*])(.+)$)/", $httpagent, $matches))
		return "Azureus/$matches[1]";
	elseif (preg_match("/^Azureus ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]\_CVS)/", $httpagent, $matches))
		return "Azureus/$matches[1]";
	elseif (preg_match("/^Java\/([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
		return "Azureus/<2.0.7.0";
	elseif (preg_match("/^Azureus ([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
		return "Azureus/$matches[1]";
	elseif (preg_match("/BitTorrent\/S-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
		return "Shadow's/$matches[1]";
	elseif (preg_match("/BitTorrent\/U-([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
		return "UPnP/$matches[1]";
	elseif (preg_match("/^BitTor(rent|nado)\\/T-(.+)$/", $httpagent, $matches))
		return "BitTornado/$matches[2]";
	elseif (preg_match("/^BitTornado\\/T-(.+)$/", $httpagent, $matches))
		return "BitTornado/$matches[1]";
	elseif (preg_match("/^uTorrent\\/(.+)$/", $httpagent, $matches)) {
		if (is_numeric(substr($matches[1],0,2))) {
			$version = $matches[1][0] . '.' . $matches[1][1];
		} else $version = $matches[1];
		return "µTorrent/$version";
	}
	elseif (preg_match("/^BitTorrent\/ABC-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
		return "ABC/$matches[1]";
	elseif (preg_match("/^ABC ([0-9]+\.[0-9]+(\.[0-9]+)*)\/ABC-([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
		return "ABC/$matches[1]";
	elseif (preg_match("/^Python-urllib\/.+?, BitTorrent\/([0-9]+\.[0-9]+(\.[0-9]+)*)/", $httpagent, $matches))
		return "BitTorrent/$matches[1]";
	elseif (ereg("^BitTorrent\/BitSpirit$", $httpagent))
		return "BitSpirit";
	elseif (ereg("^DansClient", $httpagent))
		return "XanTorrent";
	elseif (preg_match("/^BitTorrent\/brst(.+)/", $httpagent, $matches))
		return "Burst/$matches[1]";
	elseif (preg_match("/^RAZA (.+)$/", $httpagent, $matches))
		return "Shareaza/$matches[1]";
	elseif (preg_match("/Rufus\/([0-9]+\.[0-9]+\.[0-9]+)/", $httpagent, $matches))
		return "Rufus/$matches[1]";
	elseif (preg_match("/^BitTorrent\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/", $httpagent, $matches))
	{
		if(substr($peer_id, 0, 6) == "exbc\08")
			return "BitComet/0.56";
		elseif(substr($peer_id, 0, 6) == "exbc\09")
			return "BitComet/0.57";
		elseif(substr($peer_id, 0, 6) == "exbc\0:")
			return "BitComet/0.58";
		elseif(substr($peer_id,0,4) == '-BC0') {
			if (is_numeric(substr($peer_id,4,3))) {
				return 'BitComet/'.$peer_id[4].'.'.substr($peer_id,5,2);
			}
		}
		elseif(substr($peer_id, 0, 7) == "exbc\0L")
			return "BitLord/1.0";
		elseif(substr($peer_id, 0, 7) == "exbcL")
			return "BitLord/1.1";
		else
			return "BitTorrent/$matches[1]";
	}
	elseif (preg_match("/^Python-urllib\\/([0-9]+\\.[0-9]+(\\.[0-9]+)*)/", $httpagent, $matches))
		return "G3 Torrent";
	elseif (preg_match("/MLdonkey( |\/)([0-9]+\\.[0-9]+).*/", $httpagent, $matches))
		return "MLdonkey$matches[1]";
	elseif (preg_match("/ed2k_plugin v([0-9]+\\.[0-9]+).*/", $httpagent, $matches))
		return "eDonkey/$matches[1]";
	else
		return "---";
}

// Will take cache in account
function update_torrent_field($id, $field, $value) {
	if (strstr($field,'`') !== false) die('Mmm');
	q("UPDATE torrents SET `{$field}`=:field WHERE id=:id", array('field'=>$value,'id'=>$id) );

	$torrent = mem_get('torrent_'.$id);
	if ($torrent !== false) {
		$torrent = unserialize($torrent);
		$torrent[$field] = $value;
		mem_set('torrent_'.$id,serialize($torrent),60,MEMCACHE_COMPRESSED);
	}
	if ($field == 'moder_status') on_torrent_moder_status_update($id);
}

$aUser = isset($CURUSER['id']);

if ($aUser != true) return;

//Thanks, redirect back if yes
if (isset($_POST['thank'])) { //If user thanked
	if ( '' == q_singleval("SELECT thank_time FROM torrents_thanks WHERE torrent=$id AND user=".$CURUSER["id"]) ) { //If doesn't already thanked

		// Get torrent owner
		$tOwner = q_singleval('SELECT owner FROM torrents WHERE id='.$id);
		if ( $tOwner != $CURUSER['id'] ) {
			Q("INSERT INTO torrents_thanks (torrent,user,torrent_owner,thank_time) VALUES ($id,{$CURUSER['id']},$tOwner,NOW())");
			Q('UPDATE users SET thanks=thanks+1 WHERE id=:id',array('id'=>$tOwner));
			Q('UPDATE torrents SET thanks = thanks + 1 WHERE id=:id', array('id'=>$id));
		}

		//Update torrent_thank cache
		$current_torrents_thanks = mem_get('torrent_thank2_'.$id);
		if ($current_torrents_thanks && strlen($current_torrents_thanks)) {
			$current_torrents_thanks .= ', ';
			$current_torrents_thanks .= '[url=./userdetails.php?id='.$CURUSER['id'].']'.$CURUSER['username'].'[/url]';
			mem_set('torrent_thank2_'.$id,$current_torrents_thanks,86400);
		} else {
			mem_delete('torrent_thank2_'.$id);
		}
	}
	if (isset($_POST['async'])) die(); // Comming from ajax
	header('Location: '. $_SERVER['HTTP_REFERER']);
	exit();
}

	if ( isset($_GET['action']) && $_GET['action']=='unsubscribe' ) {
		stderr( __('Atenție!'), '<span style="font-size:10pt">'. __("Sunteți sigur că doriți să renunțați la acest torrent?") .'
		<br>'. __("Vă amintim că această acțiune rupe orice legătură dintre tine și torrentul dat, prin urmare nu o să-l mai puteți edita vreodată iar numărul de mulțumiri a torrentului la care renunțați nu vă va modifica cifra totală a mulțumirilor.") .'</span>
<form method="POST" action="details.php"><input type="hidden" name="id" value='. $id .'>
<input type="hidden" name="unsubscribe" value="1">
<input type="submit" value="'. __("Sunt sigur că doresc să renunț la acest torrent") .'">
</form>' );
	}

	if ( isset($_POST['unsubscribe']) && $CURUSER['id'] == $torrent['owner'] && $torrent['moder_status'] == 'verificat' ) {
		Q("UPDATE torrents SET torrents.owner=0 WHERE torrents.id=:id", array('id'=>$id));
		write_sysop_log("[url=./userdetails.php?id={$CURUSER['id']}]{$CURUSER['username']}[/url] s-a dezis de: [url=./details.php?id=$id]{$torrent['name']}[/url]");
		cleanTorrentCache($id);
		redirect('/details.php?id='.$id);
	}

	if ( isset($_POST['get_torrent_owner']) && ( get_user_class() == UC_SANITAR || get_user_class() >= UC_MODERATOR ) ) {
		if ( torrent_have_flag('anonim_unverified', $torrent['torrent_opt']) ) {
			$owner = '<a href="userdetails.php?id='. $torrent['owner'] .'"><b>'. $torrent['username'] .'</b></a>';
			write_moders_log("[url=./userdetails.php?id={$CURUSER['id']}]{$CURUSER['username']}[/url] a cerut numele autorului torrentului [url=./details.php?id=$id]{$torrent['name']}[/url]");
			echo $owner;
			die;
		}
	}

	if (isset($_POST['moder_status']) && ( get_user_class() == UC_SANITAR || get_user_class() >= UC_MODERATOR || have_flag('copyrighter') ) ) {
		if ($_POST['moder_status'] == 'se_verifica' && $torrent['moder_status'] == 'se_verifica') {
			barkk('Se pare că cineva deja a pus statutul Se verifică');
		}
		//
		if ( have_flag('copyrighter') && !in_array($_POST['moder_status'],array('copyright','neverificat')) ) {
			$_POST['moder_status'] = 'neverificat';
		}

		//Anonymous
		if ( !torrent_have_flag( 'anonim', $torrent['torrent_opt'] ) && torrent_have_flag( 'anonim_unverified', $torrent['torrent_opt'] ) ) {
			if ( in_array( $_POST['moder_status'], array( 'verificat', 'inchis', 'dublare', 'absorbit', 'copyright' ) ) ) {
				$torrent['torrent_opt'] = setflag($torrent['torrent_opt'], $conf_torrent_opt['anonim'], true);
				$torrent['torrent_opt'] = setflag($torrent['torrent_opt'], $conf_torrent_opt['anonim_unverified'], false);
				update_torrent_field($id,'torrent_opt',$torrent['torrent_opt']);
			}
		}

		update_torrent_field($id,'moder_status',$_POST['moder_status']);
		$torrent['moder_status'] = $_POST['moder_status'];
		$pm_msg = sprintf(__('Statutul torrentului tău: [url=./details.php?id=%d]%s[/url] a fost schimbat în: %s de către [url=./userdetails.php?id=%d]%s[/url]'),$id, $torrent["name"], torrent_get_moder_status_text($torrent),$CURUSER["id"], $CURUSER["username"]);
		newPM(0, $torrent['owner'], $pm_msg);
		write_torrent_moders_log(sprintf('Statutul torrentului: [url=./details.php?id=%d]%s[/url] a fost schimbat în: %s de către [url=./userdetails.php?id=%d]%s[/url] ',$id, $torrent["name"], torrent_get_moder_status_text($torrent),  $CURUSER["id"], $CURUSER["username"]));
		redirect('/details.php?id='.$id);
	}

