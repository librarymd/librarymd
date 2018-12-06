<?php
global $WWW_ROOT, $INCLUDE, $DEFAULTBASEURL;

/**
 * TODO, would be best to not use static files anymore but to use memcache instead.
 * Managing static files adds uneeded complexity.
 */

require_once $INCLUDE . "functions_additional.php";
//Tops location
$tops_dir = $WWW_ROOT . "cache/tops";

$time = time();

$tops = array();
$tops[] = array('1 day','24h');
$tops[] = array('3 day','3d');
$tops[] = array('7 day','7d');
$tops[] = array('1 month','1m');
$tops[] = array('1 year','all');

$js_template =
'function show_menu_{%name}() {
	$(\'torrents_header\').innerHTML = top_header;
	$(\'torrents\').innerHTML = \'{%html}\';
}

loading_img.hide();
show_menu_{%name}();';

foreach($tops as $top) {
	$interval = $top[0];
	$topname = $top[1];

	$res = q("
		SELECT torrents.id, torrents.category, torrents.leechers, torrents.seeders,
	   		torrents.name, torrents.times_completed, torrents.size, torrents.added,
	   		torrents.comments,torrents.numfiles,torrents.torrent_opt,torrents.filename,torrents.owner,
	   		categories.name AS cat_name, categories.image AS cat_pic, users.username,
	   		(torrents.dht_peers / 5) + torrents.thanks + (torrents.comments / 5) + (torrents.views / 10) AS rating,
            torrents.team,teams.name AS teamName, teams.initials AS teamInitials,
            torrents.moder_status
		FROM torrents
		LEFT JOIN categories ON category = categories.id
		LEFT JOIN users ON torrents.owner = users.id
        LEFT JOIN teams ON (torrents.team > 0 AND torrents.team = teams.id)
		WHERE torrents.added >= NOW() - INTERVAL $interval AND category != 6 AND category != 5 AND category != 3
		      AND torrents.visible='yes'
		ORDER BY rating
		DESC LIMIT 50
		");
	ob_start();
	torrenttable($res,'','top');
	$torrents_table = ob_get_contents();
	ob_end_clean();

	$html = str_replace( array("\n","\r",'        '), array('','',''), addslashes($torrents_table) );
	$js = str_replace( array('{%name}','{%html}'), array($topname,$html), $js_template);

	file_put_contents("$tops_dir/{$time}_{$topname}.js",  $js);
	unset($html,$js);
}
if (isset($_GET['tops'])) var_dump(mem_get('tops_generation_time'));
mem_set('tops_generation_time',$time);
if (isset($_GET['tops'])) var_dump(mem_get('tops_generation_time'));

//Now delete the old generated
//Delete all older than 3 hours
if (strlen($tops_dir) < 5) return; //just to be sure..
$d = dir($tops_dir);

while (false !== ($entry = $d->read())) {
	$curfile = $tops_dir.'/'.$entry;
	if (is_file($curfile)) {
		//60*60*3 = 10800 secs
		if ( (time() - filemtime($curfile)) > 10800) {
			unlink($curfile);
		}
	}
}
$d->close();


?>