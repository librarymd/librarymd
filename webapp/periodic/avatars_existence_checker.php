<?php
chdir(dirname(__FILE__));
if (php_sapi_name() != "cli") die();

require '../include/bittorrent.php';

set_time_limit(0);
ignore_user_abort(1);
q('SET @@wait_timeout=988000');
ini_set('display_errors',1);
ini_set('error_reporting',E_ALL);

$query = q("SELECT id,avatar,avatar_version FROM users WHERE avatar='yes'");
echo "Now processing"; ob_flush();
$total=0;
while($dat=mysql_fetch_assoc($query)) {
    if ($dat['avatar'] != "yes") continue;
    list($width,$height,,) = @getimagesize(avatarWww($dat['id'],$dat["avatar_version"],false) );
    if ( !($width > 0 && $height > 0) ) {
        echo "Avatar $dat[id] doesn't exist\n<br>";
        q("UPDATE users SET avatar='no' WHERE id=:id",array('id'=>$dat['id']));
  cache_user_expire($dat['id']);
    }
    $total++;
    if (($total % 100) == 0) echo ".";
    ob_flush();
}
