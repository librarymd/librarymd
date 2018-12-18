<?php
require_once($INCLUDE . 'classes/users.php');
require_once($INCLUDE . 'classes/user.php');
require_once($INCLUDE . 'classes/sql_query.php');
require_once($INCLUDE . 'classes/sql_counter.php');
require_once($INCLUDE . 'classes/security.php');
require_once($INCLUDE . 'bbcode.php');
require_once($INCLUDE . 'html_generators.php');
require_once($INCLUDE . 'logs.php');

define('UC_USER', 0);
define('UC_POWER_USER', 1);
define('UC_UPLOADER', 2);
define('UC_KNIGHT', 3);
define('UC_RELEASER', 5);
define('UC_SANITAR', 6);
define('UC_VIP', 7);
define('UC_MODERATOR', 8);
define('UC_ADMINISTRATOR', 9);
define('UC_SYSOP', 10);

function get_user_class() {
  global $CURUSER;
  if (!isset($CURUSER["class"])) return 0;
  return $CURUSER["class"];
}

function isAdmin() {
  return get_user_class() >= UC_MODERATOR;
}

function isAdminReal() {
  return get_user_class() >= UC_ADMINISTRATOR;
}

function isModerator() {
  return isAdmin();
}

function isTorrentModer() {
  return get_user_class() == UC_SANITAR || get_user_class() >= UC_MODERATOR;
}

function isForumModer() {
  return have_flag('forum_moderator') || get_user_class() >= UC_MODERATOR;
}

function aHonoredUser() {
    global $CURUSER;
    return (
      get_user_class() >= UC_UPLOADER ||
      isForumModer() ||
      (isset($CURUSER['donor']) && $CURUSER['donor'] == 'yes')
    );
}

function isUserWarned() {
  global $CURUSER;
  return $CURUSER['warned'] == 'yes';
}

function isCTenabled() {
  global $CURUSER;
  $enabled = aHonoredUser() || get_user_class() > UC_UPLOADER || $CURUSER['thanks'] > 10000;
  return $enabled && !isUserWarned();
}

function isDeveloper($userId) {
  global $developersArray;
  return in_array($userId, $developersArray);
}

function isSysop() {
  return get_user_class() >= UC_SYSOP;
}

function isSuperSysop() {
  global $CURUSER;
  return isSysop() && $CURUSER['id'] == get_config_variable('security','email_user_id');
}

function allowSysOp() {
  if (!isSysop()) die();
}


function get_user_class_name($class)
{
  switch ($class) {
    case UC_USER: return "User";

    case UC_POWER_USER: return "Power User";

    case UC_UPLOADER: return "Uploader";

    case UC_KNIGHT: return "Knight of TMD";

    case UC_RELEASER: return "Releaser";

    case UC_SANITAR: return "Moderator pe torrente";

    case UC_VIP: return "VIP";

    case UC_MODERATOR: return "Moderator";

    case UC_ADMINISTRATOR: return "Administrator";

    case UC_SYSOP: return "SysOp";
  }
  return "";
}

function is_valid_user_class($class) {
  return is_numeric($class) &&
         floor($class) == $class &&
         $class >= UC_USER &&
         $class <= UC_SYSOP;
}

function is_valid_id($id)
{
  return is_numeric($id) && ($id > 0) && (floor($id) == $id);
}


function get_ratio_color($ratio) {
  if ($ratio < 0.1) return "#ff0000";
  if ($ratio < 0.2) return "#ee0000";
  if ($ratio < 0.3) return "#dd0000";
  if ($ratio < 0.4) return "#cc0000";
  if ($ratio < 0.5) return "#bb0000";
  if ($ratio < 0.6) return "#aa0000";
  if ($ratio < 0.7) return "#990000";
  if ($ratio < 0.8) return "#880000";
  if ($ratio < 0.9) return "#770000";
  if ($ratio < 1) return "#660000";
  return "#000000";
}

function get_slr_color($ratio) {
  if ($ratio < 0.025) return "#ff0000";
  if ($ratio < 0.05) return "#ee0000";
  if ($ratio < 0.075) return "#dd0000";
  if ($ratio < 0.1) return "#cc0000";
  if ($ratio < 0.125) return "#bb0000";
  if ($ratio < 0.15) return "#aa0000";
  if ($ratio < 0.175) return "#990000";
  if ($ratio < 0.2) return "#880000";
  if ($ratio < 0.225) return "#770000";
  if ($ratio < 0.25) return "#660000";
  if ($ratio < 0.275) return "#550000";
  if ($ratio < 0.3) return "#440000";
  if ($ratio < 0.325) return "#330000";
  if ($ratio < 0.35) return "#220000";
  if ($ratio < 0.375) return "#110000";
  return "#000000";
}

