<?php
require_once($INCLUDE . 'torrent_opt.php');

function torrenttableSqlColumns() {
    return "torrents.moder_status, teams.name AS teamName, teams.initials AS teamInitials, torrents.torrent_opt";
}

$torrentsCatImgHeightMap = array();

$torrentsCatImgHeightMap["cat_animation.gif"] = 42;
$torrentsCatImgHeightMap["cat_anime.gif"] = 42;
$torrentsCatImgHeightMap["cat_apps_misc.gif"] = 32;
$torrentsCatImgHeightMap["cat_book_audio.gif"] = 32;
$torrentsCatImgHeightMap["cat_dvd.gif"] = 14;
$torrentsCatImgHeightMap["cat_e_book.gif"] = 32;
$torrentsCatImgHeightMap["cat_games.gif"] = 32;
$torrentsCatImgHeightMap["cat_hdtv.gif"] = 29;
$torrentsCatImgHeightMap["cat_misc.gif"] = 32;
$torrentsCatImgHeightMap["cat_movie.gif"] = 32;
$torrentsCatImgHeightMap["cat_movie_doc.gif"] = 32;
$torrentsCatImgHeightMap["cat_music.gif"] = 32;
$torrentsCatImgHeightMap["cat_music_video.gif"] = 32;
$torrentsCatImgHeightMap["cat_photos.gif"] = 32;
$torrentsCatImgHeightMap["cat_sport.gif"] = 32;
$torrentsCatImgHeightMap["cat_tv.gif"] = 32;
$torrentsCatImgHeightMap["cat_video_lessons.gif"] = 32;

function getImgCategHeight($catImgFileName) {
  global $torrentsCatImgHeightMap;
  return $torrentsCatImgHeightMap[$catImgFileName];
}

