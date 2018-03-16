<?php
/**
Some functions
**/
function html_selected($val1,$val2) {
    if ($val1 == $val2) echo ' selected';
}

function browseShowTags($categtags_full) {
    if (!is_array($categtags_full)) return;
    foreach( $categtags_full AS $categtag ) {
        echo stringIntoEsc('<span data-tagid=":id">',array('id'=>$categtag->id));
        echo $categtag->getAcestorsPath();
        $name_lang = 'name_'.get_lang();
        echo '<b>',$categtag->tag[$name_lang],'</b></span>';
    }
}
/**
* @param $categtags Array of CategTag objects
* return
*/
function getTorrentsByCategTags($categTagsObjects, $not_categTagsObjects) {
  global $torrentsperpage, $addparam;
  global $skip, $limit, $count;

  $categtags = array();
  $not_categtags = array();

  if (count($categTagsObjects) == 0) return;

  foreach($categTagsObjects AS $categItem) {
    $categtags[] = (int)$categItem->id;
  }
  if (count($not_categTagsObjects)) {
    foreach($not_categTagsObjects AS $categItem) {
        $not_categtags[] = (int)$categItem->id;
    }
  }

  list($skip,$limit) = pagerSkip($torrentsperpage, 1000);

  Torrents::$skip = $skip;
  Torrents::$torrentsperpage = $torrentsperpage;
  list($count,$torrents_id) = Torrents::getByCategTags($categtags, $not_categtags);

    if (is_numeric($count) && $count > 0) {

        $addparam .= 'categtags='.esc_html(join(',',$categtags)).'&';

        list($skip,$limit) = pagerSkip($torrentsperpage, $count);

        return $torrents_id;
    }
    return array();
}

function appendToCategtagUrlParameter($oneCategTag) {
    $categtags = get("categtags");
    $categtags = ($categtags == NULL)?"":$categtags.",";
    return $categtags . $oneCategTag;
}


function cat_id2name($categs,$id) { //Category id to name
    if (!is_array($categs)) return;
    foreach ($categs as $categ) {
        if ($categ['id'] == $id) return $categ['name'];
    }
}
function genre_id2name($categ_id,$genre_id) {
    global $conv_movie_genres_list_ids,$cats;
    $categ_id = (int)$categ_id;
    $genre_id = (int)$genre_id;
    //a var present only in torrent_description.php
    if (!isset($conv_movie_genres_list_ids)) include $GLOBALS['INCLUDE'].'torrent_description.php';
    if ($categ_id == 1) {
        if (!isset($conv_movie_genres_list_ids[$genre_id])) die('Genre out of range');
        return $conv_movie_genres_list_ids[$genre_id];
    }
    $cat_name = strtolower(cat_id2name($GLOBALS['cats'],$categ_id));

    global ${'conv_'.$cat_name.'_genre'};
    if (isset( ${'conv_'.$cat_name.'_genre'} )) {
        $genres = &${'conv_'.$cat_name.'_genre'};
        if (!isset($genres[$genre_id])) die('Genre out of range');
        return $genres[$genre_id];
    }
    return false;
}

function getCategTagsInactiveFull($categtags_inactive) {
  if (strlen($categtags_inactive) == 0) return;

  $categtags_inactive = explode(',',$categtags_inactive);
  $categtags_inactive_full = array();

  if (!count($categtags_inactive)) return;

  $categtags_inactive = array_unique( $categtags_inactive );

  foreach($categtags_inactive AS $catetagi=>$catetag) {
      if (!is_numeric($catetag)) {
          unset($categtags_inactive[$catetagi]);
          continue;
      }
      $categtags_full_item = new CategTag($catetag);
      if ($categtags_full_item->isEmpty()) {
          unset($categtags_inactive[$catetagi]); // Not a valid tag
          continue;
      }
      $categtags_inactive_full[] = $categtags_full_item;
      $categtags_inactive[$catetagi] = (int)$catetag;
  }
  return array($categtags_inactive,$categtags_inactive_full);
}

/**
* @return array of CategTag objects
* @param $categtags Array
*/
function getCategTagsObjects($categtags) {
  $categtags = explode(',',$categtags);

    if (count($categtags) > 10) stderr(__("Error"), __("Maxim 10 categtaguri concomitent"), true);

    if (count($categtags)) {

        $categtags_full = array();

        $categtags = array_unique( $categtags );

        // Validate
        foreach($categtags AS $catetagi=>$catetag) {
            if ( !is_numeric($catetag)) {
                unset($categtags[$catetagi]);
                continue;
            }
            $categtags_full_item = new CategTag($catetag);
            if ($categtags_full_item->isEmpty()) {
                unset($categtags[$catetagi]); // Not a valid tag
                continue;
            }
            $categtags_full[] = $categtags_full_item;
            $categtags[$catetagi] = (int)$catetag;
        }

        $categtags = array_values( array_unique( $categtags ) ); // If there was any change to the array Index, mongodb will complain, indexes should be 0,1,2..
    }
    return $categtags_full;
}