function get_dht_peers_color($ratio) {
  if ($ratio < 1) return "#ff0000";
  if ($ratio < 2) return "#ee0000";
  if ($ratio < 3) return "#dd0000";
  if ($ratio < 4) return "#cc0000";
  if ($ratio < 5) return "#bb0000";
  if ($ratio < 6) return "#aa0000";
  if ($ratio < 7) return "#990000";
  if ($ratio < 8) return "#880000";
  if ($ratio < 9) return "#770000";
  if ($ratio < 10) return "#660000";
  if ($ratio < 11) return "#550000";
  if ($ratio < 12) return "#440000";
  if ($ratio < 13) return "#330000";
  if ($ratio < 14) return "#220000";
  if ($ratio < 15) return "#110000";
  return "#000000";
}

function get_elapsed_time($ts) {
  global $lang;
  $mins = floor((time() - $ts) / 60);
  $hours = floor($mins / 60);
  $mins -= $hours * 60;
  $days = floor($hours / 24);
  $hours -= $days * 24;
  $weeks = floor($days / 7);
  $days -= $weeks * 7;
  $t = "";
  $ago = ' ' . $lang['elapsed_ago'];
  if ($weeks > 0) {
      if (get_lang() == 'ru') { //Russian language have some special rules
          if ($weeks == 1 || ($weeks > 11 && substr($weeks,-1,1) == 1) ) return $weeks . ' ' . $lang['elapsed_week'] . $ago;
          if ( in_range($weeks, 2, 4) || in_range($weeks, 22, 24) ) return $weeks . ' ' . $lang['elapsed_weeks2-4'] . $ago;
          return $weeks . ' ' . $lang['elapsed_weeks'] . $ago;
      }
    return $weeks . ' ' . ($weeks > 1 ? $lang['elapsed_weeks'] : $lang['elapsed_week']) . $ago;
  }
  if ($days > 0) {
      if (get_lang() == 'ru') { //Russian language have some special rules
          if ($days == 1 || $days == 21) return $days . ' ' . $lang['elapsed_day'] . $ago;
          if ( in_range($days, 2, 4) || in_range($days, 22, 24) ) return $days . ' ' . $lang['elapsed_days2-4'] . $ago;
          return $days . ' ' . $lang['elapsed_days'] . $ago;
      }
      return $days . ' ' . ($days > 1 ? $lang['elapsed_days'] : $lang['elapsed_day']) . $ago;
  }
  if ($hours > 0) {
      if (get_lang() == 'ru') { //Russian language have some special rules
          if ($hours == 1 || $hours == 21) return $hours . ' ' . $lang['elapsed_hour'] . $ago;
          if ( in_range($hours, 2, 4) || in_range($hours, 22, 24) ) return $hours . ' ' . $lang['elapsed_hours2-4'] . $ago;
          return $hours . ' ' . $lang['elapsed_hours'] . $ago;
      }
      return $hours . ' ' . ($hours > 1 ? $lang['elapsed_hours'] : $lang['elapsed_hour']) . $ago;
  }
  if ($mins > 0) {
      if (get_lang() == 'ru') { //Russian language have some special rules
          if ($mins == 1 || ($mins > 11 && substr($mins,-1,1) == 1) ) return $mins . ' ' . $lang['elapsed_minute'] . $ago;
          if ( in_range($mins, 2, 4) || in_range($mins, 22, 24) ) return $mins . ' ' . $lang['elapsed_minutes2-4'] . $ago;
          return $mins . ' ' . $lang['elapsed_minutes'] . $ago;
      }
      return $mins . ' ' . ($mins > 1 ? $lang['elapsed_minutes'] : $lang['elapsed_minute']) . $ago;
  }
  return $lang['elapsed_less_1_min'] . $ago;
}

function iAmBlockedBy($userId) {
  global $CURUSER;

  $imBlocked = fetchOne(
    'SELECT 1 FROM blocks WHERE userid= :receiver AND blockid= :myUserId',
    array('receiver' => $userId, 'myUserId' => $CURUSER['id'])
  );
  return $imBlocked == 1;
}


function barkIfUserBlockedMe($userId) {
  if (iAmBlockedBy($userId)) barkk('Nu puteti scrie acestui utilizator');
}
?>
