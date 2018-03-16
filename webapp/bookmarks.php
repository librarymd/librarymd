<?php
require_once("include/bittorrent.php");
require_once($INCLUDE . 'torrent_opt.php');

loggedinorreturn();

function bark($msg) {
	stdhead();
   	stdmsg('Eroare', $msg);
	stdfoot();
	exit;
}
function successfully($msg) {
	stdhead();
   	stdmsg('Succes', $msg);
	stdfoot();
	exit;
}

//Handle Post/Get

allow_only_local_referer_domain();

if (isset($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
	if ($action == 'add') { //New torrent to the bookmarks
		if (!isset($_REQUEST['torrentid']) || !is_numeric($_REQUEST['torrentid']) ) {
			bark ("Faild ... No torrent selected");
		}

		$torrent_id = $_REQUEST['torrentid'] + 0;

		if (q_singleval("SELECT id FROM bookmarks WHERE userid=$CURUSER[id] AND torrentid = $torrent_id") > 0) {
			bark("$lang[bookmarks_already_bookmarked] (<a href='detail.php?id=$torrent_id'>$torrent_id</a>)");
		}

		q("INSERT INTO bookmarks (userid, torrentid) VALUES ($CURUSER[id], $torrent_id)");

		header('Location: ./bookmarks.php?new=1');
	}
	if ($action == 'del') { //Delete torrent from the bookmarks
		if (!isset($_REQUEST['torrentid']) && !is_numeric($_REQUEST['torrentid']) ) bark ("Nothing selected");

		$del_torrentid = (int) $_REQUEST['torrentid'];

		$del_bookmarkid = q_singleval("SELECT id FROM bookmarks WHERE torrentid = $del_torrentid AND userid = $CURUSER[id]");

		if ($del_bookmarkid > 0) {
			q("DELETE FROM bookmarks WHERE id = $del_bookmarkid");

			if (isset($_POST['ajax'])) return 1;
			if (strpos($_SERVER['HTTP_REFERER'],'bookmarks.php') === TRUE) $referer = './bookmarks.php';
			else $referer = './bookmarks.php';
			header("Refresh: 0; url=" . $referer );
			exit();
		} else {
			bark($lang['bookmarks_del_not_found']);
		}
	}
	bark('Bad action');
}

stdhead($lang['bookmarks_title']);
print ("<h1>$lang[bookmarks_label]</h1>");

if (isset($_GET["new"])) echo "<h2>$lang[bookmarks_succesfully_bookmarked] !</h2>\n";

$count = q_singleval("SELECT COUNT(id) FROM bookmarks WHERE userid = $CURUSER[id]");

if ($count == 0) {
	echo "<h2>$lang[bookmarks_no_bookmarks]</h2>";
	stdfoot();
	exit();
}

list($pagertop, $pagerbottom, $limit) = pager(25, $count, "bookmarks.php?");

$res = q("SELECT bookmarks.id as bookmarkid, users.username,users.id as owner, torrents.id, torrents.name,
				torrents.type, torrents.comments, torrents.leechers, torrents.seeders,
				ROUND(torrents.ratingsum / torrents.numratings) AS rating, categories.name AS cat_name,
				categories.image AS cat_pic, torrents.save_as, torrents.numfiles, torrents.added, torrents.filename, torrents.size,
				torrents.views, torrents.visible, torrents.hits, torrents.times_completed, torrents.category,
				torrents.moder_status, torrents.team, teams.name AS teamName, teams.initials AS teamInitials, torrents.torrent_opt
         FROM bookmarks
    	 RIGHT JOIN torrents ON bookmarks.torrentid = torrents.id
		 LEFT JOIN users ON torrents.owner = users.id
		 LEFT JOIN categories ON torrents.category = categories.id
		 LEFT JOIN teams ON (torrents.team > 0 AND torrents.team = teams.id)
         WHERE bookmarks.userid = $CURUSER[id] ORDER BY torrents.id DESC $limit");

print($pagertop);
torrenttable($res, "index", TRUE);
print($pagerbottom);

stdfoot();

function torrenttable($res, $variant = "index") {
	global $CURUSER, $lang;

	$pic_base_url = "pic/categs/";
	$wait = 0;

?>
      <table border="1" cellspacing=0 cellpadding=5 width="100%" id="bookmark_table">
        <tr>
          <td class="colhead" align="center" width="32"><?=__("Tip")?></td>
          <td class="colhead" align="left"><?=__("Denumire")?></td>
          <td class="colhead" align="right" width="20"><?=__("Fişiere")?></td>
          <td class="colhead" align="right" width="20"><?=__("Com.")?></td>
          <td class="colhead" align="center" width="20"><?=__("Adăugat")?></td>
          <td class="colhead" align="center" width="55">&nbsp;&nbsp;&nbsp;<?=__("Mărime")?>&nbsp;&nbsp;&nbsp;</td>
          <td class="colhead" align="center" width="60"><?=__("Descărcat")?></td>
          <td class="colhead" align="right" width="9"><img src="pic/arrowup.gif"></td>
          <td class="colhead" align="right" width="9"><img src="pic/arrowdown.gif"></td>
          <td class="colhead" align="center" width="80"><?=__("Încărcat de")?></td>
          <td class="colhead" align="right" width="14"><?=__("Şterge")?></td>
        </tr>
          <?php
    while ($row = mysql_fetch_assoc($res)) {
        $id = $row["id"];


        print("<tr>\n");

        print("<td align=center style='padding: 0px'>");
        if (isset($row["cat_name"])) {
            if (isset($row["cat_pic"]) && $row["cat_pic"] != "")
                print("<img src=\"$pic_base_url" . $row["cat_pic"] . "\" alt=\"" . $row["cat_name"] . "\" />");
            else
                print($row["cat_name"]);
        }
        else
            print("-");
        print("</td>\n");

        $dispname = $row["name"];
        $moder_status = getTorrentStatusHtml($row,true) . ' ';
        if (strstr($moder_status,'Neverifica') !== false) $moder_status = '';
        echo '<td align=left>'.$moder_status.'<a href="details.php?id=',$id,'"', ((is_freeleeche($row))?' style="color:#707607"':''),"><b>$dispname</b></a>\n";

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

        if ($row["type"] == "single")
            print("<td align=\"right\">" . $row["numfiles"] . "</td>\n");
        else {
            if ($variant == "index")
                print("<td align=\"right\"><b><a href=\"details.php?id=$id&amp;&amp;filelist=1\">" . $row["numfiles"] . "</a></b></td>\n");
            else
                print("<td align=\"right\"><b><a href=\"details.php?id=$id&amp;filelist=1#filelist\">" . $row["numfiles"] . "</a></b></td>\n");
        }

        if (!$row["comments"])
            print("<td align=\"right\">" . $row["comments"] . "</td>\n");
        else {
            if ($variant == "index")
                print("<td align=\"right\"><b><a href=\"details.php?id=$id&amp;&amp;tocomm=1\">" . $row["comments"] . "</a></b></td>\n");
            else
                print("<td align=\"right\"><b><a href=\"details.php?id=$id&amp;page=0#startcomments\">" . $row["comments"] . "</a></b></td>\n");
        }

        //For ADDED colone
        if ($elapsed == "0") $added = floor((time() - strtotime($row["added"])) / 60) . " " . $lang['browse_added_mins_ago'];
        elseif ($elapsed < 24) {
        	$added = $elapsed . " " . $lang['browse_added_hours_ago'];
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
        if ($row["times_completed"] != 1)
          $_s = "s";
        print("<td align=center>" . number_format($row["times_completed"]) . "<br>time$_s</td>\n");

        if ($row["seeders"]) {
            if ($variant == "index")
            {
               if ($row["leechers"]) $ratio = $row["seeders"] / $row["leechers"]; else $ratio = 1;
                print("<td align=right><b><a href=details.php?id=$id&amp;&amp;toseeders=1><font color=" .
                  get_slr_color($ratio) . ">" . $row["seeders"] . "</font></a></b></td>\n");
            }
            else
                print("<td align=\"right\"><b><a class=\"" . linkcolor($row["seeders"]) . "\" href=\"details.php?id=$id&amp;dllist=1#seeders\">" .
                  $row["seeders"] . "</a></b></td>\n");
        }
        else
            print("<td align=\"right\"><span class=\"" . linkcolor($row["seeders"]) . "\">" . $row["seeders"] . "</span></td>\n");

        if ($row["leechers"]) {
            if ($variant == "index")
                print("<td align=right><b><a href=details.php?id=$id&amp;&amp;todlers=1>" .
                   number_format($row["leechers"]) . "</a></b></td>\n");
            else
                print("<td align=\"right\"><b><a class=\"" . linkcolor($row["leechers"]) . "\" href=\"details.php?id=$id&amp;dllist=1#leechers\">" .
                  $row["leechers"] . "</a></b></td>\n");
        }
        else
            print("<td align=\"right\">0</td>\n");

        $uploaded_by_str = '';

        $torrent_opt = $row["torrent_opt"];
        if (!isset($row["torrent_opt"]))
            $torrent_opt = q_singleval('SELECT torrent_opt FROM torrents WHERE id=:id',array('id'=>$id));

		if ( torrent_have_flag('anonim', $torrent_opt) || torrent_have_flag('anonim_unverified', $torrent_opt) )
				$uploaded_by_str = '<i>'. __('Anonim') .'</i>';
		else
			$uploaded_by_str = (isset($row["username"]) ? ("<a href=userdetails.php?id=" . $row["owner"] . "><b>" . esc_html($row["username"]) . "</b></a>") : "<i>unknown</i>");

		if ($row['teamName'])
        	$uploaded_by_str = '<a href="./team.php?name='.str_replace(' ','_',$row['teamName']).'"><b>'.$row['teamInitials'].'</b></a> / ' . $uploaded_by_str;


        print("<td align=center nowrap>". $uploaded_by_str ."</td>\n");

		//Delete icon
		echo '<td align="center"><a href="bookmarks.php?action=del&amp;torrentid=',$id,'"><img src="./pic/close_x.gif" class="delete_bookmark" id="d',$id,'"></a></td>';

        print("</tr>\n");
    }

    print("</table>\n");
    echo '<script type="text/javascript" src="./js/bookmarks.js"></script>';
}

?>
