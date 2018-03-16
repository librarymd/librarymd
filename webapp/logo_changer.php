<?php
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);
require "include/bittorrent.php";
loggedinorreturn();

if (get_user_class() < UC_ADMINISTRATOR) {
	stderr("Sorry", "Access denied!");
	exit();
}
/* first run: INSERT INTO avps VALUES ("logo", "logo.png|280||||") */

function logoCacheExpire() {
	mem2_force_delete('site_logo');
}

$mappath = $GLOBALS['WWW_ROOT'] . 'pic/logo/';

function file_upload($upfile,$sufix='ro') {
        global $mappath;
        $tmpname = $upfile['tmp_name'];
		if (!is_uploaded_file($tmpname)) { barkk('ERROR! Can\'t get the uploaded image, please reupload.'); }
		if (!filesize($tmpname)) { barkk('ERROR! Uploaded image is empty.'); }
		list($width, $height, $type, $attr) = getimagesize($tmpname);
		if ($type != 1 && $type != 2 && $type != 3) { barkk('ERROR! Only a jpg or png or gif image are allowed.'); }
		if ($type == 1) $ext = 'gif';
		elseif ($type == 2) $ext = 'jpg';
		elseif ($type == 3) $ext = 'png';
		if ($height != 76 && $height != 152) barkk('Logotipul trebuie sa aiba exact 76 sau 152 pixeli in inaltime!');

		$logo_name = trim($_POST['logo_name']);
		if (empty($logo_name)) barkk('Name of the image required.');
		if (!preg_match('/^([a-zA-Z0-9_])*$/i', $logo_name)) barkk('numele fisierului trebuie sa alcatuit din a-z, A-Z, 0-9, _'); //numele fisierului tre sa fie alfanumeric... cu o mica exceptie... :]

		$logo_name = basename($logo_name) . '_' . $sufix . '.' . $ext;
		$new_path = $mappath . $logo_name;
		if (file_exists($new_path)) {
			barkk('Logo cu asa nume exista deja');
		}
		if (move_uploaded_file($upfile['tmp_name'], $new_path)) return array($logo_name,$width);
		else echo('<b>ceva nu a mers</b>');
}

stdhead('Logo changer');
if (isset($_POST['submit'])) {
	if (isset($_POST['predef_logo']) && $_POST['predef_logo'] == 1) {
		$logo_q = 'logo.png|logo.png|280||||||';
		Q('UPDATE avps SET value=:logo_q WHERE arg="logo"', array('logo_q'=>$logo_q) );
		logoCacheExpire();
		echo('<b>Logoul predefinit a fost pus</b>');
		write_admins_log('Logo schimbat in cel predefinit de către '. $CURUSER['username']);
		stdfoot();
		exit();
	} else {


        list($logo_name_ro,$logo_width_ro) = file_upload($_FILES['logoro'],'ro');
        list($logo_name_ru,$logo_width_ru) = file_upload($_FILES['logoru'],'ru');

		$logo_title_ro = trim($_POST['logo_title_ro']);
		$logo_title_ro = str_replace('|', '&#124;', $logo_title_ro);

		$logo_title_ru = trim($_POST['logo_title_ru']);
		$logo_title_ru = str_replace('|', '&#124;', $logo_title_ru);

		/* prelucrarea usemap ;D */
		if (isset($_POST['logo_map'])){
      $_POST['logo_map_title'] = '';
			if(empty($_POST['logo_map_link']) || empty($_POST['logo_map_link_ru'])) barkk('Campuri obligatorii <b>Map Title</b> si <b>Map Link</b>!');
			$usemap = 'usemap="#logo_map"';

			$map_title = $_POST['logo_map_title'];
			$map_title = str_replace('|', '&#124;', $map_title);

			$map_link = $_POST['logo_map_link'];
			$map_link = str_replace('|', '&#124;', $map_link);

			$map_link_ru = $_POST['logo_map_link_ru'];
			$map_link_ru = str_replace('|', '&#124;', $map_link_ru);
		}
		//structura: logo.png|width|logo_title|logo_map_title|usemap|map_link
		$logo_q = $logo_name_ro . '|' . $logo_name_ru . '|' . $logo_width_ro . '|' . $logo_width_ru . '|' . $logo_title_ro . '|' . $logo_title_ru . '|' . $map_title . '|' . $usemap . '|' . $map_link . '|' . $map_link_ru;
		Q('UPDATE avps SET value=:logo_q WHERE arg="logo"', array('logo_q'=>$logo_q) );
		logoCacheExpire();

		$log_message = 'Logo schimbat de către '. $CURUSER['username'] . ' în ' . ' http:// ... /pic/logo/' . $logo_name_ro;
		write_admins_log($log_message);
		stdfoot();
		exit();
	}
}
?>
<script type="text/javascript">
$j(document).ready(function(){
	if (_ge_by_name('logo_map').checked == true) $j("#panel").slideToggle("slow");

    $j("#logo_map").click(function(){
        $j("#panel").slideToggle("slow");
    });

    $j("#default_logo").click(function(){
        $j("#chlogo").slideUp("slow");
        $j("#default").slideDown("slow");
        _ge_by_name('predef_logo').value = '1';
    });
});
</script>
<h1>Modifica logoul curent</h1>
(<span id="default_logo" class="lnk">logo predefinit</span>)
<br><br>
<form action="logo_changer.php" method="POST" enctype="multipart/form-data">
<div id="default" style="display: none">
<input type="hidden" name="predef_logo">
	<table width="350px" cellspacing="0" cellpadding="5">
		<tr>
			<td><a href="/"><img src="/pic/logo/logo.png" alt="Logo" height="76" width="280" border="0"></a></td>
		</tr>
	</table>
</div>
<div id="chlogo">
	<table width="350px" cellspacing="0" cellpadding="5">
	<tr>
		<td>Logo ro*:</td>
		<td><input type="file" name="logoro"></td>
	</tr>
	<tr>
		<td>Logo ru*:</td>
		<td><input type="file" name="logoru"></td>
	</tr>

	<tr>
		<td>File Name*:</td>
		<td><input type="text" name="logo_name" size="40"></td>
	</tr>

	<tr>
		<td>Logo Title ro:</td>
		<td><input type="text" name="logo_title_ro" size="40"></td>
	</tr>

	<tr>
		<td>Logo Title ru:</td>
		<td><input type="text" name="logo_title_ru" size="40"></td>
	</tr>

	<tr>
		<td>Use Map:</td>
		<td><input type="checkbox" name="logo_map" id="logo_map"></td>
	</tr>
	</table>
	<div id="panel" style="display: none"><br>
		<table width="350px" cellspacing="0" cellpadding="5">

		<tr>
			<td>Map Link ro*:</td>
			<td><input type="text" name="logo_map_link" size="40"></td>
		</tr>

		<tr>
			<td>Map Link ru*:</td>
			<td><input type="text" name="logo_map_link_ru" size="40"></td>
		</tr>
		</table>
	</div>
</div>
<br>
<table width="350px" cellspacing="0" cellpadding="5">
	<tr>
		<td style="text-align: center;"><input type="submit" name="submit" value="Do it!"></td>
	</tr>
</table>
</form>
<?php stdfoot(); ?>