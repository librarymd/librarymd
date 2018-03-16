<?php

function import_torrent($torrent, $fullImageBase) {
  $remoteTorrentId = $torrent['id'];
  $torrentDescrArSer = serialize($torrent['descr_ar']);

  $foundLocalId = fetchOne('
    SELECT id
    FROM torrents
    WHERE info_hash_sha1 = :info_hash_sha1', array('info_hash_sha1' => $torrent['info_hash_sha1'])
  );

  if ($foundLocalId != null) {
    return array(
      "inserted" => false,
      "reason" => "Already there"
    );
  }

  $descriptionTooShort = strlen($torrentDescrArSer) < 10;
  if ($descriptionTooShort) {
    return array(
      "inserted" => false,
      "reason" => "Not valid remote torrent id $remoteTorrentId"
    );
  }

  q('INSERT INTO torrents
      (name, info_hash_sha1, size, added, filename, category, numfiles, dht_peers, dht_peers_updated)
    VALUES (
      :name, :info_hash_sha1, :size, :added, :filename, :category, :numfiles, :dht_peers, :dht_peers_updated
    )',
    array(
      'name'              => $torrent['name'],
      'info_hash_sha1'    => $torrent['info_hash_sha1'],
      'size'              => $torrent['size'],
      'added'             => $torrent['added'],
      'filename'          => $torrent['filename'],
      'category'          => $torrent['category'],
      'numfiles'          => $torrent['numfiles'],
      'dht_peers'         => $torrent['dht_peers'],
      'dht_peers_updated' => $torrent['dht_peers_updated'],
    )
  );

  $newId = q_mysql_insert_id();

  q('INSERT INTO torrents_details (id, descr_ar, descr_html) VALUES (:id, :descr_ar, :descr_html)',
    array('id' => $newId, 'descr_ar' => $torrentDescrArSer, 'descr_html' => $torrent['descr_html'])
  );

  q('INSERT INTO searchindex (id, name) VALUES (:id, :name)',
    array('id' => $newId, 'name' => $torrent['name'])
  );

  $torrent_image_name = $torrent['image'];

  if (strlen($torrent_image_name) >= 2) {
    $fullImageUrl = $fullImageBase . $torrent_image_name;

    q('INSERT INTO torrents_importer_images_scheduled (torrent_id, url)
       VALUES
       (:torrent_id, :url)',
      array('torrent_id' => $newId, 'url' => $fullImageUrl)
    );
  }

  torrentCategsAutodetect($newId);

  return array(
    "inserted" => true,
    "newId"    => $newId
  );
}


function printFlush($msg) {
  echo $msg;
  @ob_flush();
}