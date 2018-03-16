<?php
require_once("include/bittorrent.php");
require_once("include/functions_signup.php");
require_once("include/classes/login_service.php");

assert_user_signup();

if (!mkglobal("wantusername:wantpassword:passagain:email"))
	die();

function bark($msg) {
  stdhead();
	stdmsg("Signup failed!", $msg);
  stdfoot();
  exit;
}

function validusername($username)
{
	if ($username == "")
	  return false;

	// The following characters are allowed in user names
	$allowedchars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

	for ($i = 0; $i < strlen($username); ++$i)
	  if (strpos($allowedchars, $username[$i]) === false)
	    return false;

	return true;
}

function assertValidCaptcha() {
  $captcha        = post('captcha_id');
  $captcha_answer = post('captcha_answer');

  // Check if captcha is correct
  $expectedCorrectCaptchaAnswer = mem_get('captcha_'.$captcha);
  if (!strlen($expectedCorrectCaptchaAnswer)) {
    bark("Nu am putut gasi datele pentru imaginea de prevenire a robotilor.");
  }

  $expectedCorrectCaptchaAnswer = strtolower($expectedCorrectCaptchaAnswer);
  $captcha_answer               = strtolower($captcha_answer);

  if ($expectedCorrectCaptchaAnswer !== $captcha_answer) {
    bark("Litere care le-ati scris nu corespund cu cele scrise pe imaginea de control (antirobot).");
  }

  // Delete the used captcha..
  mem_delete('captcha_'.$captcha);
}

if (empty($wantusername) || empty($wantpassword) || empty($email))
	bark("Don't leave any fields blank.");

if (strlen($wantusername) > 16)
	bark("Sorry, username is too long (max is 16 chars)");

if ($wantpassword != $passagain)
	bark("The passwords didn't match! Must've typoed. Try again.");

if (validate_new_password($wantpassword) !== true) {
  bark(validate_new_password($wantpassword));
}

if ($wantpassword == $wantusername)
	bark("Parola nu poate fi identica cu numele de utilizator.");

if (!validemail($email) || !usableemail($email))
	bark("Adresa email nu este un valida.");

if (!validusername($wantusername))
	bark("Invalid username.");

// check if email addy is already in use
$sql['email'] = _esc($email);
$a = (@mysql_fetch_row(@q("select count(*) from users where email=".$sql['email'])));
if ($a[0] != 0) bark("The e-mail address $email is already in use.");

// check if username is already in use
$sql['username'] = _esc($wantusername);
$a = ( @mysql_fetch_row( @q("select count(id) from users where username=" . $sql['username']) ) );
if ($a[0] != 0) bark("The username is already in use.");

assertValidCaptcha();

$secret = mksecret();
$wantpasshash = md5($secret . $wantpassword . $secret);
$editsecret = mksecret();

$passkey = md5($wantusername . time() . $wantpasshash);

/**
	New user by default have_voted&have_seen_vote&have_seen_news
*/
global $conf_user_opt;
$default_flags = 0;
$default_flags = setflag($default_flags, $conf_user_opt['have_voted'], true);
$default_flags = setflag($default_flags, $conf_user_opt['have_seen_vote'], true);
$default_flags = setflag($default_flags, $conf_user_opt['have_seen_news'], true);

$email_confirmation = get_config_variable('registration','email_confirmation');

$new_user_status = $email_confirmation ? 'pending' : 'confirmed';
$ret = q("
  INSERT INTO users
  (username, passhash, secret, editsecret, email, status, added,
    passkey, last_browse_see, user_opt) VALUES (
  :username, :passhash, :secret, :editsecret, :email, :status, :added,
    :passkey, :last_browse_see, :user_opt)",
array('username'=>$wantusername, 'passhash'=>$wantpasshash, 'secret'=>$secret,
  'editsecret'=>$editsecret, 'email'=>$email, 'status'=>$new_user_status, 'added'=>get_date_time(),
    'passkey'=>$passkey, 'last_browse_see'=>time(), 'user_opt'=>$default_flags)
);

if (!$ret) {
	if (mysql_errno() == 1062)
		bark("Username already exists!");
	bark("borked");
}

$id = q_mysql_insert_id();

event_user_added($id);

$psecret = md5($editsecret);

if ($email_confirmation == false) {
  logincookie(fetchRow("SELECT id, passhash, editsecret, status FROM users WHERE id = :id", array('id' => $id)));
  redirect('/ok.php?type=confirm');
}

$confirm_url = "https://{$DEFAULTBASEURL}/confirm.php?id=$id&secret=$psecret";
$body = str_replace(array('{$SITENAME}','{$email}', '{$confirm_url}','{$username}', '{$SITENAME_SHORT}'),
	                  array($SITENAME    ,$email    , $confirm_url    , $wantusername, $SITENAME_SHORT),
				           $GLOBALS['lang']['email_confirmation_body']);

if (email_to($email, __("Confirmarea înregistrării utilizatorului"), $body)) {
	header("Refresh: 0; url=ok.php?type=signup&email=" . urlencode($email));
} else {
	die("Din pacate emailul cu confirmare n-a putut fi trimis, va rugam sa asteptati 10 minute, si sa intrati http://$DEFAULTBASEURL/confirm_no_email.php si sa va confirmati !<br><br>Ne cerem iertare pentru incomoditatile provocate");
}
?>