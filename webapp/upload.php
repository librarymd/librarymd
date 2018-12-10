<?php

require_once("include/bittorrent.php");

loggedinorreturn();

stdhead("Upload");

$power_user_only = get_config_variable('upload', 'power_user_only');

if (get_user_class() < UC_POWER_USER && $power_user_only) {
  stdmsg(__('Ne pare rău...'), $lang['upload_status_not_power_user']);
  stdfoot();
  exit;
}

// Check if this user is not banned for torrents upload
if ($CURUSER["user_opt"] & $conf_user_opt['torrentsUploadBan']) {
  stdmsg(__('Ne pare rău...'), $lang['upload_banned']);
  stdfoot();
  exit;
}
?>
<script>
var user_lang='<?=get_lang()?>';
var transl_torr_name='<?=__('Numele torrentului')?>';
var transl_do_it='<?=__('Încarcă')?>';
var transl_show_mand='<?=__('Evidenţiază câmpurile obligatorii necompletate')?>';
var conf_template_ver = 7;
</script>
<script type="text/javascript" src="js/upload/upload.js?v=6"></script>
<script type="text/javascript" src="js/upload/upload_edit.js?v=6"></script>
<div>
<form enctype="multipart/form-data" action="takeupload.php" method="post" id="noenter">
<input type="hidden" name="MAX_FILE_SIZE" value="<?=$max_torrent_size?>" />
<!--Err Torrent File-->
<div id="err_tnt_file" style="border-color:#DEDF00;border-style: dashed;width:675;text-align:center;font-size: 10pt;font-weight: bold;" class="hideit">
    <img src="pic/alert.gif" style="float:left;">
  <div style="margin-top:12px;"><?= $lang['upload_only_torrent']; ?></div>
</div>
<!--Err Img File-->
<div id="err_img_file" style="border-color:#DEDF00;border-style: dashed;width:675;text-align:left;font-size: 10pt;font-weight: bold;margin-top:5px;" class="hideit">
    <img src="pic/alert.gif" style="float:left;">
  <div style="margin-left:55px;margin-top:12px;"><?= $lang['upload_only_jpg_png']; ?></div>
</div>
<br>
<p>Trackerul nu are anunt dedicat.
Reteaua DHT va fi utilizata pentru afisarea nrul peerurilor.
Torrentul nu trebuie recopiat dupa reincarcare.</p>
<br/><br/>
<p>
Ideal torrentul care il incarcati trebuie copiat de pe alt site de torrent, fara a fi modificat.
<br/><br/>
1. Copiat fisierul torrent de pe alt site de torrente.<br/>
2. Incarcati fisierul torrent la noi.<br/>
3. Ideal in descriere trebuie sa indicati adresa siteului de torrente de unde ati copiat fisierul torrentul.<br/>
<br/>
Seedarea de catre uploader nu este necesara pentru ca el este deja seedat de catre peeurile existenti din reteaua DHT.<br/>
Deasemenea uploaderul are o anonimitate sporita.<br/>
</p>
<br><br>
<br>
<table id="main_upload_table" border="1" cellpadding="5" cellspacing="0" width="675">
<col style="width: 75px;" align="left"><col>
<tbody>
<tr id="tr_tnt_file"><td class="heading"><?=__('Fișierul torrent')?></td>
<td align="left" valign="top"><input name="file_torrent" size="58" onchange="trnt_file_validate(this);" type="file">
(<?=__('doar fișierul cu extensia .torrent), numele fişierului trebuie să conţină doar caractere simple.')?><br>

</td>
</tr>
<tr><td class="heading"><?=__('Tip')?></td>
<td align="left" valign="top">
<select name="type" onchange="changecat($j(this.options[this.selectedIndex]).attr('customCat'),this.selectedIndex,this);" id="type_select">
<option value="0">(<?=__('alege o categorie din listă')?>)</option>
<?php
$s = '';
$cats = genrelist();
foreach ($cats as $row) {
  if ($row["id"] == 6) continue;
  $s .= sprintf( '<option value="%d" customCat="%s">%s</option>'."\n",$row["id"],esc_html($row["name"]),esc_html(__($row["name"])) );
}
$s .= "</select>\n";
echo $s;
?>
</td></tr>
<tr id="tr_img_file">
<td class="heading"><?=__('Imagine Poster')?></td>
<td align="left" valign="top"><input name="file_image" size="58" onchange="img_file_validate(this);" type="file">
(<?=__('doar')?> .jpg,.png)<br>
<?=$GLOBALS['lang']['upload_type_of_image_allowed'] ?>
</td>
</tr>
<?php
  if ($CURUSER['team']) {
?>
<tr>
<td class="heading"><?=__('Echipă')?></td>
<td align="left" valign="top">
  <?=__('Acest torrent este un release a echipei')?> <input type="checkbox" name="teamRelease" value="1" checked>
</td>
</tr>
<?php } ?>

<tr>
<td class="heading"><?=__('Anonimat')?></td>
<td align="left" valign="top">
  <?=__('Acest torrent va deveni anonim (acțiune ireversibilă după verificarea torrentului de către moderator)')?> <input type="checkbox" name="anonim" value="1">
</td>
</tr>

</tbody></table>

<div id="place_for_category_name" align="left" style="padding:10px;width:675px;FONT-SIZE: 18px; FONT-FAMILY: Arial, Helvetica, sans-serif;font-weight:bold;"></div>
<div id="place_for_upload_table"></div>

</form>
</div>

<!-- Check if Type is not already selected (happen whan upload failed and user click the Back button) -->
<script type="text/javascript">
  var type_select = _ge_by_name('type');
  if (type_select.selectedIndex != 0 && _ge_by_name('name') == null) {
    changecat($j(type_select.options[type_select.selectedIndex]).attr('customCat'),type_select.selectedIndex);

  }
</script>

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

<div class="fixedsidebar" id="notepad">
<img id="_close_notepad" src="/pic/close.gif" align="right"><br>
<h2>Notepad</h2>
<textarea rows="17" id="notepad_textarea" style="margin-top:5px;"></textarea>
</div>
<style>#table_upload { line-height:15px; }</style>
<?php
stdfoot();
?>
