<?php
require_once("include/bittorrent.php");

stdhead("Login");

function redirectToReturnIfCameFromLogin() {
  if (Users::isLogged() && $_SERVER['HTTP_REFERER'] && isset($_GET["returnto"])) {
    $parsedReferer = parse_url($_SERVER['HTTP_REFERER']);
    if ($parsedReferer && $parsedReferer['path'] == '/login.php') {
      redirect($_GET["returnto"]);
    }
  }
}

redirectToReturnIfCameFromLogin();

unset($returnto);
if (!empty($_GET["returnto"])) {
	$returnto = $_GET["returnto"];
	if (!$_GET["nowarn"]) {
		print("<h1>". __('Nu sunteţi autentificat!') ."</h1>\n");
		print("<p>". __('<b>Eroare:</b> Puteţi vizualiza această pagină doar atunci când sunteţi logat.') ."</p>\n");
	}
}
$ask_captcha = isset($_GET['c']);
?>
<h1>Autentificare</h1>
<form method="POST" action="takelogin.php" name="form_login" class="signin-form" id="signin_form" >
<p class="center"><?=__('Notă: E nevoie ca modulele cookies să fie activate pentru a vă autentifica.')?></p>
<table class="mCenter" border="0" cellpadding=5 class=main>
<tr><td class=rowhead><?=__('User')?>:</td><td align=left><input type="text" size=40 name="username" <?php if (isset($_GET['username'])) echo 'value="'.esc_html($_GET['username']) . '"'; ?> /></td></tr>
<tr><td class=rowhead><?=__('Parola')?>:</td><td align=left><input type="password" size=40 name="password" /></td></tr>
<?php if ($ask_captcha): ?>
<tr>
  <td colspan="2" align="center">
    <div class="g-recaptcha" data-sitekey="6Le3Pg4UAAAAAJVExD_vd-lny51wnyvaxslI48D0"></div>
  </td>
</tr>
<?php endif; ?>
<tr><td colspan="2" align="left"><input type="checkbox" name="autologin" id="autologing" value="1" checked/> <label for="autologing"><?php echo $lang['login_autologin'];?></label> </td></tr>
<tr><td colspan="2" align="center"><input type="submit" value="<?=__('Intră!')?>" class=btn></td></tr>
</table>
<!--<script src='https://www.google.com/recaptcha/api.js'></script>-->
<?php

if (isset($returnto))
	print("<input type=\"hidden\" name=\"returnto\" value=\"" . esc_html($returnto) . "\" />\n");

?>
</form>
<p class="center"><?=__('Nu aveţi cont? <a href="signup.php">Înregistraţi-vă</a> chiar acum! | <a href="recover.php">Recuperarea</a> parolei.')?></p>
<?php
if (isset($_GET['username'])) echo '<script language="JavaScript">document.forms.form_login["password"].focus();</script>';
else echo '<script language="JavaScript">document.forms.form_login["username"].focus();</script>';
?>

<table class="mCenter" border="0" cellpadding=5 class=main>
<tr><td>
<b>Util de știut</b>, verificarea sumplimentară este cerută cel mai des când:
<ul>
  <li>aveți o parolă foarte simplă (exemplu 1234)</li>
  <li>ați încercat să vă autentificati nereusit de prea multe ori.</li>
  <li>alte motive care trezesc suspiciuni.</li>
</ul>
Motivul verificarii este blocarea roboților care încerca să<br>
se autentifice cu parole simple pentru toți utilizatorii.
</td></tr>
</table>
<?php
stdfoot();
?>
