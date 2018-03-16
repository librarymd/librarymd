<?php
require_once("../../include/bittorrent.php");

if (php_sapi_name() != "cli") die();

$destination_file = 'torrents.json';
$handle = fopen($destination_file, 'w') or die('Cannot open file:  '.$destination_file);

$cursor = q(
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
  ORDER BY torrents.id ASC
  WHERE dht_peers != 0'
);

$currentItem = 0;

while ($torrent = @mysql_fetch_assoc($cursor)) {
  $torrent['descr_ar'] = unserialize($torrent['descr_ar']);
  if (strlen($torrent['image']) > 0)
    $torrent['image'] = $torrent['id'] . '_' . $torrent['image'];
  $torrent_json = json_encode($torrent, JSON_UNESCAPED_UNICODE);

  fwrite($handle, $torrent_json . "\n");

  $currentItem++;

  if ($currentItem % 1000 == 0) {
    echo "$currentItem ";
  }
}
