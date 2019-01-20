<?php
chdir(dirname(__FILE__));

require_once("../../../include/bittorrent.php");
require_once($WWW_ROOT . 'include/categtags/torrent_edit_inc.php');
require_once("../torrents_import_inc.php");

if (php_sapi_name() != "cli") die();

if (!isset($argv[1])) die('First parameter is expected to be the source file');
if (!isset($argv[2])) die('The second parameter is expected to be the image directory');

if (!isset($torrent_img_dir)) {
  die('$torrent_img_dir is not set');
}

$tsvFile = $argv[1];
$importImageDirectory = $argv[2];

$csvColumns = array(
    'id',
    'info_hash_sha1',
    'size',
    'filename',
    'name',
    'added',
    'category',
    'numfiles',
    'image',
    'dht_peers',
    'dht_peers_updated',
    'imdb_tt',
    'descr_ar',
    'descr_html'
);

$handle = @fopen($tsvFile, "r") or die("Cannot open file");

function decodeTsvLine($str) {
  $array = explode("\t", $str);

  foreach ($array as $key => $value) {
    $array[$key] = trim(str_replace('\\\\n', "\n", $value));
  }

  return $array;
}

function arrayToAssoc($row, $columns) {
  $assocArray = [];
  foreach ($row as $keyI => $value) {
    $columnName = $columns[$keyI];

    $assocArray[$columnName] = $value;
  }

  return $assocArray;
}

function assertTsvFormatWillConsumeFirstLine($handle, $expectedHeaderArray) {
  $firstLine = fgets($handle, 409600);
  $firstLineElements = decodeTsvLine($firstLine);

  if ($firstLineElements != $expectedHeaderArray) {
    echo "The TSV header doesn't match the expected one.\n";
    echo "Got: ";
    var_dump($firstLineElements);
    echo "Expected: ";
    var_dump($expectedHeaderArray);
    die();
  }
}

assertTsvFormatWillConsumeFirstLine($handle, $csvColumns);

$position = 0;

if ($handle) {
    while (($buffer = fgets($handle, 409600)) !== false) {
        $lineArray = decodeTsvLine($buffer);
        $torrent = arrayToAssoc($lineArray, $csvColumns);

        $torrent['descr_ar'] = json_decode($torrent['descr_ar'], JSON_UNESCAPED_UNICODE);
        $torrent['descr_ar'] = serialize($torrent['descr_ar']);

        $source_image_full_path = $importImageDirectory . '/' . $torrent['image'];
        // if (strlen($torrent['image']) > 2) {
        //   if (!is_file($source_image_full_path)) {
        //     die('Cannot find the image to import on the FS ' . $source_image_full_path);
        //   }
        // }

        $result   = import_torrent_without_image($torrent);
        $inserted = $result['inserted'];
        $newId    = $result['newId'];

        if ($inserted == false) {
          $reason = $result['reason'];
          echo "$torrent[id] not inserted, reason: $reason\n<br/>";
        } else {
          if (strlen($torrent['image']) > 2 && is_file($source_image_full_path)) {
            import_image($newId, $source_image_full_path);
          }

          echo "Imported ! $newId";
        }

        $position++;

        if ($position % 1000 == 0) {
          echo "Position: $position, imported torrent_id $torrent[id]";
        }

    }
    fclose($handle);
}

function import_image($torrent_id, $source_image_full_path) {
  global $torrent_img_dir;

  // Image 9209_933.jpg
  $random_letters   = substr(md5(uniqid(rand(), true)), 0, 3);
  $random_file_part = $random_letters . '.jpg';

  $file_name = $torrent_id . '_' . $random_file_part;

  echo "Copy $source_image_full_path to $torrent_img_dir/$file_name";

  copy($source_image_full_path, $torrent_img_dir . '/' . $file_name);
}


