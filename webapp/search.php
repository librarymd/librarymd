<?php
mb_internal_encoding("UTF-8");
require "include/bittorrent.php";
require_once $INCLUDE . "functions_additional.php";
include('./sphinx/sphinxapi.php');
include('./sphinx/utf8_normalize_map.php');
require_once($WWW_ROOT . 'include/categtags/torrent_edit_inc.php');
require_once($INCLUDE . 'classes/torrents.php');
require_once($INCLUDE . 'classes/users.php');

loggedinorreturnNotMd();
$aUser = Users::isLogged();

stdhead("Search");

$sql = array();

$addparam= '';
$sortparam = (isset($_GET['sort']))? $_GET['sort'] :'';
$orderparam = (isset($_GET['order']) && ($_GET['order'] == 'asc') )? 'ASC' :'DESC';
$showNoPeers = isset($_GET['show_no_peers']) && $_GET['show_no_peers'] == 1;

switch ($sortparam)
{
    case "name":
        $querySort = 'torrents.name';
        break;

    case "peers":
        $querySort = 'torrents.dht_peers';
        break;

    case "date":
    default:
        $sortparam = '';
        $querySort = 'torrents.added';
        $queryColumn = '';
}
$querySort .= ' '.$orderparam;

$peersFiltering = $showNoPeers ? '' : ' AND (torrents.dht_peers > 0 OR torrents.added > now() - interval 6 month)';


