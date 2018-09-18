<?php
error_reporting(E_ALL ^ E_DEPRECATED);

mb_internal_encoding("UTF-8");
require 'secrets.php';
require 'variables.php';
require $INCLUDE . 'bittorrents_functions.php';
require $INCLUDE . 'classes/https_support_detection.php';
require $INCLUDE . 'classes/login_service.php';
require $INCLUDE . 'classes/torrents.php';
require $INCLUDE . 'common_page_parts.php';
require $INCLUDE . 'database.php';
require $INCLUDE . 'functions.php';
require $INCLUDE . 'functions_login.php';
require $INCLUDE . 'global.php';
require $INCLUDE . 'global_memcache_keys.php';
require $INCLUDE . 'memcache.php';
require $INCLUDE . 'user_opt.php';

// Set this to your site URL... No ending slash!
$DEFAULTBASEURL = 'www.' . get_normalized_host_name();

$allowed_hosts = array(get_normalized_host_name(), "www." + get_normalized_host_name());

if (IsPost() && strlen($_SERVER['HTTP_REFERER'])) {
    $referer_hostname = get_normalized_host_name_value($_SERVER['HTTP_REFERER']);

    if ( !in_array($referer_hostname, $allowed_hosts) ) {
        die('bad referer');
    }
}

if (is_https()) {
    header('Strict-Transport-Security: max-age=31536000');
}

init_memcache();

// Initilize DB
dbconn($mysql_host, $mysql_user, $mysql_pass, $mysql_db);

check_ip_banned();
tryToLoginUser();

// get_lang() depends on userlogin();
// Load language vars
$lang = array();
include($GLOBALS['WWW_ROOT'] . 'lang/main_' . get_lang(). '.php');

// Custom language vars
$f = $GLOBALS['WWW_ROOT'] . 'lang/' . basename($_SERVER['SCRIPT_NAME']) . '_' . get_lang(). '.php';
if (file_exists($f)) {
    include_once($f);
}
$GLOBALS['lang'] = $lang;

function show_jucarii() {
    $jucarii = mem_get('jucarii_'.get_current_id());
    if ($jucarii === FALSE) {
         $jucarii = fetchAll('SELECT * FROM an_nou_jucarii WHERE user=:user', array('user'=>get_current_id()) );
         mem_set('jucarii_'.get_current_id(), $jucarii, 43200, MEMCACHE_COMPRESSED);
    }
    foreach ($jucarii AS $jucarie) :?>
        <img class="jucarie" src="<?=esc_html($jucarie['url'])?>" id="<?=esc_html($jucarie['img_id'])?>" style="top:<?=$jucarie['toppx']?>px;left:<?=$jucarie['leftpx']?>px;">
    <?php endforeach;
}

function show_fulgi() {
    global $SNOW_no_UP, $SNOW_Picture_UP, $SNOW_Enabled_UP;

    $fulgi = mem_get('fulgi_'.get_current_id());
    if ($fulgi === FALSE) {
         $fulgi = fetchRow('SELECT * FROM an_nou_fulgi WHERE user_id=:user_id LIMIT 1', array('user_id'=>get_current_id()) );
         if (!$fulgi) $fulgi = 0;
        mem_set('fulgi_'.get_current_id(), $fulgi, 43200);
    }
    if(is_array($fulgi) && count($fulgi) > 0){
        $SNOW_no_UP =       $fulgi['fulgi_no'];
        $SNOW_Picture_UP =  $fulgi['fulgi_url'];
        $SNOW_Enabled_UP =  $fulgi['fulgi_enable'];
    }
    ?>
    <?php
    if ($SNOW_Enabled_UP == 1) { ?>
        <script type="text/javascript">
            var SNOW_no_UP = <?=$SNOW_no_UP?>;
            var SNOW_Picture_UP = "<?=esc_html($SNOW_Picture_UP)?>";
        </script>
        <script type="text/javascript" src="/js/snow.js"></script>
    <?php }
}

function mainWithId($title, $page_id) {
  stdhead($title, false, $page_id);
}

