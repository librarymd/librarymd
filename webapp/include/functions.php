<?php
require $INCLUDE . 'functions_antiflood.php';

// #################### Start user language ##########################
// Return user language
function get_lang() {
    global $default_lang;
    if (isset($_GET['__lang'])) return ($_GET['__lang'] == 'ru')?'ru':'ro';
    if (!isset($GLOBALS['CURUSER']['2letter_lang'])) {
      $cookie = (isset($_COOKIE['lang']))?$_COOKIE['lang']:'';
      return ($cookie == 'ru')?'ru':'ro';
    }
    return $GLOBALS['CURUSER']['2letter_lang'];
}

// #################### Start lang_translator ##########################
// Read from lang/lang.conf
function lang_translator($lang_i) {
    //$v = file($GLOBALS['WWW_ROOT'] . 'lang/lang.conf');
    $v = array('ro','ru');
    $lang_i -= 1; //-1 because Arrays beggins from 0
    if (!isset($v[$lang_i])) return $GLOBALS['default_lang'];
    return trim($v[$lang_i]);
}

// #################### Start logged or not detector ##########################
// Return true or false
function is_logged() {
    if (isset($GLOBALS['CURUSER']['2letter_lang'])) return true;
    else return false;
}

function get_current_id() {
    return $GLOBALS['CURUSER']['id'];
}

// #################### Start is microtime_float ##########################
// ussed for benchmarks
function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

// #################### Start getip ##########################
function getip($forAuth=false) {
    return $_SERVER["REMOTE_ADDR"];
}

// #################### Start mksize ##########################
// Ussed for transforming files lengh measures
function mksize($bytes) {
    if ($bytes < 1000 * 1024)
        return number_format($bytes / 1024, 2) . " kB";
    elseif ($bytes < 1000 * 1048576)
        return number_format($bytes / 1048576, 2) . " MB";
    elseif ($bytes < 1000 * 1073741824)
        return number_format($bytes / 1073741824, 2) . " GB";
    elseif ($bytes < 1000 * 1099511627776)
        return number_format($bytes / 1099511627776, 3) . " TB";
    else
        return number_format($bytes / 1125899906842624, 3) . " PB";
}

function mksizeint($bytes) {
    $bytes = max(0, $bytes);
    if ($bytes < 1000)
        return floor($bytes) . " B";
    elseif ($bytes < 1000 * 1024)
        return floor($bytes / 1024) . " kB";
    elseif ($bytes < 1000 * 1048576)
        return floor($bytes / 1048576) . " MB";
    elseif ($bytes < 1000 * 1073741824)
        return floor($bytes / 1073741824) . " GB";
    elseif ($bytes < 1000 * 1099511627776)
        return floor($bytes / 1099511627776) . " TB";
    else
        return floor($bytes / 1125899906842624) . " PB";
}

