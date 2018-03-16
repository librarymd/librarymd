<?php
chdir(dirname(__FILE__));
if (php_sapi_name() != "cli") die();

require_once("../../include/bittorrent.php");

require_once($WWW_ROOT . 'include/categtags/torrent_edit_inc.php');
require_once("./torrents_import_inc.php");

poll_last_torrents();

function poll_last_torrents() {
  $globalImportTorrentsFromUrl  = 'https://torrentsmd.com'; // Update with the domain that has /tools/torrents_export.php script
  $fullImageBase     = $globalImportTorrentsFromUrl . '/torrents_img/';
  $torrentsExportUrl = $globalImportTorrentsFromUrl . '/tools/torrents_export.php?last_page=true';

  printFlush("Request to $torrentsExportUrl");
  $receivedDataJson = file_get_contents($torrentsExportUrl);
  $receivedData     = json_decode($receivedDataJson, true);

  if (!isset($receivedData['torrents'])) {
    die('Bad format');
  }

  $torrents = $receivedData['torrents'];

  if (empty($torrents)) {
    die('No new torrents available');
  }
  
  $totalInserted = 0;

  foreach ($torrents as $torrent) {
    $remoteTorrentId = $torrent['id'];

    $result   = import_torrent($torrent, $fullImageBase);
    $inserted = $result['inserted'];

    if ($inserted == false) {
      $reason = $result['reason'];
      printFlush("$remoteTorrentId not inserted, reason $reason\n<br/>");
    } else {
      $totalInserted++;
    }
  }

  if ($totalInserted > 0) 
    event_torrent_changed_any();
  
  echo "Total inserted $totalInserted";
}
