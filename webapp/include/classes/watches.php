<?php
class Watches {

  public static function startForType($type, $userId, $thread) {

    if ($type != 'topic' && $type != 'torrent') {
      bark('Bad type');
    }

    // Check if is not already in watch list
    $watchId = q_singleval(
      'SELECT id
       FROM watches
       WHERE user=:userid AND thread=:thread AND type=:type',
       array('userid' => $userId, 'thread' => $thread, 'type' => $type)
    );

    if ($watchId > 0)
      return false;


    // Get last post/comment from the forum/torrent
    if ($type == 'topic') {
      $lastMsg = q_singleval('SELECT lastpost FROM topics WHERE id = :thread', array('thread' => $thread));
    } elseif ($type == 'torrent') {
      $lastMsg = q_singleval('SELECT MAX(id) FROM comments WHERE torrent = :thread', array('thread' => $thread));
      if (!is_numeric($lastMsg)) {
        $lastMsg = 1;
      }
    }
    if (!is_numeric($lastMsg)) die('No messages found for this thread');

    q('INSERT INTO watches VALUES (0, :userId, :type, :thread, :lastMsg, :lastMsg)',
      array('userId' => $userId, 'type' => $type, 'thread' => $thread, 'lastMsg' => $lastMsg)
    );

    return true;
  }

  public static function startForTopic($userId, $thread) {
    return self::startForType('topic', $userId, $thread);
  }

}
?>