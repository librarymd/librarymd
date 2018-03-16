<?php
$GLOBALS['NOIMAGES_PROXIFY'] = true;
ini_set('display_errors','0');
require_once("include/bittorrent.php");
require_once($INCLUDE . 'torrent_opt.php');
require_once($INCLUDE . 'classes/users.php');

// Include browse.php lang file
include $GLOBALS['WWW_ROOT'] . 'lang/browse.php_' . get_lang(). '.php';

if (!preg_match(':^/(\d{1,10})/(.+)\.torrent$:', $_SERVER["PATH_INFO"], $matches) && !isset($_GET['id']) )
	httperr();

$id = 0 + $matches[1];

if (isset($_GET['id'])) $id = $_GET['id'] + 0;

if (!$id)
	httperr();

$torrent = mem_get('torrent_'.$id);

if ($torrent === false) {
	$torrent = fetchRow('SELECT name,filename,category,added,UNIX_TIMESTAMP(added) AS ts,moder_status FROM torrents WHERE id=:id',array('id'=>$id));
} else {
	$torrent = unserialize($torrent);
	$torrent['ts'] = strtotime($torrent['added']);
}

if (torrent_status_downloadable($torrent) !== true && !isModerator()) {
	barkk(torrent_status_downloadable($torrent));
}

$row = $torrent;

$fn = "$torrent_dir/$id.torrent";

if (!$row || !is_file($fn) )
	httperr();

require_once "include/benc.php";

$dict = bdec_file($fn, (1024*1024));

unset($dict['value']['announce']);

$announceListTrackers = array();
foreach (getPublicTrackers() as $tracker) {
	$announceListTrackers[] =
		array('type' => "list", 'value' =>
			array(
				array('type' => 'string', 'value' => $tracker)
			)
		);
}

$dict['value']['announce-list'] = array('type' => "list", 'value' => $announceListTrackers);

header('Content-Disposition: inline; filename="' . $row['filename'] . '"');
header("Content-Type: application/x-bittorrent; charset=utf-8;");
print(benc($dict));
flush();
?>