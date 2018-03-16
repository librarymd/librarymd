<?php
require "./include/bittorrent.php";
require "./include/functions_additional.php";
require_once($INCLUDE . 'torrent_opt.php');
require_once($INCLUDE . 'classes/torrents.php');
include_once($INCLUDE . 'classes/categtag.php');
require_once($INCLUDE . 'classes/users.php');
require_once($INCLUDE . 'browse_utils.php');

loggedinorreturnNotMd();
$aUser = Users::isLogged();

//Benchmark
$time_start = microtime_float();

stdhead();

$cats = genrelist();

//Determine when to not show "categories" box
$show_categories = true; //By default show
if (isset($_GET['unseen']) || isset($_GET['tag'])) $show_categories = false;

echo '<br/>';

if ($show_categories) {
    $categSelected = get('categtags')?'?categtags='.esc_html($categSelected):'';

  echo '<div align="left" style="width:780px;"><a href="/browse_filters.php'.$categSelected.'" id="browseAdvFilterLink">'.__('Filtrare avansată').'</a> </div><br>';

}

$orderby = "torrents.added DESC";

$addparam = "";
$wherea = array();
$wherecatina = array();

//Filter - incldead
// $wherea[] = '(leechers > 0 OR seeders > 0)';

$all = (isset($_GET["all"]))?$_GET["all"]:null;

if (!$all) {
    if ((!$_GET || isset($_GET['unseen']) ) && $CURUSER["notifs"]) { //If no Get, show only favorite categs
      $all = true;
      foreach ($cats as $cat) {
        $all &= $cat['id'];
        if (strpos($CURUSER["notifs"], "[cat" . $cat['id'] . "]") !== False) {
          $wherecatina[] = $cat['id'];
          $addparam .= "c{$cat['id']}=1&";
        }
      }
    }
    elseif (isset($_GET["cat"])) { //That mean what user whant to see torrents only from 1 categ
      $category = (int)$_GET["cat"];
      if (!is_valid_id($category)) stderr("Error", "Invalid category ID " . esc_html($category) . ".", true);
      $wherecatina[] = $category;
      $addparam .= "cat=$category&";
    }
    else { //Maybe user have specify only some of categories ?
      $all = true;
      foreach ($cats as $cat) {
        $all &= isset($_GET["c{$cat['id']}"]);
        if (isset($_GET["c{$cat['id']}"]))
        {
          $wherecatina[] = $cat["id"];
          $addparam .= "c{$cat['id']}=1&";
        }
      }
    }
}
if ($all) { //User whant to see all categories, even those what are not in his favorites
  $wherecatina = array();
  $addparam = "";
}

$userCustomCategsString = '';

//Now transform array->string for sql query
if (count($wherecatina) > 0) {
    if (isset($wherecatin)) $where .= ($where ? " AND " : "") . "category IN(" . $wherecatin . ")";

    $wherecatin = implode(",",$wherecatina); //Now we have a string in format: catid,catid,catid..
    $userCustomCategsString = $wherecatin;
    $wherecatin = 'category IN('.$wherecatin.')'; //Now: category IN(catid,catid,catid..)
    $wherea[] = $wherecatin;
}

$wherebase = $wherea;

$where = implode(" AND ", $wherea);
if ($where != "") $where = " $where";


$torrentsperpage = $CURUSER["torrentsperpage"];
if (!$torrentsperpage) $torrentsperpage = 100;

$unseen = 0;
if ($aUser && isset($_GET['unseen']) && $_GET['unseen']) {
    $unseen = 1;
    $not_default = true;
    if ( isset($_GET['last_browse_see']) && $_GET['last_browse_see'])
        $last_browse_see = (int)$_GET['last_browse_see'];
    else
        $last_browse_see = (int)$CURUSER['last_browse_see'];

    // 5184000 = 60 days, prevent abuse
    if ( (time() - 5184000) > $last_browse_see ) $last_browse_see = (time() - 5184000);

    $where = " torrents.added > '" . date('Y-m-d G:i:s',$last_browse_see) . "' ";

    if (isset($wherecatin))
        $where .= 'AND ' . $wherecatin;

    //$where .= ' AND visible="yes"';
    $addparam .= 'unseen=1&last_browse_see=' . $last_browse_see . '&';
    $torrentsperpage = 150;
}