function mkprettytime($s) {
    if ($s < 0)
        $s = 0;
    $t = array();
    foreach (array("60:sec","60:min","24:hour","0:day") as $x) {
        $y = explode(":", $x);
        if ($y[0] > 1) {
            $v = $s % $y[0];
            $s = floor($s / $y[0]);
        }
        else
            $v = $s;
        $t[$y[1]] = $v;
    }
    if ($t["day"])
        return $t["day"] . "d " . sprintf("%02d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
    if ($t["hour"])
        return sprintf("%d:%02d:%02d", $t["hour"], $t["min"], $t["sec"]);
        return sprintf("%d:%02d", $t["min"], $t["sec"]);
}

function TimeDiffStr($from, $to) {
    $diff = $from - $to;
    if ($diff >= 86400) {
        $days = round($diff / 86400);
        return $days . ' ' . 'days';
    } elseif ($diff >= 3600) {
        $hours = round($diff / 3600);
        return $hours . ' ' . (($hours>1)?'hours':'hour');
    } elseif($diff > 60) {
        $minutes = round($diff / 60);
        return $minutes . ' ' . 'minutes';
    } else {
        return 1 . ' ' . 'minute';
    }
}

// #################### Start mkglobal ##########################
// Make global
function mkglobal($vars) {
    if (!is_array($vars))
        $vars = explode(":", $vars);
    foreach ($vars as $v) {
        if (isset($_GET[$v]))
            $GLOBALS[$v] = $_GET[$v];
        elseif (isset($_POST[$v]))
            $GLOBALS[$v] = $_POST[$v];
        else
            return 0;
    }
    return 1;
}

function hash_pad($hash) {
    return str_pad($hash, 20);
}

function validemail($email) {
    return preg_match('/^[\w.-]+@([\w.-]+\.)+[a-z]{2,6}$/is', $email);
}

function usableemail($email) {
    $email_parts = explode("@",strtolower($email));
    if (isset($email_parts[1])) {
        $email_domain = $email_parts[1];
        $first = fetchFirst('SELECT count(id) FROM banned_email WHERE domain=:domain', array("domain" => $email_domain));
        return $first == 0;
    } else {
        false;
    }

}

function sqlesc($x) {
    return "'".mysql_real_escape_string($x)."'";
}
function _esc($s) {
    return "'".mysql_real_escape_string($s)."'";
}

// ###################### Start esc_html_uni #######################

function esc_html($t) {
    return htmlspecialchars($t,ENT_QUOTES,'UTF-8');
}

// ### Bitwise function ###
function setflag($var, $flag, $set) {
    if (($set == true)) $var = ($var | $flag);
    if (($set == false)) $var = ($var & ~$flag);
    return $var;
}

function have_flag($flag_name, $user_opt = '') {
    global $conf_user_opt,$CURUSER;
    if (!isset($conf_user_opt[$flag_name])) die('have flag err');
    if ($user_opt == '') {
        if (!isset($CURUSER['user_opt'])) return false;
        $user_opt = $CURUSER['user_opt'];
    }
    if ($user_opt & $conf_user_opt[$flag_name]) return true;
    return false;
}

//Return var config
function get_config_var($varname) {
    $value = mem_get('config_'.$varname);
    if ($value == null) {
        $value = q_singleval('SELECT value FROM avps WHERE arg='._esc($varname));
        if ($value == null) $value = 0;
        mem_set('config_'.$varname,$value,0);
    }
    return $value;
}

function set_config_var($varname, $value) {
    mem_set('config_'.$varname, $value, 0);
    $varname = _esc($varname);
    $value = _esc($value);
    q("REPLACE INTO avps VALUES($varname,$value)");
}

/**
    Return true if @n is between or equal to $entre1 and $entre2
**/
function in_range($n, $entre1, $entre2) {
    if (!is_numeric($n)) return false;
    if ($n >= $entre1 && $n <= $entre2) return true;
    else return false;
}

/**
    Email function with flood protection
**/
function mail_prot($from, $to, $p2, $p3, $p4, $p5 = '') {
    if ($p5 == '') {
        return mail2($from, $to, $p2, $p3, $p4);
    } else {
        return mail2($from, $to, $p2, $p3, $p4, $p5);
    }

}

/**
    New private message
*/
function newPM($sender, $receiver, $msg, $location='in') {
    q('INSERT INTO messages (sender, receiver, added, msg, location)
        VALUES(:sender, :receiver, NOW(), :msg, :location)',
        array('sender'=>$sender, 'receiver'=>$receiver, 'msg'=>$msg, 'location'=>$location) );

    userPmsCount($receiver,'inbox','inc');
    userPmsCount($sender,'sentbox','inc');
}

function newPMEmailNotif($receiver_notifs, $receiver_email, $sender_username) {
    global $DEFAULTBASEURL, $SITENAME;
    if (strpos($receiver_notifs, '[pm]') !== false && strlen($receiver_email) > 0) {
      //Check if we really need to send a email, send only if the user last_access is bigger than 300 secs
    $body = <<<EOD
You have received a PM from $sender_username!

You can use the URL below to view the message (you may have to login).

$DEFAULTBASEURL/inbox.php

Email notification settings $DEFAULTBASEURL/my.php.

--
$SITENAME
EOD;

    email_to($receiver_email, "You have received a PM from " . $sender_username . "!", $body, false);
  }
}

class CachedUserData {
    public static function incUnreadNotifications($userid) {
        q('UPDATE users_inbox SET unread_notifications = unread_notifications + 1 WHERE id = :userid',
            array('userid' => $userid));

        $userdata = mem_get('users_'.$userid);
        // Update user cache
        if ($userdata) {
            $userdata = unserialize($userdata);
            if (!is_numeric($userdata['unread_notifications'])) $userdata['unread_notifications'] = 0;
            $userdata['unread_notifications'] += 1;
            mem_set('users_'.$userid,serialize($userdata),86400);
        }
    }

    public static function setCacheValue($userid, $key, $value) {
        $userdata = mem_get('users_'.$userid);
        // Update user cache
        if ($userdata) {
            $userdata = unserialize($userdata);
            $userdata[$key] = $value;
            mem_set('users_'.$userid,serialize($userdata),86400);
        }
    }

    public static function resetNotifications($userid) {
        q('UPDATE users_inbox SET unread_notifications = 0 WHERE id = :userid',
            array('userid' => $userid));

        $userdata = mem_get('users_'.$userid);
        // Update user cache
        if ($userdata) {
            $userdata = unserialize($userdata);
            $userdata['unread_notifications'] = 0;
            mem_set('users_'.$userid,serialize($userdata),86400);
        }
    }

    public static function hasReadAllGlobalMessages($userid) {
        $max_id_global_notif = fetchOne('SELECT max(id) FROM global_notifications');
        q('UPDATE users_inbox SET last_read_global_notification = :max_notif WHERE id = :userid',
            array('max_notif' => $max_id_global_notif, 'userid' => $userid)
        );

        $userdata = mem_get('users_'.$userid);
        // Update user cache
        if ($userdata) {
            $userdata = unserialize($userdata);
            $userdata['last_read_global_notification'] = $max_id_global_notif;
            mem_set('users_'.$userid,serialize($userdata),86400);
        }
    }
}

function newNotification($receiver, $msg) {
    q('INSERT INTO notifications (user_id, added, msg)
        VALUES(:receiver, NOW(), :msg)',
        array('receiver' => $receiver,
              'msg'      => $msg) );

    CachedUserData::incUnreadNotifications($receiver);

    q('UPDATE users_inbox SET last_notification_received = NOW() WHERE id = :receiver',
       array('receiver' => $receiver)
    );

    $id_20 = fetchOne('SELECT id
        FROM `notifications`
        WHERE user_id = :userid
        ORDER BY id DESC
        LIMIT 45,1', array('userid' => $receiver) );

    if ($id_20 > 0)
        q(
            'DELETE FROM `notifications` WHERE id < :id_20 and user_id = :userid',
            array('userid' => $receiver, 'id_20' => $id_20)
        );
}

//what - inbox,sentbox
//action - inc,dec
function userPmsCount($userid,$what,$action='inc') {
    if ($userid == 0) return;
    if ( !in_array($action, array('inc','dec')) ) return false;

    $actionSign = ($action=='inc')?'+':'-';

    $userdata = mem_get('users_'.$userid);
    if ($what == 'inbox') {
        if ($action == 'inc') {
            Q('UPDATE users_inbox SET received=received + 1, unread = unread + 1 WHERE id=:userid',array('userid'=>$userid));
        } else {
            Q('UPDATE users_inbox SET received=received - 1 WHERE id=:userid',array('userid'=>$userid));
        }

        // Update user cache
        if ($userdata) {
            $userdata = unserialize($userdata);

            if ($action == 'inc') {
                $userdata['received'] += 1;
                $userdata['unread'] += 1;
            }
            else $userdata['received'] -= 1;

            mem_set('users_'.$userid,serialize($userdata),86400);
        }
    }

    if ($what == 'sentbox') {
        Q('UPDATE users_inbox SET sended=sended '. $actionSign .' 1 WHERE id=:userid',array('userid'=>$userid));

        // Update user cache
        if ($userdata) {
            $userdata = unserialize($userdata);

            if ($action == 'inc') $userdata['sended']+=1;
            else $userdata['sended']-=1;

            mem_set('users_'.$userid,serialize($userdata),86400);
        }
    }
}

function pmJustReaded($userid,$messages) {
    if (!$messages) return;

    $userdata = mem_get('users_'.$userid);

    // Update user cache
    if ($userdata) {
        $userdata = unserialize($userdata);

        if ($userdata['unread'])

        $userdata['unread'] += 1;

        mem_set('users_'.$userid,serialize($userdata),86400);
    }
}

function userPmsCountRegenerate($userId,$what='both') {
    if ($userId == 0) return;
    if (in_array($what,array('in','both'))) {
        $res = fetchRow("SELECT COUNT(id), SUM(if(unread='yes',1,0)) FROM messages WHERE receiver=:user AND location IN ('in', 'both')", array('user'=>$userId) );
        $received = (int)$res[0];
        $unread = (int)$res[1];

        q("UPDATE users_inbox SET received=:received, unread=:unread WHERE id=:user",
            array('received'=>$received,  'unread'=>$unread, 'user'=>$userId) );
    }

    if ($what == 'unread') {
        $unread = fetchOne("SELECT COUNT(id) FROM messages WHERE receiver=:user AND unread='yes' AND location IN ('in', 'both')", array('user'=>$userId) );

        q("UPDATE users_inbox SET unread=:unread WHERE id=:user",
            array('unread'=>(int)$unread, 'user'=>$userId) );
    }

    if (in_array($what,array('out','both'))) {
        $sended = fetchOne("SELECT COUNT(id) FROM messages WHERE sender=:user AND location IN ('out','both')", array('user'=>$userId) );

        q("UPDATE users_inbox SET sended=:sended WHERE id=:user",
            array('sended'=>(int)$sended, 'user'=>$userId) );
    }

    // Delete user's cache
    mem_delete('users_'.$userId);
}

function userPmsArchiveCountRegenerate($userId,$what='both') {
    if ($userId == 0) return;
    if (in_array($what,array('in','both'))) {
        $received = fetchOne("SELECT COUNT(id) FROM messages_archive WHERE receiver=:user AND location IN ('in', 'both')", array('user'=>$userId) );

        q("UPDATE users_inbox_archive SET received=:received WHERE id=:user",
            array('received'=>$received, 'user'=>$userId) );
    }

    if (in_array($what,array('out','both'))) {
        $sended = fetchOne("SELECT COUNT(id) FROM messages_archive WHERE sender=:user AND location IN ('out','both')", array('user'=>$userId) );

        q("UPDATE users_inbox_archive SET sended=:sended WHERE id=:user",
            array('sended'=>$sended, 'user'=>$userId) );
    }
}

function expirePmCache($id, $one_time_per_session=0) {
    if ($id == 0) return;
    if (!is_numeric($id)) return;

    if ($one_time_per_session) {
        if (isset( $GLOBALS['expirePmCache'.$id] ) ) return;
        $GLOBALS['expirePmCache'.$id] = true;
    }
    //mem_delete('pm_'.$id);
    mem_delete('users_'.$id);
    userPmsCountRegenerate($id,'unread');
}

function get_alert_email() {
    $alert_user_id = get_config_variable('security','email_user_id');

    if (strlen($alert_user_id) == 0) {
        $alert_user_id = 1;
    }

    return q_singleval('SELECT email FROM users WHERE id=:id', array('id'=>$alert_user_id) );
}

function email_to_lots($email, $subject, $body, $mail_prot = true) {
    global $SITENAME, $SITEEMAIL, $SITEEMAILNOREPLY, $SITEEMAILNOREPLYRETURN, $SITENAME_SHORT, $DEFAULTBASEURL;
   $headers="";
   $headers .="From: $SITENAME_SHORT <$SITEEMAILNOREPLY>\n";
   $headers .= "Reply-To: $SITEEMAILNOREPLY <$SITEEMAILNOREPLY>\n";
   $headers .= "Date: ".date("r")."\n";
   $headers .= "Message-ID: <".date("YmdHis")."selman@$DEFAULTBASEURL>\n";
   $headers .= "Return-Path: $SITEEMAILNOREPLYRETURN <$SITEEMAILNOREPLYRETURN>\n";
   $headers .= "MIME-Version: 1.0\n";
   $headers .= "Content-Type: text/plain;charset=UTF-8\n";
   $headers .= "Content-Transfer-Encoding: 8bit";

   mail3($SITEEMAILNOREPLY,$email,"$SITENAME_SHORT - $subject",$body, $headers);
}

function isEmailInvalid($email) {
  return fetchOne('SELECT email_is_invalid FROM users WHERE email = :email',
        array('email' => $email)
    ) == 'yes';
}

function recordEmailSent($email) {
    $user_id = fetchOne('SELECT id FROM users WHERE email = :email',
        array('email' => $email));
    if ($user_id) {
        q('UPDATE users_inbox SET last_email_sent = NOW(), emails_sent = emails_sent + 1
        WHERE id = :id', array('id' => $user_id));
    }
}

function email_to($email, $subject, $body, $mail_prot = true) {
  global $SITENAME, $SITEEMAIL, $SITEEMAILNOREPLY, $SITEEMAILNOREPLYRETURN, $SITENAME_SHORT, $DEFAULTBASEURL;

  if (isEmailInvalid($email)) return true;
  recordEmailSent($email);

   $headers="";
   $headers .="From: $SITENAME_SHORT <$SITEEMAILNOREPLY>\n";
   $headers .= "Reply-To: $SITEEMAIL <$SITEEMAIL>\n";
   $headers .= "Date: ".date("r")."\n";
   $headers .= "Message-ID: <".date("YmdHis")."selman@$DEFAULTBASEURL>\n";
   $headers .= "Return-Path: $SITEEMAILNOREPLYRETURN <$SITEEMAILNOREPLYRETURN>\n";
   $headers .= "MIME-Version: 1.0\n";
   $headers .= "Content-Type: text/plain;charset=UTF-8\n";
   $headers .= "Content-Transfer-Encoding: 8bit";

    if ($mail_prot) {
        return mail_prot($SITEEMAIL, $email, $subject, $body, $headers);
    } else {
        return mail2($SITEEMAIL, $email, $subject, $body, $headers);
    }
}

function email_swift_to($email, $subject, $body, $mail_prot = true) {
  global $SITENAME, $SITENAME_SHORT, $SITEEMAIL, $SITEEMAILNOREPLY, $SITEEMAILNOREPLYRETURN, $INCLUDE;

  require_once($INCLUDE.'standalone/swift_mailer/swift_required.php');

  $subject = "$SITENAME - $subject";

  $transport = Swift_SmtpTransport::newInstance(ini_get("SMTP"), ini_get("smtp_port"));
  $mailer = Swift_Mailer::newInstance($transport);

  try {
    $message = Swift_Message::newInstance()->setSubject($subject)
                                           ->setFrom(array($SITEEMAILNOREPLY => $SITENAME_SHORT))
                                           ->setReplyTo( $SITEEMAILNOREPLY)
                                           ->setReturnPath($SITEEMAILNOREPLY)
                                           ->setTo(array($email))
                                           ->setBody($body)
                                           ->setCharset("utf-8");
    if (!$mailer->send($message, $failures)) {
      @error_log(var_export($failures,true), 3, $GLOBALS['ERROR_PATH'].'mails');
    }
  } catch(Exception $e) {
    @error_log("Fatal error:".var_export($e->getMessage(),true), 3, $GLOBALS['ERROR_PATH'].'mails');
  }
}

function get($par){
  return isset($_GET[$par]) ? $_GET[$par] : NULL;
}
function post($par){
  return isset($_POST[$par]) ? $_POST[$par] : NULL;
}

function redirect($str,$permanent=false) {
	$str = str_replace(array("\n", "\r"), '', $str);

	if (headers_sent()) {
		echo '<script>window.location="'.$str.'";</script>"';
	} else {
        if ($permanent) {
            header("HTTP/1.1 301 Moved Permanently");
        }
		header('Location: '.$str);
	}
	exit();
}

// With this function you can do smth like:
// stringInto ('<input name=":name">', array(name=>'careva nume') )
function stringInto($str, $elm) {
    foreach($elm as $name=>$value) {
        $str = str_replace(':'.$name, $value, $str);
    }

    return $str;
}
function stringIntoEsc($str, $elm) {
    foreach($elm as $name=>$value) {
        $str = str_replace(':'.$name, esc_html($value), $str);
    }
    return $str;
}

// Alias, rename stringIntoEsc to formatHtmlSafe
function formatHtmlSafe($str, $elm) {
  return stringIntoEsc($str, $elm);
}

// Script to check referer
// $scriptName can be an array of script
// Example array('teams.php','/tracker/teams.php')
function check_referer_script($scriptName) {
    if (is_array($scriptName)) {
        $scriptNameTemp = '';
        foreach ($scriptName AS $sName) {
            //(teams.php)|(\/tracker\/teams.php)
            if ($scriptNameTemp != '') $scriptNameTemp.='|';
            $sName = str_replace('/','\\/',$sName);
            $scriptNameTemp.='('.$sName.')';
        }
        $scriptName = $scriptNameTemp;
    } else {
        $scriptName = str_replace('/','\\/',$scriptName);
    }

    if (!isset($_SERVER['HTTP_REFERER'])) return false;
    $referer = $_SERVER['HTTP_REFERER'];
    $host = $_SERVER['HTTP_HOST'];
    $referer = str_replace( array('http://','https://','www.'), array('','',''), $referer);
    $host = str_replace( array('http://','https://','www.'), array('','',''), $host);

    if (preg_match("/^$host(\/)+$scriptName/",$referer) > 0) return true;
    return false;
}

function allow_only_local_referer_domain($allow_empty=true) {
  global $allowed_hosts;
    if (empty($_SERVER['HTTP_REFERER']) && $allow_empty) return;
    $referer_hostname = str_replace( array('http://','https://','www.'), array('','',''), $_SERVER['HTTP_REFERER'] );
    $referer_hostname = explode('/',$referer_hostname);
    if ( !in_array($referer_hostname[0],$allowed_hosts) ) {
        redirect('/');
    }
}

/*
    Replace fields with the language equivalents, add torrent image
*/
function prepare_descr_html($html,$cat,$image='',$id='') {
    global $lang_input_all_names, $lang_input_all_values, $lang_category, $translation_type_tr;

    if (!isset($GLOBALS['lang_input_all_names'])) {
        include $GLOBALS['WWW_ROOT'] . 'lang/details.php_' . get_lang() . '.php';
    }

    // A empty line before description
    $rez = str_replace('<b>descr</b>','<br><b>descr</b>',$html);

    $rez = str_replace($lang_input_all_names,$lang_input_all_values,$rez);
    if(isset($lang_category[$cat])) {
        $rez = str_replace('%category%',$lang_category[$cat],$rez);
    }
    if ($image) {
        $rez = '<img src="'.$GLOBALS['torrent_img_dir_www'].'/'.$id.'_'.$image.'"><br><br>'.$rez;
    }

    $translation_type_what = array();
    $translation_type_to = array();
    foreach($translation_type_tr AS $what=>$to) {
        $translation_type_what[] = 'language_type{'.$what.'}';
        $translation_type_to[] = '<br><b><a href="/forum.php?action=viewtopic&topicid=378913" target="_blank">'.__('Traducere').'</a></b>: ' . $to;
    }
    $rez = str_replace($translation_type_what,$translation_type_to,$rez);

    return $rez;
}

/**
    Some cache fncts..h,
**/

function cache_user_expire($userid=0) {
    if ($userid == 0) $userid = $GLOBALS['CURUSER']['id'];
    mem_delete('users_'.$userid);
}

/** ########################
    Some events
######################## **/

/**
    event_user_added - when new user is added, this function must be called
    @var $id - newly added user id
**/

function event_user_added($id) {
    $user = fetchRow('SELECT * FROM users WHERE id=:id', array('id'=>$id) );

    Q('INSERT INTO users_hot (id,last_browse_see) VALUES (:id, UNIX_TIMESTAMP())', array('id'=>$id) );
    Q('INSERT IGNORE INTO users_down_up (id,uploaded,downloaded,last_access) VALUES (:id,0,0,:last)',array('id'=>$id,'last'=>get_date_time() ) );
    Q('INSERT INTO users_inbox (id) VALUES (:userid)', array('userid'=>$id) );
    Q('INSERT INTO users_additional (id) VALUES (:userid)', array('userid'=>$id) );
    Q('INSERT INTO users_rare (id,modcomment) VALUES (:userid,"")', array('userid'=>$id) );
    Q('INSERT INTO users_username SET id=:id, username=:username, gender=:gender',
        array('id'=>$id, 'username'=>$user['username'], 'gender'=>$user['gender']) );
}

function event_user_delete($id) {
    Q("DELETE FROM users_hot WHERE id=$id");
    Q("DELETE FROM users_down_up WHERE id=$id");
    Q("DELETE FROM users_down_up_onHdd WHERE id=$id");
    Q("DELETE FROM users_inbox WHERE id=$id");
    Q("DELETE FROM users_additional WHERE id=$id");
    Q("DELETE FROM users_rare WHERE id=$id");
    Q("DELETE FROM users WHERE id=$id");
    q("UPDATE torrents SET owner = 0 WHERE owner = $id");
}

// Used to masure time between two points
function time_between($name) {
    $vname = 'time_between_'.$name;
    if (isset($GLOBALS[$vname])) {
        return microtime_float() - $GLOBALS[$vname];
    } else {
        $GLOBALS[$vname] = microtime_float();
    }
}

function isPost() {
    return $_SERVER["REQUEST_METHOD"] == "POST";
}

function isGet() {
    return $_SERVER["REQUEST_METHOD"] == "GET";
}

//$to_translate = array();
function __($index,$cut_size=0) {
    global $lang,$to_translate;
    $index = str_replace(array("\n","\r"),array(' ',''),$index);
    if (!isset( $lang[$index] ) ) {
        if (defined('forum_debug')) {
          $to_translate[$index] = '';
        }
        return $index;
    } else {
        if ($cut_size != 0) return mb_substring($lang[$index],0,$cut_size);
        return $lang[$index];
    }
}

function get_cookie_domain() {
    $hostWithPort = str_replace('www.','',$_SERVER['HTTP_HOST']);
    $host         = explode(":", $hostWithPort)[0];

    if (substr_count($host,'.')==1) $host = '.' . $host; // .domain.com
    else {
        $tmpa = explode('.', $host);
        if (is_numeric(implode('',$tmpa))) { //That mean, this is smth. like 127.0.0.1
            $host = $host;
        } else $host = '.' . @$tmpa[ count($tmpa) - 2 ] . '.' . @$tmpa[ count($tmpa) - 1 ];
    }
    return $host;
}

// For php 4, for support of HTTPOnly
function set_cookie_fix_domain($Name, $Value = '', $Expires = 0, $Path = '', $Secure = false, $HTTPOnly = false) {
    $Domain = get_cookie_domain();

    header('Set-Cookie: ' . rawurlencode($Name) . '=' . rawurlencode($Value)
                          . (empty($Expires) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', $Expires) . ' GMT')
                          . (empty($Path) ? '' : '; path=' . $Path)
                          . (empty($Domain) ? '' : '; domain=' . $Domain)
                          . (!$Secure ? '' : '; secure')
                          . (!$HTTPOnly ? '' : '; HttpOnly'), false);
}

/**
 * user_set_flag
 * @var period - should be in the mysql interval format
 **/
function user_set_flag($id, $conf_user_opt_key, $period='') {
    global $conf_user_opt;
    if ( !isset($conf_user_opt[$conf_user_opt_key]) ) {
        var_dump($conf_user_opt_key);
        return false;
    }

    $bits = $conf_user_opt[$conf_user_opt_key];

    q("UPDATE users SET user_opt = (user_opt | $bits) WHERE id=:id", array('id'=>$id) );

    if ($period == '') {
        cache_user_expire($id);
        return;
    }

    $table = 'users';
    $column = '';

    if ( $conf_user_opt_key == 'postingban') {
        $column = 'postingbanuntil';
    }

    q("UPDATE $table SET $column = NOW() + interval $period WHERE id=:id", array('id'=>$id) );

    cache_user_expire($id);
}

function user_add_modcomment($userid,$text) {
    global $CURUSER;
    if (empty($text)) return false;
    $modcomment =  fetchOne('SELECT modcomment FROM users_rare WHERE id=:id', array('id'=>$userid) );
    $modcomment = date("Y-m-d H:i") . " - $text de catre $CURUSER[username].\n". $modcomment;
    q('UPDATE users_rare SET modcomment=:modcomment WHERE id=:id', array('id'=>$userid, 'modcomment'=>$modcomment) );

    cache_user_expire($userid);
}

function site_add_moder_log($text) {
    $text = $text . ' (' . getip() . ')';
    q('INSERT INTO `moderslog` (added,txt) VALUES (NOW(),:txt)',array('txt'=>$text));
}

// Numai nu 0
function nu_zero($nr) {
    if ($nr > 0) return $nr;
    return 1;
}

function echon($e) {
    echo $e,"<br>\n";
}

/*
Example of use:
mkglobal2('topicid:req:int,userid:req:int');
@param $source - REQUEST/GET/POST
*/
function mkglobal2($str,$source='REQUEST') {
    $vars = explode(',',$str);
    $source = strtoupper($source);
    if (!in_array($source,array('REQUEST','GET','POST'))) {
        throw new Exception('Bad source');
    }
    $source = $GLOBALS['_'.$source];
    foreach($vars AS $var) {
        $comps = explode(':',$var);
        $var_name = array_shift($comps); // Shift an element off the beginning
        $cur_val = @$source[$var_name];
        foreach($comps AS $comp) {
            switch($comp) {
                case 'req':
                    if (!isset($source[$var_name])) barkk("Parametrul $var_name este obligator");
                    break;
                case 'int':
                    if (!isset($source[$var_name])) break;
                    if (is_array($cur_val)) {
                        foreach($cur_val AS $cur_val_val) {
                            if (!ctype_digit($cur_val_val)) barkk("$var_name nu e numeric");
                        }
                    } else
                        if (!ctype_digit($cur_val)) barkk("$var_name nu e numeric");
                    break;
            }
        }
        $GLOBALS[$var_name] = $cur_val;
    }
}

/*
Example of use:
$topicid = getVarValid('topicid:req:int');
@param $source - REQUEST/GET/POST
*/
function getVarValid($str,$source='REQUEST') {
    if (strrpos($str, ',') !== false) die('Comma not supported');

    $source = strtoupper($source);
    if (!in_array($source,array('REQUEST','GET','POST'))) {
        throw new Exception('Bad source');
    }
    $source = $GLOBALS['_'.$source];

    $comps = explode(':',$str);
    $var_name = array_shift($comps); // Shift an element off the beginning
    $cur_val = @$source[$var_name];
    foreach($comps AS $comp) {
        switch($comp) {
            case 'req':
                if (!isset($source[$var_name])) barkk("Parametrul $var_name este obligator");
                break;
            case 'int':
                if (!isset($source[$var_name])) break;
                if (is_array($cur_val)) {
                    foreach($cur_val AS $cur_val_val) {
                        if (!ctype_digit($cur_val_val)) barkk("$var_name nu e numeric");
                    }
                } else
                    if (!ctype_digit($cur_val)) barkk("$var_name nu e numeric");
                break;
            default:
                barkk("Incorect parameter $comp for $var_name");

        }
    }
    return $cur_val;
}

function login_attempt_die_if_banned_any($username, $ip) {
    return login_atempt_die_if_banned($username) || login_atempt_die_if_banned($ip);
}

function login_atempt_die_if_banned($username) {
    // Check if user is not banned
    if (mem_get($username.'_login_banned') != NULL) {
        return true;
    }
    return false;
}

function login_attempt_faild_key($username) {
    return 'login_tries_'.$username;
}

function get_login_attempt_faild($username) {
    $user_key = 'login_tries_'.$username;

    return mem_get($user_key);
}

function login_atempt_faild_increment_only($username) {
    $user_key = login_attempt_faild_key($username);

    $user_i = get_login_attempt_faild($username) + 1;

    mem_set($user_key, $user_i, 86400);

    return $user_i;
}

function login_atempt_faild_increment($username) {
    // Anti brutforcer, increase tries counter
    $user_login_attempts = login_atempt_faild_increment_only($username);
    if ($user_login_attempts >= 15) {
        mem_set($username.'_login_banned', 1, 86400); //21600 = 24h
        write_admins_log("$username password brute force detected, banned by username");
    }
}

/**
* This function will cut the part before the @ in a email addres
*
*/
function email_cruncher($email) {
    $parts = explode('@',$email);
    $len = -3;
    if (strlen($parts[0]) <= 3) $len = -1;
    return substr($parts[0],0,$len).'..@'.$parts[1];
}

function messages_die_if_url_blacklist($text,$text_parsed='') {
    if (strstr($text_parsed,'login.ru-es.com') !== false) barkk(__('O adresa interzisa a fost depistata in mesaj.'));
}

function event_util_load_comment($comment_id) {
    return fetchRow('SELECT * FROM comments WHERE id=:id',array('id'=>$comment_id));
}

function event_insert_comment($comment_id) {
    $comment = event_util_load_comment($comment_id);
    if (empty($comment)) return;
    q('UPDATE users_additional SET users_additional.comments = users_additional.comments + 1 WHERE users_additional.id = :user',
        array('user'=>$comment['user']));
}
function event_delete_comment($comment_id) {
    $comment = event_util_load_comment($comment_id);
    if (empty($comment)) return;
    q('UPDATE users_additional SET users_additional.comments = users_additional.comments - 1 WHERE users_additional.id = :user',
        array('user'=>$comment['user']));
}

function cleanTorrentCache($torrent) {
    mem_delete("torrent_$torrent");
}

function cleanTorrentDetailsCache($torrent) {
    mem_delete('torrents_details_'.$torrent);
}

function accessToIpBind() {
    global $CURUSER;
    if ( get_user_class() < UC_MODERATOR ) return false;
    if ( ($CURUSER["id"] == 1 && isset($_COOKIE['hari'])) ||
         ($CURUSER["id"] == 108973 && isset($_COOKIE['bunbom'])) ) {
        return true;
    }
    return false;
}

// BINDATA ends in /
function configBinData($name) {
    return $GLOBALS['BINDATA'];
}

function avatarWww($user_id, $version, $www=true) {
    $avatar_file = 'avatar_' . $user_id . '_'.$version. '.gif';
    if ($www) return '/avatars/'.$avatar_file;
    else return $GLOBALS['BINDATA'] . 'avatars/' . $avatar_file;
}

function getAvatarLink($user_id, $is_avatar_enabled_str, $avatar_version) {
    $default_avatar = '/pic/forum/default_avatar.gif';

    $posterid = $user_id;

    if ($is_avatar_enabled_str == 'yes') {
      $avatar_file = avatarWww($posterid, $avatar_version);
      $width_height = mem_get($avatar_file);
      if ($width_height == null || strpos($width_height,'.') === FALSE) { //If null, get the height and store it
          list($width,$height,,) = getimagesize(avatarWww($posterid, $avatar_version, false) );
          if ($width > 0 && $height > 0) {
              mem_set($avatar_file, $width . '.' . $height, 86400);
              $avatar = avatarWww($posterid, $avatar_version, true);
          } else {
              mem_set($avatar_file, '0.0', 86400);
              $avatar = $default_avatar;
          }
      } else {
        list($width,$height) = explode('.', $width_height);
        $avatar = avatarWww($posterid,$avatar_version,true);
        if ($width == 0 || $height == 0) $avatar = $default_avatar;
      }
    } else {
      $avatar = $default_avatar;
    }

    if ($avatar == $default_avatar) { $width = 150; $height = 75; }

    return array($width,$height,$avatar);
}

function mail2($from,$to,$subject,$body,$headers) {
    mail3($from,$to,$subject,$body,$headers);

    return true;
}

function mail3($from,$to,$subject,$body,$headers) {
    global $INCLUDE,$smtpmail;
    if (!isset($smtpmail)) {
        require_once($INCLUDE.'standalone/smtp.inc.php');
        $smtpmail=new SMTPMAIL;
    }

    $headers .= "\n\n".$body;
    if (!$smtpmail->send_smtp_mail($to,$subject,$headers,$from)) {
        @error_log($smtpmail->error . "\n".$headers."\n\n", 3, $GLOBALS['ERROR_PATH'].'mails');
        return false;
    }
    $smtpmail->close();
    return true;
}

function isMDUser() {
    $cf_is_md = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ? $_SERVER["HTTP_CF_IPCOUNTRY"] == "MD" : false;
    return $_SERVER["HTTP_ISMDIP"] == "1" || isset($_COOKIE['hari']) || $cf_is_md;
}

function isTORUser() {
    $tor_host = "91.121.132.";
    if (strpos($_SERVER["REMOTE_ADDR"], $tor_host) === 0)
        return true;
    else
        return false;
}

function getUserGenderColor($gender, $inClassTag=true) {
    $return = '';
    if ($gender === 'fem')
        $return = $inClassTag?' class="female"':'female';

    return $return;
}

function echoJson($msg, $isError=false) {
    header('Content-Type: application/json; charset=utf-8');
    $msg = $isError ? array('error'=>$msg) : $msg;
    die(json_encode($msg, JSON_UNESCAPED_UNICODE));
}

/**
 * Create an indexed array with $property as index.
 **/
function array_set_index($objs,$property) {
  $new_arr = array();
  foreach ($objs AS $obj) {
    $propr_value = $obj[$property];

    if (isset($new_arr[$propr_value])) {
      // If not array, then create array and store current obj
      if (is_array($new_arr[$propr_value])) $new_arr[$propr_value][] = $obj;
      else $new_arr[$propr_value] = array($new_arr[$propr_value]);
      continue;
    }
    $new_arr[$propr_value] = array($obj);
  }
  return $new_arr;
}

function arrayToString($arr) {
    return var_export($arr, true);
}

function get_config_variable($key1, $key2) {
    global $siteVariables;
    if (!isset($siteVariables[$key1][$key2])) {
        trigger_error("get_config_variable key nou found key1: $key1 key2: $key2", E_USER_ERROR);
    }
    return $siteVariables[$key1][$key2];
}

function arrayToObject($array) {
    $object = new stdClass();
    foreach ($array as $key => $value)
    {
        $object->$key = $value;
    }
    return $object;
}

/**
 * Render a portion of a html with a specific context
 * @param  String $file To include
 * @param  $context_arr Context available to template
 * @return String
 */
function renderTemplateToString($file, $context_arr) {
    $context = arrayToObject($context_arr);
    global $WWW_ROOT;
    ob_start();
    include $WWW_ROOT . "views/" . $file;
    return ob_get_clean();
}

/**
 * Render a portion of a html with a specific context
 * @param  String $file To include
 * @param  $context_arr Context available to template
 * @return String
 */
function renderAssociatedTemplateToString($file, $context_arr) {
    $context = arrayToObject($context_arr);
    global $WWW_ROOT;
    ob_start();
    include $file;
    return ob_get_clean();
}

function daysToSecond($days) {
  return $days * 24 * 60*60;
}

function print_if_forum_debug($to_be_var_dumped) {
    if(defined('forum_debug')) var_dump($to_be_var_dumped);
}

function elapsedSince($added) {
    $elapsed = floor((time() - strtotime($added)) / 3600);

    if ($elapsed == "0")
        $added = floor((time() - strtotime($added)) / 60) . " " . $lang['browse_added_mins_ago'];
    elseif ($elapsed < 24) {
        $added = $elapsed . " " . $lang['hours'];
    } else {
      if ( date("y",strtotime( $added)) != date("y")) {
          $added = date("m-d Y",strtotime( $added));
      } else {
          $added = date("m-d",  strtotime( $added));
      }
    }
    return $added;
}

function timeLeft($theTime) {
    $now = strtotime("now");
    $timeLeft = $theTime - $now;

    if ($timeLeft > 0) {
        $days = floor($timeLeft/60/60/24);
        $hours = $timeLeft/60/60%24;
        $mins = $timeLeft/60%60;
        $secs = $timeLeft%60;

        $theText = "$days  " . __('zile');
        $theText .= " $hours " . __('ore');
        $theText .= " $mins " . __('minute');
        $theText .= " $secs " . __('secunde');

        if ($days == 0) {
            $theText .= ' <b><blink>'.__('Mîine').'!</blink></b>';
        }
    } else {
        $theText = '<b><blink>'.__('Astăzi').'!</blink></b>';
    }
    if ($timeLeft < -86400) $theText = '';
    return $theText;
}


function getUserColourCssClass($class) {
    switch ($arr["class"]) {
        case UC_SYSOP:
            return 'sysop';
        case UC_ADMINISTRATOR:
            return 'admin';
        case UC_MODERATOR:
            return 'moder';
        case UC_SANITAR:
            return 'sanitar';
        case UC_VIP:
            return 'vip';
        case UC_RELEASER:
            return 'releaser';
        case UC_KNIGHT:
            return 'faithful';
        case UC_UPLOADER:
            return 'uploader';
        case UC_POWER_USER:
            return 'p_user';
        case UC_USER:
    }
    return '';
}


function linkForUser($user, $withLink, $appendTextIntoLink = '') {
    $userCssColourClass = getUserColourCssClass($user["class"]);
    $coloured = "<span class='$userCssColourClass'>" . $user["username"] . "</span>";
    if ($withLink) {
        return "<a href=userdetails.php?id=" . $user["id"] . ">" . $coloured . $appendTextIntoLink . "</a>";
    } else {
        return $coloured;
    }
}