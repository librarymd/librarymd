<?php

/**
 * All DB related function should go here.
 */

function dbconn($mysql_host='', $mysql_user='', $mysql_pass='', $mysql_db='') {
  global $db_link;
  if(isset($GLOBALS['db_links'][$mysql_host])) {
      return $GLOBALS['db_links'][$mysql_host];
  }

  if (empty($mysql_host)) {
      list($mysql_host,$mysql_user,$mysql_pass,$mysql_db) = array($GLOBALS['mysql_host'],$GLOBALS['mysql_user'],$GLOBALS['mysql_pass'],$GLOBALS['mysql_db']);
  }

  $db_link = mysql_connect($mysql_host, $mysql_user, $mysql_pass);

  if (!$db_link)
  {
    switch (mysql_errno()) {
      case 1040:
      case 2002:
        if ($_SERVER['REQUEST_METHOD'] == "GET")
            die('<h2>Se duc niste lucrari, revenim in citeva minute</h2><br><br><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="360" height="420">  <param name="movie" value="pacman.swf">  <param name=quality value=high>  <embed src="pacman.swf" quality=high pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="360" height="420"></embed></object>');
        else
            die("Too many users. Please press the Refresh button in your browser to retry.");
      default:
          die(sql_error_handler('dbconn: mysql_connect', mysql_error(), mysql_errno()) );
    }
  }
  q('SET NAMES utf8','','',false);
  q("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'",'','',false);
  mysql_select_db($mysql_db) or die(sql_error_handler('dbconn: mysql_select_db', mysql_error(), mysql_errno()) );

  if (!isset($GLOBALS['db_links'])) $GLOBALS['db_links'] = array();
  $GLOBALS['db_links'][$mysql_host] = $db_link;

  return $db_link;
}

function q_mysql_insert_id() {
    global $db_link;
    if ($db_link) return mysql_insert_id($db_link);
    else return mysql_insert_id();
}

function q_mysql_affected_rows() {
    global $db_link;
    if ($db_link) return mysql_affected_rows($db_link);
    else return mysql_affected_rows();
}

function q($query,$bind='',$onErrorDontDie=false,$detectServerTable=true) {
    global $db_link;

    if ($bind != '' && is_array($bind)) $query = sqlEscapeBind($query,$bind);

    if (!isset($GLOBALS['query_with_union']) && stripos($query,'UNION') !== false) {
        $query = str_ireplace('UNION','uniion',$query);
    }
    // Sql profiling
    if(defined('forum_debug') && isset($_COOKIE['sql_profiling'])) {
        mysql_free_result( mysql_query("FLUSH STATUS",$db_link) ); // resets the session values to zero

        // Try to put sql_no_cache flag
        if (strtoupper(substr($query,0,6)) == 'SELECT') $query = "SELECT sql_no_cache " . substr($query,6,999999);
    }

    $r = mysql_query($query,$db_link) or sql_error_handler($query, mysql_error($db_link), mysql_errno($db_link), $onErrorDontDie);
    if(defined('forum_debug')) {
        $GLOBALS['qrs'] .= $query . sprintf(" - Num rows: %d Affected rows: %d<br>",@mysql_num_rows($db_link),@mysql_affected_rows($db_link));
        if (isset($_COOKIE['sql_profiling'])) {
            $res = mysql_query('SHOW STATUS LIKE "Handler_%"',$db_link);
            while($row = mysql_fetch_assoc($res)) {
                if ($row['Value']) $GLOBALS['qrs'] .= sprintf(" <b>%s</b> | %d<br>",$row['Variable_name'],$row['Value']);
            }
            $GLOBALS['qrs'] .=  "<br>";
        }
        $GLOBALS['qrs'] .=  '<br>';
    }
    return $r;
}

function q_singleval($query,$bind='') {
    if ($bind != '' && is_array($bind)) $query = sqlEscapeBind($query,$bind);

    if (($res = @mysql_fetch_row(q($query))) !== false) {
        return $res[0];
    }
    return false;
}
function q_firstrow($query)
{
    if (($res = mysql_fetch_assoc(q($query))) !== false) {
        return $res;
    }
    return false;
}
//sql_error_handler("Initiating $connect_func", mysql_error(fud_sql_lnk), mysql_errno(fud_sql_lnk)
function sql_error_handler($query, $error_string, $error_number,$dontdie=false) {
    $_SERVER['PATH_TRANSLATED'] = '';
    foreach (debug_backtrace() as $v) {
        $_SERVER['PATH_TRANSLATED'] .= "{$v['file']}:{$v['line']}<br />\n";
    }

    $error_msg = "(".$_SERVER['PATH_TRANSLATED'].") ".$error_number.": ".$error_string."<br />\n";
    $error_msg .= "Query: ".esc_html($query)."<br />\n";
    if (!empty($_GET)) {
        $error_msg .= "_GET: ";
        if (count($_GET, 1) < 100) {
            $error_msg .= esc_html(var_export($_GET, 1));
        } else {
            $error_msg .= "Too many vars: ".count($_GET, 1);
        }
        $error_msg .= "<br />\n";
    }
    if (!empty($_POST)) {
        $error_msg .= "_POST: ";
        if (count($_POST, 1) < 100) {
            $error_msg .= esc_html(var_export($_POST, 1));
        } else {
            $error_msg .= "Too many vars: ".count($_POST, 1);
        }
        $error_msg .= "<br />\n";
    }
    if (function_exists('getip')) {
        $error_msg .= "[User Ip] ".getip()."<br />\n";
    }
    if (isset($_SERVER['REQUEST_URI'])) {
        $error_msg .= "[Request uri] ".esc_html($_SERVER['REQUEST_URI'])."<br />\n";
    }
    if (isset($_SERVER['HTTP_REFERER'])) {
        $error_msg .= "[Referring Page] ".esc_html($_SERVER['HTTP_REFERER'])."<br />\n";
    }
    if (isset($GLOBALS['db_link_info'])) {
        $error_msg .= "Db:".$GLOBALS['db_link_info'][0]."<br />\n";
    } else $error_msg .= "Db: default<br />\n";

    $pfx = sprintf("?%-10d?%-10d?", strlen($error_msg) + 1, time());
    ini_set('log_errors_max_len', 0);
    if (!error_log($pfx.$error_msg."\n", 3, $GLOBALS['ERROR_PATH'].'sql_errors')) {
        echo "<b>UNABLE TO WRITE TO SQL LOG FILE</b><br>\n";
        if (php_sapi_name() == "cli")
            echo $error_msg;
    } else {
        if (defined('forum_debug')) {
            echo $error_msg;
        } else {
            trigger_error('SQL Error has occurred, please contact the <a href="mailto:'.$GLOBALS['SITEEMAIL'].'?subject=SQL%20Error">administrator</a> of the tracker and have them review the tracker&#39;s SQL query log', E_USER_ERROR);
            if (ini_get('display_errors') !== 1) {
                exit('SQL Error has occurred, please contact the <a href="mailto:'.$GLOBALS['SITEEMAIL'].'?subject=SQL%20Error">administrator</a> of the tracker and have them review the tracker&#39;s SQL query log');
            }
        }
    }
    if ($dontdie) return;
    exit;
}