function mainWithCanonicalUrl($title, $canonical_url) {
  stdhead($title, false, null, $canonical_url);
}

function commonStdfootEnd() {
  if (defined('forum_debug')) {
    echo debug_show_sql();

    $pageLoadTotal = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
    $pageLoadTotal = round($pageLoadTotal * 1000);
    echo "Page load time: ${pageLoadTotal}ms<br/><br/>";
  }

  print("</body></html>\n");
}

function stdfoot() {
  print('</div><br/><table class="main footdisclaimer" align=center border=0 cellspacing=0 cellpadding=0><tr><td class=embedded>');
  if (get_lang()=='ro') print("Schimbul de informație este realizat de către utilizatorii siteului. <strong>Acest site este bazat pe voluntariat, de la utilizatori pentru utilizatori.</strong> Întregul text privind regulile şi condiţiile de utilizare poate fi găsit <a href=disclamer.php>aici</a>.<br/>\n");
  else print("Информация для обмена предоставлены пользователями сайта. <strong>Этот сайт основан на волонтерской основе, от пользователей для пользователей.</strong> Прочтите полный текст Правил использования сайта и наше Соглашение с пользователями <a href=disclamer.php>здесь</a>.<br/>\n");
  print("</td></tr></table>\n<br/>");

  commonStdfootEnd();
}

function debug_show_sql() {
    return $GLOBALS['qrs'];
}

function genbark($x,$y) {
    stdhead($y);
    print("<h2>" . esc_html($y) . "</h2>\n");
    print("<p>" . esc_html($x) . "</p>\n");
    stdfoot();
    exit();
}

function barkk($msg) {
    global $STDHEAD_CALL;
    if (isset($_POST['ajax'])) {
            echo __("Eroare") . "! " . $msg;
            die;
    }

    if (!headers_sent() && !isset($STDHEAD_CALL)) stdhead();
    stdmsg(__('Eroare'), $msg);
    stdfoot();
  exit();
}

function barkkIfNull($var, $msg) {
    if ($var === null)
        barkk($msg);
}

function mksecret($len = 20) {
    $ret = "";
    for ($i = 0; $i < $len; $i++)
        $ret .= chr(mt_rand(97, 122));
    return $ret;
}

function httperr($code = 404) {
    print("<h1>Not Found</h1>\n");
    print("<p>Sorry :(</p>\n");
    exit();
}

function timeFromNowInDays($days) {
  return time()+60*60*24 * $days;
}

function logincookie($user_row, $updatedb = 1, $expires = 0x7fffffff) {
    $hmac_cookie_name = get_config_variable("login", "hmac_cookie_name");
    $login_scheme = get_config_variable("login", "mode");

    $id = $user_row["id"];

    if ($expires == 0x7fffffff) $expires = time()+60*60*24*7*4; // One month

    if ($login_scheme != "sign") {
        $passhash = md5($user_row["passhash"]."tralala7");

        set_cookie_fix_domain("uid", $id, $expires, "/", false, true);
        set_cookie_fix_domain("pass", $passhash, $expires, "/", false, true);
    } else {
        if ($expires == 0) $hmac_expires = timeFromNowInDays(1);
        else $hmac_expires = timeFromNowInDays(30);

        $hmac_data = new Hmac_Session();
        $hmac_data->set("id", $id);
        $hmac_data->set("until", $hmac_expires);
        $hmac_data->setKey($user_row["passhash"]);
        $signed_data = $hmac_data->export();

        set_cookie_fix_domain($hmac_cookie_name, $signed_data, $expires, "/", false, true);
    }

  if ($updatedb) {
    q("UPDATE users SET last_login = NOW() WHERE id = :id", array("id" => $id));
  }
}

function n_setcookie($var, $val, $expires) {
    if ($expires == 0x7fffffff) $expires = time()+60*60*24*30; //30 days
    $host = get_cookie_domain();

    setcookie($var, $val, $expires, "/", $host);
}


