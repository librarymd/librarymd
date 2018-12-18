<?php

require_once("include/bittorrent.php");

loggedinorreturn();

stdhead($CURUSER["username"] . "'s private page");

if (isset($_GET['edited'])) {
	print("<h1>{$lang['profile_updated']}</h1>\n");
	if (isset($_GET['mailsent']))
		print("<h2>{$lang['profile_succ_confirm_email']}</h2>\n");
}

if (isset($_GET['password_changed'])) {
	print('<h2 class="block_get_attention_green">
		Parola a fost schimbată cu succes, vă mulțumim !<br/><br/>
		Un email v-a fost trimis în semn de confirmare, care îl puteți ignora.
</h2>');
}

if (isset($_GET['weak_pass'])) {
	echo '<h2 class="block_get_attention_orange">Aveți o parolă foarte slabă, oricine poate usor gasi parola la contul dvs., vă rugăm insistent <a href="/my.php#change_password">s-o schimbați</a> ca să evitați verificarile suplimentare.</h2>';
}

if ($CURUSER['email_is_invalid'] == 'yes') {
	echo '<h2 class="block_get_attention_orange">Emailul dvs. nu mai este valid. Ca să puteți avea oricand acces la cont, vă rugăm <a href="/my.php#email_need_change">să-l schimbați</a>.</h2>';
}

elseif (isset($_GET['emailch']))
	print("<h1>{$lang['profile_succ_email_changed']}</h1>\n");
else
	print("<h1>{$lang['profile_head']}</h1>\n");

?>
<form enctype="multipart/form-data" method="post" action="takeprofedit.php">
<table class="mCenter" border="1" cellspacing=0 cellpadding="5" width="880">
<colgroup><col width="150"><col></colgroup>
<?php

// For languages, hard writed, oh..ohh..I am ashamed ;) How lame, but how optimised ;o)

$languages = "<option value=0" . ($CURUSER["language"] == 0?" selected" : "") . ">-</option>\n
	         <option value=1" . ($CURUSER["language"] == 1?" selected" : "") . ">Română</option>\n
	         <option value=2" . ($CURUSER["language"] == 2?" selected" : "") . ">Русский</option>\n
";



// For regions
$regions = '
<option value=0>-</option>
<option value=10>Chişinău</option>
<option value=1>&nbsp;&nbsp;Botanica</option>
<option value=2>&nbsp;&nbsp;Buiucani</option>
<option value=3>&nbsp;&nbsp;Centru</option>
<option value=4>&nbsp;&nbsp;Ciocana</option>
<option value=6>&nbsp;&nbsp;Poşta Veche</option>
<option value=5>&nbsp;&nbsp;Rîşcani</option>
<option value=7>&nbsp;&nbsp;Telecentru</option>

<option value=11>Municipiul Chişinău</option>
<option value=12>&nbsp;&nbsp;Codru</option>
<option value=13>&nbsp;&nbsp;Cricova</option>
<option value=14>&nbsp;&nbsp;Durleşti</option>
<option value=15>&nbsp;&nbsp;Sîngera</option>
<option value=16>&nbsp;&nbsp;Vadul lui Vodă</option>
<option value=17>&nbsp;&nbsp;Vatra</option>
<option value=20>&nbsp;&nbsp;Băcioi</option>
<option value=21>&nbsp;&nbsp;Bubuieci</option>
<option value=22>&nbsp;&nbsp;Budeşti</option>
<option value=23>&nbsp;&nbsp;Ciorescu</option>
<option value=24>&nbsp;&nbsp;Coloniţa</option>
<option value=25>&nbsp;&nbsp;Condriţa</option>
<option value=26>&nbsp;&nbsp;Dumbrava</option>
<option value=27>&nbsp;&nbsp;Cruzeşti</option>
<option value=28>&nbsp;&nbsp;Ghidighici</option>
<option value=29>&nbsp;&nbsp;Grătieşti</option>
<option value=30>&nbsp;&nbsp;Stăuceni</option>
<option value=31>&nbsp;&nbsp;Tohatin</option>
<option value=32>&nbsp;&nbsp;Truşeni</option>

<option value=40>Municipiul Bălţi</option>
<option value=50>Municipiul Tighina</option>
<option value=60>Municipiul Comrat</option>
<option value=70>Municipiul Tiraspol</option>
<option value=80>Raionul Anenii Noi</option>
<option value=90>R. Basarabeasca</option>
<option value=100>R. Briceni</option>
<option value=110>R. Cahul</option>
<option value=120>R. Cantemir</option>
<option value=130>R. Călăraşi</option>
<option value=140>R. Căuşeni</option>
<option value=150>R. Cimişlia</option>
<option value=160>R. Criuleni</option>
<option value=170>R. Donduşeni</option>
<option value=180>R. Drochia</option>
<option value=190>R. Dubăsari</option>
<option value=200>R. Edineţ</option>
<option value=210>R. Faleşti</option>
<option value=220>R. Floreşti</option>
<option value=230>R. Glodeni</option>
<option value=240>R. Hînceşti</option>
<option value=250>R. Ialoveni</option>
<option value=260>R. Leova</option>
<option value=270>R. Nisporeni</option>
<option value=280>R. Ocniţa</option>
<option value=290>R. Orhei</option>
<option value=300>R. Rezina</option>
<option value=310>R. Rîşcani</option>
<option value=320>R. Sîngerei</option>
<option value=330>R. Soroca</option>
<option value=340>R. Străşeni</option>
<option value=350>R. Şoldăneşti</option>
<option value=360>R. Ştefan Vodă</option>
<option value=370>R. Taraclia</option>
<option value=380>R. Teleneşti</option>
<option value=390>R. Ungheni</option>
<option value=400>R. Vulcăneşti</option>
';

$regions = str_replace('<option value='.$CURUSER['region'].'>', '<option value='.$CURUSER['region'] . ' selected>', $regions);

//$ct_r = q("SELECT id,name FROM regions ORDER BY name") or die;

/*while ($ct_a = mysql_fetch_array($ct_r))
  $regions .= "<option value=$ct_a[id]" . ($CURUSER['region'] == $ct_a['id'] ? " selected" : "") . ">$ct_a[name]</option>\n";*/

function format_tz($a)
{
	$h = floor($a);
	$m = ($a - floor($a)) * 60;
	return ($a >= 0?"+":"-") . (strlen(abs($h)) > 1?"":"0") . abs($h) .
		":" . ($m==0?"00":$m);
}

tr($lang['profile_accept_pms'],
"<input type=radio name=acceptpms" . ($CURUSER["acceptpms"] == "yes" ? " checked" : "") . " value=yes>{$lang['profile_accept_pms_all']}
<input type=radio name=acceptpms" .  ($CURUSER["acceptpms"] == "friends" ? " checked" : "") . " value=friends>{$lang['profile_accept_pms_friends']}
<input type=radio name=acceptpms" .  ($CURUSER["acceptpms"] == "no" ? " checked" : "") . " value=no>{$lang['profile_accept_pms_staff']}",1);

tr($lang['profile_email_notify'], "<input type=checkbox name=pmnotif" . (strpos($CURUSER['notifs'], "[pm]") !== false ? " checked" : "") . " value=yes> {$lang['profile_email_notify_txt']}<br>\n", 1);

// Invisibility are accesible only to >=VIP
if (get_user_class() == UC_SYSOP) {
	tr($lang['profile_invisible'], "<input type=checkbox name=invisible" . ($CURUSER['invisible'] === 'yes' ? " checked" : "") . " value=yes> {$lang['profile_invisible_txt']}<br>\n", 1);
}

//$categories = "{$lang['profile_category_show_none']}<br>\n";
if (strpos($CURUSER['notifs'],'[cat') === FALSE) $show_all = 1;
else $show_all = 0;


$r = q("SELECT id,name FROM categories ORDER BY name");
if (mysql_num_rows($r) > 0)
{
	$categories = '<table id="categs">
	   <tr><td colspan=2 class=bottom align="center"><input name="catall" value="yes" type="checkbox"'.(($show_all==1)?'checked':'').'> '.$lang['profile_category_show_all']."</td></tr>
	   <tr>\n";
	$i = 0;
	while ($a = mysql_fetch_assoc($r))
	{
	  $categories .=  ($i && $i % 2 == 0) ? "</tr><tr>" : "";
	  if ($show_all==1) $categories .= "<td class=bottom style='padding-right: 5px'><input name=cat$a[id] type=\"checkbox\" checked disabled value='yes'>&nbsp;" . esc_html($a["name"]) . "</td>\n";
	  else $categories .= "<td class=bottom style='padding-right: 5px'><input name=cat$a[id] type=\"checkbox\" " . (strpos($CURUSER['notifs'], "[cat$a[id]]") !== false ? " checked" : "") . " value='yes'>&nbsp;" . esc_html($a["name"]) . "</td>\n";
	  ++$i;
	}
	$categories .= "</tr></table>\n";
}
tr("<a name=\"defaut_browse\"></a>{$lang['profile_category_show']}",$categories,1, "my_category_show");
tr($lang['profile_language'], "<select name=language>\n$languages\n</select>",1);
tr($lang['profile_region'], "<select name=region>\n$regions\n</select>",1);


$genders = "<option value=0" . ($CURUSER["gender"] == 'none'?" selected" : "") . " >-</option>
		   <option value=1 " . ($CURUSER["gender"] == 'masc'?" selected" : "") . ">{$lang['profile_gender_m']}</option>
		   <option value=2" . ($CURUSER["gender"] == 'fem'?" selected" : "") . ">{$lang['profile_gender_f']}</option>";
tr($lang['profile_gender'], "<select name=gender>\n$genders\n</select>",1);

//#####################
//Avatar Section
//#####################
if ($CURUSER["avatar"] == 'yes') {

$avatar = '<div style="margin-left:3px;text-align:left;">'.$lang['profile_avatar_current'].'<br><img src="' . avatarWww($CURUSER["id"],$CURUSER["avatar_version"]).'" style="margin:4px;float:left;">
  '.$lang['profile_avatar_mean'].'
  <br><br><input name="dltavatar" value="yes" type="checkbox"> '.$lang['profile_avatar_delete'].'
  </div></div>
  <div style="clear:both;"></div><br>';
} else { $avatar = $lang['profile_avatar_mean'].'<br><br>'; }
$avatar .= ($CURUSER["warned"] == 'yes')?'<b>'.__('Aveți "warning"(preîntâmpinare), nu puteți schimba avatarul.').'</b><br><br>':

 $lang['profile_avatar_upload'].'<br>
  <input type="file" name="upload_avatar" size="50" style="margin-right:5px;"><br>
  '.$lang['profile_avatar_spec'].'<br>
  '.$lang['profile_avatar_rule'];

tr($lang['profile_avatar'], $avatar, 1);

tr($lang['profile_torrents_per_page'], "<input type=text size=10 name=torrentsperpage value=$CURUSER[torrentsperpage]> (0={$lang['profile_default_settings']})",1, "torrents_per_page");
tr($lang['profile_topics_per_page'], "<input type=text size=10 name=topicsperpage value=$CURUSER[topicsperpage]> (0={$lang['profile_default_settings']})",1);
tr($lang['profile_posts_per_page'], "<input type=text size=10 name=postsperpage value=$CURUSER[postsperpage]> (0={$lang['profile_default_settings']})",1);
tr($lang['profile_show_avatars'], "<input type=checkbox name=avatars" . ($CURUSER["avatars"] == "yes" ? " checked" : "") . "> (". __('Utilizatorii cu conexiunea slabă pot dezactiva această funcţie') .")",1);
if ( isCTenabled() )
{
	tr($lang['profile_customtitle'], "<label><input type='checkbox' id='changeCT' name='changeCT'> ". __('changeCT') ."</label><br /><input id=title type=text size=60 name=title value=\"" . ( ($CURUSER['title']) ) . "\"> <span><span id=\"TitleLen\"></span> ". __('caractere') ."</span>", 1);
}
tr($lang['profile_info'], "<textarea name=info cols=50 rows=8>" . esc_html($CURUSER["info"]) . "</textarea><br>{$lang['profile_info_bb']}", 1);
tr($lang['profile_email'], '<a id="change_email"></a>' . "<input type=\"text\" name=\"email\" id=email size=50 disabled=disabled value=\"" . esc_html($CURUSER["email"]) . "\" /><br>".
	'<label><input type="checkbox" name="ckb_change_email" id="ckb_change_email" value="yes"/>'.__('Schimbă emailul').'</label><br>'
	//__('Din motive de securitate, ultimile litere din emailul vostru(inainte de @) au fost ascunse(prin înlocuire cu puncte).')
, 1);
print("<tr><td colspan=\"2\" align=left>{$lang['profile_email_note']}</td></tr>\n");

tr($lang['profile_reset_passkey'],"<input type=checkbox name=resetpasskey value=1 /><font class=small>{$lang['profile_reset_passkey_about']}</font>", 1, "my_reset_passkey");
tr($lang['profile_change_password'], '<a id="change_password"></a><table class="no_td_border" cellspacing="5"><tr><td align="right">'.$lang['profile_cur_password'].':</td><td><input type="password" name="password" size="40" /></td></tr>
<tr><td align="right">'.$lang['profile_new_password'].':</td><td><input type="password" name="chpassword" size="40" /></td></tr>
<tr><td align="right">'.$lang['profile_repeat_password'].':</td><td><input type="password" name="passagain" size="40" /></td></tr></table>
<ul>
	<li>Lungimea minimă este 6 caractere.</li>
	<li>Nu uitilizati parole simple de genul 1234</li>
	<li><a target="_blank" href="http://www.google.com/search?q=Cum+să+alegi+o+parolă+ghid">Cum să alegeți o parolă bună ?</a></li>
</ul>
', 1);

function priv($name, $descr) {
	global $CURUSER;
	if ($CURUSER["privacy"] == $name)
		return "<input type=\"radio\" name=\"privacy\" value=\"$name\" checked=\"checked\" /> $descr";
	return "<input type=\"radio\" name=\"privacy\" value=\"$name\" /> $descr";
}

?>
<tr><td colspan="2" align="center"><input type="submit" value="<?=$lang['profile_submit']?>" style='height: 25px'> <input type="reset" value="<?=$lang['profile_revert']?>" style='height: 25px'></td></tr>
</table>
</form>
<script type="text/javascript" src="./js/my.js"></script>
<script type="text/javascript">
$j(function($){
	var email_temp = '';
	$('#ckb_change_email').click(function() {
		if ($(this).attr('checked')) {
			$('#email').attr('disabled',false);
			email_temp = $('#email')[0].value;
			$('#email')[0].value='';
		} else {
			$('#email').attr('disabled',true);
			$('#email')[0].value=email_temp;
		}
	});

	<?php
			if ( isCTenabled() )
			{
			?>
	var $cahngeCT = $('#changeCT');
	var $t=$('#title');


	$cahngeCT.change(function()
	{
		var val = $cahngeCT.is(':checked');
		disableCT(!val);
	});

	function disableCT(val)
	{
		$t.attr('disabled', val);
	}

    function titleCheck()
    {
		$('#TitleLen').text($t.val().length);

		if ($t.val().length > <?php echo $CTmaxLen; ?>)
			$t.css('background-color', '#FFCFCF');
		else
			$t.css('background-color', '#FFF');

		 $t.not('.binded').bind("change keyup", function(){titleCheck();}).addClass('binded');
    }
    titleCheck();
    disableCT(true);

	<?php } ?>
});
</script>
<?php
stdfoot();

?>