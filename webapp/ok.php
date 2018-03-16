<?php
require_once("include/bittorrent.php");

if (!mkglobal("type"))
	die();

if ($type == "signup" && mkglobal("email")) {
	stdhead("User signup");
	$GLOBALS['lang']['ok_check_the_mail'] = str_replace('{$email}',esc_html($email),$GLOBALS['lang']['ok_check_the_mail']);
	stdmsg($GLOBALS['lang']['ok_signup_successful'],$GLOBALS['lang']['ok_check_the_mail']);
	stdfoot();
}
elseif ($type == "confirmed") {
	stdhead($GLOBALS['lang']['ok_already_confirmed_head']);
	stdmsg($GLOBALS['lang']['ok_already_confirmed_body_head'],$GLOBALS['lang']['ok_already_confirmed_body']);
	stdfoot();
}
elseif ($type == "confirm") {
	if (isset($CURUSER)) {
		stdhead($GLOBALS['lang']['ok_confirmed_head']);
		stdmsg($GLOBALS['lang']['ok_confirmed_body_head'],
			"Contul dumneavoastra a fost activat! Aţi fost automat autentificaţi. <br/><br/>
			Bun venit în comunitatea noastră! Ne pare bine că sunteți cu noi!");
		stdfoot();
	} else {
		stdhead($GLOBALS['lang']['ok_confirmed_head_but_cookie']);
		stdmsg($GLOBALS['lang']['ok_confirmed_body_head_but_cookie'],$GLOBALS['lang']['ok_confirmed_body_but_cookie']);
		stdfoot();
	}
}
else {
	die();
}

?>