<?php
// All libs should be already included

if (!isset($_POST['id'])) return;

mkglobal2('id:req:int,categtag_list:int','post');
if (!isset($categtag_list)) $categtag_list = array();

$torrent = fetchRow(
		 "SELECT torrents.name, torrents.owner, torrents.category, torrents.moder_status
          FROM torrents
          WHERE torrents.id = :id", array('id'=>$id)
);

if (!$torrent) {
	barkk(__('Torrent inexistent'));
}

if (tags_allow_edit_torrent($torrent) !== true) {
	barkk(__('Nu sunteți proprietarul acestui torrent'));
}

$table_index =	'torrents_catetags_index'; 	// associate torrents and catetags
$table = 		'torrents_catetags';		// just a list of catetags

// Tags assigned to current torrent
$old_catetags = fetchColumn(
	"SELECT catetag
	FROM $table_index
	LEFT JOIN torrents_catetags ON torrents_catetags.id = $table_index.catetag
	WHERE torrent=:id AND torrents_catetags.visible='yes'",array('id'=>$id));

/* Vrem 
	1) sa adaugam noile selectate
	2) sa scoatem din table_index tag-urile care nu mai sunt folosite
*/
//q('START TRANSACTION');
updateTags($id,$categtag_list);

$returl = "details.php?id=$id&edited=2";
if (isset($_POST["returnto"])) {
	$returl .= "&returnto=" . urlencode($_POST["returnto"]);
}
header("location: $returl");