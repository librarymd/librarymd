<?php
require_once("include/bittorrent.php");
require_once($GLOBALS['INCLUDE'].'benc.php');
require_once($GLOBALS['INCLUDE'].'torrent_description.php');
require_once($INCLUDE . 'torrent_opt.php');
require_once($INCLUDE . 'imdb.php');
require_once($WWW_ROOT . 'include/categtags/torrent_edit_inc.php');

// Functions
function show_error($msg) {
	genbark($msg, __('Încărcarea torrentului a eşuat!'));
}
function validfilename($name) {
    return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
}

ini_set("upload_max_filesize", $max_torrent_size);


loggedinorreturn();

$power_user_only = get_config_variable('upload', 'power_user_only');

if (get_user_class() < UC_POWER_USER && $power_user_only) die;


// Check if this user is not banned for torrents upload
if ($CURUSER["user_opt"] & $conf_user_opt['torrentsUploadBan']) die;


$t_details=torrent_get_array_of_description(); //Return the array for torrent_details sql query

$sql=array(); //This will contain escaped elements


//Validate torrent file
if (!isset($_FILES["file_torrent"])) show_error("missing form data");
$f = $_FILES["file_torrent"];
$fname = $f["name"];
if (empty($fname)) show_error(__('Nu ai încărcat fişierul .torrent'));

if (!validfilename($fname))
	show_error(__('Numele fisierului incorect.'));
if (!preg_match('/^(.+)\.torrent$/si', $fname, $matches))
	show_error(__('Numele fisierului incorect (trebuie să fie .torrent)'));
$shortfname = $matches[1];

$tmpname = $f["tmp_name"];
if (!is_uploaded_file($tmpname)) {
	show_error("eek");
}
if (!filesize($tmpname)) {
	show_error("Empty file!");
}

$dict = bdec_file($tmpname, $max_torrent_size);
if (!isset($dict)) {
	show_error("What the hell did you upload? This is not a bencoded file!");
}

$clean = array();

//Function to get torrent content
function dict_check($d, $s) {
	if ($d["type"] != "dictionary")
		show_error("not a dictionary");
	$a = explode(":", $s);
	$dd = $d["value"];
	$ret = array();
	foreach ($a as $k) {
		unset($t);
		if (preg_match('/^(.*)\((.*)\)$/', $k, $m)) {
			$k = $m[1];
			$t = $m[2];
		}
		if (!isset($dd[$k])) {
			if ($k == 'announce') show_error("Dictionary is missing key, please regenerate the torrent and include annonce url from previous upload page !");
			show_error("dictionary is missing key(s)");
		}
		if (isset($t)) {
			if ($dd[$k]["type"] != $t)
				show_error("invalid entry in dictionary");
			$ret[] = $dd[$k]["value"];
		}
		else
			$ret[] = $dd[$k];
	}
	return $ret;
}

function dict_get($d, $k, $t) {
	if ($d["type"] != "dictionary")
		show_error("not a dictionary");
	$dd = $d["value"];
	if (!isset($dd[$k]))
		return;
	$v = $dd[$k];
	if ($v["type"] != $t)
		show_error("invalid dictionary entry type");
	return $v["value"];
}

list($info) = dict_check($dict, "info");
list($dname, $plen, $pieces) = dict_check($info, "name(string):piece length(integer):pieces(string)");

if (strlen($pieces) % 20 != 0)
	show_error("invalid pieces");

$filelist = array();
$totallen = dict_get($info, "length", "integer");
if (isset($totallen)) {
	$filelist[] = array($dname, $totallen);
	$type = "single";
}
else {
	$flist = dict_get($info, "files", "list");
	if (!isset($flist))
		show_error("missing both length and files");
	if (!count($flist))
		show_error("no files");
	$totallen = 0;
	foreach ($flist as $fn) {
		list($ll, $ff) = dict_check($fn, "length(integer):path(list)");
		$totallen += $ll;
		$ffa = array();
		foreach ($ff as $ffe) {
			if ($ffe["type"] != "string")
				show_error("filename error");
			$ffa[] = $ffe["value"];
		}
		if (!count($ffa))
			show_error("filename error");
		$ffe = implode("/", $ffa);
		$filelist[] = array($ffe, $ll);
	}
	$type = "multi";
}

/**
	Limit little files to be uploaded
*/

$cat = $t_details['type'];

// Other, min 10mb
// 2 - muzica
// 7 - alte
// 16 - foto
if ( ($cat == 7 || $cat == 2 || $cat == 16) && $totallen < 10485760) {
	show_error(__('Vă rugăm să nu puneţi fişiere prea mici pe torrents. Puneţi doar ceea ce ar putea fi întradevăr util restul lumii. Vă mulţumim.'));
}

if (isset($dict['value']['info']['value']['private']['value'])) {
	unset($dict['value']['info']['value']['private']);
}

if (isset($dict['value']['announce-list'])) {
	unset($dict['value']['announce-list']);
}

// There is a bug in utorrent <1.8 with very long "created by" fields - http://secunia.com/advisories/31441/
if (isset($dict['value']['created by'])) {
	if (!is_string($dict['value']['created by']['value'])) {
		unset($dict['value']['created by']);
	} elseif (strlen($dict['value']['created by']['value']) > 30) {
		unset($dict['value']['created by']);
	}
}

