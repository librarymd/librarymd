<?php
$topicsWithLastSeen = fetchAll('
SELECT *
FROM
  (SELECT thread, lastSeenMsg, topics.forumid, topics.posts
   FROM watches
   LEFT JOIN topics ON topics.id = watches.thread
   WHERE user = 108976 AND lastThreadMsg > lastSeenMsg and type="topic") as watched

  LEFT JOIN posts
    ON posts.topicid = watched.thread AND
       posts.forumid = watched.forumid AND
       posts.page >= GREATEST(CEILING(watched.posts / 25) - 5, 0) AND
       posts.id > watched.lastSeenMsg
');

// if (count($topic_ids) > 0) {
//   $topic_ids_csv = join(',',$topic_ids);

//   $topics = fetchAll('SELECT id, posts, forumid
//   FROM topics
//   WHERE id IN ($topic_ids_csv)
//   locked = false');
// }
