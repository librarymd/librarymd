<?php
chdir(dirname(__FILE__));
if (php_sapi_name() != "cli") die();

require_once("../../include/bittorrent.php");

require_once($WWW_ROOT . 'include/categtags/torrent_edit_inc.php');
require_once("./torrents_import_inc.php");

function updateLastId($domain, $lastId) {
  q('UPDATE torrents_importer_status SET last_id = :lastId WHERE domain = :host',
    array('lastId' => $lastId, 'host' => $domain)
  );
  if (q_mysql_affected_rows() == 0) {
    q('insert into torrents_importer_status (domain, last_id) VALUES (:domain, :last_id)',
        array('lastId' => $lastId, 'host' => $domain)
    );
  }
}

function getLastId($domain) {
  return fetchOne('
    SELECT last_id
    FROM torrents_importer_status
    WHERE domain = :domain', array('domain' => $domain)
  );
}

echo nextIteration(0);

function logFetchUrl($url) {
  echo $url;
  ob_flush();
}

function nextIteration($processed_counter) {
  $globalImportTorrentsFromUrl  = 'https://torrentsmd.com'; // Update with the domain that has /tools/torrents_export.php script
  $fullImageBase                = $globalImportTorrentsFromUrl . '/torrents_img/';
  $parsedUrl                    = parse_url($globalImportTorrentsFromUrl);
  $globalImportTorrentsFromHost = $parsedUrl['host'];

  $lastId                       = getLastId($globalImportTorrentsFromHost);

  if ($lastId > 0) {
    $url = $globalImportTorrentsFromUrl . '/tools/torrents_export.php?start_id=' . $lastId;
  } else {
    $url = $globalImportTorrentsFromUrl . '/tools/torrents_export.php?last_page=true';
  }

  logFetchUrl($url);
  $receivedDataJson = file_get_contents($url);
  $receivedData     = json_decode($receivedDataJson, true);

  if (!isset($receivedData['torrents'])) {
    die('Bad format');
  }

  $torrents = $receivedData['torrents'];

  if (empty($torrents)) {
    die('No new torrents available');
  }

  if ($lastId == null) {
    $latestId = $torrents[0]['id'];
    q('insert into torrents_importer_status (domain, last_id) VALUES (:domain, :last_id)',
      array('domain'  => $domain,'last_id' => 0)
    );
  }

  foreach ($torrents as $torrent) {
    $remoteTorrentId = $torrent['id'];

    $result   = import_torrent($torrent, $fullImageBase);
    $inserted = $result['inserted'];

    updateLastId($globalImportTorrentsFromHost, $remoteTorrentId);

    if ($inserted == false) {
      $reason = $result['reason'];
      echo "$remoteTorrentId not inserted, reason $reason\n<br/>";
    }
  }

  echo "Processed " . $processed_counter . ", current remote id: $remoteTorrentId\n";

  event_torrent_changed_any();

  return nextIteration($processed_counter + 100);
}

