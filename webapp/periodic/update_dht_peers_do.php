<?php
if (php_sapi_name() != "cli") die();

require dirname(__FILE__) . "/../include/bittorrent.php";

$script_debug = false;

$maxRunIterations = 4;

function run($currentRunIteration) {
  global $script_debug;
  global $globalDhtClientHost, $maxRunIterations;

  if ($currentRunIteration > $maxRunIterations) {
    die('Max iteration achieved');
  }

  if (!isset($globalDhtClientHost)) {
    die('dhtClientHost is not set');
  }

  $infoHashesWithTorrentId = fetchAll('
    SELECT id, info_hash_sha1 AS hash
    FROM torrents
    WHERE dht_peers_update_scheduled = "yes"
    ORDER BY torrents.id DESC
    LIMIT 100');

  if (empty($infoHashesWithTorrentId)) {
    echo "Nothing to do";
    die();
  }

  $infoHashes = [];
  $torrentIds = [];
  $infoHashToTorrentId = [];
  foreach ($infoHashesWithTorrentId as $infoHashWithTId) {
    $hash                       = $infoHashWithTId['hash'];
    $infoHashes[]               = $hash;
    $infoHashToTorrentId[$hash] = $infoHashWithTId['id'];
    $torrentIds[]               = $infoHashWithTId['id'];
  }

  q('UPDATE torrents
     SET dht_peers_update_scheduled = "no",
         dht_peers_job_started      = NOW()
    WHERE id IN (' . join(',', $torrentIds) . ')');

  $requestUrl = $globalDhtClientHost . '/dht-peers?hashcsv=' . join(',', $infoHashes);

  $json = file_get_contents($requestUrl);
  if ($json === false) {
    $error = error_get_last();
    var_dump($error);
    echo "There was an error while fetching dht-peers";
    return false;
  }
  $jsonHashes = json_decode($json, true);

  foreach ($jsonHashes as $hashWithPeers) {
    $peers     = $hashWithPeers['peers'];
    $torrentId = $infoHashToTorrentId[$hashWithPeers['hash']];
    q('UPDATE torrents
       SET dht_peers = :peers, dht_peers_updated = NOW()
       WHERE id = :id', array(
        'id' => $torrentId, 'peers' => $peers
      )
    );
    cleanTorrentCache($torrentId);
  }

  if ($script_debug) {
    var_dump($jsonHashes);
    var_dump($infoHashToTorrentId);
  }

  echo "Updated " . count($jsonHashes) . " hashes.";

  return run($currentRunIteration + 1);
}

run(1);