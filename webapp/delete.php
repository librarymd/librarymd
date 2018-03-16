<?php
require_once("include/bittorrent.php");

function bark($msg) {
  stdhead();
  stdmsg("Delete failed!", $msg);
  stdfoot();
  exit;
}

if (!mkglobal("id"))
	bark("missing form data");

$id = 0 + $id;
if (!$id)
	die();



loggedinorreturn();

// Anti mass delete prot

if (mem_get("deletephp_lock") == 1) {
	stderr("Eroare","Temporar functia de stergere a torrentelor a fost închisă");
	die();
}

function mass_delete_triggered() {
	mem_set("deletephp_lock",1,600);
	email_to(get_config_variable('security','email'),'Delete.php anti-flood prot triggered', "Referer: {$_SERVER[HTTP_REFERER]} Userid: $_COOKIE[uid] REQUEST_URI: {$_SERVER[REQUEST_URI]} \n\n" . var_export($_POST,true) . "\n\n" . var_export( $_SERVER, true ) , false);
	die();
}

// Delete part start here

$res = q("SELECT id,name,owner,seeders,leechers,image,added FROM torrents WHERE id = $id");
$row = mysql_fetch_array($res);
if (!$row)
	die();

$dead = 0;
if ($row['seeders'] == 0 && $row['leechers'] == 0) $dead = 1;

if ($CURUSER["id"] != $row["owner"] && get_user_class() < UC_MODERATOR) {
	bark("You're not the owner! How did that happen?\n");
}

// Anti mass delete prot
if ( ($antiflood = mem_get("deletephp_deletes")) != null && ($antiflood_ftime = mem_get("deletephp_time")) != null ) { //ftime first time
	$antiflood_seconds_ago = time() - $antiflood_ftime; //first message nseconds ago

	$antiflood_seconds_left = 300 - $antiflood_seconds_ago;
	if ($$antiflood_seconds_left > 300 || $$antiflood_seconds_left <= 0) $antiflood_seconds_left = 1;

	if ( $antiflood >= 15 && $antiflood_seconds_ago <= 60 ) {
		mass_delete_triggered();
	} elseif ($antiflood >= 30) {
		mass_delete_triggered();
	} else {
		mem_set("deletephp_deletes", $antiflood + 1, $antiflood_seconds_left );
	}
} else {
	mem_set("deletephp_deletes", 1, 300);
	mem_set("deletephp_time",time(),300);
}

// Normal behavior

if ($CURUSER["id"] == $row["owner"]) {
	if ( (time() - strtotime($row['added'])) > 259200 && $row['seeders'] > 0 ) {
		write_moders_log("Utilizatorul [url=./userdetails.php?id={$CURUSER[id]}]{$CURUSER[username]}[/url] a încercat să-și șteargă [url=/details.php?id={$row['id']}]torrentul[/url].");
		stderr("Eroare","Nu puteți șterge torrentele mai vechi de 3 zile. Torrentele sunt seedate și de alte persoane, deasemenea sunt si comentariile care apartin autorilor. Adresați-vă administratorilor dacă întradevăr aveți un motiv întemeiat.");
		die();
	}
}

$rt = 0 + $_POST["reasontype"];

if (!is_int($rt) || $rt < 1 || $rt > 5)
	bark("Invalid reason $rt.");
$reason = $_POST["reason"];

if ($rt == 1 && $dead)
	$reasonstr = "Dead: 0 seeders, 0 leechers = 0 peers total";
elseif ($rt == 2)
	$reasonstr = "Dupe" . ($reason[0] ? (": " . trim($reason[0])) : "!");
elseif ($rt == 3)
	$reasonstr = "Nuked" . ($reason[1] ? (": " . trim($reason[1])) : "!");
elseif ($rt == 4)
{
	if (!$reason[0])
		bark("Please describe the violated rule.");
  $reasonstr = "TB rules broken: " . trim($reason[0]);
}
else
{
	if (!$reason[1])
		bark("Please enter the reason for deleting this torrent.");
  $reasonstr = trim($reason[1]);
}

deletetorrent($id,$row['image']);
cleanTorrentCache($id);
cleanTorrentDetailsCache($id);

if ($row['owner'] != $CURUSER['id']) $msg = "Torrent $id ($row[name]) was deleted by Staff ($reasonstr)\n";
else $msg = "Torrent $id ($row[name]) was deleted ($reasonstr)\n";
$msg_moder = "Torrent $id ([i]{$row['name']}[/i]) was deleted by [url=/userdetails.php?id={$CURUSER['id']}]{$CURUSER['username']}[/url] ($reasonstr)\n";

write_log($msg);
write_moders_log($msg_moder);

//Send private message to the torrent owner
if ($row['owner'] != $CURUSER['id']) {
	newPM(0, $row['owner'], $msg);
}

stdhead("Torrent deleted!");

if (isset($_POST["returnto"]))
	$ret = "<a href=\"" . esc_html($_POST["returnto"]) . "\">Go back to whence you came</a>";
else
	$ret = "<a href=\"./\">Back to index</a>";

?>
<h2>Torrent deleted!</h2>
<p><?= $ret ?></p>
<?php

stdfoot();


?>