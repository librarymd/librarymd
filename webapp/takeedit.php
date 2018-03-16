<?php

require_once("include/bittorrent.php");
require_once($INCLUDE . 'torrent_opt.php');

require_once($GLOBALS['INCLUDE'].'torrent_description.php');
require_once($INCLUDE . 'imdb.php');


function show_error($msg) {
	genbark($msg, "Edit failed!");
}

loggedinorreturn();

if(!isset($_POST['id'])) die('Missing Id.');
$id = (int)$_POST['id'];

$id = 0 + $id;
if (!$id)
	die();


$res = q('SELECT * FROM torrents WHERE id = :id', array('id' => $id));
$row = mysql_fetch_array($res);
if (!$row) die();

$torrent = $row;

/*if (torrent_status_downloadable() !== true && !isAdmin()) {
	barkk(__('Torrentul are un statut care nu vă permite să-l editați'));
}*/

if ($CURUSER["id"] != $row["owner"] && get_user_class() < UC_MODERATOR && get_user_class() != UC_SANITAR) {
	show_error(__('Autorul nu sunteţi dvs! Cum s-a întimplat asta?'));
}

$t_details=torrent_get_array_of_description(); //Return the array for torrent_details sql query

$sql=array(); //This will contain escaped elements
//Torrent name
$sql['torrent_name'] = _esc($t_details['name']);
$sql['type'] = (int)$t_details['type'];

// Anti mass edit prot

if ( ($editphp_lock = mem_get_user("editphp_lock")) > 0 ) {
	stderr("Eroare",'Temporar functia de editare a torrentelor a fost închisă în mod automat din cauza nr. <a href="/log.php">abundent</a> de editări, curind va reveni.');
	die();
}

function mass_edit_triggered($str) {
	mem_set_user("editphp_lock",time(),3600);
    write_torrent_moders_log("Edit.php anti-flood prot triggered by " . $GLOBALS['CURUSER']['username']);
	email_to(get_config_variable('security','email'),'Edit.php anti-flood prot triggered', $str . " user id:".$GLOBALS['CURUSER']['id'], false);
	die();
}

if ( ($antiflood = mem_get_user("editphp_edits")) != null) { //ftime first time
	$antiflood_ftime = mem_get_user("editphp_time");
	$antiflood_seconds_ago = time() - $antiflood_ftime; //first message nseconds ago
	//echo "total: $antiflood sec ago: $antiflood_seconds_ago<br/>";die();
	if ( $antiflood >= 5 && $antiflood_seconds_ago <= 7 ) {
		mass_edit_triggered("more than 5 edits in less or equal 7 seconds");
	} elseif ($antiflood >= 20 && $antiflood_seconds_ago <= 100 ) {
		mass_edit_triggered("more than 20 edits in less than 100 seconds");
	} else {
		mem_set_user("editphp_edits", $antiflood + 1, nu_zero(60 - $antiflood_seconds_ago) );
	}
} else {
	mem_set_user("editphp_edits", 1, 60);
	mem_set_user("editphp_time",time(),60);
}



// Normal behavior

$updateset = array();

list($image,$img_tmpname) = check_torrent_image();
if (strlen($image) > 1) {
	if ($row['image'] != 0) {
		unlink("$torrent_img_dir/{$id}_{$row['image']}");
	}
	resize_prop($img_tmpname,"$torrent_img_dir/{$id}_{$image}",700,700);
	$updateset[] = "image = '".$image."'";
}
if ($row['image'] && isset($_POST['delete_image']) && $_POST['delete_image'] == 1) {
	unlink("$torrent_img_dir/{$id}_{$row['image']}");
	$updateset[] = 'image = 0';
}

/**
	Team section
*/
// Only owner of the torrent can set/unset the team
if ($CURUSER["id"] == $row["owner"]) {
	$teamRelease = 0;
  if (post('teamRelease') && $CURUSER['team'])
	  $teamRelease = $CURUSER['team'];
  $updateset[] = 'team = ' . _esc($teamRelease);
}

//Update torrents table
$name = $row['name'];

$fname = $row["filename"];
preg_match('/^(.+)\.torrent$/si', $fname, $matches);
$shortfname = $matches[1];
$dname = $row["save_as"];


$updateset[] = "name = " . $sql['torrent_name'];
$updateset[] = "category = " . $sql['type'];
q('UPDATE torrents SET ' . join(",", $updateset) . " WHERE id = $id");


//Update torrents_description table

$sql['desc_ar'] = _esc($t_details['description_ar']);
$sql['desc_search_str'] = _esc($t_details['description_search_str']);
$sql['desc_details_html'] = _esc($t_details['description_details_html']);

q('UPDATE torrents_details
   SET descr_ar='.$sql['desc_ar'] . ', descr_html=' . $sql['desc_details_html'] . "
   WHERE id = $id"
 );