// $type can be search
function torrenttable($res,$data='',$type='',$addParam='') {

	// Normalize all to array
	if ($res != '') {
		$data = array();
		while ($row=@mysql_fetch_assoc($res)) {
			$data[] = $row;
		}
	}

	global $pic_base_url, $CURUSER, $lang;
    if (!count($data)) {
    	echo __('Ne pare rău, nici un torrent n-a putut fi găsit');
    	return;
    }
?>
<style>td.colhead a {color: #ffffff;text-decoration: underline;}
.tableTorrents a {
    font-weight: bold;
}
</style>
        <table border="1" cellspacing=0 cellpadding=5 width="990" class="tableTorrents">

        <colgroup>
        	<col width="42"></col>
			<col></col>
			<col></col>
			<col></col>
			<col></col>
			<col></col>
			<col></col>
            <col></col>
			<col></col>
        </colgroup>

        <tr>
          <td class="colhead" align="center"><?=__('Tip')?></td>
          <td class="colhead" align="left"><?=__('Nume')?></td>
          <td class="colhead" align="right"><?=__('Fiş.')?></td>
          <td class="colhead" align="right"><?=__('Com.')?></td>
          <?php if ($type == "search"): ?>
              <td class="colhead" align="center"><a href="search.php?<?php echo $addParam?>sort=date" class="ablank"><?=__('Adăugat')?></a></td>
          <?php else: ?>
            <td class="colhead" align="center"><?=__('Adăugat')?></td>
          <?php endif; ?>
          <td class="colhead" align="center"><?=__('Mărime')?></td>
          <?php if ($type == "search"): ?>
            <td class="colhead nowrap" align="right" style="white-space: nowrap;"><a href="search.php?<?php echo $addParam?>sort=peers">DHT&nbsp;Seed.&nbsp;<img src="/pic/arrowdownup2.gif"></a></td>
          <?php else: ?>
            <td class="colhead" align="right" title="DHT Peers" style="white-space: nowrap;">DHT <img src="/pic/arrowdownup2.gif"/></td>
          <?php endif; ?>

          <td class="colhead" align="right">Mulț.</td>
          <td class="colhead" align="center" style="white-space: nowrap;"><?=__('Încărcat de')?></td>
          </tr>
<?php

    if (isset($GLOBALS['browse_show_lastest'])) {
    	global $top_3;
    	$top_3_i = 0;
    	$in_top = array(); //To prevent double show
    }

    while ( (isset($top_3) && $top_3_i < 5 && isset($top_3[$top_3_i]) && $row = $top_3[$top_3_i]) || list(,$row) = each($data)) {
        $id = $row["id"];

        if (isset($in_top) && in_array($id,$in_top)) continue; //prevent double show of tor from top

        $is_row_top3 = false;

        //Highlight top 3
        if (isset($top_3) && isset($top_3[$top_3_i]) && $top_3_i < 5) {
        	echo '<tr style="background-color: #F2DCDC;">',"\n";
        	$top_3_i++;
        	$in_top[] = $id;
            $is_row_top3 = true;
        } else {
        	if ( isset($CURUSER) && (strtotime($row["added"]) - $CURUSER['last_browse_see']) > 0 && !isset($_GET['unseen']) && $type != "top" )
                echo '<tr style="background-color: #DBF2E7;">',"\n";
        	else print("<tr>\n");
        }

        if (!array_key_exists('moder_status',$row) || !array_key_exists('torrent_opt',$row) || !array_key_exists('teamName',$row)) {
            // @TODO, show an error and log the error, temporary quick hack
            sql_error_handler("functions_additional::torrenttable(). moder_status || torrent_opt || teamName are not set." . var_export($row,true) ,"","1");
            die();
        }

        // Don't show nr of leechers and seeders on the copyrighted torrents
        if ($row['moder_status'] == 'copyright') {
            $row["dht_peers"] = 0;
        }
        print('<td class="torrentCategImg">');
        if (isset($row["cat_name"])) {
            print("<a href=\"browse.php?cat=" . $row["category"] . "\">");
            if (isset($row["cat_pic"]) && $row["cat_pic"] != "")
                printf('<img src="%s" height="%s" alt="%s"/>', $pic_base_url . "categs/" . $row["cat_pic"], getImgCategHeight($row["cat_pic"]), $row["cat_name"] );
            else
                print($row["cat_name"]);
            print("</a>");
        }
        else
            print("-");
        print("</td>\n");

        $dispname = $row["name"];
        $moder_status = getTorrentStatusHtml($row,true) . ' ';
        if (strstr($moder_status,'Neverifica') !== false || strstr($moder_status,'Не проверено') !== false)
            $moder_status = '';

        $url = "/details.php?id=$id";
        if ($is_row_top3 && $row["imdb_tt"]) {
            $url = "browse.php?imdb=" . $row["imdb_tt"];
        }

        if ($row['imdb_rating']) {
          $dispname .= sprintf(" [%.1f/10]", $row['imdb_rating'] / 10);
        }

        if ($row['catetags']) {
          $row['catetags'] = explode(',', $row['catetags']);
          if (in_array(27, $row['catetags'])) {
            $dispname .= " [RO]";
          }

          if (in_array(180, $row['catetags'])) {
            $dispname .= " [EN]";
          }

        }

        echo '<td align=left>'.$moder_status.'<a href="',$url,'"',">$dispname</a>\n";

        //For Wait&Added colone
        $elapsed = floor((time() - strtotime($row["added"])) / 3600);

        print("</td>\n");
        if ($row["numfiles"] == 1) print("<td align=\"right\">" . $row["numfiles"] . "</td>\n");
        else {
        	echo '<td align="right"><a href="details.php?id=',$id,'&amp;filelist=1">', $row["numfiles"], '</a></td>', "\n";
        }

        if (!$row["comments"])
            echo '<td align="right">', $row["comments"], "</td>\n";
        else {
            echo '<td align="right"><a href="details.php?id=',$id,'&amp;page=0#startcomments">', $row["comments"], '</a></td>', "\n";
        }

        //For ADDED colone
        if ($elapsed == "0")
            $added = floor((time() - strtotime($row["added"])) / 60) . " " . $lang['browse_added_mins_ago'];
        elseif ($elapsed < 24) {
        	$added = $elapsed . " " . $lang['hours'];
        } else {
              if ( date("y",strtotime( $row["added"])) != date("y")) {
              	  $added = date("m-d Y",strtotime( $row["added"]));
              } else {
              	  $added = date("m-d",  strtotime( $row["added"]));
              }
        }

        print("<td align=center width=20><nobr>" . $added . "</nobr></td>\n");

        //Size colone
        print("<td align=center><nobr>" . mksize($row["size"]) . "</nobr></td>\n");

        $_s = "";
        if ($row["times_completed"] != 1) $_s = "s";

        $dhtPeersUrl = $url . '#dht_peers';

        $dht_peers_formatted = sprintf('<a href="%s"><font color="%s">%s</font></a>',
                                        $dhtPeersUrl, get_dht_peers_color($row["dht_peers"]), $row["dht_peers"]);

        print("<td align=center>" . ($row["dht_peers"] == -1 ? '<span title="Refresh in progress">...</span>' : $dht_peers_formatted) . "</td>\n");

        printf('<td align="right">%s</td>', $row['thanks']);

        // Team name
        $uploaded_by_str = '';

        $torrent_opt = $row["torrent_opt"];
        if (!isset($row["torrent_opt"]))
            $torrent_opt = q_singleval('SELECT torrent_opt FROM torrents WHERE id=:id',array('id'=>$id));

		if ( torrent_have_flag('anonim_unverified', $torrent_opt) || torrent_have_flag('anonim', $torrent_opt) )
			$uploaded_by_str = __('Anonim');
		else
			$uploaded_by_str = (isset($row["username"]) ? ("<a href=userdetails.php?id=" . $row["owner"] . ">" . esc_html($row["username"]) . "</a>") : "<i>unknown</i>");

		if ($row['teamName'])
        	$uploaded_by_str = '<a href="./team.php?name='.str_replace(' ','_',$row['teamName']).'">'.$row['teamInitials'].'</a> / ' . $uploaded_by_str;

        echo '<td align=center nowrap>',$uploaded_by_str,"</td>\n",
        '</tr>',"\n";
    }
    print("</table>\n");
    unset($records); //Cleaning
}

function torrenttable2(&$res, $variant = "index", $impersonal = false) {
	global $pic_base_url, $CURUSER, $lang;

?>
      <table border="1" cellspacing=0 cellpadding=5>
        <tr>
          <td class="colhead" align="center"><?=__('Tip')?></td>
          <td class="colhead" align="left"><?=__('Nume')?></td>
          <?php

	if ($variant == "mytorrents") {
		echo '<td class="colhead" align="center">'. __('Editează') .'</td>
			  <td class="colhead" align="center">'. __('Vizibil') .'</td>',"\n";
	}

?>
          <td class="colhead" align=right><?=__('Fişiere')?></td>
          <td class="colhead" align=right><?=__('Com.')?></td>
          <td class="colhead" align="center"><?=__('Adăugat')?></td>
          <td class="colhead" align="center">&nbsp;&nbsp;&nbsp;<?=__('Mărime')?>&nbsp;&nbsp;&nbsp;</td>
          <td class="colhead" align="center"><?=__('Descărcat')?></td>
          <td class="colhead" align=right><img src="pic/arrowup.gif"></td>
          <td class="colhead" align=right><img src="pic/arrowdown.gif"></td>
          <?php

    if ($variant == "index")
        print("<td class=\"colhead\" align=center>Upped&nbsp;by</td>\n");

    print("</tr>\n");

//

if (!is_array($res)) {
    if (isset($GLOBALS['browse_show_lastest'])) {
    	global $top_3;
    	$top_3_id = array();
    	foreach($top_3 as $top) {
    		$records[] = $top;
    		$top_3_id[] = $top['id'];
    	}
    	$top_3 = 3;
    }
	while ($record = mysql_fetch_array($res)) {
      $records[] = $record;
    }



} else $records =& $res;

    //while ($row = mysql_fetch_assoc($res)) {
    if (count($records) == 0) return;
    foreach ($records as $id=>$row) {
        $id = $row["id"];

        //Highlight top 3
        if (isset($top_3)) {
        	if ($top_3 > 0) {
        		echo '<tr style="background-color: #F2DCDC;">',"\n";
        		$top_3--;
        	} else {
        		//Skip top3, to no repeat
        		if (in_array($row['id'],$top_3_id)) {
        			next;
        		}
        	}
        }
        else print("<tr>\n");

        print("<td align=center style='padding: 0px'>");
        if (isset($row["cat_name"])) {
            print("<a href=\"browse.php?cat=" . $row["category"] . "\">");
            if (isset($row["cat_pic"]) && $row["cat_pic"] != "")
                printf('<img src="%s" height="%s" alt="%s"/>', $pic_base_url . "categs/" . $row["cat_pic"], getImgCategHeight($row["cat_pic"]), $row["cat_name"] );
            else
                print($row["cat_name"]);
            print("</a>");
        }
        else
            print("-");
        print("</td>\n");


        // Don't show nr of leechers and seeders on the copyrighted torrents
        if ($row['moder_status'] == 'copyright') {
            $row["leechers"] = 0;
            $row["seeders"] = 0;
        }

        $dispname = $row["name"];
        $moder_status = getTorrentStatusHtml($row,true) . ' ';
        if (strstr($moder_status,'Neverifica') !== false) $moder_status = '';
		echo '<td align=left>',$moder_status,'<a href="details.php?id=',$id,
			//(($variant == "mytorrents")?"&amp;returnto=" . urlencode($_SERVER["REQUEST_URI"]):''),
		'"',"><b>{$dispname}</b></a>\n";

         //For Wait&Added colone
        $elapsed = floor((time() - strtotime($row["added"])) / 3600);

        if ($variant == "mytorrents")
            print("<td align=\"center\"><a href=\"edit.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]) . "&amp;id=" . $row["id"] . "\">edit</a>\n");
            print("</td>\n");
        if ($variant == "mytorrents") {
            print("<td align=\"right\">");
            if ($row["visible"] == "no")
                print("<b>no</b>");
            else
                print("yes");
            print("</td>\n");
        }
        if ($row["numfiles"] == 1)
            print("<td align=\"right\">" . $row["numfiles"] . "</td>\n");
        else {
            if ($variant == "index")
                print("<td align=\"right\"><a href=\"details.php?id=$id&amp;filelist=1\">" . $row["numfiles"] . "</a></td>\n");
            else
                print("<td align=\"right\"><a href=\"details.php?id=$id&amp;filelist=1#filelist\">" . $row["numfiles"] . "</a></td>\n");
        }

        if (!$row["comments"])
            print("<td align=\"right\">" . $row["comments"] . "</td>\n");
        else {
            if ($variant == "index")
                print("<td align=\"right\"><a href=\"details.php?id=$id&amp;tocomm=1\">" . $row["comments"] . "</a></td>\n");
            else
                print("<td align=\"right\"><a href=\"details.php?id=$id&amp;page=0#startcomments\">" . $row["comments"] . "</a></td>\n");
        }


        if ($elapsed == "0") {
            $added = floor((time() - strtotime($row["added"])) / 60) . " " . $lang['browse_added_mins_ago'];
        } elseif ($elapsed < 24) {
            $added = $elapsed . " " . $lang['browse_added_hours_ago'];
        } else { //mai mult de 1 zi, 06-22
            $added = date("m-d",strtotime( $row["added"]));
            if ( date("y",strtotime( $row["added"])) != date("y")) {
                $added = date("m-d Y",strtotime( $row["added"]));
            } else {
                $added = date("m-d",  strtotime( $row["added"]) );
            }
        }

        print("<td align=center width=20><nobr>" . $added . "</nobr></td>\n");

        //Size colone
        print("<td align=center><nobr>" . mksize($row["size"]) . "</nobr></td>\n");

        $_s = "";
        if ($row["times_completed"] != 1)
          $_s = "s";
        print("<td align=center>" . number_format($row["times_completed"]) . "<br>time$_s</td>\n");

        if ($row["seeders"]) {
            if ($variant == "index")
            {
               if ($row["leechers"]) $ratio = $row["seeders"] / $row["leechers"]; else $ratio = 1;
                print("<td align=right><a href=details.php?id=$id&amp;toseeders=1><font color=" .
                  get_slr_color($ratio) . ">" . $row["seeders"] . "</font></a></td>\n");
            }
            else
                print("<td align=\"right\"><a class=\"" . linkcolor($row["seeders"]) . "\" href=\"details.php?id=$id&amp;dllist=1#seeders\">" .
                  $row["seeders"] . "</a></td>\n");
        }
        else
            print("<td align=\"right\"><span class=\"" . linkcolor($row["seeders"]) . "\">" . $row["seeders"] . "</span></td>\n");

        if ($row["leechers"]) {
            if ($variant == "index")
                print("<td align=right><a href=details.php?id=$id&amp;todlers=1>" .
                   number_format($row["leechers"]) . "</a></td>\n");
            else
                print("<td align=\"right\"><a class=\"" . linkcolor($row["leechers"]) . "\" href=\"details.php?id=$id&amp;dllist=1#leechers\">" .
                  $row["leechers"] . "</a></td>\n");
        }
        else
            print("<td align=\"right\">0</td>\n");

        if ($variant == "index")
            print("<td align=center>" . (isset($row["username"]) ? ("<a href=userdetails.php?id=" . $row["owner"] . ">" . esc_html($row["username"]) . "</a>") : "<i>(unknown)</i>") . "</td>\n");

        print("</tr>\n");
    }

    print("</table>\n");
    unset($records); //Cleaning
}





function torrenttableFull($res,&$data) {
	// Normalize all to array
	if ($res != '') {
		$data = array();
		while ($row=@mysql_fetch_assoc($res)) {
			$data[] = $row;
		}
	}

	global $pic_base_url, $CURUSER, $lang;

    foreach($data AS $torrent) {
        $id = $torrent["id"];

		$templ = '
		<table cellpadding="10" width="100%" class="bigger">
			<tr><td colspan="2" align="center">:name</td><tr>
			<tr>
				<td width="100" align="top">:img</td>
				<td style="line-height: 1.4;" valign="top" align=left>
					:desc
				</td>
			<tr>
		</table><br><br>';

		$name = $torrent["name"];

		$img = '';

		if ($torrent['image']) {
			$img = '<img width="100" src="'.$GLOBALS['torrent_img_dir_www'].'/'.$torrent['id'].'_'.$torrent['image'].'">';
		}

		$desc = prepare_descr_html($torrent['descr_html'],$torrent['category']);

		// Download link
		$desc = '<b>Download:</b> ' . '<a class="index" href="download.php?id='.$id.'">'.esc_html($torrent["filename"]).'</a><br><br>'.$desc;

		/**
			Generate
		*/
		$name = '<a href="./details.php?id='.$id.'">'.$name.'</a>';
		$img = '<a href="./details.php?id='.$id.'">'.$img.'</a>';

		echo stringInto($templ, array('name'=>$name, 'img'=>$img, 'desc'=>$desc) );
	}
}


/*
	@param1 format (like for sprintf)
	@param2-.. params
*/
function echoe() {
	$params = func_get_args();
	$format = array_shift($params);
	foreach($params AS $paramI=>$value) {
		$params[$paramI] = esc_html($value);
	}
	echo call_user_func_array('sprintf',array_merge(array($format),$params));
}

function sechoe() {
	$params = func_get_args();
	$format = array_shift($params);
	foreach($params AS $paramI=>$value) {
		$params[$paramI] = esc_html($value);
	}
	return call_user_func_array('sprintf',array_merge(array($format),$params));
}

	/*
		@param $name - input name
		@param $label - text for this element
		@param $value - fill the element with this value
		@param $select - [0] id, [1] label, [2] rows
						can serve for the type ENUM as values format array( array(value,label),.. )
		@prama $opts - additional element options, describe as associative array: array("attribute"=>"value")
	*/
	function form_element($name,$type,$label,$value='',$select='',$opts='') {
		if (is_array($opts)) $opt = opt_2_html($opts); //pastram parametrul origianl

		if (!empty($label)) echo '<tr>';
		switch($type) {
			case 'text':
				echoe('<td>%s:</td><td><input type="text" name="%s" value="%s"'.$opt.'></td>',$label, $name, $value);
				break;
			case 'select':
				if (empty($opt)) $opt = ' size="5"';
				if (!empty($label))	echoe('<td>%s:</td><td>', $label);
				if (isset($opts['multiple']) && $opts['multiple']==='multiple')
				{
					$name = $name.'[]'; //pentru a primi corect valorile
					$value = explode(',', $value);
				}

				echoe('<select name="%s"'.$opt.'>', $name);

				foreach($select[2] AS $row)
				{
					if(is_array($value))
						$sel = (in_array($row[$select[0]], $value))?' selected ':'';
					else
						$sel = ($value!=''&&$value==$row[$select[0]])?' selected ':'';

					echoe('<option value="%s"%s>%s</option>', $row[$select[0]],$sel,$row[$select[1]]);
				}
				echo '</select>';
				if (!empty($label))	echo '</td>';
				break;
			case 'submit':
				echoe('<td colspan="2" align="center"><input type="submit" value="%s"></td>',$label);
				break;
			case 'enum':
				if (!is_array($select)) throw new Exception('Bad enum arg, param is missing');
				echoe('<td>%s:</td><td>',$label, $name, $value);
				foreach($select AS $enum) {
					echoe('<input type="radio" name="%s" value="%s"%s>%s &nbsp;', $name, $enum[0], ($value!=''&&$value==$enum[0])?' checked ':'',$enum[1]);
				}
				echo '</td>';
				break;
			default:
				if (function_exists('form_element_adapter'.$type)) {
					call_user_func_array('form_element_adapter'.$type,array($name,$type,$label,$value,$select,$opt));
					break;
				}

				echoe ('Bad type %s', $type);
				die();
		}
		if (!empty($label)) echo '</tr>';
	}

/**
 * This function is for get ids from a select result
 * @var $sql - sql to execute, that must be a select
 * @var $col_name - colone to collect and return
 * @return string
 **/
function get_ids_for_in($sql,$col_name='id') {
	$rows = fetchAll($sql);
	$ids = array();
	foreach($rows AS $row) {
		$ids[] = $row[$col_name];
	}
	if (count($ids) == 0) $ids[] = '0';
	return join(',',$ids);
}

/**
 * This function will take an associative array, the key will become attribute name and it's value the value
 * Example: array('size'=>'5') becomes size="5"
 *
 * @param array $arr
 * @return string
 **/
function opt_2_html($arr) {
	$r = array();
	foreach($arr AS $key=>$value) {
		$r[] = sechoe('%s="%s"',$key,$value);
	}
	return join(' ', $r);
}

/**
* Return array of ids of torrents
*/
function get_sql_select_torrents($torrents_ids,$orderby) {
	if (is_array($torrents_ids)) $torrents_ids = join(',',$torrents_ids);
	return "SELECT torrents.id, torrents.category, torrents.leechers, torrents.seeders, torrents.name, torrents.times_completed, torrents.size, torrents.added, torrents.comments,torrents.numfiles,torrents.filename,torrents.owner,torrents.team,
	   			categories.name AS cat_name, categories.image AS cat_pic, users.username, teams.name AS teamName, teams.initials AS teamInitials, torrents.moder_status
	   			FROM torrents
	   			LEFT JOIN categories ON category = categories.id
	   			LEFT JOIN users ON torrents.owner = users.id
	   			LEFT JOIN teams ON (torrents.team > 0 AND torrents.team = teams.id)
	   			WHERE torrents.id IN ($torrents_ids) $orderby";
}


?>
