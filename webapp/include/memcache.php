<?php

$memcache_debug = false;

class MemcacheKeys {
    static $IP_BAN_MEMCACHE_KEY = 'IP_BAN_MEMCACHE_KEY';
}

function init_memcache() {
    global $memcache, $memcache_host, $memcache_port;
    // Memcache
    $memcache = new Memcache;
    $connected = $memcache->connect($memcache_host, $memcache_port);
    if (!$connected) {
        trigger_error("Cannot connect to memcache", E_USER_WARNING);
    }
}

function mem_is_locked($name) {
    return mem_get($name.'_lock');
}

function mem_lock($name) {
    mem_set($name.'_lock', true, 900);
}

function mem_unlock($name) {
    mem_delete($name.'_lock');
}

function mem_get_user($name) {
  if (!isset($GLOBALS['CURUSER']['id'])) trigger_error("mem_get_user user is missing");
  return mem_get($name."_".$GLOBALS['CURUSER']['id']);
}

function mem_set_user($name,$data,$ttl=0,$flag=0) {
  if (!isset($GLOBALS['CURUSER']['id'])) trigger_error("mem_get_user user is missing");
  return mem_set($name."_".$GLOBALS['CURUSER']['id'],$data,$ttl,$flag);
}

function mem_renew_request_admin() {
    return isset($_GET['renew_memcache']) && isset($GLOBALS['CURUSER']['id']) && isSysop();
}

// Returns FALSE if the value was not found
function mem_get($name) {
    global $memcache, $memcache_debug;
    if (mem_renew_request_admin()) {
        return false;
    }
    if ($memcache_debug) {
        echo "mem_get: $name value: " . $memcache->get($name) . "<br/>";
    }
    return $memcache->get($name);
}

function mem_set($name, $data, $ttl=0, $flag=0) {
    global $memcache, $memcache_debug;
    if ($data === 0) $data = "0";
    if ($data == '') {
        $_SERVER['PATH_TRANSLATED'] = '';
        foreach (debug_backtrace() as $v) {
            $_SERVER['PATH_TRANSLATED'] .= "{$v['file']}:{$v['line']}<br />\n";
        }

        trigger_error('mem_set second parameter is missing '.$name . ' val ' . var_export($data,true) . ' ' . nl2br($_SERVER['PATH_TRANSLATED']), E_USER_WARNING);
    }
    if ($ttl == 0) {
        $ttl = "86400"; //Max 24 hours
    }
    if ($flag == 0) $flag = MEMCACHE_COMPRESSED;
    if (is_int($data)) {
        $data = (string)$data;
    }
    if ($memcache_debug) {
        echo "mem_set: $name data: $data<br/>";
    }
    $result = $memcache->set($name, $data, $flag, $ttl);
    if ($memcache_debug) {
        $after = mem_get($name);
        echo "mem_set after set: $after<br/>";
    }
    return $result;
}
function mem_delete($name,$timeout=0) {
    global $memcache, $memcache_debug;
    if ($memcache_debug) {
        echo "mem_delete: $name<br/>";
    }
    if ($timeout == 0) return $memcache->delete($name);
    else return $memcache->delete($name,$timeout);
}

function mem_increment($name,$value=1) {
    global $memcache, $memcache_debug;
    if ($memcache_debug) {
        $before = mem_get($name);
        echo "mem_increment: $name before increment: $before<br/>";
    }
    $memcache->increment($name, $value);
}

// Memcache multi-get functionality
$memcache_get_multi_cache = array();

function mem_get_multi_prepare($keys_to_fetch) {
    global $memcache_get_multi_cache;

    $keys_data = mem_get($keys_to_fetch);
    $memcache_get_multi_cache = array_merge($memcache_get_multi_cache,$keys_data);
}

function mem_get_multi_get($key) {
    global $memcache_get_multi_cache;
    return (isset($memcache_get_multi_cache[$key])?$memcache_get_multi_cache[$key]:false);
}

/** mem2_* is ussing special tehnique to avoid cache flood.. */
function mem2_key_name($name) {
    if (is_array($name))
        $name = arrayToString($name);
    if (strlen($name)>32)
        $name = md5($name);
    return $name;
}
/*
Internal strucutre of stored values:
[0] - set time(unix time)
[1] - expire(in seconds)
[2] - data
*/
// You should call this f only to $name setted by mem2_set
function mem2_get($name) {
    $name = mem2_key_name($name);
    $v = mem_get($name);
    if ($v == false) return false;
    // Check if expired
    if ( (time() - $v[0]) > $v[1] ) {
        // Make as non-expired and return false to make the current caller to regenerate
        $v[0] = time();
        $v[1] = 60;
        mem_set($name, $v, 120);
        return false;
    }
    return $v[2];
}

function mem2_set($name, $value, $expire=0) {
    $name = mem2_key_name($name);
    if ($expire == 0) $expire = "86400"; //Max 24 hours
    mem_set($name, array(time(),$expire,$value), $expire * 2);
}

function mem2_force_delete($name) {
    mem_delete(mem2_key_name($name));
}

function mem2_expire($name) {
    $key = mem2_key_name($name);
    $val = mem_get($key);
    if ($val == false) return false;
    $val[1] = "0";
    mem_set($key, $val, 600);
    return true;
}

/**
    Query with Cache
**/
function fetchAll_memcache($q,$secs=300,$forceUpdate=false) {
    $key = md5($q);
    return fetchAll_memcache_with_key($q, $key, $secs, $forceUpdate);
}

function fetchAll_memcache_with_key($q, $key, $secs=300, $forceUpdate=false) {
    $rows = mem2_get($key);
    if ($rows === false || $forceUpdate) {
        $rows = fetchAll($q);
        mem2_set($key,$rows,$secs);
    }
    return $rows;
}

function fetchOne_memcache_with_key($q, $key, $secs=300, $forceUpdate=false) {
    $rows = mem2_get($key);
    if ($rows === false || $forceUpdate) {
        $rows = fetchOne($q);
        mem2_set($key,$rows,$secs);
    }
    return $rows;
}

function fetchAll_memcache_with_key_clean($key) {
    clean_memcache_with_key($key);
}

function clean_memcache_with_key($key) {
    mem2_force_delete($key);
}

function fetchRow_memcache($q,$secs=300) {
    $key = md5($q);
    $rows = mem2_get($key);
    if ($rows == false) {
        $rows = fetchRow($q);
        mem2_set($key,$rows,$secs);
    }
    return $rows;
}

function fetchOne_memcache($q,$secs=300,$forceUpdate=false) {
    $key = md5($q);
    $rows = mem2_get($key);
    if ($rows == false || $forceUpdate) {
        $rows = fetchOne($q);
        mem2_set($key,$rows,$secs);
    }
    return $rows;
}

function fetchAllInOneArray_memcache($q,$id,$val='',$bind='',$secs=300,$forceUpdate=false)
{
    $key = md5($q);
    $rows = mem2_get($key);
    if ($rows == false || $forceUpdate)
    {
        $rows = fetchAllInOneArray($q,$id,$val,$bind);
        mem2_set($key,$rows,$secs);
    }
    return $rows;
}