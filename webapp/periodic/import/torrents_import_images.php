<?php
chdir(dirname(__FILE__));
if (php_sapi_name() != "cli") die();

require_once("../../include/bittorrent.php");


$debug = false;

if (!isset($torrent_img_dir)) {
  die('$torrent_img_dir is not set');
}

function downloadToFile($url, $absoluteFilename) {
  global $debug;

  if ($debug) {
    echo "Download $url to $absoluteFilename";
  }

  $fp = fopen($absoluteFilename, 'w+');
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_TIMEOUT, 50);
  // write curl response to file
  curl_setopt($ch, CURLOPT_FILE, $fp);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
  // get curl response
  curl_exec($ch);

  $errno = curl_errno($ch);

  // $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  curl_close($ch);
  fclose($fp);

  return $errno;
}

q("UPDATE torrents_importer_images_scheduled
SET scheduled = 'scheduled'
WHERE
    scheduled != 'scheduled'
AND job_started != '1970-01-01 00:00:00'
AND NOW() - INTERVAL 10 HOUR > job_started
AND retries < 100");

$images = fetchAll(
  "SELECT torrent_id, url, retries
   FROM torrents_importer_images_scheduled
   WHERE scheduled = 'scheduled'
   ORDER BY torrent_id DESC
   LIMIT 100");

foreach ($images as $image) {
  $torrent_id = $image['torrent_id'];

  q("UPDATE torrents_importer_images_scheduled
  SET scheduled = 'in_progress', job_started = NOW()
  WHERE torrent_id = :torrent_id AND scheduled = 'scheduled'",
  array('torrent_id' => $torrent_id));

  $isImageAlreadyInProgress = mysql_affected_rows() != 1;

  if ($isImageAlreadyInProgress) {
    continue;
  }

  if ($debug)
    var_dump($image);

  $random_letters   = substr(md5(uniqid(rand(), true)), 0, 3);
  $random_file_part = $random_letters . '.jpg';

  $file_name      = $torrent_id . '_' . $random_file_part;
  $full_file_path = $torrent_img_dir . '/' . $file_name;

  $result = downloadToFile($image['url'], $full_file_path);

  if (is_file($full_file_path)) {
    list($width, $height, $type, $attr) = getimagesize($full_file_path);
    if ($type != 2 && $type != 3) {
      unlink($full_file_path);
      echo 'ERROR! Only a jpg or png image are allowed.';

      errorDownloadFile($torrent_id);
    } else {
      echo "Success $file_name";
      successFileDownloaded($torrent_id, $random_file_part);
    }
  } else {
    echo "Error, no file downloaded";

    errorDownloadFile($torrent_id);
  }
}

function errorDownloadFile($torrent_id) {
    q("UPDATE torrents_importer_images_scheduled
        SET scheduled = 'error', retries = retries + 1
        WHERE torrent_id = :torrent_id",
       array('torrent_id' => $torrent_id)
     );
}

function successFileDownloaded($torrent_id, $random_file_part) {
  q('UPDATE torrents SET image = :file_name WHERE id = :torrent_id',
    array('torrent_id' => $torrent_id, 'file_name' => $random_file_part)
  );

  q(
    'delete
     from torrents_importer_images_scheduled
     where
     torrent_id = :torrent_id', array('torrent_id' => $torrent_id)
  );
}

