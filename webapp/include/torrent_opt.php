<?php
require "classes/base32.php";

global $conf_torrent_opt;
$conf_torrent_opt['is_comment_locked'] = 1;
$conf_torrent_opt['is_comments_hidden'] = 2;
$conf_torrent_opt['have_imdb'] = 4;
$conf_torrent_opt['anonim'] = 8; // e definitiv!
$conf_torrent_opt['anonim_unverified'] = 16;

/**
 @flag_name - flag name to check
 @opt       - current options
*/

function torrent_have_flag($flag_name, $opt = '') {
	global $conf_torrent_opt,$CURUSER;
	if (!isset($conf_torrent_opt[$flag_name])) throw new Exception('have flag err');
	if ($opt == '') throw new Exception('torrent option');
	if ($opt & $conf_torrent_opt[$flag_name]) return true;
	return false;
}

$status_desc = array(
	 'neverificat'=>array('<span style="color: #993399;">*</span>',__('Neverificat'),'Neverificat'),
	 'se_verifica'=>array('<span style="color: #0000FF;">%</span>',__('Se verifică'),'Se verifică'),
	 'verificat'=>	  array('<span style="color: green;">√</span>',__('Verificat'),'Verificat'),
 	 'parital_necomplet'=>array('<span style="color: red;">?</span>',__('Descriere parțial necompletă'),'Descriere parțial necompletă'),
 	 'necomplet'=>    array('<span style="color: red;">!</span>',__('Descriere necompletă'),'Descriere necompletă'),
 	 'dublare'  =>  array('<span style="color: blue;">D</span>',__('Dublare'),'Dublare'),
 	 'inchis'   => array('<span style="color: red;">x</span>',__('Închis'),'Închis'),
 	 'copyright'=>  array('<span style="color: red;">©</span>',__('Închis de către deținătorul dreptului de autor'),'Închis de către deținătorul dreptului de autor'),
 	 'absorbit '=>  array('<span style="color: #996600;">∑</span>',__('Absorbit'),'Absorbit'),
 	 'dubios'   =>  array('<span style="color: green;">#</span>',__('Dubios'),'Dubios'),
	 'temporar' =>  array('<span style="color: blue;">T</span>',__('Temporar'),'Temporar')
 );

/*
	@return bool/string
	If bool true, then it's downloadable
	If string, then it's a reason why it's not downloadable
*/
function torrent_status_downloadable($torrent) {
	switch($torrent['moder_status']) {
		case 'se_verifica':
			//return __('Temporar nu se poate de copiat, se verifică.');
      return true;
		case 'inchis':
			//return __('Torrentul este închis, nu poate fi copiat.');
      return true;
		case 'necomplet':
			//return __('În descriere sunt abateri semnificative de la reguli, nu poate fi copiat.');
      return true;
		case 'dublare':
			//return __('Torrentul e o dublare, nu poate fi copiat.');
      return true;
		case 'copyright':
			return __('Deținătorul drepturilor a închis acest torrent.');
		case 'absorbit':
			//return __('Torrentul a fost absorbit, nu poate fi copiat, vezi în ultimul mesaj din comentarii de către ce a fost absorbit.');
      return true;
		default:
			return true;
	}
}

function torrent_download_link_html($torrent) {
	global $torrent;
	return sprintf( '<a class="index" href="download.php?id=%s">%s</a>', $torrent['id'], esc_html($torrent["filename"]) );
}

function torrent_get_moder_status_text($torrent) {
	return torrent_moder_status_to_text($torrent['moder_status']);
}

function torrent_moder_status_to_text($status) {
	global $status_desc;
	return $status_desc[$status][2];//am pus [2] ca statutul sa nu difere in dependentza de limba moderului...
}

function getPublicTrackers() {
  return array(
    "udp://tracker.leechers-paradise.org:6969",
    "udp://tracker.coppersurfer.tk:6969",
    "udp://zer0day.ch:1337",
    "udp://open.demonii.com:1337",
    "udp://exodus.desync.com:6969"
  );
}

		function getTorrentStatusHtml($torrent,$no_text=false) {
			global $status_desc;
			$t = @$status_desc[$torrent['moder_status']];
			if ($no_text == false)return sprintf('<b>%s</b>  &nbsp;<a href="/forum.php?action=viewtopic&topicid=361692#torstatus" target="_blank"><b>%s</b></a>',$t[0],$t[1]);
			else return sprintf('<b title="%s">%s</b>',$t[1],$t[0]);
		}
function get_announce_url($id) {
    $torrents_trackers = array();

    return '';
}

function magnet_extract_hashinfo($link) {
  $url_query = parse_url($link, PHP_URL_QUERY);
  parse_str($url_query, $result);

  $infohash = str_replace("urn:btih:", "", $result['xt']);

  if (strlen($infohash) == 32) {
    $infohash = bin2hex(Base32::decode($infohash));
  } else {
    if (strlen($infohash) != 40) {
      return "Invalid magnet link, infohash.";
    }
  }

  if (!isset($result['dn'])) {
    return 'Invalid magnet link, missing dn element.';
  }

  return array("infohash" => $infohash, "filename" => urldecode($result['dn']));
}
?>