//Torrents of day
if (count($_GET) == 0 || $_GET["regenerate_top"]) {
    global $top_3;
    $GLOBALS['browse_show_lastest'] = 1;

    $top_3_in_last_24 = mem2_get('top_3_in_last_24');

    if ($top_3_in_last_24 == null || $_GET["regenerate_top"]) {
        //Regenerate
        $top_3 = Torrents::getTopTorrents();
        mem2_set('top_3_in_last_24',serialize($top_3),1200);
    } else {
        $top_3 = unserialize($top_3_in_last_24);
    }
}

//Genres handler, DEPRECATED
if (isset($_GET['cat']) && isset($_GET['genre'])) {
    $cat = (int)$_GET['cat'];
    $genre = (int)$_GET['genre'];
    $addparam .= 'genre='.$genre.'&';
    $where = " WHERE torrents_genres.categ=$cat AND torrents_genres.genre=$genre";
    $orderby = " ORDER BY torrents_genres.torrentid DESC";
}

// Categtag
$showNoTorrentsFound = false;
if (isset($_GET['categtags'])) {
  $categtags_full = getCategTagsObjects($_GET['categtags']);
  $not_categtags_full = getCategTagsObjects($_GET['not_categtags']);
  $torrents_id = getTorrentsByCategTags($categtags_full, $not_categtags_full);
  $categtags = true;
  if (count($torrents_id) == 0) {
        echo "<h2 class='center'>".__("Nici un torrent găsit.")."</h2>\n";
        stdfoot();
        exit();
      $showNoTorrentsFound = true;
  } else {
  $where = ' torrents.id IN ('.join(',',$torrents_id).')';
  list($categtags_inactive,$categtags_inactive_full) = getCategTagsInactiveFull(@$_GET['categtags_inactive']);
}
}

//Imdb handler
if (isset($_GET['imdb'])) {
    $imdb = str_replace('tt','',$_GET['imdb']);
    if (is_numeric($imdb)){
        $addparam .= 'imdb='.esc_html($imdb).'&';
        $torrents_id = fetchColumn('SELECT torrent FROM torrents_imdb WHERE imdb_tt=:imdb',array('imdb'=>$imdb));
        if (!count($torrents_id)) {
            echo "<h2 class='center'>".__("Nici un torrent care corespunde acestui număr IMDB n-a putut fi gasit.")."</h2>\n";
            stdfoot();
            exit();
        }
        $where = ' torrents.id IN ('.join(',',$torrents_id).')';
    } else {
        unset($imdb);
    }
}


function make_query_torrents_in($torrents_ids, $orderby) {
  $where = "torrents.id IN ($torrents_ids)";
  return Torrents::list_query('SQL_CALC_FOUND_ROWS', $where, $orderby);
}

$query = '';

