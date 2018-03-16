<?php
//define('ROOT_PATH','./../');
require_once("bittorrent.php");
//$time_start = microtime_float();
//docleanup();
//autoupdate_stats();
q('SET @@wait_timeout=98800');
set_time_limit(0);
ignore_user_abort(1);
function docleanupRare() {
	global $torrent_dir, $signup_timeout, $max_dead_torrent_time, $autoclean_interval;

	write_sysop_log('cleanup.php docleanupRare begun');

	q('delete from invites where added < now() - interval 3 day');

	write_sysop_log('cleanup.php docleanupRare 0.1');

	/** Delete old users **/
	$deadtime = time() - $signup_timeout;
	$where = "WHERE status = 'pending' AND added < FROM_UNIXTIME($deadtime) AND last_login < FROM_UNIXTIME($deadtime) AND last_access < FROM_UNIXTIME($deadtime)";

	q("DELETE FROM users $where");

	write_sysop_log('cleanup.php docleanupRare 0.2');

	//delete inactive user accounts
	$secs = 336*86400;
	$dt = sqlesc(get_date_time(time() - $secs));
	$maxclass = UC_POWER_USER;

	// Sux bugs
	q('UPDATE users_down_up SET last_access=NOW() WHERE last_access="0000-00-00 00:00:00"');
	write_sysop_log('cleanup.php docleanupRare 0.3');

	// delete avatars
	$res = q("SELECT users.id,u_du.last_access,username,users.avatar_version
		FROM users
		LEFT JOIN users_down_up AS u_du ON users.id = u_du.id
		WHERE status='confirmed' AND class < $maxclass AND u_du.last_access < $dt");

	write_sysop_log('cleanup.php docleanupRare checkpoint 1');

	global $WWW_ROOT;

	// demote power users
	$minratio = 0.9;
	$res = q("SELECT users.id
		FROM users
		LEFT JOIN users_down_up AS u_du ON users.id = u_du.id
		WHERE class = 1 AND u_du.uploaded / u_du.downloaded < $minratio");

	if (mysql_num_rows($res) > 0)
	{
		$dt = sqlesc(get_date_time());
		$msg = "You have been auto-demoted from [b]Power User[/b] to [b]User[/b] because your share ratio has dropped below $minratio.\n";
		while ($arr = mysql_fetch_assoc($res))
		{
			q("UPDATE users SET class = 0 WHERE id = $arr[id]");
			newPM(0,$arr['id'],$msg);
		}
	}

	// promote Uploader
	//$limit = 1099511627776; //1TB in bytes
	$limit = 3298534883328; // 3TB in bytes
	$minratio = 2;
	$maxdt = sqlesc(get_date_time(time() - 86400*200)); //86400 = 1day
	$res = q("SELECT users.id, users.language, users.username
		FROM users
		LEFT JOIN users_down_up AS u_du ON users.id = u_du.id
		WHERE users.class = 1 AND u_du.uploaded >= $limit AND
			  u_du.uploaded / u_du.downloaded >= $minratio AND users.added < $maxdt
			  AND (SELECT count(id) FROM torrents WHERE torrents.owner=users.id) > 100");

	$uploaderClass = UC_UPLOADER;

	if (mysql_num_rows($res) > 0) {
		$dt = sqlesc(get_date_time());
		while ($arr = mysql_fetch_assoc($res)) {
			if ($arr['language'] == 2) $msg = "Поздравляем, Ваш статус повысился до [b]Uploader[/b]. :)\n\nВаш вклад высоко оценен!\n\n[b]Grivei благодарит вас, Спасибо, что Вы с нами![/b]";
			else $msg = "Felicitări, aţi fost promovat automat la statutul de [b]Uploader[/b]. :)\n\nContributia dvs este inalt apreciata.\n\n[b]MULTUMESC de la Grivei, multumesc ca sunteti cu noi![/b]";

			q("UPDATE users SET class = $uploaderClass WHERE id = $arr[id]");
			newPM(0,$arr['id'],$msg);
			write_admins_log('Auto Promoted to Uploader user: ' . $arr['username']);
		}
	}


	/**
		Delete old torrents count
	**/
	Q('DELETE FROM torrents_added WHERE addedUnix < '.(time() - 2678400)); // Delete torrents(for counts) older than 30 days

	regenerate_categs_counts();


	write_sysop_log('cleanup.php docleanupRare checkpoint 2');

	write_sysop_log('cleanup.php docleanupRare checkpoint 3');

	/**
		Clean the downloaded_torrents table
		Delete download status for torrents older than 32 days
	*/

	$torrent_32 = fetchOne('select min(id) from torrents where added > now() - interval 32 day');
	if ($torrent_32) {
		$torrent_32_100 = $torrent_32 + 150;
		$snatched_32 = fetchOne("select min(id) from snatched where torrentid >= $torrent_32 AND torrentid <= $torrent_32_100");
		if ($snatched_32) {
			q("DELETE FROM snatched WHERE id <= $snatched_32");
		}
	}


	write_sysop_log('cleanup.php docleanupRare checkpoint 4');

	update_forum_stats();

	write_sysop_log('cleanup.php docleanupRare checkpoint 7');
}

function update_forum_stats() {

	// Update forums tags
	$subcats_count = q('SELECT count(id) AS total, subcat
	FROM topics
	WHERE subcat != 0
	GROUP BY subcat');
	while ($subcat = mysql_fetch_assoc($subcats_count)) {
		q('UPDATE forums_tags SET total=:total WHERE id=:id', array('total'=>$subcat['total'], 'id'=>$subcat['subcat'] ) );
	}

	// update forum post/topic count
	$forums = q("select id from forums");
	while ($forum = mysql_fetch_assoc($forums))
	{
		$postcount = 0;
		$topiccount = 0;
		$topiccount = fetchOne("select count(id) from topics where forumid=$forum[id]");
		$postcount = fetchOne("select count(id) from posts where forumid=$forum[id]");
		q("update forums set postcount=$postcount, topiccount=$topiccount where id=$forum[id]");
	}

}

// After new peers have arived, this function should be called
function update_torrents_peers() {
	q('SET SQL_LOG_BIN=0');

	if (date('H') > 05 && date('H') < 07) {
		/*q('
			UPDATE LOW_PRIORITY torrents
			SET torrents.seeders = 0, torrents.leechers = 0
       ');*/
	}

	q('DROP TABLE IF EXISTS temp_torrents_sl');
	q("CREATE TEMPORARY TABLE temp_torrents_sl (
		`torrent` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`seeders` smallint(6) NOT NULL DEFAULT '0',
		`leechers` smallint(6) NOT NULL DEFAULT '0',
		PRIMARY KEY (`torrent`)
	) ENGINE=MYISAM");
	q('ALTER TABLE temp_torrents_sl DISABLE KEYS');

	q('INSERT INTO temp_torrents_sl
	SELECT torrent, SUM(if(seeder="yes",1,0)) AS seeders, SUM(if(seeder="no",1,0)) AS leechers
	FROM peers
	GROUP BY torrent');
	q('ALTER TABLE temp_torrents_sl ENABLE KEYS');

	$upper_limit = fetchOne('SELECT MAX(torrent) FROM temp_torrents_sl');

	for ($i=1; $i<=$upper_limit; $i=$i+25000) {
		$pass_jos = $i;
		$pass_sus = $i+25000;
		q("UPDATE torrents, temp_torrents_sl
			SET torrents.seeders = temp_torrents_sl.seeders,
			    torrents.leechers = temp_torrents_sl.leechers
		  WHERE torrents.id = temp_torrents_sl.torrent AND temp_torrents_sl.torrent >= $pass_jos AND temp_torrents_sl.torrent < $pass_sus");
		usleep(100000);// 0.1
	}
	q('SET SQL_LOG_BIN=1');
}


// After new peers have arived, this function should be called
function update_torrents_peers_old() {
	if (date('H') > 05 && date('H') < 07) {
		q('
			UPDATE LOW_PRIORITY torrents
			SET torrents.seeders = 0, torrents.leechers = 0
       ');
	}
	//
	q('
		UPDATE LOW_PRIORITY torrents
			RIGHT JOIN
		    	(SELECT peers.torrent, SUM(if(seeder="yes",1,0)) AS seeders, SUM(if(seeder="no",1,0)) AS leechers FROM peers peers GROUP BY peers.torrent) AS peersLeechers ON torrents.id = peersLeechers.torrent
		SET torrents.seeders = peersLeechers.seeders, torrents.leechers = peersLeechers.leechers
		WHERE torrents.id = peersLeechers.torrent AND (torrents.leechers != peersLeechers.leechers OR torrents.seeders != peersLeechers.seeders)
	');
}

function docleanup120() {
	global $torrent_dir, $signup_timeout, $max_dead_torrent_time, $autoclean_interval;

	set_time_limit(0);
	ignore_user_abort(1);

	// This is not refered to cleanup ;p
	check_if_all_values_are_present();

  //remove expired warnings
  $res = q("SELECT id FROM users WHERE warned='yes' AND warneduntil < NOW() AND warneduntil <> '0000-00-00 00:00:00'");
  if (mysql_num_rows($res) > 0)
  {
    $dt = sqlesc(get_date_time());
    $msg = "Your warning has been removed. Please keep in your best behaviour from now on.\n";
    while ($arr = mysql_fetch_assoc($res))
    {
      q("UPDATE users SET warned = 'no', warneduntil = '0000-00-00 00:00:00' WHERE id = $arr[id]");
      newPM(0, $arr['id'], $msg);
    }
  }

  //remove expired posting bans
  $res = q("SELECT id FROM users WHERE postingbanuntil <> '0000-00-00 00:00:00' AND postingbanuntil < NOW() AND user_opt & 8");
  if (mysql_num_rows($res) > 0)
  {
    $dt = sqlesc(get_date_time());
    $msg = "Your posting ban has been removed. Please keep in your best behaviour from now on.\n";
    while ($arr = mysql_fetch_assoc($res))
    {
      q("UPDATE users SET user_opt = user_opt & ~ 8, postingbanuntil = '0000-00-00 00:00:00' WHERE id = $arr[id]");
      newPM(0,$arr['id'],$msg);
    }
  }

  //remove expired upload bans
  $res = q("SELECT id FROM users WHERE uploadbanuntil <> '0000-00-00 00:00:00' AND uploadbanuntil < NOW() AND user_opt & 128");
  if (mysql_num_rows($res) > 0)
  {
    $dt = sqlesc(get_date_time());
    $msg = "Your torrent uploading ban has been removed. Please keep in your best behaviour from now on.\n";
    while ($arr = mysql_fetch_assoc($res))
    {
      q("UPDATE users SET user_opt = user_opt & ~ 128, uploadbanuntil = '0000-00-00 00:00:00' WHERE id = $arr[id]");
      newPM(0,$arr['id'],$msg);
    }
  }


  //remove expired download bans
  $res = q("SELECT id FROM users WHERE downloadbanuntil <> '0000-00-00 00:00:00' AND downloadbanuntil < NOW() AND user_opt & 512");
  if (mysql_num_rows($res) > 0)
  {
    $dt = sqlesc(get_date_time());
    $msg = "Your torrent download ban has been removed. Please keep in your best behaviour from now on.\n";
    while ($arr = mysql_fetch_assoc($res))
    {
      q("UPDATE users SET user_opt = user_opt & ~ 512, downloadbanuntil = '0000-00-00 00:00:00' WHERE id = $arr[id]");
      newPM(0,$arr['id'],$msg);
    }
  }

  //remove expired spanks
  $res = q("SELECT users.id
  			FROM users
  			LEFT JOIN users_additional ON users.id = users_additional.id
  WHERE users_additional.spankuntil <> '0000-00-00 00:00:00' AND users_additional.spankuntil < NOW() AND user_opt & 1024");
  if (mysql_num_rows($res) > 0)
  {
    $dt = sqlesc(get_date_time());
    $msg = "Your spank-mode has been removed. Please keep in your best behaviour from now on.\n";
    while ($arr = mysql_fetch_assoc($res))
    {
      q("UPDATE users SET user_opt = user_opt & ~ 1024 WHERE id = $arr[id]");
      q("UPDATE users_additional SET spankuntil = '0000-00-00 00:00:00' WHERE id = $arr[id]");
      newPM(0,$arr['id'],$msg);
    }
  }

	// promote power users
	$limit = 53687091200; //50*1024*1024*1024;
	$minratio = 1.05;
	$maxdt = sqlesc(get_date_time(time() - 86400*60)); //86400 = 1day
	$res = q("SELECT users.id
		FROM users
		LEFT JOIN users_down_up AS u_du ON users.id = u_du.id
		WHERE users.class = 0 AND u_du.uploaded >= $limit AND u_du.uploaded / u_du.downloaded >= $minratio
			  AND users.added < $maxdt");
	$powerUserClass = UC_POWER_USER;

	if (mysql_num_rows($res) > 0)
	{
		$dt = sqlesc(get_date_time());
		$msg = "Congratulations, you have been auto-promoted to [b]Power User[/b]. :)\n";
		while ($arr = mysql_fetch_assoc($res))
		{
			q("UPDATE users SET class = $powerUserClass WHERE id = $arr[id]");
			newPM(0,$arr['id'],$msg);
		}
	}


	// delete old private messages, if total nr. of private messages for a user exceed 100 messages

	//Cleanup eaccelerator

	if (mem_get('genres_count_3') == false) {
		regenerate_categs_counts();
	}
}

function regenerate_categs_counts() {
	/**
	  Regenerate categs Genres counters array
	**/
	// Get all categs id
	$res = q('SELECT categ FROM torrents_genres GROUP BY categ');

	while ($row = mysql_fetch_assoc($res)) {
		$categ = $row['categ'];
		$res_genres = q("SELECT genre,count(genre) as g_count FROM torrents_genres WHERE categ=$categ GROUP BY genre");
		$cur_cat_genres_count = array();
		while ($row_genre = mysql_fetch_assoc($res_genres)) {
			$cur_cat_genres_count[$row_genre['genre']] = $row_genre['g_count'];
		}
		$key_n = 'genres_count_'.$categ;
    	mem_set($key_n, serialize($cur_cat_genres_count),90800);
	}
}

function update_stats_peers() {
	$stats = mem_get('stats');
	if (!empty($stats)) $stats = unserialize($stats);
	else $stats = array();

	$peers = fetchRow("SELECT SUM(if(seeder='yes',1,0)), SUM(if(seeder='no',1,0)) FROM peers");

	$stats['seeders'] = $peers[0];
	$stats['leechers'] = $peers[1];

	mem_set('stats',serialize($stats));
}

function update_stats_get_current() {
	$stats = mem_get('stats');
	if (!empty($stats)) $stats = unserialize($stats);

	if (empty($stats) || !is_array($stats) || !is_numeric($stats['users']) || $stats['users'] == 0 ) {
		$stats = fetchOne('SELECT value FROM avps WHERE arg="stats"');
		$stats = unserialize($stats);
	}
	return $stats;
}

function update_stats_expensive() {
	$stats = update_stats_get_current();

	// Total downloaded & uploaded
	$result = q("SELECT SUM(downloaded) AS totaldl, SUM(uploaded) AS totalul FROM users_down_up");
	$row = mysql_fetch_assoc($result);
	$stats['totaldl'] = $row["totaldl"];
	$stats['totalul'] = $row["totalul"];
	$stats['users'] = q_singleval('SELECT COUNT(id) FROM users WHERE enabled="yes"');
	$stats['torrents'] = number_format(q_singleval("SELECT count(id) FROM torrents"));

	mem_set('stats',serialize($stats));
	q("UPDATE avps SET value='" . addslashes(serialize($stats)) . "' WHERE arg='stats'");
}


function update_stats() {
	set_time_limit(0);
	ignore_user_abort(1);

	$stats = update_stats_get_current();

	$stats['todayVisit'] = q_singleval('SELECT COUNT(id) FROM users_down_up WHERE last_access >= CURDATE()');

	mem_set('stats',serialize($stats));
	q("UPDATE avps SET value='" . addslashes(serialize($stats)) . "' WHERE arg='stats'");
}


function check_if_all_values_are_present() {
	$bans = mem_get('bans');
	if ($bans) return;

	mem_set('bans',1,0);

	// Bans
	$bans = fetchAll('SELECT first FROM bans');

	foreach ($bans AS $ban) {
		mem_set('ban'.$ban['first'],1,0);
	}

	// Browser Hash Ban
	$bans = fetchAll('SELECT browser_hash FROM bans_browser');

	foreach ($bans AS $ban) {
		mem_set('banh'.$ban['browser_hash'],1,0);
	}
}

?>