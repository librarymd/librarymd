<?php
chdir(dirname(__FILE__));
require "../include/bittorrent.php";
require_once($INCLUDE.'cleanup.php');

docleanup120();

require_once($INCLUDE.'generate_tops.php');