$searchindex = $t_details['search_sql']; //All fields are already escaped, preparated for sql insert
q("UPDATE searchindex
   SET name=$searchindex[name]
   WHERE id = $id");

cleanTorrentCache($id);
cleanTorrentDetailsCache($id);

/**
	Torrent Tags
**/
//First delete all torrent tags, then add them again

/**
	Torrent Genre
**/
//Clean up, then add again, if need
q("DELETE FROM torrents_genres WHERE torrentid=$id");
if ($t_details['genre'] != '') {
	$genre = $t_details['genre'];
	if (is_array($genre)) { //If multiple
		foreach ($genre as $gid) {
			$gid = (int)$gid;
			q('INSERT INTO torrents_genres (id,categ,genre,torrentid) VALUES
				                          (0,'.(int)$t_details['type'].",$gid,$id)");
		}
	} else {
		$genre = (int)$genre;
		q('INSERT INTO torrents_genres (id,categ,genre,torrentid) VALUES
			                          (0,'.(int)$t_details['type'].",$genre,$id)");
	}
}


deletetorrent_imdb($id);

// IMDB
if (isset($t_details['imdb_tt_id'])) {
	$imdb_tt = $t_details['imdb_tt_id'];
	q('INSERT INTO torrents_imdb (torrent,imdb_tt) VALUES(:torrent,:imdb)', array('torrent'=>$id,'imdb'=>$imdb_tt));
	// If doesnt exist
	if ( fetchOne('SELECT id FROM imdb_tt WHERE id=:imdb_id', array('imdb_id'=>$imdb_tt) ) == null ) {
		q('INSERT INTO imdb_tt (id) VALUES(:imdb)', array('imdb'=>$imdb_tt));
	}
  event_new_imdb_entry_added($imdb_tt);
	$torrent_opt = q_singleval('SELECT torrent_opt FROM torrents WHERE id=:id',array('id'=>$id));
	$torrent_opt = setflag($torrent_opt, $conf_torrent_opt['have_imdb'], true);
	q('UPDATE torrents SET torrent_opt=:opt WHERE id=:id',array('id'=>$id,'opt'=>$torrent_opt));

	$total = fetchFirst('SELECT COUNT(*) FROM torrents_imdb WHERE imdb_tt=:id', array('id'=>$imdb_tt) );
	q('UPDATE imdb_tt SET torrents = :total WHERE id=:id', array('total'=>$total, 'id'=>$imdb_tt) );

	on_expire_torrent_imdb_id($id);
	on_expire_imdb_id($imdb_tt);
} else {
	$torrent_opt = q_singleval('SELECT torrent_opt FROM torrents WHERE id='.$id);
	$torrent_opt = setflag($torrent_opt, $conf_torrent_opt['have_imdb'], false);
	q('UPDATE torrents SET torrent_opt='.$torrent_opt.' WHERE id='.$id);
}

//Anonymous
$torrent_opt = q_singleval('SELECT torrent_opt FROM torrents WHERE id=:id',array('id'=>$id));
if ( !torrent_have_flag( 'anonim', $torrent_opt ) && isset($_POST['anonim']) ) {
	if ( in_array( $torrent['moder_status'], array( 'verificat', 'inchis', 'dublare', 'absorbit', 'copyright' ) ) ) {
		$torrent_opt = setflag($torrent_opt, $conf_torrent_opt['anonim'], true);
		q('UPDATE torrents SET torrent_opt=:opt WHERE id=:id',array('id'=>$id,'opt'=>$torrent_opt));
	} else {
		$torrent_opt = setflag($torrent_opt, $conf_torrent_opt['anonim_unverified'], true);
		q('UPDATE torrents SET torrent_opt=:opt WHERE id=:id',array('id'=>$id,'opt'=>$torrent_opt));
	}
} elseif ( !torrent_have_flag( 'anonim', $torrent_opt ) && !isset($_POST['anonim']) ) {
	$torrent_opt = setflag($torrent_opt, $conf_torrent_opt['anonim_unverified'], false);
	q('UPDATE torrents SET torrent_opt=:opt WHERE id=:id',array('id'=>$id,'opt'=>$torrent_opt));
}

if ($CURUSER["id"] != $row["owner"]) {
  write_torrent_moders_log("[url=./userdetails.php?id={$CURUSER['id']}]{$CURUSER['username']}[/url] a editat torrentul [url=./details.php?id=$id]{$row['name']}[/url]");
  write_log("Torrent $id ($name) was edited by staff");
} else
  write_log("Torrent $id ($name) was edited");

$returl = "details.php?id=$id&edited=1";
if (isset($_POST["returnto"])) {
	$returl .= "&returnto=" . urlencode($_POST["returnto"]);
}
header("location: $returl");
?>
