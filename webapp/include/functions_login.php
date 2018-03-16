<?php

function tryToLoginUser() {
  // only when we are sure that it's a http request and not a syntentic script invocation
  if (isset($_SERVER['REMOTE_ADDR'])) {
      // Log the user
      userlogin_hmac();
      if (User::isAuthenticated()) {
          userlogin_onSuccess();
      }
      browserhash_assertNotBanned();
      browserhash_update();
  }
}

function userlogin_hmac() {
    if (User::isAuthenticated()) {
        return;
    }

    $hmac_cookie_name = get_config_variable("login", "hmac_cookie_name");
    if (!isset($_COOKIE[$hmac_cookie_name]) || strlen($_COOKIE[$hmac_cookie_name]) == 0) return;

    $hmac_data = new Hmac_Session();
    if ($hmac_data->load($_COOKIE[$hmac_cookie_name])) {
        $id = $hmac_data->get("id");
        $until = $hmac_data->get("until");

        // Expired
        if ($until < time()) return;
        if ($id > 0) {
            $row = User::fetchCurrentUser($id);
            if ($hmac_data->verifyKey($row["passhash"])) {
                User::setUserAsCurrent($row);
            }
        }
    }
}

function userlogin_onSuccess() {
    global $CURUSER;

    $row = $CURUSER;
    $id = $row['id'];

    if ($row['id'] == 1 || $row['id'] == 352 || $GLOBALS['devenv']) {
      define('forum_debug',1);
      error_reporting(E_ALL);
      $GLOBALS['qrs']='';
    }

    if (isset($GLOBALS['devenv']) && $GLOBALS['devenv'] && !defined('forum_debug') ) {
        define('forum_debug',1);
        $GLOBALS['qrs']='';
    }

    q_delayed('UPDATE users_down_up SET last_access = NOW() where id = :id', array('id' => $id));

    //userlogin_updateip();
}

function browserhash_assertNotBanned() {
    $browserHash = (isset($_COOKIE['phpsessid2']))?$_COOKIE['phpsessid2']:'';

    // Hash ban
    if ( isset($_COOKIE['phpsessid2']) && mem_get('banh'.$browserHash) ) {
        $res = q('SELECT id,comment FROM bans_browser WHERE browser_hash = :hash', array('hash'=>$browserHash) );

        if (mysql_num_rows($res) > 0) {
            global $SITEEMAIL;
            $row = mysql_fetch_array($res);
            header("HTTP/1.0 403 Forbidden");
            print("<html><body><h1>403 Forbidden</h1>You are temporarily banned.<br><br><font color=red><b>BAN REASON:</b></font> {$row['comment']}<br>Write to $SITEEMAIL for any comment</body></html>\n");
            die;
        }
    }
}

function browserhash_cookie_name() {
    return 'phpsessid2';
}

function browserhash_update() {
    $cookie_name = browserhash_cookie_name();

    $browserHash = (isset($_COOKIE[$cookie_name]))?$_COOKIE[$cookie_name]:'';

    if (!User::isAuthenticated()) return;
    $row = User::current();

    // User without hash
    if (strlen($browserHash) != 32) {
        $clone_hash = '';
        if (strlen($row['browserHash']) == 32) {
            $clone_hash = $row['browserHash'];
        } else {
            $clone_hash = md5(time() . rand(5, 150) . rand(7, 777) . time());
        }

        n_setcookie($cookie_name, $clone_hash, time() + 60*60*24*999 );

        header("Location: ".$_SERVER["REQUEST_URI"]);
        die();
    }

    // Assign hash to user db
    if ($row['browserHash'] != $browserHash) {
        Q_delayed("UPDATE users SET browserHash=:hash WHERE id=:id", array("hash" => $browserHash, "id" => $row["id"]));

        User::cleanCache($row['id']);
    }
}

function haship($ip) {
    return Security::hash(ip2long($ip) + 258923);
}

function get_current_user_hashed_ip() {
    $ip = getip(true);
    return haship($ip);
}

function userlogin_updateip() {
    $user = User::current();
    $hashed_ip = get_current_user_hashed_ip();

    if ($user['ip'] !== $hashed_ip) {
        Q_delayed("UPDATE users SET ip=:ip WHERE id=:id",
          array("ip" => $hashed_ip, "id" => $user['id'])
        );
        User::cleanCache($user['id']);
    }
}

function is_ip_banned($ip_to_check) {
    $ips = fetchAll_memcache_with_key("SELECT ip FROM bans_ip_hash", MemcacheKeys::$IP_BAN_MEMCACHE_KEY);
    foreach ($ips as $ip) {
        if ($ip['ip'] === $ip_to_check) {
            return true;
        }
    }
    return false;
}

function clean_ip_ban_memcache() {
    fetchAll_memcache_with_key_clean(MemcacheKeys::$IP_BAN_MEMCACHE_KEY);
}

function check_ip_banned() {
    if (is_ip_banned(get_current_user_hashed_ip())) {
        write_sysop_log("Ip a fost blocat: " . getip(true) . " => " . get_current_user_hashed_ip());
        die("Siteul este momentan inaccesibil.");
    }
}

function validate_new_password($wantpassword) {
    require_once("include/classes/password_strength_evaluator.php");

    if (strlen($wantpassword) < 6)
        return "Parola aleasă este prea scurtă, ea trebuie să aibă cel putin 6 caractere.";

    if (strlen($wantpassword) > 80)
        return "Parola aleasă este prea lunga, ea trebuie să aibă cel mult 80 caractere.";

    if (PasswordStrengthEvaluator::is_weak($wantpassword))
        return "Parola aleasă este prea simplă, vă rugăm să alegeți o parolă mai complicată.";

    return true;
}