if (isset($_GET['search_str']) && !isset($_GET['adv'])) {

    $torrentsperpage = $CURUSER["torrentsperpage"];
    if (!$torrentsperpage) $torrentsperpage = 50;

    $_GET['search_str'] = trim($_GET['search_str']);

    $search_str = mb_strtolower($_GET['search_str']);

    $search_str = str_replace($unicode_map_replace_what,$unicode_map_replace_with,$search_str);
    // - is a ignore char
    $search_str = str_replace("-","",$search_str);
    $boolean_search_str = $search_str;

    // Prepare matcher
    if ( strpos( $boolean_search_str, '+') === false && strpos( $boolean_search_str, '-') === false && strpos( $boolean_search_str, '"') === false ) {
        // Remove multiple consecutive spaces
        $boolean_search_str = preg_replace('/\s\s+/', ' ', $boolean_search_str);
        $boolean_search_str = trim($boolean_search_str);
        $boolean_search_str = str_replace(' ', ' +', $boolean_search_str);
        // In front also put a +
        $boolean_search_str = '+'.$boolean_search_str;
    }
    $sql['search_str'] = _esc($boolean_search_str);

/**
    Spinx part
    **/
    if (!$devenv || $devenenv_sphinx_enabled) {
        $sphinx_mode = 'SPH_MATCH_BOOLEAN';
        $sphinx_index = 'torrents_search';
        $cl = new SphinxClient();
        $cl->SetServer ( $sphinx_host, $sphinx_port );
        $cl->SetWeights ( array ( 100, 1 ) );
        $cl->SetMatchMode ( $sphinx_mode );
        $cl->SetLimits ( 0, 1000 );
        $cl->SetSortMode ( SPH_SORT_EXTENDED, '@id DESC' );

        time_between('sphinx_torrents_search');
        $sphinx_res = $cl->Query ( $boolean_search_str, $sphinx_index );
        $spinx_time = time_between('sphinx_torrents_search');

        if ( $sphinx_res === false && $CURUSER['id'] == 1) {
            print "Query failed: " . $cl->GetLastError() . ".\n";
        }
    } else {
        $sphinx_res = array();
        $sphinx_res["matches"] = array("1028"=>1,"1017"=>"1");
    }

    if (is_array($sphinx_res) && isset($sphinx_res["matches"]) && count($sphinx_res["matches"])) {
        $matched_torrents_ids_arr = array();

        foreach ( $sphinx_res["matches"] as $doc => $docinfo )
        {
            if (!is_numeric($doc)) continue;
            $matched_torrents_ids_arr[] = $doc;
        }


        /**
            Mysql part
        **/

        list(,, $limit) = pager($torrentsperpage, 1000, "zz?");

    // Mongo filter
    if (isset($_GET['categtags'])) {
      list($count,$matched_torrents_ids_arr) = getTorrentsByCategTagsAndTorrentsId($matched_torrents_ids_arr,$_GET['categtags']);
    }

    $matched_torrents_ids = @join(',', $matched_torrents_ids_arr );

    if (empty($matched_torrents_ids)) $matched_torrents_ids = 0;

    time_between('sql_torrents_search');

    $query = Torrents::list_query($queryColumn, "torrents.id IN ($matched_torrents_ids) $peersFiltering", $querySort, $limit);

    $res = q($query);

    $sql_time = time_between('sql_torrents_search');

    $count = count($matched_torrents_ids_arr);

    if ($count > 0)
    {

?>
<h1  class="center search_str"><?=__("Rezultatele căutării")?>: <?=esc_html($_GET['search_str'])?></h1>
<br/>
<?php

            $addparam = 'search_str=' . urlencode($search_str) . "&".$addparam;
            $showNoPeersQueryParam = $showNoPeers ? 'show_no_peers=1&' : '';
            $addparamSort = $addparam . (strlen($sortparam)?"sort={$sortparam}&":'') . $showNoPeersQueryParam;
            $addparamOrder = ($orderparam == 'ASC')? "order=asc&":'';
            list($pagertop, $pagerbottom, $limit) = pager($torrentsperpage, $count, "search.php?" . $addparamSort . $addparamOrder);
        } else {
            echo '<h1 class="center search_str">'. __('Nici un torrent care să conţină în denumire cuvântul / cuvintele ') . esc_html($_GET['search_str']) . __(' n-a putut fi gasit.') .'</h2>';
        }
    } else {
        echo '<h1 class="center search_str">'. __('Nici un torrent care să conţină în denumire cuvântul / cuvintele ') . esc_html($_GET['search_str']) . __(' n-a putut fi gasit.') .'</h2>';
    }

    $useSearchInFiltersBody = true;


    //Fill search input
    echo '<div id=searc_str_container style="display:none;">'.esc_html($_GET['search_str']).'</div>';
    echo "<script type=\"text/javascript\">_ge_by_name('search_str').value=$('searc_str_container').firstChild.data;</script>";


    $showNoPeersToggleValue = $showNoPeers ? '0' : '1';
?>



<a href="?<?=$addparam?>show_no_peers=<?=$showNoPeersToggleValue?>"><?=__('Afisează torrentele fără peeruri')?></a><br/><br/>

<?


}
?>

<noscript>
    <form method="get" action="search.php" style="display:inline;"><input name="search_str" type="text" size="20"> <input type="submit" value="Caută Torrenturi"></form>
    <style>
        .prnt{display:none;}
    </style>
</noscript>
<?php

include_once 'browse_filters_body.php';

stdfoot();

/**
 * @param array $torrents_ids
 * @param string $categories separtor(,)
 * @return array($count,$torrents_ids)
 */
function getTorrentsByCategTagsAndTorrentsId($torrents_ids, $categories) {
  global $addparam;
  if (!is_array($categories)) $categories = explode(",",$categories);
  foreach($categories AS $categoryI=>$category) {
    if ( !is_numeric($category) || !($category > 0 && $category < 10000) )
      unset($categories[$categoryI]);
    $categories[$categoryI] = (int)$category;
  }

  if (!count($categories)) return $torrents_ids;

  $addparam .= 'categtags='.esc_html(join(',',$categories)).'&';

  Torrents::$skip = 0;
  Torrents::$torrentsperpage = 1000;
  return Torrents::getByCategTagsAndIds($categories,$torrents_ids);
}
