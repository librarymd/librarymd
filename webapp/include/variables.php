<?php

$sphinx_host = 'localhost';
$sphinx_port = '3312';

define('TB_INSTALLED', true);

$WWW_ROOT = __DIR__ . '/../';
$INCLUDE = $WWW_ROOT . 'include/';
$SETTINGS_PATH  = $WWW_ROOT . 'static-db/';
$ERROR_PATH = $WWW_ROOT . 'errors/';
$BINDATA = $WWW_ROOT;

$memcache_host     = '127.0.0.1';
$memcache_port     = 11211;
$memcache_presence = true;

ini_set('SMTP','127.0.0.1');
ini_set('smtp_port','25');

$mongo_host = "127.0.0.1:27017";
$mongo_db = "torrent";

$devenv = false;
$devenenv_sphinx_enabled = false;
if ($devenv)
  ini_set('display_errors', 'On');
else
  ini_set('display_errors', 'Off');

// Override default variables.php
$siteVariables['login']['https_only'] = false;

// Dht http daemon to fetch peers, ex. http://ip:port
$globalDhtClientHost = 'http://127.0.0.1:3000';

$siteVariables = array();

// Config vars
$max_torrent_size = 3145728;
$signup_timeout = 86400 * 3;
$minvotes = 1;
$max_dead_torrent_time = 6 * 3600;
$poll_enabled = false;
//Description search field sep
$description_sep = ',+';

$freeleache = true;

$torrent_dir = $BINDATA . "torrents";    # must be writable for httpd user
$torrent_img_dir = $BINDATA . "torrents_img";    # must be writable for httpd user
$torrent_img_dir_www = "./torrents_img";

// Buffer for delayed q
$q_delayed = array();

# the first one will be displayed on the pages
$announce_urls = array();

if ($_SERVER["HTTP_HOST"] == "") $_SERVER["HTTP_HOST"] = $_SERVER["SERVER_NAME"];
$BASEURL = "http://" . $_SERVER["HTTP_HOST"];

$pic_base_url = "/pic/";
// Default language
$default_lang = "ro";

$SITE_ONLINE = true;

$emaildomainbase = 'domain.com';

// Email for sender/return path.
$SITEEMAIL = 'support@' + $emaildomainbase;
$SITEEMAILNOREPLY = 'noreply@' + $emaildomainbase;
$SITEEMAILNOREPLYRETURN = 'noreplyreturn@' + $emaildomainbase;
$SITENAME_SHORT = 'OpenLibrary';
$SITENAME = "$SITENAME_SHORT - Information for everyone";

//Temporary fulgi variables
$SNOW_no_UP = 15;
$SNOW_Picture_UP = "/pic/fulgi/snow1.gif";
$SNOW_Enabled_UP = 0;


//CT
$CTmaxLen=35; //CT max length

//torrente promovate
$torrente_promovate_isEnable = false;
$torrente_promovate_catID = 522; //introducem manual ID-ul ascociat categoriei de promovare

//Inbox.php
$isPrivMessSearchEnabled = true;
$isPrivMessSearchByKeywordEnabled = false;


//browse_filters.php
$browseGCatVariable = 85; //ID-ul categoriei "Tip de conținut"

//userdetails.php
$developersArray = array(); //developers ID's array

// details.php
$siteVariables['torrents']['checkIfOndisk'] = true; // It's used in every page of details.php
$siteVariables['categtag']['languageCatID'] = 26; // folosit pentru identificarea numărului de bande sonore
$siteVariables['categtag']['movie'] = 89; // Movie categtag
$siteVariables['categtag']['filme_animate'] = 98; // Movie categtag
$siteVariables['categtag']['original'] = 303; // Original (no translation) categtag

//forum.php
$siteVariables['forum']['toStaffID'] = 24;

//browse.php
$siteVariables['browse']['useNewCategTable'] = true;
$siteVariables['security']['email'] = 'pub@' + $emaildomainbase;
$siteVariables['security']['email_user_id'] = 1;

$siteVariables['login']['hmac_cookie_name'] = "KOGAIONON_DATA";
$siteVariables['login']['mode'] = "sign";

$siteVariables['login']['https_only'] = false;
$siteVariables['login']['https_only_domain'] = ""; // example domain.com
$siteVariables['registration']['email_confirmation'] = false;
$siteVariables['upload']['power_user_only'] = false;

$siteVariables['forum']['moderators_activated'] = false;
$siteVariables['torrents']['releasers_moderators'] = false;

$siteVariables['general']['allow_fresh_new_users_write_messages'] = true;
$siteVariables['general']['allow_anonymous_browse'] = true;
