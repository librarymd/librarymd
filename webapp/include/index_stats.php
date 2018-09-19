<?php

$statsCacheTtlSeconds = 60 * 5;
$numberOfUsers    = fetchOne_memcache('SELECT COUNT(*) FROM users'   , $statsCacheTtlSeconds);
$numberOfTorrents = fetchOne_memcache('SELECT COUNT(*) FROM torrents', $statsCacheTtlSeconds);
$numberOfDhtPeers = fetchOne_memcache('SELECT SUM(dht_peers) FROM torrents', $statsCacheTtlSeconds);
$numberOfComments = fetchOne_memcache('SELECT COUNT(*) FROM comments', $statsCacheTtlSeconds);
$numberOfTopics   = fetchOne_memcache('SELECT COUNT(*) FROM topics'  , $statsCacheTtlSeconds);
$numberOfPosts    = fetchOne_memcache('SELECT COUNT(*) FROM posts', $statsCacheTtlSeconds);
?>

<h2>Statistica (date reînnoite în fiecare oră)</h2>
<table width="100%" border="1" cellspacing="0" cellpadding="10">
  <tr>
    <td align="center" width="50%">
      <table border="1" cellspacing="0" cellpadding="5">
        <tr>
          <td class="rowhead">Utilizatori</td>
          <td align="right"><a href="./users.php"><?=$numberOfUsers?></a></td>
        </tr>
        <tr>
          <td class="rowhead">Torrente</td>
          <td align="right"><?=$numberOfTorrents?></td>
        </tr>
        <tr>
          <td class="rowhead">Peers (DHT)</td>
          <td align="right"><?=$numberOfDhtPeers?></td>
        </tr>
    </table></td>
    <td align="center" width="50%">
      <table border="1" cellspacing="0" cellpadding="5">
        <tr>
          <td class="rowhead">Comentarii in torrente</td>
          <td align="right"><?=$numberOfComments?></td>
        </tr>
        <tr>
          <td class="rowhead">Topicuri pe forum</td>
          <td align="right"><?=$numberOfTopics?></td>
        </tr>
        <tr>
          <td class="rowhead">Mesaje pe forum</td>
          <td align="right"><?=$numberOfPosts?></td>
        </tr>

    </table></td>
  </tr>
</table>
