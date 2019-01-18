<?php
chdir(dirname(__FILE__));

require_once("../../../include/bittorrent.php");

if (php_sapi_name() != "cli") die();

if (!isset($torrent_img_dir)) {
  die('$torrent_img_dir is not set');
}

if (!isset($argv[1])) die('First parameter is expected to be the destination file');
if (!isset($argv[2])) die('The second parameter is expected to be the image directory');

$destination_file = $argv[1];
$destination_image_dir = $argv[2];
$handle = fopen($destination_file, 'w') or die('Cannot open file:  '.$destination_file);

if (!is_dir($destination_image_dir)) {
  die('Destination image directory is not readable');
}

function nextPageRows($last_id) {
  return fetchAll(
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
    torrents_imdb.imdb_tt,
    torrents_details.descr_ar,
    torrents_details.descr_html
    FROM torrents
    INNER JOIN torrents_details ON torrents_details.id = torrents.id
    LEFT JOIN torrents_imdb ON torrents_imdb.torrent = torrents.id
    WHERE dht_peers != 0 AND torrents.id > :last_id
    ORDER BY torrents.id ASC
    LIMIT 100',
    array('last_id' => $last_id)
  );
}

function writeTsvLine($handle, $array) {
  foreach ($array as $key => $value) {
    $array[$key] = str_replace("\t", '', $array[$key]);
    $array[$key] = str_replace("\n", '\\\\n', $array[$key]);
    $array[$key] = str_replace("\r", '', $array[$key]);
  }
  fwrite($handle, implode("\t", $array) . "\n");
}

function writeTsvHeader() {
  global $handle;

  $sampleRows = nextPageRows(0);
  $headerKeys = array_keys($sampleRows[0]);

  writeTsvLine($handle, $headerKeys);
}

function copyImage($image, $destination_image_dir) {

}

function nextBatch($last_id) {
  global $handle, $torrent_img_dir, $destination_image_dir;

  $torrents = nextPageRows($last_id);
  if (empty($torrents)) return;

  $csvDelimiter = "\t";

  foreach ($torrents as $torrent) {
    $torrent['descr_ar'] = json_encode(unserialize($torrent['descr_ar']), JSON_UNESCAPED_UNICODE);

    if (strlen($torrent['image']) > 0) {
      $torrent['image'] = $torrent['id'] . '_' . $torrent['image'];
      $pathToImage = $torrent_img_dir . '/' . $torrent['image'];
      $destinationWithImage = $destination_image_dir . '/' . $torrent['image'];
      if (!is_file($pathToImage)) {
        die("Calea spre image este gresita $pathToImage");
      }
      if (!is_file($destinationWithImage)) {
        $resut = copy($pathToImage, $destinationWithImage);
        if ($resut != true) {
          die("Error while copying $pathToImage to $destinationWithImage");
        }
      }
    }

    writeTsvLine($handle, $torrent);


    $last_id = $torrent['id'];
  }

  return nextBatch($last_id);
}

writeTsvHeader();
nextBatch(0);