function logoutcookie() {
    $host = str_replace('www.','',$_SERVER['HTTP_HOST']);

    if (substr_count($host,'.')==1) $host = '.' . $host;
    else {
        $tmpa = explode('.',$host);
        $host = '.' . $tmpa[ count($tmpa) - 2 ] . '.' . $tmpa[ count($tmpa) - 1 ];
    }

    setcookie("uid", "", 0x7fffffff, "/");
    setcookie("pass", "", 0x7fffffff, "/");
    setcookie("uid", "", 0x7fffffff, "/", $host);
    setcookie("pass", "", 0x7fffffff, "/", $host);
    setcookie(get_config_variable("login", "hmac_cookie_name"), "", 0x7fffffff, "/", $host);
    setcookie(get_config_variable("login", "hmac_cookie_name"), "", 0x7fffffff, "/");
}


function flash_success($msg) {
    return n_setcookie('flash_success', $msg, time() + 60*60*24);
}

function get_flash_success() {
    $result = isset($_COOKIE['flash_success']) && strlen($_COOKIE['flash_success']) > 0 ? $_COOKIE['flash_success'] : false;

    if ($result !== false) {
        n_setcookie('flash_success', null, time() + 60*60*24);
    }
    return $result;
}

function loggedinorreturn($idzero=false) {
    global $CURUSER;
    if (!$CURUSER || ($idzero && $CURUSER['id'] == 0) ) {
        header("Location: /login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]));
        exit();
    }
}

function loggedinorreturnNotMd() {
    global $CURUSER;
    $allow_anonymous = get_config_variable('general', 'allow_anonymous_browse');
    if (!$CURUSER && !$allow_anonymous)  {
        header("Location: login.php?returnto=" . urlencode($_SERVER["REQUEST_URI"]));
        exit();
    }
}

function deletetorrent($id,$image_name='') {
    global $torrent_dir,$torrent_img_dir;
    q("DELETE FROM torrents WHERE id = $id");
    deletetorrent_imdb($id);
    event_torrent_changed_any();
    q("DELETE FROM torrents_details WHERE id = $id");
    q("DELETE FROM searchindex WHERE id = $id");

    q("DELETE FROM torrents_tags WHERE torrentid = $id");
    q("DELETE FROM torrents_genres WHERE torrentid = $id");
    q("DELETE FROM bookmarks WHERE torrentid = $id");

    q("DELETE FROM watches WHERE thread = $id AND type='torrent'");

    foreach(explode(".","peers.files.comments") as $x)
        q("DELETE FROM $x WHERE torrent = $id");
    unlink("$torrent_dir/$id.torrent");

    if (strlen($image_name) > 1) {
        $image_name = basename($image_name);
        $image_path = $torrent_img_dir . '/' . $id . '_' . $image_name;
        if (is_file($image_path)) unlink($image_path);
    }
}

function event_torrent_changed_any() {
  mem_delete('browse_torrents_' . md5('torrents.added DESCLIMIT 0,100'));
  mem_delete('browse_torrents_count');
}

function on_torrent_moder_status_update($id) {
    $imdb_tt = fetchOne('SELECT imdb_tt FROM torrents_imdb WHERE torrent = :id',array('id'=>$id));
    if ($imdb_tt) {
        on_expire_torrent_imdb_id($imdb_tt);
    }
}

function deletetorrent_imdb($id) {
    $imdb_tt = fetchFirst('SELECT imdb_tt FROM torrents_imdb WHERE torrent=:id',array('id'=>$id));
    if (!$imdb_tt) return;
    q('DELETE FROM torrents_imdb WHERE torrent=:id',array('id'=>$id));
    $total = fetchFirst('SELECT COUNT(*) FROM torrents_imdb WHERE imdb_tt=:id', array('id'=>$imdb_tt) );
    q('UPDATE imdb_tt SET torrents = :total WHERE id=:id', array('total'=>$total, 'id'=>$imdb_tt) );
    on_expire_torrent_imdb_id($id);
    on_expire_imdb_id($imdb_tt);
}

function on_expire_torrent_imdb_id($torrentid) {
    mem_delete('t_imdb_id_'.$torrentid);
}
function on_expire_imdb_id($imdbid) {
    mem_delete('imdb_'.(int)$imdbid);
}

function searchfield($s) {
    return preg_replace(array('/[^\w]/si', '/^\s*/s', '/\s*$/s', '/\s+/s'), array(" ", "", "", " "), $s);
}

function genrelist() {
    if ( ($cat_cache = mem_get('categories_id_name')) == null) {
        $ret = array();
        $res = q("SELECT id, name FROM categories WHERE name!='XXX' ORDER BY name");
        while ($row = mysql_fetch_array($res)) $ret[] = $row;
        mem_set('categories_id_name',serialize($ret),86400);
        return $ret;
    } else {
        return unserialize($cat_cache);
    }
}

function linkcolor($num) {
    if (!$num)
        return "red";
    return "green";
}

function ratingpic($num) {
    global $pic_base_url;
    $r = round($num * 2) / 2;
    if ($r < 1 || $r > 5)
        return;
    return "<img src=\"$pic_base_url$r.gif\" border=\"0\" alt=\"rating: $num / 5\" />";
}

function get_user_icons(&$arr, $big = false) {
    global $conf_user_opt, $user_icons;
    if ($big) {
        $donorpic    = 'starbig.gif width="16" height="16"';
        $warnedpic   = 'warnedbig.gif width="16" height="16"';
        $disabledpic = 'disabledbig.gif width="16" height="16"';
    }
    else {
        $donorpic    = 'star.gif width="11" height="11"';
        $warnedpic   = 'warned.gif width="13" height="11"';
        $disabledpic = 'disabled.gif width="11" height="11"';
    }
    $pics = $arr["donor"] == 'yes' ? " <img src=/pic/user_state/$donorpic alt='Donor' title='". __('A donat bani trackerului') ."' border=0>" : '';
    if ($arr["enabled"] == 'yes')
        $pics .= $arr["warned"] == 'yes' ? " <img src=/pic/user_state/$warnedpic alt=\"Warned\" title='". __('A încălcat o regulă a trackerului') ."' border=0>" : '';
    else
        $pics .= "<img src=/pic/user_state/$disabledpic alt=\"Disabled\" border=0>\n";

    foreach( $user_icons AS $flag=>$flag_data )
    {
        if ( have_flag($flag, $arr["user_opt"]) )
        {
            if ( $big ) $img = $flag_data['big_img'];
            else $img = $flag_data['img'];
            $pics .= ' <img src=/pic/user_state/'. $img .' alt="'. $flag_data['alt'] .'" title="'. __($flag_data['title']) .'">';
        }
    }

    $height = $big ? 16 : 13;

    return $pics;
}

function touchLastAccess($userid) {
    $rand20      = rand(1, 1000);
    $hourMinutes = date('G:i');
    $key         = "users:lastaccess:$hourMinutes:$rand20";

    $time = time();
    $tostore = "$userid\t$time";

    $current = mem_get($key);
    if (!strlen($current)) {
        $current = $tostore;
    } else {
        $current .= "," . $tostore;
    }

    mem_set($key,$current,120);
}

function getMongoDb() {
  global $_mongo_db_instance, $mongo_host, $mongo_db;
  if (isset($_mongo_db_instance)) return $_mongo_db_instance;

  $mongo = new Mongo("mongodb://$mongo_host");
  $db = $mongo->selectDB($mongo_db);

  $_mongo_db_instance = $db;

  return $db;
}

function showCssLangHide() {
    $hide_lang_classes = '';
    $css_hide_rule = ' { display:none; visibility: hidden;}</style> <!-- special lang bbcode -->';
    if (get_lang() == 'ro') {
        if (!isModerator()) $hide_lang_classes = ', .lang-ru-hide';
        return '<style type="text/css">.lang-ru-hide-all' . $hide_lang_classes . $css_hide_rule;
    } elseif (get_lang() == 'ru') {
        if (!isModerator()) $hide_lang_classes = ', .lang-ro-hide';

        return '<style type="text/css">.lang-ro-hide-all' . $hide_lang_classes . $css_hide_rule;
    }
}
?>