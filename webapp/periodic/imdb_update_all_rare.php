<?php
chdir(dirname(__FILE__));
if (php_sapi_name() != "cli") die();

require dirname(__FILE__) . "/../include/bittorrent.php";
require dirname(__FILE__) . "/../include/imdb.php";

q('SET @@wait_timeout=98800');
set_time_limit(0);
ignore_user_abort(1);
ob_implicit_flush(1);

$to_process = q('SELECT * FROM imdb_tt order by date_published desc');

while ($tt_id = mysql_fetch_assoc($to_process)) {
	$tt_id = $tt_id['id'];
	$ratings = imdb_get_rating($tt_id);

	if ($ratings == false) {
		echo "Eroare la $tt_id\n";
		continue;
	}
	if (isset($GLOBALS['imdb_error']) && strlen($GLOBALS['imdb_error'])) {
		echo "Eroare la $tt_id ".$GLOBALS['imdb_error']."\n";
		continue;
	}

	$ratings['votes'] = (int)str_replace(',','',$ratings['votes']);

	if ( !($ratings['votes'] > 0) ) {
		echo "Skip $tt_id votes: ".$ratings['votes']."\n";
		continue;
	}

	echo "Update Id $tt_id rating: ",((float)$ratings['rating'])*10," votes: ",$ratings['votes'], " ",imdb_bayesian_rating($ratings['rating'],$ratings['votes'])*10,"\n";

  imdb_db_update($tt_id, $ratings);

	// Clear the imdb cache for
	$torrents = fetchAll('SELECT torrent FROM torrents_imdb WHERE imdb_tt=:imdb_tt',array('imdb_tt'=>$tt_id));
	foreach($torrents AS $torrent) {
		$torrent = $torrent['torrent'];
		mem_delete('t_imdb_'.$torrent);
	}
	sleep(1);
}
