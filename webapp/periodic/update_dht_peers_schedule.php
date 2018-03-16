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
   SET dht_peers_update_scheduled    = "yes"
   WHERE dht_peers_update_scheduled != "yes"                    AND
         added                      > NOW() - INTERVAL 1 HOUR   AND
         dht_peers_updated          < NOW() - INTERVAL 10 MINUTE');

q('UPDATE torrents
   SET dht_peers_update_scheduled    = "yes"
   WHERE dht_peers_update_scheduled != "yes"                      AND
         added                      > NOW() - INTERVAL 30 MINUTE  AND
         dht_peers_updated          < NOW() - INTERVAL 5 MINUTE');

q('UPDATE torrents
   SET dht_peers_update_scheduled    = "yes"
   WHERE dht_peers_update_scheduled != "yes"                      AND
         added                      > NOW() - INTERVAL 10 MINUTE  AND
         dht_peers_updated          < NOW() - INTERVAL 1 MINUTE');


q('UPDATE torrents
   SET dht_peers_update_scheduled    = "yes"
   WHERE dht_peers_updated < NOW() - INTERVAL 12 HOUR
   ORDER BY torrents.id DESC
   LIMIT 200');