<?php
require_once("include/bittorrent.php");
require_once("include/functions_signup.php");

assert_user_signup();

stdhead($GLOBALS['lang']['singup_header']);

?>
<p>
<form method="post" action="takesignup.php">
<table border="1" cellspacing=0 cellpadding="10">
<tr><td align="center" colspan="2"><h2><?=$GLOBALS['lang']['singup_header']?></h2></td></tr>
<tr><td align="right" class="heading"><?=$GLOBALS['lang']['singup_username']?>:</td><td align=left><input type="text" size="40" name="wantusername" /><br>
	<b>Nu folosiţi numere de telefoane şi cuvinte indecente !</b></td></tr>
<tr>
	<td align="right" class="heading">
		<?=$GLOBALS['lang']['singup_pass']?>:
	</td>
	<td align=left>
		<input type="password" size="40" name="wantpassword" /><br/>
		<ul>
			<li>Lungimea minimă este 6 caractere.</li>
			<li>Nu uitilizati parole simple de genul 1234</li>
			<li><a target="_blank" href="http://www.google.com/search?q=Cum+să+alegi+o+parolă+ghid">Cum să alegeți o parolă bună ?</a></li>
		</ul>
	</td>
</tr>
<tr><td align="right" class="heading"><?=$GLOBALS['lang']['singup_pass_again']?>:</td><td align=left><input type="password" size="40" name="passagain" /></td></tr>
<tr valign=top><td align="right" class="heading"><?=$GLOBALS['lang']['singup_email']?>:</td><td align=left><input type="text" size="40" name="email" />
<table width=250 border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>
  <b>La moment confirmarea pe email nu este necesara. Recuperarea pe email nu functioneaza.</b>
  </td></tr>
</font></td></tr></table>
</td></tr>
</td></tr>

<?php
// Generate some random for captcha
$unique_random = md5(time() . rand(1,10000) . rand(1,10000));
?>

<input type="hidden" name="captcha_id" value="<?php echo $unique_random;?>">
<tr valign="top">
  <td align="right" ><?=$GLOBALS['lang']['singup_image_check']?></td>
  <td>
    <input type="text" name="captcha_answer" size="40"/><br><br>
    <img width="180" height="60" src="/captcha_image.php?id=<?php echo $unique_random;?>">
  </td>
</tr>

<tr><td colspan="2" align="center"><input type=submit value="<?=$GLOBALS['lang']['singup_submit_btn']?>" style='height: 30px; '></td></tr>
</table>
</form>
<?php

stdfoot();
?>
