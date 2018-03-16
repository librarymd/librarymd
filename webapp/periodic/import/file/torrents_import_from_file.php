<?php
require_once("../../../include/bittorrent.php");
require_once($WWW_ROOT . 'include/categtags/torrent_edit_inc.php');
require_once("./torrents_import_inc.php");

if (php_sapi_name() != "cli") die();

$file = 'torrents.json';

$handle = @fopen($file, "r") or die("Cannot open file");

$fullImageBase = 'https://torrentsmd.com/torrents_img/';

if ($handle) {
    while (($buffer = fgets($handle, 409600)) !== false) {
        $torrent  = json_decode($buffer, true);
        $result   = import_torrent($torrent  , $fullImageBase);
        $inserted = $result['inserted'];

        if ($inserted == false) {
          $reason = $result['reason'];
          echo "$torrent[id] not inserted, reason: $reason\n<br/>";
        }
    }
    fclose($handle);
}

function nextIteration() {
  $globalImportTorrentsFromUrl  = 'https://torrentsmd.com'; // Update with the domain that has /tools/torrents_export.php script
  $fullImageBase                = $globalImportTorrentsFromUrl . '/torrents_img/';
  $parsedUrl                    = parse_url($globalImportTorrentsFromUrl);
  $globalImportTorrentsFromHost = $parsedUrl['host'];

  $lastId = getLastId($globalImportTorrentsFromHost);

  $url              = $globalImportTorrentsFromUrl . '/tools/torrents_export.php?start_id=' . $lastId;
  $receivedDataJson = file_get_contents($url);
  $receivedData     = json_decode($receivedDataJson, true);

  if (!isset($receivedData['torrents'])) {
    die('Bad format');
  }

  $torrents = $receivedData['torrents'];

  if (empty($torrents)) {
    die('No new torrents available');
  }

  foreach ($torrents as $torrent) {
    $remoteTorrentId = $torrent['id'];

    $result   = import_torrent($torrent);
    $inserted = $result['inserted'];

  }
}