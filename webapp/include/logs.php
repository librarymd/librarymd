<?php
function write_log($text) {
  $text = sqlesc($text);
  q("INSERT IGNORE INTO sitelog (added, txt) VALUES(now(), $text)");
}

function write_moders_log($text) {
  if ($text == '') {
    $text = 'No text ? ' . $_SERVER['REQUEST_URI'];
  }
  $text = sqlesc($text);
  q("INSERT IGNORE INTO moderslog (added, txt) VALUES(now(), $text)");
}

function write_torrent_moders_log($text) {
  if ($text == '') {
    $text = 'No text ? ' . $_SERVER['REQUEST_URI'];
  }
  $text = sqlesc($text);
  q("INSERT IGNORE INTO torrentsmoderslog (added, txt) VALUES(now(), $text)");
}

function write_admins_log($text) {
  if ($text == '') {
    $text = 'No text ? ' . $_SERVER['REQUEST_URI'];
  }
  $text = sqlesc($text);
  q("INSERT IGNORE INTO adminslog (added, txt) VALUES(now(), $text)");
}

function write_sysop_log($text) {
  if ($text == '')
    $text = 'No text ? ' . $_SERVER['REQUEST_URI'];


  q("INSERT IGNORE INTO sysopslog (added, txt) VALUES(now(), :text)", array("text"=>$text));
}

function write_user_modcomment($user,$comment,$toModersLogs=true) {
  global $CURUSER;
  $modcomments = fetchOne('SELECT modcomment FROM users_rare WHERE id=:id',array('id'=>$user));
  $modcomments = date("Y-m-d H:i") . ' - ' . $comment . ' by ' . $CURUSER['username'] . ".\n" . $modcomments;
  Q('UPDATE users_rare SET modcomment=:comment WHERE id=:id',
    array('id'=>$user, 'comment'=>$modcomments) );

  if ($toModersLogs) {
    write_moders_log($comment . ' by ' . $CURUSER['username']);
  }
}