<?php
require_once "include/bittorrent.php";
require_once $INCLUDE . "functions_additional.php";

loggedinorreturn();

stdhead($CURUSER["username"] . "'s torrents");

$where = "WHERE owner = " . $CURUSER["id"];
$res = q("SELECT COUNT(*) FROM torrents $where");
$row = mysql_fetch_array($res);
$count = $row[0];

if (!$count) {
?>
<h1><?=__('Nu aveţi torrente')?></h1>
<p><?=__('Nu aţi încărcat nici un torrent încă, aşa că pagina este goală')?></p>
<?php
}
else {
	list($pagertop, $pagerbottom, $limit) = pager(20, $count, "mytorrents.php?");

	$res = q("SELECT torrents.type, torrents.comments, torrents.leechers, torrents.seeders, 
			IF(torrents.numratings < $minvotes, NULL, ROUND(torrents.ratingsum / torrents.numratings, 1)) AS rating, 
			torrents.id, categories.name AS cat_name, categories.image AS cat_pic, torrents.name, save_as, numfiles, 
			added, size, views, visible, hits, times_completed, category,
			torrents.moder_status
			FROM torrents 
			LEFT JOIN categories ON torrents.category = categories.id 
			$where 
			ORDER BY added DESC $limit");

	print($pagertop);

	torrenttable2($res, "mytorrents");

	print($pagerbottom);
}

stdfoot();


?>
