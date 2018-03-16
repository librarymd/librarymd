<?php
require_once("include/bittorrent.php");
allow_only_local_referer_domain(false);
// Dirty xss prot
if (@$_SERVER['QUERY_STRING'] != '') {
	die("...");
}

logoutcookie();

header("Location: //".$_SERVER['HTTP_HOST']."/");

?>