$dict = benc($dict);
$dict = bdec($dict);

list($info) = dict_check($dict, "info");

$infohash_sha1 = sha1($info["string"]);
$infohash 		 = pack("H*", $infohash_sha1);

//Check if that torrent is already on the tracker
q('SET NAMES latin1');
if(q_singleval( "SELECT id FROM torrents WHERE info_hash="._esc($infohash) )) {
	show_error("torrent already uploaded!");
}
q('SET NAMES utf8');

list($clean['image'], $img_tmpname) = check_torrent_image();

// release
$teamRelease = '0';
if (post('teamRelease') && $CURUSER['team']) {
	$teamRelease = $CURUSER['team'];
}

$ret = q("
	INSERT INTO torrents (name, info_hash_sha1, image, filename, owner, visible, size, numfiles,
 										    type, category, save_as, added, last_action, team)
 							  VALUES (:name, :info_hash_sha1, :image, :filename, :owner, :visible, :size, :numfiles,
												:type, :category, :save_as, :added, :last_action, :team)",
  array(
    'name' => $t_details['name'],
    'info_hash_sha1' => $infohash_sha1,
    'image' => $clean['image'],
    'filename' => $fname,
    'owner' => $CURUSER["id"],
    'visible' => "yes",
    'size' => $totallen,
    'numfiles' => count($filelist),
    'type' => $type,
    'category' => (int)$t_details['type'],
    'save_as' => $dname,
    'added' => get_date_time(),
    'last_action' => get_date_time(),
    'team' => $teamRelease
  )
);

$id = q_mysql_insert_id();

q('SET NAMES latin1');
Q('UPDATE torrents SET info_hash='._esc($infohash).' WHERE id='.$id);
q('SET NAMES utf8');

$sql['desc_ar'] = _esc($t_details['description_ar']);
$sql['desc_search_str'] = _esc($t_details['description_search_str']);
$sql['desc_details_html'] = _esc($t_details['description_details_html']);

$torrent_details_sql = "INSERT INTO torrents_details (id,descr_ar,descr_html)
      VALUES(".(int)$id.", ".$sql['desc_ar'].", ".$sql['desc_details_html'] . ")";
q($torrent_details_sql);

foreach ($filelist as $file) {
	@q("REPLACE INTO files (torrent, filename, size) VALUES ($id, ".sqlesc($file[0]).",".$file[1].")");
}

move_uploaded_file($tmpname, "$torrent_dir/$id.torrent");

if ($clean['image'] !== 0) {
	resize_prop($img_tmpname,"$torrent_img_dir/{$id}_{$clean['image']}",700,700);
	if (!is_file("$torrent_img_dir/{$id}_{$clean['image']}")) {
		q("UPDATE torrents SET image='' WHERE id=$id");
	}
}

event_torrent_changed_any();

$searchindex = $t_details['search_sql']; //All fields are already escaped, preparated for sql insert
q("INSERT INTO searchindex (id,name)
		    VALUES ($id,$searchindex[name])");

/**
	Torrent Genre
**/
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

$genre = $t_details['genre'];
if ($genre == '') {
 $genre = 0;
}
/**
	Torrent Year
**/
if ($t_details['year'] != '') {
	if ( is_array($t_details['year']) ) {
		$t_details['year'] = array_unique($t_details['year']);

  /*`categ` tinyint(4) unsigned DEFAULT NULL,
  `genre` smallint(6) unsigned NOT NULL DEFAULT '0',
  `torrentid` int(11) unsigned NOT NULL DEFAULT '0',
  `year` year(6) unsigned NOT NULL DEFAULT '0',
  `added` int(11) unsigned NOT NULL DEFAULT '0',*/

	} else {
		$t_details['year'] = (int)$t_details['year'];
	}
}

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
}

//Anonymous
if ( isset($_POST['anonim']) ) {
	$torrent_opt = q_singleval('SELECT torrent_opt FROM torrents WHERE id=:id',array('id'=>$id));
	$torrent_opt = setflag($torrent_opt, $conf_torrent_opt['anonim_unverified'], true);
	q('UPDATE torrents SET torrent_opt=:opt WHERE id=:id',array('id'=>$id,'opt'=>$torrent_opt));
}

torrentCategsAutodetect($id);

function prepare_descr_html2($lang,$html,$cat,$image,$id) {
	include($GLOBALS['WWW_ROOT'] . 'lang/details.php_' . $lang . '.php');
	$rez = str_replace($lang_input_all_names,$lang_input_all_values,$html);
	if(isset($lang_category[$cat])) {
		$rez = str_replace('%category%',$lang_category[$cat],$rez);
	}
	$image_path = '';
	if (strlen($image) > 1) {
		$image_path = 'http://' . $GLOBALS['DEFAULTBASEURL'].'/'. ltrim(ltrim($GLOBALS['torrent_img_dir_www'],'.'),'/') ; //The www path //Trim <- remove the leading dot
		$image_path .= '/'.$id.'_'.$image; //Now concat the image name
	}
	return array($rez,$image_path);
}

$fp = fopen("$torrent_dir/$id.torrent", "w");
if ($fp) {
 @fwrite($fp, benc($dict), strlen(benc($dict)));
 fclose($fp);
}

write_log("Torrent $id (" . $t_details['name'] .  " ) was uploaded");

header("Location: ./details.php?id=$id");
?>