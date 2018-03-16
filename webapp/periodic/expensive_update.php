<?php
if (php_sapi_name() != "cli") die();

chdir(dirname(__FILE__));
require "../include/bittorrent.php";
require_once($INCLUDE.'cleanup.php');

update_stats_expensive();
docleanupRare();
