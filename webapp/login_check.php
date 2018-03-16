<?php
require_once("include/bittorrent.php");
require_once("include/classes/login_service.php");
require_once("include/classes/password_strength_evaluator.php");

header('Content-type: text/html; charset=utf-8');
header("Pragma: No-cache");

/**
 * Function converts an Javascript escaped string back into a string with specified charset (default is UTF-8).
 * Modified function from http://pure-essence.net/stuff/code/utf8RawUrlDecode.phps
 *
 * @param string $source escaped with Javascript's escape() function
 * @param string $iconv_to destination character set will be used as second paramether in the iconv function. Default is UTF-8.
 * @return string
 */
function unescape($source, $iconv_to = 'UTF-8') {
   $decodedStr = '';
   $pos = 0;
   $len = strlen ($source);
   while ($pos < $len) {
       $charAt = substr ($source, $pos, 1);
       if ($charAt == '%') {
           $pos++;
           $charAt = substr ($source, $pos, 1);
           if ($charAt == 'u') {
               // we got a unicode character
               $pos++;
               $unicodeHexVal = substr ($source, $pos, 4);
               $unicode = hexdec ($unicodeHexVal);
               $decodedStr .= code2utf($unicode);
               $pos += 4;
           }
           else {
               // we have an escaped ascii character
               $hexVal = substr ($source, $pos, 2);
               $decodedStr .= chr (hexdec ($hexVal));
               $pos += 2;
           }
       }
       else {
           $decodedStr .= $charAt;
           $pos++;
       }
   }

   if ($iconv_to != "UTF-8") {
       $decodedStr = iconv("UTF-8", $iconv_to, $decodedStr);
   }

   return $decodedStr;
}

/**
 * Function coverts number of utf char into that character.
 * Function taken from: http://sk2.php.net/manual/en/function.utf8-encode.php#49336
 *
 * @param int $num
 * @return utf8char
 */
function code2utf($num){
   if($num<128)return chr($num);
   if($num<2048)return chr(($num>>6)+192).chr(($num&63)+128);
   if($num<65536)return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
   if($num<2097152)return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128) .chr(($num&63)+128);
   return '';
}

if (!isset($_POST['username']) || !isset($_POST['password']) || strlen($_POST['username']) == 0 || strlen($_POST['password']) == 0) {
	echo 'Error! Please enter username and the password.';

	die();
}

$sql = array();

$username = $_POST['username'];
$password = $_POST['password'];
$ip = getip();

function login_check_unsuccessful() {
  die('1');
}

$log_full_details = "(login_check) " . $_POST['username'] . ' ussing pass: ' . $password . " ip: " . $ip;

if (login_attempt_die_if_banned_any($username, $ip)) {
  write_sysop_log('Attempt to login but is now banned because too many login tries: ' . $log_full_details);
	die('Autentificarea de pe ip-ul dvs. a fost temporar inchisa din cauza a multor incercari nereusite de autentificare.');
}

if (LoginService::is_captcha_required($ip, $username, $password)) {
  $captcha_verbose = LoginService::is_captcha_required_verbose($ip, $username, $password);
  write_sysop_log("Captcha is needed, " . $log_full_details . ' reason: ' . $captcha_verbose['reason']);
  die('3'); // Need captcha authentication
}

/**
 * Fetch data from DB
 */
$res = q("SELECT id, passhash, secret, enabled, status, class FROM users WHERE username = :name", array("name"=>$username));
$row = mysql_fetch_array($res);
if (!$row) die('2');
if ($row['status'] != 'confirmed') die('Error! Your account are not confirmed, check your email for confirmation link.');

/**
 * If not successful or if cookie is not set
 */
if ($row['passhash'] !== md5($row['secret'] . $password . $row['secret'])) {
  /**
   * Anti brute-force, username and ip
   */
  login_atempt_faild_increment($username);
  if ($ip != "" && $ip != "127.0.0.1" && $ip != "localhost")
    login_atempt_faild_increment($ip);

	if ($row['class'] >= UC_MODERATOR) {
		write_sysop_log('Attempt to login as ' . $_POST['username'] . ' ussing pass: ' . $password);
	}
  login_check_unsuccessful();
}

//write_sysop_log("Login_check login success");
die('0'); //Login successfully
?>