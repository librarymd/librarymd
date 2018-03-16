<?php
include dirname(__FILE__).'/../../include/bittorrent.php';
include dirname(__FILE__).'/../../include/imdb.php';

$to_process = fetchAll('SELECT id FROM imdb_tt_to_process WHERE verified="no" LIMIT 100');

if (mem_get('moder_imdb_update_running')) {
	die('Already running');
}
mem_set('moder_imdb_update_running',1,3600);

foreach($to_process AS $tt_id) {
	$tt_id = $tt_id['id'];
	$ratings = imdb_get_rating($tt_id);

	if ($ratings === FALSE) {
		q('DELETE FROM imdb_tt_to_process WHERE id=:id', array('id'=>$tt_id));
		continue;
	}
	$ratings['votes'] = (int)str_replace(',','',$ratings['votes']);

  imdb_db_update($tt_id, $ratings);

	if ($ratings['top250'] > 0) { // A torrent in 250 rating
		q('INSERT INTO imdb_tt_top250 SET imdb=:imdb, rank=:rank',array('imdb'=>$tt_id, 'rank'=>$ratings['top250']) );
	}

	q('DELETE FROM imdb_tt_to_process WHERE id=:id', array('id'=>$tt_id));

	// Clear the imdb cache for
	$torrents = fetchAll('SELECT torrent FROM torrents_imdb WHERE imdb_tt=:imdb_tt',array('imdb_tt'=>$tt_id));
	foreach($torrents AS $torrent) {
		$torrent = $torrent['torrent'];
		on_expire_torrent_imdb_id($torrent);
	}
}

mem_delete('moder_imdb_update_running');