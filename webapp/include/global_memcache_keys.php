<?php

$memcache_key_global['global_notifications_last_id'] = 'global_notifications_last_id';
$memcache_key_global['onlineTopicUsers_40']          = 'onlineTopicUsers_40';
$memcache_key_global['onlineTopicUsers_count']       = 'onlineTopicUsers_count';
$memcache_key_global['onlineTopicUsers_all']         = 'onlineTopicUsers_all';

$memcache_key_global['ontracker_users']              = 'ontracker_users';
$memcache_key_global['ontracker_users_40']           = 'ontracker_users_40';
$memcache_key_global['ontracker_users_count']        = 'ontracker_users_count';


$memcache_key_global['onlineTopicUsers_all_keys']    = array($memcache_key_global['onlineTopicUsers_40'],
                                                             $memcache_key_global['onlineTopicUsers_count'],
                                                             $memcache_key_global['onlineTopicUsers_all'],
                                                             $memcache_key_global['ontracker_users'],
                                                             $memcache_key_global['ontracker_users_40'],
                                                             $memcache_key_global['ontracker_users_count']);


function getMemcacheGlobalKey($name) {
  global $memcache_key_global;

  if (!isset($memcache_key_global[$name])) {
    die('Memcache key is not defined');
  } else {
    return $memcache_key_global[$name];
  }
}

