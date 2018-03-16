<?php
require_once($WWW_ROOT . 'include/categtags/torrents_desc_tags_map.php');

function tags_allow_edit_torrent($torrent) {
	global $CURUSER;

	if (get_user_class() >= UC_MODERATOR || get_user_class() == UC_SANITAR) return true;

	if ($CURUSER["id"] != $torrent["owner"]) {
		return false;
	}

	if (torrent_status_downloadable($torrent) !== true) {
		barkk(__('Torrentul are un statut care nu vă permite să-l editați'));
	}
	return true;
}

function getTags($details,$torrent) {
    return mapDescToTags($details,$torrent);
}

function updateTags($id,$tags) {
    // Preserve old tags
    q('DELETE FROM torrents_catetags_index WHERE torrent=:id',array('id'=>$id));
    foreach($tags AS $tag) {
        q('INSERT IGNORE INTO torrents_catetags_index (torrent,catetag) VALUES(:id,:tag)',array('id'=>$id,'tag'=>$tag) );
    }

    $tags = array_map("intval", $tags);

    $db = getMongoDb();
    $db->torrents->update(array('_id'=>(int)$id), array('$set'=>array('tags'=>$tags)), array('upsert'=>true) );
}

function torrentCategsAutodetect($id) {
  $torrent = fetchRow('SELECT * FROM torrents WHERE id=:id',array('id'=>$id));
  $details = fetchOne('SELECT descr_ar FROM torrents_details WHERE id=:id',array('id'=>$id));

  $tags = getTags(unserialize($details),$torrent);
  updateTags($id, $tags);

  cleanTorrentCache($id);

  return $tags;
}
