<?php
require_once("../../include/bittorrent.php");

$max_page_size = 100;
$startId       = (int)@$_GET['start_id'];
$lastPage      = (bool)@$_GET['last_page'];

/**
  last_page=true parameter is supposed to be used alone, without last_page,
  to pool for the latest torrents.
*/

if (!is_numeric($startId) && $lastPage !== true) {
  stderror('start_id parameter is invalid');
}

$filterById = $lastPage ? '' : 'WHERE torrents.id < :lastId';

$torrents = fetchAll(
  'SELECT
  torrents.id,
  torrents.info_hash_sha1,
  torrents.size,
  torrents.filename,
  torrents.name,
  torrents.added,
  torrents.category,
  torrents.numfiles,
  torrents.image,
  torrents.dht_peers,
  torrents.dht_peers_updated,
  torrents_details.descr_ar,
  torrents_details.descr_html
  FROM torrents
  LEFT JOIN torrents_details ON torrents_details.id = torrents.id
  ' . $filterById . '
  ORDER BY torrents.id DESC
  LIMIT 100',
  array('lastId' => $startId)
);

$torrentsDecoded = array();

foreach ($torrents as $torrent) {
  $torrent['descr_ar'] = unserialize($torrent['descr_ar']);
  if (strlen($torrent['image']) > 0)
    $torrent['image'] = $torrent['id'] . '_' . $torrent['image'];
  $torrentsDecoded[]   = $torrent;
}

$response = array('torrents' => $torrentsDecoded);

echoJson($response);