$browseDataGreenSuckFlag = false; // Flag, mean torrentable must be sucket into browseData memcache
list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, 1000, "browse.php?" . $addparam);
if (isset($genre)) { //If need find torrents by genres
    $query = "SELECT SQL_CALC_FOUND_ROWS " . Torrents::$sql_columns . "
        FROM torrents_genres
        RIGHT JOIN torrents ON torrents_genres.torrentid = torrents.id" .
        Torrents::$sql_joins .
        "$where " . Torrents::$sql_group_by . " $orderby $limit";
} elseif (isset($imdb)) {
    $query = Torrents::list_query('SQL_CALC_FOUND_ROWS', $where, $orderby, $limit);
    $label_torrents_by_imdb = 1;
} elseif (isset($categtags)) {
    $query = Torrents::list_query('SQL_CALC_FOUND_ROWS', $where, $orderby);
    $label_torrents_by_categtags = 1;
} else {
    //Execute default
        // Cache rows count
    if (!isset($not_default)) {
        $rowsCount = mem_get('browse_torrents_count'.$userCustomCategsString);

        $browsePatern = md5($where.$orderby.$limit);
        $browseData   = mem_get('browse_torrents_'.$browsePatern);

        if ($browseData != false) {
            $browseData = unserialize($browseData);
        }

        function get_ids($select, $where, $orderby, $limit) {
          if (strlen($orderby) > 0) $orderby = "ORDER BY " . $orderby;
          if (strlen($where) > 0) $where = "WHERE " . $where;
          return get_ids_for_in("SELECT $select torrents.id FROM torrents $where $orderby $limit",'id');
        }

        if ($rowsCount == NULL) {
            $torrents_ids = get_ids('SQL_CALC_FOUND_ROWS', $where, $orderby, $limit);
            $before_count = q_singleval('SELECT FOUND_ROWS()');
            $query = make_query_torrents_in($torrents_ids, $orderby);

            if (!isset($not_default)) $cache_rows_count = true;
        } elseif ($browseData == NULL) {
            $torrents_ids = get_ids('', $where, $orderby, $limit);

            $query = make_query_torrents_in($torrents_ids, $orderby);
            $count = $rowsCount;
        } else {
            $count = $rowsCount;
        }

        if (strlen($query)) {
            $browseDataGreenSuckFlag = true;
            $browseData = fetchAll($query);
            mem_set('browse_torrents_'.$browsePatern, serialize($browseData), 60, MEMCACHE_COMPRESSED);
            $query = '';
        }

    } else {
        $query = Torrents::list_query('SQL_CALC_FOUND_ROWS', $where, $orderby, $limit);
    }
}
$res = '';
if (strlen($query)) {
    $res = q($query);
}

if (!isset($count)) {
    if (isset($before_count)) $count = $before_count;
    else $count = q_singleval('SELECT FOUND_ROWS()');
    if (isset($cache_rows_count)) {
        mem_set('browse_torrents_count'.$userCustomCategsString, $count, 60);
    }
}

list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, "browse.php?" . $addparam);

if ($aUser) {
    //Update last torrents seen
    q_delayed('UPDATE users_hot SET last_browse_see=UNIX_TIMESTAMP() WHERE id='.$CURUSER['id']);

    mem_set('last_browse_see'.$CURUSER['id'],time(),86400);
    mem_delete('user_new_torrents_'.$CURUSER['id']);
}
?>

<script src="./js/browse.js" type="text/javascript"></script>

<?php

$forceUpdate = get_user_class()==UC_SYSOP && (int)get('forceUpdateCategories')==1;

