<?php
if (php_sapi_name() != "cli") die();

require dirname(__FILE__) . "/../include/bittorrent.php";

/**
 * This script should be run every minute.
 * It will schedule DHT updates of the torrents based on number of factors.
 * Such as:
 * - When then torrent was added. If it was added recently then it will have priority update.
 * - When was the last time DHT info was updated for a torrent.
 * - Update last 200 torrents.
 */

q('UPDATE torrents
   SET dht_peers_update_scheduled    = "yes"
   WHERE dht_peers_update_scheduled != "yes"                  AND
         added                      > NOW() - INTERVAL 1 DAY  AND
         dht_peers_updated          < NOW() - INTERVAL 1 HOUR');

q('UPDATE torrents
   INNER JOIN (
      SELECT id FROM torrents ORDER BY torrents.id DESC LIMIT 1000
   ) as most_recent_torrents ON most_recent_torrents.id = torrents.id
   SET torrents.dht_peers_update_scheduled = "yes"
   WHERE dht_peers_updated < NOW() - INTERVAL 24 HOUR');
