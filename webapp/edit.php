<?php

require_once("include/bittorrent.php");
require_once($INCLUDE . 'torrent_opt.php');

function get_movie_genres_html() {
	return file_get_contents($GLOBALS['SETTINGS_PATH'].'movie_genres_html');
}
//Escape Javascript string
function js_esc($str) {
	//Replace new lines char, with \n,
	//Replace '/' with '\/' because of html parsers, at </script> they think what it's the end.. They don't see js string..
	// ' -> \' and " -> \"
	return preg_replace("/\r?\n/", "\\n", str_replace('/','\/', addslashes($str)) );
}

if (!mkglobal("id"))
	die();

$id = 0 + $id;
if (!$id)
	die();

loggedinorreturn();

$res = q("SELECT torrents.name, torrents.owner, torrents.category, torrents.image, torrents.team, torrents_details.descr_ar, torrents.moder_status, torrents.torrent_opt
          FROM torrents
          LEFT JOIN torrents_details ON torrents.id = torrents_details.id
          WHERE torrents.id = $id");
$row = mysql_fetch_array($res);
if (!$row) die();
$torrent = $row;


if (!isset($CURUSER) || ($CURUSER["id"] != $row["owner"] && get_user_class() < UC_MODERATOR && get_user_class() != UC_SANITAR))
{
	barkk(__('Nu sunteți proprietarul acestui torrent'));
}

stdhead(__('Editarea torrentului') . ' "' . $row["name"] . '"');
?>
<script>
var user_lang='<?=get_lang()?>';
var transl_do_it='<?=__('Încarcă')?>';
var transl_show_mand='<?=__('Evidenţiază câmpurile obligatorii necompletate')?>';
var conf_template_ver = 7;
</script>
<script type="text/javascript" src="js/upload/edit.js?v=8"></script>
<script type="text/javascript" src="js/upload/upload_edit.js?v=6"></script>
<?php
echo '<div class="center"><h2 style="display:inline;">'.__('Editarea torrentului').':</h2> ' . $row["name"] . '<br><br>';
?>
<script>
if (browser.isOpera) {
  document.write('<span style="font-size: 10pt;color:red;">WARRING!</span> Din cauza browserului Opera, cimpurile de tip lista(select box) nu se pot selecta automat. <br>Folositi Firefox/IE daca va deranjeaza.<br><br>')
}
</script>

<?php

echo '<div><a href="./edit_categories.php?id=',$id,'">['.__("Editarea categoriilor").']</a>
</div><br/>';

	print("<form method=post action=takeedit.php enctype=multipart/form-data>\n");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
	if (isset($_GET["returnto"])) print("<input type=\"hidden\" name=\"returnto\" value=\"" . esc_html($_GET["returnto"]) . "\" />\n");

    $s = '<option value="0">(choose one)</option>';
	$cats = genrelist();
	foreach ($cats as $cat) {
    //if ($subrow["id"] == $row["category"])
    $s .= sprintf( '<option value="%d" %s customCat="%s">%s</option>'."\n",
                          $cat["id"], (($cat["id"] == $row["category"])?'selected="selected"':''),esc_html($cat["name"]), esc_html(__($cat["name"])) );
	}

	//Get tags, if set
	$res_tags = q("SELECT tagid, tag_name
	   FROM torrents_tags
	   LEFT JOIN torrents_tags_index ON torrents_tags.tagid=torrents_tags_index.id
	   WHERE torrents_tags.torrentid=$id");
	$tags = '';
	if (mysql_num_rows($res_tags) > 0) {
		while ($row_tag = mysql_fetch_assoc($res_tags)) {
			$tag_name = esc_html($row_tag['tag_name']);
			$tags .= $tag_name.',';
		}
		$tags = substr($tags,0,-1); //remove last comma
	}
	?>

<table class="mCenter" id="main_upload_table" border="1" cellpadding="5" cellspacing="0" width="675">
<col style="width: 75px;" align="left"><col>
<tbody>
<tr><td class="heading"><?=__('Tip')?></td>
<td align="left" valign="top">
<select name="type" onchange="changecat(this.options[this.selectedIndex].getAttribute('customCat'),this.selectedIndex);">
<?=$s?>
</select>
</td></tr>

<tr id="tr_img_file">
<td class="heading"><?=__('Imagine Poster')?></td>
<td align="left" valign="top"><input name="file_image" size="58" onchange="img_file_validate(this);" type="file">
(<?=__('doar')?> .jpg,.png)<br>
<?php
	if (strlen($row['image']) > 1) {?>
(<?=__('Dacă veţi încărca o altă imagine, cea veche va fi înlocuită cu cea nouă.')?>)<br>
<?=__('Şterge imaginea curentă')?> <input type="checkbox" name="delete_image" value="1">
<?php } ?>
</td>
</tr>

<?php
	// Only owners can see Team section
	$checked = '';
	if ($CURUSER["id"] == $row["owner"] && $CURUSER["team"]) {
		if ($row['team'] == $CURUSER["team"]) {
			$checked = ' checked';
		}
?>
<tr>
<td class="heading"><?=__('Echipă')?></td>
<td align="left" valign="top">
	<?=__('Acest torrent este un release a echipei')?> <input type="checkbox" name="teamRelease" value="1" <?php echo $checked;?>>
</td>
</tr>

<?php } ?>

<?php
	if ( $CURUSER["id"] == $row["owner"] ) {
		$anonim_checked = '';
		if ( torrent_have_flag('anonim', $row['torrent_opt']) || torrent_have_flag('anonim_unverified', $row['torrent_opt']))
			$anonim_checked = ' checked';

		$anonim_disabled = '';
		if ( torrent_have_flag( 'anonim', $row['torrent_opt'] ) )
			$anonim_disabled = ' disabled';
?>
<tr>
<td class="heading"><?=__('Anonim')?></td>
<td align="left" valign="top">
	<?=__('Acest torrent va deveni anonim (acțiune ireversibilă după verificarea torrentului)')?> <input type="checkbox" name="anonim" value="1"<?php echo $anonim_checked . $anonim_disabled;?>>
</td>
</tr>
<?php } ?>
</tbody></table>

<div id="place_for_category_name" align="left" style="padding:10px;width:580px;FONT-SIZE: 18px; FONT-FAMILY: Arial, Helvetica, sans-serif;font-weight:bold;"></div>
<div id="place_for_upload_table"></div>

</form>

<!--Sprm bank-->
<table id="dna_clone" class="hideit">
<tbody>
<tr id="tr_additional">
<td><?=__('Câmp suplimentar')?> <a style="cursor:pointer;" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);">[x]</td>
<td><input name="additional_name[]" size="15" type="text"> <input name="additional_descr[]" size="50" maxlength="80" type="text"></td>
</tr>
<tr id="tr_add_custom">
<td colspan="2"><a style="cursor:pointer;" id="_add_new_additional"><?=__('Adaugă câmp suplimentar')?></a></td>
</tr>
</tbody>
</table>
	<?php
	$descr = unserialize($row['descr_ar']);
	//JS data for input filler
	$js = "torrent_category = {$row['category']};\n";
	$js.= "filed_inputs = new Array();\n";

	foreach($descr as $n=>$v) {
		if ($n == 'separator') continue;
		if ($n == 'additional') {
			foreach($v as $a) {
				$a[0]=js_esc($a[0]);
				$a[1]=js_esc($a[1]);
				$js.="filed_inputs.push(  new Array('additional','{$a[0]}','{$a[1]}') );\n";
			}
			continue;
		}
		$v=js_esc($v);
		$js.="filed_inputs.push(  new Array('$n','$v') );\n";
	}
?>
<script type="text/javascript">
	<?=$js?>
	automatic_change_category(torrent_category);
</script>
<?php


	//tr("Description", "<textarea name=\"descr\" rows=\"10\" cols=\"80\">" . esc_html($row["ori_descr"]) . "</textarea><br>(HTML is not allowed. <a href=tags.php>Click here</a> for information on available tags.)", 1);



	/*$s = "<select name=\"type\">\n";

	$cats = genrelist();
	foreach ($cats as $subrow) {
		$s .= "<option value=\"" . $subrow["id"] . "\"";
		if ($subrow["id"] == $row["category"])
			$s .= " selected=\"selected\"";
		$s .= ">" . esc_html($subrow["name"]) . "</option>\n";
	}

	$s .= "</select>\n";
	tr("Type", $s, 1);*/
	//tr("Visible", "<input type=\"checkbox\" name=\"visible\"" . (($row["visible"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" /> Visible on main page<br /><table border=0 cellspacing=0 cellpadding=0 width=420><tr><td class=embedded>Note that the torrent will automatically become visible when there's a seeder, and will become automatically invisible (dead) when there has been no seeder for a while. Use this switch to speed the process up manually. Also note that invisible (dead) torrents can still be viewed or searched for, it's just not the default.</td></tr></table>", 1);

	//if ($CURUSER["admin"] == "yes") tr("Banned", "<input type=\"checkbox\" name=\"banned\"" . (($row["banned"] == "yes") ? " checked=\"checked\"" : "" ) . " value=\"1\" /> Banned", 1);

	//print("<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value='Edit it!' style='height: 25px; width: 100px'> <input type=reset value='Revert changes' style='height: 25px; width: 100px'></td></tr>\n");
	//print("</table>\n");
	print("</form>\n");
	print("<p>\n");
	print("<form method=\"post\" action=\"delete.php\">\n");
  print("<table class='mCenter' border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n");
  print("<tr><td class=embedded style='background-color: #F5F4EA;padding-bottom: 5px' colspan=\"2\"><b>Delete torrent.</b> Reason:</td></tr>");
  print("<td><input name=\"reasontype\" type=\"radio\" value=\"1\">&nbsp;Dead </td><td> 0 seeders, 0 leechers = 0 peers total</td></tr>\n");
  print("<tr><td><input name=\"reasontype\" type=\"radio\" value=\"4\">&nbsp;TB rules</td><td><input type=\"text\" size=\"40\" name=\"reason[]\">(req)</td></tr>");
  print("<tr><td><input name=\"reasontype\" type=\"radio\" value=\"5\" checked>&nbsp;Other:</td><td><input type=\"text\" size=\"40\" name=\"reason[]\">(req)</td></tr>\n");
	print("<input type=\"hidden\" name=\"id\" value=\"$id\">\n");
	if (isset($_GET["returnto"]))
		print("<input type=\"hidden\" name=\"returnto\" value=\"" . esc_html($_GET["returnto"]) . "\" />\n");
  print("<td colspan=\"2\" align=\"center\"><input type=submit value='Delete it!' style='height: 25px'></td></tr>\n");
  print("</table>");
	print("</form>\n");
	print("</p></div>\n");

stdfoot();
?>
