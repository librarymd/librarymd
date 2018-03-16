<?php
if (php_sapi_name() != "cli") die();

set_time_limit(0);
chdir(dirname(__FILE__));
require "../include/bittorrent.php";

$debug = 0;

updateLastAccessCounters();
sleep(20);
updateLastAccessCounters();
sleep(20);
updateLastAccessCounters();

function updateLastAccessCounters() {

  $hourMinutes   = date('G:i', time() - 59);
  $hourMinutes2  = date('G:i', time());
  $oneMinuteKeys = array();

  for ($slot=1; $slot<=1000; $slot++) {
  	$oneMinuteKeys[] = "users:lastaccess:$hourMinutes:$slot";
  	$oneMinuteKeys[] = "users:lastaccess:$hourMinutes2:$slot";
  }

  $cells = mem_get($oneMinuteKeys);

  $userTimeDateToInsert = array();
  $userTimeDateToInsertLastTime = array();

  foreach ($cells AS $cellName=>$cellValue) {
  	if (!strlen($cellValue)) continue;

    // Format of values: $userid\t$timet\$website,
  	$valuesArr = explode(",", $cellValue);

  	foreach ($valuesArr as $userWithTime) {
  		// Now we are working with: $userid\t$time
  		list($userId, $userTime) = explode("\t", $userWithTime);

      $shouldSetValue = (
        (isset($userTimeDateToInsertLastTime[$userId]) && $userTimeDateToInsertLastTime[$userId] < $userTime)
        || !isset($userTimeDateToInsertLastTime[$userId])
      );

      if ($shouldSetValue) {
        $userTimeDateToInsert[$userId]         = "($userId, $userTime)";
        $userTimeDateToInsertLastTime[$userId] = $userTime;
      }
  	}
  }

  if (!count($userTimeDateToInsert)) {
  	return false;
  }

  q("DROP TABLE IF EXISTS `temp_newUserAccess`");

  q("CREATE TEMPORARY TABLE temp_newUserAccess (
  `id` int(10) unsigned NOT NULL,
  `unixtime` int(10) unsigned NOT NULL DEFAULT '0'
  ) ENGINE = MEMORY");

  // Now prepare a real good INSERT %) Cum scrie la carte

  $userTimeDateToInsertStr = join(',', $userTimeDateToInsert);
  $insert = 'INSERT INTO temp_newUserAccess VALUES ' . $userTimeDateToInsertStr;

  if ($debug) echo $insert;

  q($insert);

  q('UPDATE users_down_up, temp_newUserAccess
     SET users_down_up.last_access = FROM_UNIXTIME(temp_newUserAccess.unixtime),
         last_access_updates       = last_access_updates + 1
     WHERE
       temp_newUserAccess.id                      = users_down_up.id AND
       FROM_UNIXTIME(temp_newUserAccess.unixtime) > users_down_up.last_access
  ');

  foreach (getMemcacheGlobalKey('onlineTopicUsers_all_keys') as $key) {
    mem2_expire($key);
  }
}