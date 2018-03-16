<?php
require_once("include/bittorrent.php");

require_once("include/classes/login_service.php");
require_once("include/classes/password_strength_evaluator.php");

$ip = getip();

if (!mkglobal("username:password"))
	die();

$url_login_again  = "/login.php?username=" . esc_html($username);
$log_full_details = "(takelogin) Autentificare nereusita pentru $username cu $ip";

if (login_attempt_die_if_banned_any($username, $ip)) {
  write_sysop_log('Attempt to login but is now banned because too many login tries: ' . $log_full_details);
	stderr(__('Eroare'),__('Autentificarea de pe ip-ul dvs. a fost temporar inchisa din cauza a multor incercari nereusite de autentificare.'));
}

function bark($text) {
  global $username, $url_login_again;
  $username = esc_html($username);
  stderr(__('Autentificare eşuată!'), $text . ". <a href=\"{$url_login_again}\">". __('Mai încercaţi.') ."</a>");
}

$res = q("SELECT * FROM users WHERE username = " . sqlesc($username) . " AND status = 'confirmed'");
$row = mysql_fetch_array($res);

if (!$row)
	bark(__('Numele sau parola este incorectă'));

function takelogin_login_unsuccessful() {
  $error = 'Numele sau parola este incorectă, daca ati uitat parola, <a href="/recover.php">restabiliti-o</a>. Nu incercati parole incorecte de prea multe ori sau veti fi blocati.';
  bark($error);
}

// Login failed, check for login or if cookie is not valid
if ($row["passhash"] !== md5($row["secret"] . $password . $row["secret"])) {
  /**
   * Anti brute-force, username and ip
   */
  login_atempt_faild_increment($username);
  if ($ip != "" && $ip != "127.0.0.1" && $ip != "localhost")
    login_atempt_faild_increment($ip);

  if ($row['class'] >= UC_MODERATOR) {
    write_sysop_log('Admin tried to login ' . $log_full_details);
  }

  write_sysop_log("Autentificare nereusita cu parola: $password pentru $username cu $ip");
	takelogin_login_unsuccessful();
}

if ($row["enabled"] == "no") {
	bark(__('Acest cont a fost dezactivat'));
}

function allow_to_login() {
  if (get_config_variable('login','https_only') && get_normalized_host_name() == get_config_variable('login','https_only_domain')) {
    return is_https();
  }
  return true;
}

if (!allow_to_login()) {
  bark(__("Accesati https://{$_SERVER["HTTP_HOST"]} pentru autentificare"));
}

if (post('autologin')) {
	logincookie($row);
} else {
	logincookie($row, 1, 0);
}

$is_password_weak = PasswordStrengthEvaluator::is_weak($password);
$password_weak_url = "/my.php?weak_pass=1";

if (!empty($_POST["returnto"]))
	$next_url = "/". str_replace("//","",trim(trim($_POST['returnto']),'/'));
else
  $next_url = "/";

if ($is_password_weak)
  $next_url = $password_weak_url;

header("Location: " . $next_url);

?>