/**
    Delayed query
**/

// Execute the query at the end of the script
function q_delayed($query,$bind='') {
    global $q_delayed;
    if (!count($q_delayed)) {
        register_shutdown_function('q_delayed_run');
    }

    if ($bind != '' && is_array($bind)) $query = sqlEscapeBind($query,$bind);

    $q_delayed[] = $query;
    if (defined('forum_debug')) $GLOBALS['qrs'] .= $query . '<br><br>';
    return true;
}
function q_delayed_run() {
    global $db_link;
    foreach($GLOBALS['q_delayed'] AS $query) {
        q($query) or die (sql_error_handler($query, mysql_error(), mysql_errno()));
    }
}

function get_row_count($table, $suffix = "") {
  if ($suffix)
    $suffix = " $suffix";
  ($r = q("SELECT COUNT(*) FROM $table$suffix"));
  ($a = mysql_fetch_row($r));
  return $a[0];
}

function sqlerr($file = '', $line = '') {
  print("<table border=0 bgcolor=blue align=left cellspacing=0 cellpadding=10 style='background: blue'>" .
    "<tr><td class=embedded><font color=white><h1>SQL Error</h1>\n" .
  "<b>" . mysql_error() . ($file != '' && $line != '' ? "<p>in $file, line $line</p>" : "") . "</b></font></td></tr></table>");
  die;
}

// Returns the current time in GMT in MySQL compatible format.
function get_date_time($timestamp = 0) {
  if ($timestamp)
    return date("Y-m-d H:i:s", $timestamp);
  else
    return date("Y-m-d H:i:s");
}


function sql_timestamp_to_unix_timestamp($s) {
  return mktime(substr($s, 11, 2), substr($s, 14, 2), substr($s, 17, 2), substr($s, 5, 2), substr($s, 8, 2), substr($s, 0, 4));
}


/**
    Database functions
**/

function fetchAll($q,$bind='') {
    $r = qParams($q, $bind);
    $ret = array();
    while ($row=@mysql_fetch_assoc($r)) {
        $ret[] = $row;
    }
    return $ret;
}

function fetchFirst($q,$bind='') {
    $r = qParams($q, $bind);
    $arr = @mysql_fetch_row($r);
    if (isset($arr[0])) return $arr[0];
    else return NULL;
}

// Alias to fetchFirst
function fetchOne($q,$bind='') {
    return fetchFirst($q,$bind);
}

function fetchRow($q,$bind='') {
    $r = qParams($q, $bind);
    $arr = @mysql_fetch_array($r);
    if (isset($arr[0])) return $arr;
    else return NULL;
}

// This will fetch first column
// @result array(col_val1,col_val2,col_valN)
function fetchColumn($q,$bind='') {
    $r = qParams($q, $bind);
    $ret = array();
    while ($row=@mysql_fetch_row($r)) {
        $ret[] = $row[0];
    }
    return $ret;
}

function fetchAllInOneArray($q,$id,$val='',$bind='')
{
    $r = qParams($q, $bind);
    $retArray = array();
    if(strlen($val))
        while ($row=@mysql_fetch_assoc($r))
            $retArray[$row[$id]] = $row[$val];
    else
        while ($row=@mysql_fetch_assoc($r))
            $retArray[$row[$id]] = $row;


    return $retArray;
}

function sqlEscapeBind($q, $elm) {
  $escaped_and_prefixed = array();

  if (isset($elm[0])) {
    foreach($elm as $name=>$value) {
      $elm[$name] = mysql_real_escape_string($value);
    }
    return vsprintf( str_replace("?","'%s'",$q), $elm );
  } else {
    foreach($elm as $name=>$value) {
      $escaped_and_prefixed[':'.$name] = "'" . mysql_real_escape_string($value) . "'";
    }
    return strtr($q, $escaped_and_prefixed);
  }
}

function qParams($q, $bind) {
    if (is_array($bind)) $q = sqlEscapeBind($q,$bind);
    return q($q);
}

function sqlEscapeValue($value) {
    return mysql_real_escape_string($value);
}