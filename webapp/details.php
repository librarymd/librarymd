<?php
require_once("./include/bittorrent.php");
require_once($INCLUDE . 'torrent_opt.php');
include_once($INCLUDE . 'classes/categtag.php');
require_once($INCLUDE . 'classes/users.php');

loggedinorreturnNotMd();
$aUser = Users::isLogged();

$id = (int) $_REQUEST["id"];
if (!is_numeric($id)) die();

//If seeder/leacher list are required, declare functions
if (isset($_GET["dllist"]) || isset($_GET["a_showpeers"])) {
   function leech_sort($a,$b) {
     if ( isset( $_GET["usort"] ) ) return seed_sort($a,$b);
     $x = $a["to_go"];
     $y = $b["to_go"];
     if ($x == $y)  return 0;
     if ($x < $y) return -1;
     return 1;
   }
   function seed_sort($a,$b) {
     $x = $a["uploaded"];
     $y = $b["uploaded"];
     if ($x == $y) return 0;
     if ($x < $y) return 1;
     return -1;
   }

   function dltable($name, $arr, $torrent) {
    global $CURUSER;
    $s = "<b>" . count($arr) . " $name</b>\n";
    if (!count($arr)) return $s;
    $s .= "\n";
    $s .= "<table class=main border=1 cellspacing=0 cellpadding=5>\n";
    $s .= "<tr><td class=colhead>". __('Utilizator') ."</td>" .
             "<td class=colhead align=center>". __('Conectabil') ."</td>".
             "<td class=colhead align=right>". __('Încărcat') ."</td>".
             "<td class=colhead align=right>". __('Rată') ."</td>".
             "<td class=colhead align=right>". __('Descărcat') ."</td>" .
             "<td class=colhead align=right>". __('Rată') ."</td>" .
             "<td class=colhead align=right>". __('Raport') ."</td>" .
             "<td class=colhead align=right>". __('Completat') ."</td>" .
             "<td class=colhead align=right>". __('Conectat') ."</td>" .
             "<td class=colhead align=right>". __('Inactiv') ."</td>" .
             "<td class=colhead align=left>". __('Client') ."</td></tr>\n";
    $now = time();
    $moderator = isAdmin();
    $mod = isAdmin();
    foreach ($arr as $e) {
       // Uploaders special color
       $userColor = '';
       if ($e['class'] == UC_UPLOADER) {
         $userColor = ' style="color:#3366FF"';
       }

         $s .= "<tr>\n";
         if ($e['username']) $s .= "<td><a href=userdetails.php?id=$e[userid] $userColor><b>{$e['username']}</b></a></td>\n";
         else $s .= "<td>" . ($mod ? $e["ip"] : preg_replace('/\.\d+$/', ".xxx", $e["ip"])) . "</td>\n";
         $secs = max(1, ($now - $e["st"]) - ($now - $e["la"]));
         $s .= "<td align=center>" . ($e['connectable'] == "yes" ? __('Da') : "<font color=red>". __('Nu') ."</font>") . "</td>\n";
         $s .= "<td align=right>" . mksize($e["uploaded"]) . "</td>\n";
         $s .= "<td align=right><nobr>" . mksize(($e["uploaded"] - $e["uploadoffset"]) / $secs) . "/s</nobr></td>\n";
         $s .= "<td align=right>" . mksize($e["downloaded"]) . "</td>\n";
         if ($e["seeder"] == "no") $s .= "<td align=right><nobr>" . mksize(($e["downloaded"] - $e["downloadoffset"]) / $secs) . "/s</nobr></td>\n";
         else $s .= "<td align=right><nobr>" . mksize(($e["downloaded"] - $e["downloadoffset"]) / max(1, $e["finishedat"] - $e['st'])) .  "/s</nobr></td>\n";
         if ($e["downloaded"]) {
           $ratio = floor(($e["uploaded"] / $e["downloaded"]) * 1000) / 1000;
           $s .= "<td align=\"right\"><font color=" . get_ratio_color($ratio) . ">" . number_format($ratio, 3) . "</font></td>\n";
         }
         else
           if ($e["uploaded"]) $s .= "<td align=right>Inf.</td>\n";
           else $s .= "<td align=right>---</td>\n";
         $s .= "<td align=right>" . sprintf("%.2f%%", 100 * (1 - ($e["to_go"] / $torrent["size"]))) . "</td>\n";
         $s .= "<td align=right>" . mkprettytime($now - $e["st"]) . "</td>\n";
         $s .= "<td align=right>" . mkprettytime($now - $e["la"]) . "</td>\n";
         $s .= "<td align=left>" . esc_html(getagent($e["agent"], $e["peer_id"])) . "</td>\n";
         $s .= "</tr>\n";
    }
    $s .= "</table>\n";
    return $s;
   }

   if (isset($_GET['ajax'])) {
       header('Content-Type: text/javascript');
       echo "{}";
       exit();
       $downloaders = array();
       $seeders = array();
       $subres = q("SELECT peers.seeder, peers.finishedat, peers.downloadoffset, peers.uploadoffset, peers.ip, peers.port, peers.uploaded, peers.downloaded, peers.to_go, UNIX_TIMESTAMP(peers.started) AS st, peers.connectable, peers.agent, peers.peer_id, UNIX_TIMESTAMP(peers.last_action) AS la, peers.userid, users.username, users.class, users.ip
                    FROM peers
                LEFT JOIN users ON peers.userid = users.id
                WHERE torrent = $id");
       while ($subrow = mysql_fetch_array($subres)) {
           if ($subrow["seeder"] == "yes") $seeders[] = $subrow;
       else $downloaders[] = $subrow;
    }
    usort($seeders, "seed_sort");
    usort($downloaders, "leech_sort");
    $row['size'] = q_singleval("SELECT size FROM torrents WHERE id=$id");

    //Transmit in JSON format
    include "JSON.php";
    $json = new Services_JSON();
    $json_output = array(esc_html($_GET['lsource']), array('Leechers',dltable("Leecher(s)", $downloaders, $row)),
               array('Seeders',dltable('Seeder(s)', $seeders, $row)) );
    header('Content-Type: text/javascript');
    echo $json->encode($json_output);
    exit();
  }
} //end if dllist

$torrent = mem_get('torrent_'.$id);

if ($torrent == false) {
  $torrent = fetchRow('SELECT torrents.seeders, torrents.leechers, torrents.filename, UNIX_TIMESTAMP( ) - UNIX_TIMESTAMP( torrents.last_action ) AS lastseed, torrents.name, torrents.owner, torrents.save_as, torrents.visible, torrents.size, torrents.added, torrents.views, torrents.hits, torrents.times_completed, torrents.id, torrents.type, torrents.numfiles, torrents.category, torrents.image, torrents.torrent_opt, torrents.team, info_hash_sha1,
        categories.name AS cat_name, users_username.username, teams.name AS teamName, torrents.comments, torrents.moder_status, GROUP_CONCAT(catetag) AS catetags,
        torrents.dht_peers,                  torrents.dht_peers_updated,
        torrents.dht_peers_update_scheduled, torrents.dht_peers_job_started
      FROM torrents
      LEFT JOIN categories ON torrents.category = categories.id
      LEFT JOIN users_username ON torrents.owner = users_username.id
      LEFT JOIN teams ON (torrents.team > 0 AND torrents.team = teams.id)
      LEFT JOIN torrents_catetags_index ON torrents.id = torrents_catetags_index.torrent
      WHERE torrents.id = :id',array('id'=>$id));
  if (!$torrent) {
    stderr(__('Eroare'), __('Nu există torrent cu ID-ul') . " $id.");
  }

  mem_set('torrent_'.$id, serialize($torrent), 60, MEMCACHE_COMPRESSED);
} else {
  $torrent = unserialize($torrent);
}
require_once($WWW_ROOT . 'scripts_sections/details_include.php');

if (torrent_have_flag('have_imdb',$torrent['torrent_opt'])) {
  $imdb_id = mem_get('t_imdb_id_'.$id);

  if ($imdb_id === false) {
    $imdb_id = fetchOne('SELECT imdb_tt FROM torrents_imdb WHERE torrent = :id', array('id'=>$id) );
    if ($imdb_id == false) $imdb_id = 0;
    mem_set('t_imdb_id_'.$id,$imdb_id,86400);
  }
  $torrent['imdb_tt_id'] = $imdb_id;
}

if (torrent_have_flag('have_imdb',$torrent['torrent_opt']) && $imdb_id) {
  $imdb = mem_get('imdb_'.$imdb_id);
  if ($imdb == false) {
    $imdb = fetchRow(
      'SELECT votes AS imdb_votes,rating AS imdb_rating, imdb_tt.torrents AS imdb_total_torrents
      FROM imdb_tt
      WHERE imdb_tt.id = :id',array('id'=>$imdb_id));
    if ($imdb == false) $imdb = array('imdb_votes'=>0,'imdb_rating'=>0,'imdb_tt_id'=>0,'imdb_total_torrents'=>0);
    mem_set('imdb_'.$imdb_id,$imdb,86400);
  }
  if (!empty($imdb)) {
    $torrent = array_merge($imdb,$torrent);
  }
}

if ($torrent['moder_status'] == 'copyright') {
  $torrent["times_completed"] = 0;
  $torrent["leechers"] = 0;
  $torrent["seeders"] = 0;
  $torrent["numfiles"] = 0;
  $torrent["views"] = 0;
  $torrent["numratings"] = 0;
  $torrent["dht_peers"] = 0;
  unset($_GET["snachlist"],$_GET["dllist"],$_GET["filelist"]);
}

// torrents_details is get from the cache
$torrents_details = mem_get('torrents_details_'.$id);

if (!$torrents_details || !strlen($torrents_details)) {
  $torrents_details = fetchOne('SELECT descr_html FROM torrents_details WHERE id=:id', array('id'=>$id) );
  mem_set('torrents_details_'.$id,serialize($torrents_details),3600,MEMCACHE_COMPRESSED);
} else {
  $torrents_details = unserialize($torrents_details);
}

$torrent['descr_html'] = $torrents_details;

$owned = $moderator = 0;
$isModeratorJunior = (get_user_class() == UC_SANITAR);
if (isAdmin())
  $owned = $moderator = 1;
elseif ($CURUSER["id"] == $torrent["owner"])
  $owned = 1;

$viewsCacheName = 'torrents_views_'.$id;
$viewsCache = mem_get($viewsCacheName);

if ($viewsCache) $torrent['views'] = $viewsCache;

  if ( (!check_referer_script('details.php') && isset($_SERVER['HTTP_REFERER'])) || isset($_GET["hit"]) ) {
    // Update each 10 times
    if ($viewsCache === FALSE) {
      mem_set($viewsCacheName,$torrent['views']+1,86400);
      $viewsCache = $torrent['views'] + 1;
    } else {
      $viewsCache = $viewsCache + 1;
      mem_set($viewsCacheName,$viewsCache,86400);
    }

    if ($viewsCache % 10 == 0) {
      q("UPDATE torrents SET views = $viewsCache WHERE id = $id");
    }

    if (isset($_GET["hit"])) {
      if ($_GET["tocomm"])
        header("Location: ./details.php?id=".$id."&page=0#startcomments");
      elseif ($_GET["filelist"])
        header("Location: ./details.php?id=".$id."&filelist=1#filelist");
      elseif ($_GET["toseeders"])
        header("Location: ./details.php?id=".$id."&dllist=1#seeders");
      elseif ($_GET["todlers"])
        header("Location: ./details.php?id=".$id."&dllist=1#leechers");
      else
        header("Location: ./details.php?id=".$id);
      exit();
    }
  }

  if (!isset($_GET['page'])) {
    stdhead($torrent["name"], true, "details");

    if ($aUser && $CURUSER["id"] == $torrent["owner"] || isAdmin())
      $owned = 1;
    else
      $owned = 0;

    $spacer = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

    if (isset($_GET["uploaded"])) {
      print("<h2 class='center'>". __('Încărcat cu succes!') ."</h2>\n");
      print("<p class='center'>". __('Acum puteţi începe seeding-ul. <b>Reţineţi</b> că torrent-ul nu va fi vizibil până când nu veţi face asta!') ."</p>\n");
    }
    elseif (isset($_GET["edited"])) {
      if (isset($_GET["newimage"])) {
        print("<h2 class='center'>". __('Imaginea nouă a fost încărcată!') ."</h2>\n");
      }
      print("<h2 class='center'>". __('Redactat cu succes!') ."</h2>\n");
      if (isset($_GET["returnto"])) {
        print("<p><b><a href=\"" . esc_html($_GET["returnto"]) . "\">". __('Înapoi.') ."</a>.</b></p>\n");
      }
    }
    elseif (isset($_GET["searched"])) {
      print("<h2>Your search for \"" . esc_html($_GET["searched"]) . "\" gave a single result:</h2>\n");
    }
    elseif (isset($_GET["rated"])) print("<h2>Rating added!</h2>\n");

    global $lang;

    if (isset($_GET['redownload'])) {
      echo '<script type="text/javascript">setTimeout("window.location=\'', "./download.php?id=$id",'\'",1000)</script>';
      echo "<h2>{$lang['uploaded_redownload']}</h2>";
    }


        $torrent_name = $torrent["name"];

        //If founded in bookmark table, then id > 0
        if ($aUser)
          $bookmarkid = q_singleval('SELECT id FROM bookmarks WHERE torrentid='.$id.' AND userid='.$CURUSER['id']);
        if ($bookmarkid > 0) {
          $bookmark_icon = 'love_on.gif';
          $bookmark_action = 'del';
        } else {
          $bookmark_icon = 'love_off.gif';
          $bookmark_action = 'add';
        }

        //Preloader
        echo '<img src="./pic/stars/love_on.gif" class="hideit">',"\n";

        $love_img = "<a href='./bookmarks.php?action=$bookmark_action&torrentid=$id'><img id='love_icon' src='./pic/stars/$bookmark_icon'></a>";

    print("<h1>$torrent_name $love_img</h1>\n");
        print('<table id="details" width="990" border="1" cellspacing="0" cellpadding="5">'."\n");
        echo '<colgroup><col width="90" align="right" valign="top"><col width="790" align="left"></colgroup>';
    $url = "edit.php?id=" . $torrent["id"];
    if (isset($_GET["returnto"])) {
      $addthis = "&amp;returnto=" . urlencode($_GET["returnto"]);
      $url .= $addthis;
      $keepget = $addthis;
    }
    $editlink = "a href=\"$url\" class=\"sublink\"";
  ?>
      <?php if (get_user_class() == UC_SANITAR || get_user_class() >= UC_MODERATOR || have_flag('copyrighter') ): ?>
    <tr>
      <td class="rowhead">
        <?=__('Statutul torrentului')?>
      </td>
      <td align=left width=710>
        <form action="details.php" method="POST">
        <input type="hidden" name="id" value="<?=$id?>">
        <select name="moder_status" size="11">
        <?php
          $status = array('neverificat','verificat','se_verifica','necomplet','parital_necomplet','inchis','dublare','absorbit','copyright','dubios','temporar');
          if (have_flag('copyrighter')) $status = array('neverificat','copyright');
          foreach($status AS $statut) :
            $statut_text = torrent_moder_status_to_text($statut);
        ?>
          <option value="<?=$statut?>" <?=($torrent['moder_status']==$statut)?'selected':''?>><?=__($statut_text)?></option>
        <?php endforeach; ?>
        </select>
        <input type="submit" value="<?=__('Schimbă')?>">
        </form>
      </td>
    </tr>
      <?php endif;?>
    <tr>
      <td class="rowhead"><?=__('Copiază')?></td>
      <td align=left>
      <?php
      //controlam pentru fisiere executabile
      $isAppzOrGame = ($torrent["cat_name"]=='Appz' || $torrent["cat_name"]=='Games');
            $isVideoTorrent = ($torrent["cat_name"]=='Movies' || $torrent["cat_name"]=='TV' || $torrent["cat_name"]=='Anime' || $torrent["cat_name"]=='Animation' || $torrent["cat_name"]=='DVD' || $torrent["cat_name"]=='Movies Documentary' || $torrent["cat_name"]=='HDTV' || $torrent["cat_name"]=='Sport');
      $isMoreThanOneFile = ($torrent["type"] == 'multi'); //sa consideram ca in torentele cun singur fiser nu esista .executabile

      $isPotDang = false;
      if(!$isAppzOrGame && $isMoreThanOneFile) {
        $isPotDang = mem_get('torrent_'.$id.'_dangerous');
        if(!$isPotDang) {
          $isExeFiles = fetchOne('SELECT id FROM files where torrent = '. sqlesc($id) .'  AND (filename LIKE "%.exe" OR filename LIKE "%.bat")');
          $isExeFiles = ($isExeFiles!=NULL);

          $isPotDang = serialize($isExeFiles);
          mem_set('torrent_'.$id.'_dangerous',$isPotDang,86400);
        }
        $isPotDang = unserialize($isPotDang);
      }

      if($isPotDang)
      {
        ?>
<script type="text/javascript">
    $j(function($)
    {
      $down = $('div.potDangerous').hide();
      $('span.i.agree').click(function()
      {
        $(this).parent().fadeOut(function()
        {
          $down.fadeIn();
        });
      });
    });
</script>
<div class="potDangDisclamer">
  <b><?=__('Atenție!')?></b> <?=__('Torentul dat conține fișiere executabile (.exe).')?> <!--<a href="#undevaPeForum"><?=__('detalii')?></a>--> <br />
  <span class="i agree"><?=__('Înțeleg riscul și doresc să downloadez torrentul')?></span>
</div>
<?php } //\\if($isPotDang)

if ($torrent['category'] == 4 || $torrent['category'] == 3):
?>

<div class="potDangDisclamer">
  <b><?=__('Atenție!')?></b> <?=__('Torentul poate conține viruși.')?><br />
  <?=__('Este necesar să scanați fișierul cu un antivirus înainte de a fi executat.<br/>
  Puteți scana online fișiere individuale utilizînd <b><a href="https://www.virustotal.com" target="_blank">VirusTotal.com</a></b>.')?>
  <?=__('In caz că depistați viruși, vă rugăm să semnalați torrentul') . ' <a href="/details_report.php?id='.$id.'">' . __('aici') . '</a>.'?>
</div>
<br/>

<?php
endif;
            //numarul canalelor audio
            $soundChannels = 0;

            //categs
            if (isset($torrent['catetags']))
            {
                $categtags = explode(',',$torrent['catetags']);
                $categtags_arr = array();

                foreach($categtags AS $categtag) {
                    $categtag = new CategTag($categtag);
                    if ($categtag->isEmpty()) continue;
                    $categtags_arr[] = $categtag;

                    //daca categtag este fiu a categtagului Limba
                    $soundChannels += (int)($categtag->tag['father'] == $siteVariables['categtag']['languageCatID']);
                }
            }

            ?>
<div class="potDangerous">
        <?php
          if(torrent_status_downloadable($torrent) !== true): ?>
            <?= torrent_status_downloadable($torrent) ?>
          <? else: ?>
                <?php
                    $opentrackerUrlParam = '';
                    foreach (getPublicTrackers() as $opentracker) {
                      $opentrackerUrlParam .= sprintf('tr=%s&', $opentracker);
                    }

                    $magnetLink = sprintf(
                      'magnet:?xt=urn:btih:%s&dn=%s&%s',
                      rawurlencode($torrent['info_hash_sha1']),
                      rawurlencode($torrent["filename"]),
                      $opentrackerUrlParam
                    );

                    $showDownloadTorrentLink = ( $siteVariables['torrents']['checkIfOndisk'] == false || is_file("$torrent_dir/".$torrent['id'].".torrent") );

                    if ( $showDownloadTorrentLink ) {
                      if (is_file("$torrent_dir/".$torrent['id'].".torrent"))
                        echo torrent_download_link_html($torrent);

                        printf('&nbsp;&nbsp;&nbsp;&nbsp;(<a href="%s" class="magnetlnk">magnet</a>)',$magnetLink);
                    } else {
                        printf('<a href="%s" class="magnetlnk">%s</a>',
                            $magnetLink,
                            esc_html($torrent["filename"])
                        );
                    }
                    echo ' <b>(<a href="/forum.php?action=viewtopic&topicid=11&page=last">'. __('cum se copiază ?') .'</a>)</b>'
                ?>
                  <? endif; ?>
      </div>

             <?php
             //alertam daca-s mai multe coloane sonore
             if ($soundChannels > 1 && $isVideoTorrent) { ?>
                 <div class="moreLangChannelsDisclamer">
                     <b><?=__('Util')?></b> <?=__('Acest torrent conține mai multe coloane audio în limbi diferite.')?>  <br />
                     <a href="/forum.php?action=viewtopic&topicid=88141794"><?=__('Vedeți aici cum să alegeți limba din video playerul dvs.')?></a>
                 </div>
        <?php } ?>
            </td>
    </tr>
    <?php

    // Team Release
    if ($torrent['teamName']) {
      $teamLink = '<a href="./team.php?name='.str_replace(' ','_',$torrent['teamName']).'">'.$torrent['teamName'].'</a> ';
      tr(__('Echipa'), $teamLink, 1);
    }

        function browseShowTags($categtags_full) {
            foreach( $categtags_full AS $loopId=>$categtag ) {
                $divSeparator = '';
                if ( $loopId == 100 )
                  $divSeparator = '</div><div class="showAllCategtags"><span>↓ '. __('Arată toate categoriile') .' ↓</span></div><div class="hiddenCategtags">';

                echo stringIntoEsc( $divSeparator . '<span><a href="browse.php?categtags=:id">:name</a></span> ',
                  array("id"=>$categtag->id, "name"=> $categtag->getAcestorsPath() . $categtag->tag['name_'.get_lang()] ) );
            }
        }

        function categtagsIsIn($categtags_full, $categtagId) {
          foreach( $categtags_full AS $loopId=>$categtag ) {
            if ($categtag->id == $categtagId) return true;
          }
          return false;
        }

        $report_torrent = '<a href="/details_report.php?id='.$id.'">'.__('Semnalează torrentul').'</a>';
        echo '<tr><td valign="top" align="right">'.__('Acțiuni').'</td><td>'.$report_torrent.'</td></tr>';
        if (isset($torrent['catetags'])) {
?>
        <tr id="detailsCategories">
        <td valign="top" align="right"><?=__('Categorii')?></td>
        <td>
        <div id="categtagInactivList">
      <div class="visibleCategtags">
<?php
            browseShowTags($categtags_arr);
        }
?>
      </div>
        </div>
        </td></tr>
<?php
    /*
      Description goes here!
    */

    echo '<tr class="description"><td valign="top" align="right">'.__('Descriere').'</td><td valign="top" align=left style="line-height: 1.4">',
        prepare_descr_html($torrent['descr_html'],$torrent['category'],$torrent['image'],$torrent['id']),'</td></tr>';

    /*
      IMDB
    */

    if (torrent_have_flag('have_imdb',$torrent['torrent_opt'])) {
      $imdb_same_torrents_html = '';
      if ( isset($torrent['imdb_total_torrents']) && $torrent['imdb_total_torrents'] > 1 ) {
        $imdb_same_torrents_html = '<br>'.$torrent['imdb_total_torrents'] . ' <a href="./browse.php?imdb='.$torrent['imdb_tt_id'].'">'.__('torrente cu același număr IMDB').'</a>';
      }


      $t ='<tr>
            <td><a href="/forum.php?action=viewtopic&topicid=361692#10" target="_blank"><img src="/pic/imdb_25.png" title="IMDB - The Internet Movie Database" align="right"></a></td>
            <td id="td_detail_imdb"><a href="http://www.imdb.com/title/tt%07d/" target="_blank">';
      if ($torrent['imdb_votes'] == 0) {
        printf($t.'%s</a> %s</td></tr>',$torrent['imdb_tt_id'],__('Încă nu sunt suficiente voturi'),$imdb_same_torrents_html);
      } else {
        printf($t.'<b>%.1f/10</b> (%s %s)</a>%s</td>
            </tr>'
         ,$torrent['imdb_tt_id'], $torrent['imdb_rating'] / 10, number_format($torrent['imdb_votes'], 0, '.', ','), __('voturi'),$imdb_same_torrents_html);
      }
    }

    if ($torrent['visible'] == "no")
      tr(__("Vizibil"), "<b>".__('nu')."</b>", 1);

    if (isset($torrent["cat_name"]))
      tr(__('Tip'), __($torrent["cat_name"]) );
    else
      tr(__('Tip'), "(none selected)");


    // Daca e film in engleza
    if (categtagsIsIn($categtags_arr,89) && categtagsIsIn($categtags_arr,180) && torrent_have_flag('have_imdb',$torrent['torrent_opt']) ) {
      $subtitle_english = sprintf('<a href="%s" target="_blank">'.__('engleză').'</a>',"http://www.opensubtitles.org/en/search/sublanguageid-eng/imdbid-".$torrent['imdb_tt_id']);
      $subtitle_romanian = sprintf('<a href="%s" target="_blank">'.__('română').'</a>',"http://www.opensubtitles.org/en/search/sublanguageid-rum/imdbid-".$torrent['imdb_tt_id']);
      $subtitle_russian = sprintf('<a href="%s" target="_blank">'.__('rusă').'</a> [<a href="%s" target="_blank">1</a>]',"http://www.opensubtitles.org/en/search/sublanguageid-rus/imdbid-".$torrent['imdb_tt_id'],"http://subtitry.ru/subtitles/?film=".$torrent['imdb_tt_id']);

      tr('<b>'.__('Subtitrări').'</b> (<span class="new_feature">new</span>)',__("În")." ".$subtitle_english.", ".$subtitle_romanian.", ".$subtitle_russian,true);
    }

    // if ($torrent['moder_status'] != 'neverificat') {
    //   tr(__('Statut'), getTorrentStatusHtml($torrent), true);
    // }

    // tr(__('Ultimul seeder'), mkprettytime($torrent["lastseed"]) . ' ' . __('în urmă') . ' '. __('(date înnoite fiecare 24 ore)') );
    tr(__('Mărime'),mksize($torrent["size"]) . " (" . number_format($torrent["size"]) . " bytes)");
    tr(__('Adăugat'), $torrent["added"]);
    tr(__('Văzut'), $torrent["views"] . ' ' . __('ori'));

$flag_filelist = (isset($_GET["filelist"]))?'&filelist=1':false;
$flag_snachlist = (isset($_GET["snachlist"]))?'&snachlist=1':false;
$flag_dllist = (isset($_GET["dllist"]))?'&dllist=1':false;

  if ( torrent_have_flag('anonim_unverified', $torrent['torrent_opt']) ) {
    if ( get_user_class() == UC_SANITAR || get_user_class() >= UC_MODERATOR )
      $uprow = (isset($torrent["username"]) ? ("<span id=owner_username><i>Anonim</i></span>{$spacer}<b><span class=lnk id=get_torrent_owner>[".__("Vezi autorul") ."]</span></b>") : "<i>unknown</i>");
    else
      $uprow = '<i>'. __('Anonim') .'</i>';
  } elseif ( torrent_have_flag('anonim', $torrent['torrent_opt']) )
      $uprow = '<i>'. __('Anonim') .'</i>';
  else
    $uprow = (isset($torrent["username"]) ? ('<a href="userdetails.php?id=' . $torrent["owner"] . '" class="username">' . esc_html($torrent["username"]) . "</a>") : "<i>unknown</i>");
  if ($owned || $isModeratorJunior)
    $uprow .= " <$editlink>[".__('Editează acest torrent')."]</a> ".
            stringIntoEsc('<a href="/edit_categories.php?id=:id" class="editCategories">['.__("Editează categoriile").']</a>',
                array("id"=>$torrent["id"]) );

    if ( $torrent['owner'] == $CURUSER['id'] && $torrent['moder_status'] == 'verificat' )
      $uprow .= '<a href="details.php?id='. $id .'&action=unsubscribe" class="unsubscribe"><b>['. __("Renunț la acest torrent") .']</b></a>';
    tr(__('Încărcat de'), $uprow, 1, 'uploadedByTd');

    $nowget = esc_html($_SERVER['QUERY_STRING']);

    if ($torrent["type"] == "multi") {
      if (!@$_GET["filelist"]) {
        tr(__('Fişiere') ."<br /><a href=\"details.php?$nowget&filelist=1#filelist\" class=\"sublink\" id=\"a_showallfiles\">[". __('Vezi întreaga listă') ."]</a>", $torrent["numfiles"] . " " . __('fişiere'), 1);
      } else {
        tr(__('Fişiere'), $torrent["numfiles"] . " files", 1);

        $s = "<table class=main border=\"1\" cellspacing=0 cellpadding=\"5\">\n";

        $subres = q("SELECT filename, size FROM files WHERE torrent = $id ORDER BY id");
        $s.="<tr><td class=colhead>". __('Locaţia') ."</td><td class=colhead align=right>". __('Mărime') ."</td></tr>\n";
        while ($subrow = mysql_fetch_array($subres)) {
          $s .= "<tr><td>" . esc_html($subrow["filename"]) .
                            "</td><td align=\"right\">" . mksize($subrow["size"]) . ' ('.number_format($subrow["size"]).' bytes)'."</td></tr>\n";
        }

        $s .= "</table>\n";
        $close_nowget = str_replace('&amp;filelist=1','',$nowget);
        tr("<a name=\"filelist\">". __('Lista fişierelor') ."</a><br /><a href=\"details.php?$close_nowget\" class=\"sublink\">[". __('Ascunde lista') ."]</a>", $s, 1);
        unset($s);
      }
    }

    $dhtUpdateDateHuman = get_elapsed_time(strtotime($torrent['dht_peers_updated']));
    $wasDhtEverUpdated = $torrent['dht_peers_updated'] != '1970-01-01 00:00:00';
    $dhtPeersText = (
       $wasDhtEverUpdated ?
       $torrent["dht_peers"] . ' peer(s), ' . __('ultimul update') . ': ' . $dhtUpdateDateHuman :
       __('Programat pentru update')
    );
    tr(__('DHT peer-uri') . '<a name="dht_peers"></a>', $dhtPeersText, 1);

    //*************
    // Thanks
    //*************
    $thanks_str = mem_get('torrent_thank2_'.$id);

    if ($thanks_str === FALSE) { //Fill, because expired

      $thanks_str = ' ';

      $thanks = fetchAll('SELECT torrents_thanks.user, users_username.username
         FROM torrents_thanks
         LEFT JOIN users_username ON torrents_thanks.user = users_username.id
         WHERE torrents_thanks.torrent='.$id.'
         ORDER BY torrents_thanks.thank_time');


      foreach ($thanks as $thank) {
        $thanks_str .= '[url=./userdetails.php?id='.$thank['user'].']'.$thank['username'].'[/url], ';
      }

      //Cutoff last 2 chars(', ')
      if (count($thanks)) {
        $thanks_str = substr($thanks_str,0,-2);

        mem_set('torrent_thank2_'.$id,$thanks_str,86400);
      }
    }

    $already_thank = false;

    if (!$aUser)
      $already_thank = true;
    elseif ( strpos( $thanks_str, '=' . $CURUSER['id'].']' ) !== FALSE ) {
      $already_thank = true;
    }

    if ($CURUSER["id"] == $torrent["owner"]) { //The owner of the torrent can't thank himself
      $already_thank = true;
    }

    $thanks_count = substr_count($thanks_str, ',');

    if (strlen($thanks_str) > 1) $thanks_count++;

    $thanks_str .= "\n";

    $thanks_str_text = __('Lista celor care au mulțumit');
    $spoilerTag = 'ospoiler';
    if ($thanks_count > 100) $spoilerTag = 'spoiler';
    $thanks_str = "[{$spoilerTag}=$thanks_str_text]${thanks_str}[/{$spoilerTag}]";

    tr('<nobr>'.$lang['comment_sayd_thanks'] . ' (<span id="thanks_count">'.$thanks_count.'</span>)</nobr>', format_comment($thanks_str) . (($already_thank === false)?
    '<form action="details.php" method="post">
    <input type="hidden" name="id" value="'.$id.'">
    <input type="submit" name="thank" value="'.$lang['comment_thanks'].'">
    </form>':'')
    ,1);

    print("</table>\n");
    echo "<table id='table2'></table>";

    /**
      End part
    */
    if ($bookmark_icon == 'love_on.gif') $bookmark_status = 'yes';
    else $bookmark_status = 'no';
    echo '<script type="text/javascript">
        torrents_md_bookmarked="' . $bookmark_status . '";
      </script>';
  }
  else {
    stdhead(__('Comentariile torrentului')." \"" . $torrent["name"] . "\"");
    print("<h1>".__('Comentariile torrentului')." <a href=details.php?id=$id>" . $torrent["name"] . "</a></h1>\n");
  }
  ?>
  <script type="text/javascript" src="./js/details.js?v=2"></script>
<?php
  if ( get_user_class() >= UC_MODERATOR || get_user_class() == UC_SANITAR )
    echo '<script type="text/javascript" src="./js/details_admin.js?v=0.1"></script>';
?>
  <script>
    torrents_md_nick="<?=$CURUSER['username']?>";
    torrents_md_torrent_id="<?=$torrent['id']?>";
    var lang_raport_ok="<?=__('Raportat, mulţumim')?>"
  </script>
  <?php
/**
  Comments
*/

  /**
    Releasers are allowed to manage comments from their own torrent
  */
  $torrentOwnerReleaser = false;
  $tOwner = $torrent['owner'];
  if ( (get_user_class() == UC_RELEASER || get_user_class() == UC_SANITAR) && $tOwner == $CURUSER['id']) {
    $torrentOwnerReleaser = true;
  }

  // Releasers have rights of moderator over their own torrents
  if (!$moderator && $torrentOwnerReleaser && get_config_variable('torrents', 'releasers_moderators')) $moderator = true;

  if ($moderator) {
    echo '<br>';
    if (torrent_have_flag('is_comment_locked',$torrent['torrent_opt'])) {
      echo '<form action="comment.php" method="post" style="display:inline">
               <input type="hidden" name="action" value="unlockcomments">
                <input type="hidden" name="tid" value="'.$id.'">
                <input type="submit" name="sure" value="Unlock comments">
                </form>';
    } else {
      echo '<form action="comment.php" method="post" style="display:inline">
               <input type="hidden" name="action" value="lockcomments">
                <input type="hidden" name="tid" value="'.$id.'">
                <input type="submit" name="sure" value="Lock comments">
                </form>';
    }
    if (torrent_have_flag('is_comments_hidden',$torrent['torrent_opt']) || $torrent['moder_status'] == 'copyright') {
      echo '<form action="comment.php" method="post" style="display:inline">
               <input type="hidden" name="action" value="unhiddecomments">
                <input type="hidden" name="tid" value="'.$id.'">
                <input type="submit" name="sure" value="Unhide comments">
                </form>';
    } else {
      echo '<form action="comment.php" method="post" style="display:inline">
               <input type="hidden" name="action" value="hiddecomments">
                <input type="hidden" name="tid" value="'.$id.'">
                <input type="submit" name="sure" value="Hide comments">
                </form>';
    }
  }

  // Top tag
  echo '<a name=top></a>';


  /*
    Watcher section
  */

  // Check if this torrent is in watch list
  if ($CURUSER['id'] > 0) {
    $userid = $CURUSER['id'];
    $watchId = q_singleval("SELECT id FROM watches WHERE user=$userid AND thread=$id AND type='torrent'");
  }
  $watchOn = ($watchId > 0)?true:false;
  echo '<script type="text/javascript">
    var langWatchOn="',$lang['watch_on'],'";
      var langWatchOff="',$lang['watch_off'],'";
      var watchStatut=',(($watchOn)?'true':'false'),';
    var topicId=',$id,';
    </script>';

  //Check if comments are not hidden
  if (torrent_have_flag('is_comments_hidden',$torrent['torrent_opt']) && !$moderator) {
    echo '<br><table class="mCenter" border=1 cellspacing=5 cellpadding=5 onclick="Watcher();" style="cursor:pointer;"><tr><td id="watcherText"',(($watchOn)?' bgcolor="#D3F1E2"':''),'>', (($watchOn)?$lang['watch_off']:$lang['watch_on']) ,'</td></tr></table>';
    echo '<h2 class="center">',$lang['details_comments_hidden'],'</h2>';
    stdfoot();
    exit();
  }

  $commentbar = file_get_contents("templates/new_comment_bit.tpl");
  //Super Smarty script :)
  $tpl_vars = array('{lang_comment_add_new}', '{lang_comment_send_comment}', '{comment_new_read_rules}', '{torrentid}', '{comment_stamps}','{comment_more_smiles}','{lang}', '{lang_comment_rules}');
  $tpl_values = array($lang['comment_add_new'], $lang['comment_send_comment'], $lang['comment_new_warring'], $torrent["id"], $lang['comment_stamps'],$lang['comment_more_smiles'],get_lang(), $lang['comment_rules']);
    $commentbar = str_replace($tpl_vars, $tpl_values, $commentbar);

  if ($torrent['category'] == 4 || $torrent['category'] == 3) {
    echo '<h2>Daca observați <a href="/forum.php?action=viewtopic&topicid=88152890">viruși in fisiere</a>, va rugăm să <a href="/details_report.php?id='.$id.'">semnalați torrentul</a>.</h2>';
  }

    //Check if comments writing are not disabled
  if ( (torrent_have_flag('is_comment_locked',$torrent['torrent_opt']) || torrent_status_downloadable($torrent) !== true ) && !isTorrentModer()) {
    $commentbar = '<h2>'.$lang['details_comments_locked'].'</h2>';
  }

  if (!$aUser)
    $commentbar = '';

  /**
    What comments table to use
  */
  $count = $torrent['comments'];

  if (!$count) {
    print("<h2 align='center'>". __('Nu există comentarii') ."</h2>\n");
  }
  else {

    // Jump to some comment
    if ( isset($_GET['viewcomm']) && is_numeric($_GET['viewcomm']) ) {
      $viewcomm = $_GET['viewcomm'] + 0;
      $commentPos = q_singleval("SELECT COUNT(id) FROM comments WHERE torrent = $id AND id<=$viewcomm");
      $_GET['page'] = ceil($commentPos/20) - 1;
    }

    list($pagertop, $pagerbottom, $limit) = pager(20, $count, "details.php?id=$id&", array('lastpagedefault' => 1));

    //Used for numerotate comments
    $temp = explode(" ", $limit);
    $temp = explode(",", $temp[1]); // $limit can be LIMIT 20,20
        $GLOBALS['start_i_comments'] = $temp[0];

        $_page = (isset($_GET["page"])? intval($_GET["page"]) : (ceil($count / 20) - 1) );
        $comments_mem_key = "comments:$id:$_page";
      if ($GLOBALS['CURUSER']["id"] == 1) {
          echo "Cleanup $comments_mem_key \n<br>";
      }
        // Check the cache
        $comments = mem_get($comments_mem_key);



        if (!$comments) {
          $comments = fetchAll('SELECT comments.id, text, user, comments.added, comments.censored, editedby,
                                editedat, avatar, avatar_version, warned, username, title, class, donor,
                                users.user_opt, users.enabled, users.gender
                      FROM comments LEFT JOIN users ON comments.user = users.id
                      WHERE torrent = :id ORDER BY comments.id '. $limit, array('id' => $id) );
          mem_set ( $comments_mem_key, $comments, 3600, MEMCACHE_COMPRESSED );
        }

    print($pagertop);
    commenttable($comments,$moderator,$id);
    print($pagerbottom);

    // Check if user have this thread in watch mode, update last seen comment
    if ($CURUSER['id'] > 0)
      $wLastSeenMsg = q_singleval("SELECT lastSeenMsg FROM watches WHERE thread=$id AND type='torrent' AND user={$CURUSER['id']}");
    if ($wLastSeenMsg > 0) {
      $lastCommentId = $comments[count($comments)-1]['id'];
      if ($wLastSeenMsg < $lastCommentId) {
        q("UPDATE watches SET lastSeenMsg=$lastCommentId WHERE thread=$id AND type='torrent' AND user={$CURUSER['id']}");
        mem_delete('user_watch_'.$CURUSER['id']);
      }
    }
  }

  /*
    Watcher section
  */
  if ($aUser)
    echo '<table class="mCenter" border=1 cellspacing=5 cellpadding=5 onclick="Watcher();" style="cursor:pointer;"><tr><td id="watcherText"',(($watchOn)?' bgcolor="#D3F1E2"':''),'>', (($watchOn)?$lang['watch_off']:$lang['watch_on']) ,'</td></tr></table>';

  echo $commentbar;


// Functions

function commenttable($rows,$moderator,$torrentid)
{
  global $CURUSER, $conf_user_opt;
  begin_main_frame();
  begin_frame(false,false,10);
  $count = (isset($GLOBALS['start_i_comments']))?$GLOBALS['start_i_comments']:0;

  $isAdmin = $moderator;
  $sanitar = (get_user_class() == UC_SANITAR);

  foreach ($rows as $row)
  {
    $count++;

    $censored = ($row['censored'] == 'y')?true:false;
    $commentId = $row['id'];

    $padding = ($count==1)?'0':'10';
    echo '<div style="margin:'. $padding .'px;"></div>';
    print('<table border=0 cellspacing=0 cellpadding=0 width="100%"><tr><td class=embedded width=99% height=18><a style="cursor: pointer;text-decoration: underline;" onmousedown="citeaza(this);">#'.$count.'</a>' . " by ");
    if (isset($row["username"]))
    {
      $title = $row["title"];
      if ($title == "")
        $title = get_user_class_name($row["class"]);
      else
        $title = $title;
        print("<a name=comm". $row["id"] .
          " href=userdetails.php?id=" . $row["user"] .(($row["gender"]=='fem')?' style="color:#F93EA0;"':'')."><b>" .
          esc_html($row["username"]) . '</b></a><span class="userIcons">' . get_user_icons($row) . "</span> ($title)\n");
    }
    else
      print("<a name=\"comm" . $row["id"] . "\"><i>(orphaned)</i></a>\n");

    echo " at " . $row["added"] . " (" . get_elapsed_time(sql_timestamp_to_unix_timestamp($row["added"])) . ')';

    // Deny editing if it was already edited by other user(it must moder)

    if (
      ( !$censored && $row["user"] == $CURUSER['id'] && ($row["editedby"] == 0 || $row["editedby"] ==  $row["user"]))
        || $isAdmin
      ) {
          echo " - [<a href=comment.php?action=edit&amp;cid=$commentId&amp;tid=$torrentid>". __('Editează') ."</a>]";
    }


    if ($isAdmin) {
      echo " - [<a href=comment.php?action=delete&amp;cid=$commentId&amp;tid=$torrentid>". __('Şterge') ."</a>]";
    }

    if ($isAdmin) {
      if (!$censored) {
        echo " - [<span class='lnk censlnk' customCid='$commentId'>Cenzurează</span>]";
      } else {
        echo " - [<a href=./comment.php?action=uncensore&cid=$commentId&amp;tid=$torrentid>Decenzurează</a>]";
      }
    }

      if ( $row['class'] < UC_MODERATOR && !$censored && $CURUSER["id"] != $row['user'] && $CURUSER['class'] < UC_MODERATOR ) {
        echo " - [<span class='lnk raportareLnk' customCid='$commentId'>".__('Raportează').'</span>]';
      }

    echo '</td>';

    echo '<td class=embedded width="1%" align="right"><a href=#top><img src="./pic/forum/top.gif" border=0 alt="Top"></a></td></tr></table>';

    if ($CURUSER["avatars"] == 'yes' && $row["avatar"] == 'yes') {
      $avatar = avatarWww($row["user"],$row["avatar_version"]);
    } else
            $avatar = "./pic/forum/default_avatar.gif";
    $text = format_comment($row["text"]);
    if ($row["editedby"]) {
      $editedBy = q_singleval('SELECT username FROM users WHERE id=:editedby',array('editedby'=>$row['editedby']) );
      $text .= "<p><font size=1 class=small>Last edited by <a href=userdetails.php?id=$row[editedby]><b>$editedBy</b></a> at $row[editedat] </font></p>\n";
    }

    if (!$censored) {
      echo '<table class=main width=100% border=1 cellspacing=0 cellpadding=3>';
        print("<tr valign=top>\n");
        print("<td align=center width='150'><img src='$avatar'></td>\n");
        print("<td class=text>$text</td>\n");
        print("</tr>\n");
    } else {
      if ($isAdmin) {
        echo '<table class=main width=100% border=1 cellspacing=0 cellpadding=3>';
          print("<tr valign=top style='background-color:#FAEBE2'>\n");
          print("<td align=center width='150'><img src='$avatar'></td>\n");
          print("<td class=text>$text</td>\n");
          print("</tr>\n");
      } else {
        echo '<table class=main width=100% border=1 cellspacing=0 cellpadding=3>';
          print("<tr valign=top>\n");
          print("<td align=center width='150'></td>\n");
          print("<td class=text><center>Censored</center></td>\n");
          print("</tr>\n");
      }
    }

     end_table();
  }
  end_frame();
  end_main_frame();
}

Torrent_Dht::markForDhtUpdateIfNeeded($torrent);

stdfoot();

function table_imdb_related_torrents($torrents) {
  $torrents = "<table class=main border=1 cellspacing=0 cellpadding=5>\n" .
    "<tr><td class=colhead>Name</td><td class=colhead>Seeders</td><td class=colhead>Leechers</td></tr>\n";

}
?>