if ($show_categories || $forceUpdate) { //Don't show for unseen mode, and tag

    if($siteVariables['browse']['useNewCategTable'])
    {
    $categories = fetchAll_memcache('SELECT *, name_'.get_lang().' AS name FROM `torrents_catetags` WHERE visible="yes" AND `father`= '. $browseGCatVariable, 86400, $forceUpdate);

    $categSelected = get('categtags');
    $categtags = explode(',',$categSelected);
    $categFatherID = (int)$categtags[0];
?>
<div class="mCenter categories">
    <div class="tipContinut">
<?
        $categFatherName = false;
    foreach ($categories as $row_cat)
    {
        if($categSelected && $row_cat['id']==$categFatherID)
            $categFatherName = $row_cat['name'];
            $categ_additional_param = '';
            if ($row_cat['id'] == 89) $categ_additional_param = "&amp;not_categtags=45";

?>
        <a href="./browse.php?categtags=<?=$row_cat['id']?><?=$categ_additional_param?>"><?=$row_cat['name']?></a>
<?  }   ?>
        <a href="./browse.php?cat=12" <?if($freeleache):?>style="color:#707607;"<?endif;?>><?=__('DVD')?></a>
        <a href="./browse.php?cat=18" <?if($freeleache):?>style="color:#707607;"<?endif;?>><?=__('HDTV')?></a>
        <a href="./browse.php?categtags=45" style="color: green; text-decoration:underline"><?=__('Seriale')?></a>
    </div>

<?php
    if($categSelected && $categFatherName  && !$showNoTorrentsFound)
    {
        $subcats=array();
        $categId = fetchOne_memcache('SELECT id FROM `torrents_catetags` WHERE father = 141 AND `dependendOnCategTagCSV` LIKE "%'. $categFatherID .'%";', 86400, $forceUpdate);
        if($categId)
            $subcats = fetchAll_memcache('SELECT *, name_'.get_lang().' AS name FROM `torrents_catetags` WHERE father = '.$categId.' OR father IN
                                        (SELECT id FROM `torrents_catetags`  WHERE father = '.$categId.');', 86400, $forceUpdate); // 3600 * 24

?>
        <br /><div class="categFatherName"><?=__('Genuri de')?> <?=$categFatherName?></div><br />
<?
        if(count($subcats))
        {
            //for customizing css attributes, ugly but simple
            //$cstmClass = get_lang()=='ru'?'ru':'';
            $cstmClass = ' c'.$categFatherID.' c'.get_lang();
?>
    <div class="subcat<?=$cstmClass?>">
<?
    /*
        $subCatsCount = fetchAllInOneArray_memcache('SELECT id,
                                    (
                                        SELECT COUNT(torrent) FROM `torrents_catetags_index` WHERE catetag = t.id
                                    ) AS count FROM `torrents_catetags` t WHERE father =
                                                    (
                                                        SELECT id FROM `torrents_catetags` WHERE father = 141 AND `dependendOnCategTagCSV` LIKE "'. $categFatherID .'"
                                                    )
                                ', 'id', 'count', '', 2592000, $forceUpdate); // 30 zile
    */
        foreach ($subcats as $subcat)
        {
            //$subCatsCount[$subcat['id']]
?>
            <a href="./browse.php?categtags=<?=$categFatherID?>,<?=$subcat['id']?>#torrents"><?=$subcat['name']?></a>
<?      }   ?>
    </div>
<?      } else { ?>
<div class="center">(<?=$lang['browse_no_genres']?>)</div>
<?      }

    } else { ?>
<?  } ?>
<!--</div>-->

<?  } else { //use old categ table ?>


<table class="mCenter"><tr><td align="center">
<div style="float: left;padding-left:10px;padding-right:10px;">
<a href="./browse.php?cat=11"><?=__('Filme animate')?></a><br>
<a href="./browse.php?cat=13"><?=__('Filme documentare')?></a><br>
</div><div style="float: left;padding-right:10px;">
<a href="./browse.php?cat=10"><?=__('Anime')?></a><br>
<a href="./browse.php?cat=2"><?=__('Muzică')?></a><br>
</div><div style="float: left;padding-right:10px;">
<a href="./browse.php?cat=3"><?=__('Software')?></a><br>
<a href="./browse.php?cat=9"><?=__('Muzică video')?></a><br>
</div><div style="float: left;padding-right:10px;">
<a href="./browse.php?cat=8"><?=__('Cărţi')?></a><br>
<a href="./browse.php?cat=7"><?=__('Alte')?></a><br>
</div><div style="float: left;padding-right:10px;">
<a href="./browse.php?cat=14"><?=__('Cărţi audio')?></a><br>
<a href="./browse.php?cat=16"><?=__('Fotografii')?></a><br>
</div><div style="float: left;padding-right:10px;">
<a href="./browse.php?cat=12"<?if($freeleache):?>style="color:#707607;font-weight: bold;"<?endif;?>><?=__('DVD')?></a><br>
<a href="./browse.php?cat=5"><?=__('TV')?></a><br>
</div><div style="float: left;padding-right:10px;">
<a href="./browse.php?cat=4"><?=__('Jocuri')?></a><br>
<a href="./browse.php?cat=15"><?=__('Lecţii video')?></a><br>
</div><div style="float: left;padding-right:10px;">
<a href="./browse.php?cat=18"<?if($freeleache):?>style="color:#707607;font-weight: bold;"<?endif;?>><?=__('HDTV')?></a><br>
<a href="./browse.php?cat=17"><?=__('Sport')?></a><br>
</div>
<div style="float: left;padding-right:10px;">
<a href="./browse.php?cat=1"><?=__('Filme')?></a><br>
<br>
</div>
<div style="display: block; clear: both; text-align: left; padding-left: 80px;"></div>




<?php

    }//if
    $showOnlyForDvd = isset($_GET['cat']) && ( (int)$_GET['cat']==18 || (int)$_GET['cat']==12) && $siteVariables['browse']['useNewCategTable'] ;

if ((isset($_GET['cat']) && is_numeric($_GET['cat']) ) || $showOnlyForDvd ) { //limitam
    $cat = (int)$_GET['cat'];
    $cat_name = strtolower(cat_id2name($cats,$cat));
    echo '<h2 align="center">',(($cat==18  || $cat==12)?strtoupper($cat_name):__(ucfirst($cat_name))),'</h2>';
    include $GLOBALS['INCLUDE'].'torrent_description.php';
    //Now, check if categ have genres, if yes, assign the list to $genres
    if ($cat == 1) $genres = &$conv_movie_genres_list_ids;
    elseif (isset( ${'conv_'.$cat_name.'_genre'} )) $genres = &${'conv_'.$cat_name.'_genre'};
    else echo $lang['browse_no_genres'];

    if (isset($genres)) {
        //Get count of genres in current categ
        $genres_count = mem_get('genres_count_'.$cat);
        if ($genres_count == null) {
            include($INCLUDE . 'cleanup.php');
            regenerate_categs_counts();
        }
        if ($genres_count != null) $genres_count = unserialize($genres_count);

        //genres_count subgenres with >0 torrents
        $per_column = ceil( count($genres_count) / 3);

        $pos = 1;
        echo '<table align="center" cellpadding=10><tr><td style="border:0px;">';

        foreach ($genres as $g_id=>$g_val) {
            if ($pos == $per_column + 1) {
                $pos = 1;
                echo '</td><td style="border:0px;" valign=top>',"\n\n";
            }
            $g_count = 0;
            //Check if the genre is in counters array
            if ($genres_count != null && isset($genres_count[$g_id])) $g_count = $genres_count[$g_id];
            if ($g_count == 0) continue;
            if ($g_count == 0) echo $g_val,' (0)<br>';
            else echo '<a href="./browse.php?cat=',"$cat&genre=$g_id#torrents",'">',__($g_val),' (',$g_count,')</a><br>';
            $pos++;
        }
        echo '</td></tr></table>';
    }

} else if(!$siteVariables['browse']['useNewCategTable']) echo '(',$lang['browse_oncat_click_genres'],')';
?>
</td></tr></table>
<br/>
<div class="categFatherName"><?=__("Limbi")?></div><br />
<div class="tipContinut">
            <?php
            foreach (array(27,28,180,185,182,183, 181, 184, 315, 316) as $key) {
                $categTag = new CategTag($key);
                echo stringIntoEsc('<a href="/browse.php?categtags=:id#torrents">:name</a>',
                                    array('id'=>appendToCategtagUrlParameter($categTag->getId()),
                                                'name'=>$categTag->getName())
                );
            } ?>
</div>
</div>  <!--de sters cand uitam de ?cat -->
<?php
    //}//OLD categ table
} //End "dont show categs" in unseen mode


if($showNoTorrentsFound)
{
    echo "<h2 class='center'>".__("Nici un torrent găsit.")."</h2>\n";
    stdfoot();
    exit();
}



if ($count) {
    global $lang;
    //If not null, that mean all is ok, and we can show the menu
    if (mem_get('tops_generation_time') != false && count($_GET) == 0) {
        //Cache identifier
        echo '<script type="text/javascript">topVerGen=',mem_get('tops_generation_time'),';
        print_browse_top_menu();</script>',"\n";
    }
    if (count($wherecatina) > 0 && !isset($_GET['cat']) ) {
        echo '<table align="center"><tr><td bgcolor="#F5F4EA" nowrap="nowrap" width="1" style="border:#ECAF9B 1px solid;"><a href="./my.php#defaut_browse">',$lang['browse_categ_filter_on'],'</a></td></tr></table>';
    }


    $what_torrents = $lang['browse_lasest_torrents_label'];
    //Movies > Action
    //List torrents by category/&genre
    if (isset($_GET['cat'])) $what_torrents = cat_id2name($cats,$_GET['cat']) .
                                ((isset($_GET['genre']) && genre_id2name($_GET['cat'],$_GET['genre']) !== false )?' > '. genre_id2name($_GET['cat'],$_GET['genre']):'');


    if (isset($_GET['unseen'])) $what_torrents = $lang['browse_last_visit_torrents'];
    if (isset($label_torrents_by_tag)) $what_torrents = esc_html($_GET['tag']);
    if (isset($label_torrents_by_imdb)) $what_torrents = sprintf('<a href="http://www.imdb.com/title/tt%07d/" target="_blank">Imdb</a>',esc_html($imdb),esc_html($imdb));
    if (isset($label_torrents_by_categtags)) $what_torrents = __("Categorii");


        echo '<h2 id="torrents_header"></h2>
<div class="center browseHeader" id="torrents">
<h2>',$what_torrents,'</h2>';

    ?>

    <?php
    // Show tags
    if (isset($label_torrents_by_categtags) && count($categtags_full)) {
        ?>
        <div id="categtagActivList">
        <?php
            browseShowTags($categtags_full);
        ?>
        </div>
        <div id="categtagInactivListBox" <?= (count($categtags_inactive_full) == 0?'style="display:none"':'') ?>>
            <div style="margin:10px;">Inactive:</div>
            <div id="categtagInactivList">

        <?php
            browseShowTags($categtags_inactive_full);
        ?>

            </div>
        </div>
        </br>

        <form action="browse.php" id="categtagApplyForm">
            <input type="hidden" name="categtags" value=""/>
            <input type="hidden" name="categtags_inactive" value=""/>
            <button style="margin-top:10px;">Afișează</button>
        </form>

        </br>
        <?php
    }

    print($pagertop);


    torrenttable($res,$browseData);


    print($pagerbottom);
    echo '</div>';
    $time = microtime_float() - $time_start;
    echo "<p style='color:#F5F4EA;'>$time</p>\n";
} else {
    if (count($wherecatina) > 0 && !isset($_GET['cat']) ) {
        echo '<table align="center"><tr><td bgcolor="#F5F4EA" nowrap="nowrap" width="1" style="border:#ECAF9B 1px solid;"><a href="./my.php#defaut_browse">',$lang['browse_categ_filter_on'],'</a></td></tr></table>';
    }
    if (isset($cleansearchstr)) {
        print("<h2 class='center'>Lista goal&#259;!</h2>\n");
        print("<p>Ne pare r&#259;u..</p>\n");
    }
    if ($unseen) {
        echo "<h2 class='center'>{$lang['browse_no_new']}</h2>\n";
    }
    else {
        print("<h2>Lista goal&#259;!</h2>\n");
        print("<p>Ne pare r&#259;u..</p>\n");
    }
}

stdfoot();
?>
