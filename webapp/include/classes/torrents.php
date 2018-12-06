<?php
class Torrents {
    public static $skip = 0;
    public static $torrentsperpage = 100;
    public static $sql_columns = " torrents.id, torrents.category, torrents.leechers, torrents.seeders, torrents.name, torrents.times_completed, torrents.size, torrents.added, torrents.comments,torrents.numfiles,torrents.torrent_opt,torrents.filename,torrents.owner,torrents.team,
         categories.name AS cat_name, categories.image AS cat_pic, users.username, teams.name AS teamName, teams.initials AS teamInitials, torrents.moder_status,
          imdb_tt.rating AS imdb_rating, GROUP_CONCAT(torrents_catetags_index.catetag) AS catetags,
          torrents.thanks, torrents.dht_peers ";

    public static $sql_joins = " LEFT JOIN categories ON torrents.category = categories.id
          LEFT JOIN users ON torrents.owner = users.id
          LEFT JOIN teams ON (torrents.team > 0 AND torrents.team = teams.id)
          LEFT JOIN torrents_imdb ON torrents.id = torrents_imdb.torrent
          LEFT JOIN torrents_catetags_index ON torrents.id = torrents_catetags_index.torrent
          LEFT JOIN imdb_tt ON torrents_imdb.imdb_tt = imdb_tt.id ";

    public static $sql_group_by = "GROUP BY torrents.id";

    public static function list_query($select, $where, $orderby, $limit = "") {
      if ($select != "") {
        if ($select != 'SQL_CALC_FOUND_ROWS') $select .= ", ";
      }

      if (strlen($orderby) > 0) $orderby = "ORDER BY " . $orderby;
      if (strlen($where) > 0) $where = "WHERE " . $where;

      return "SELECT $select " . self::$sql_columns . "
              FROM torrents
              " . self::$sql_joins . "
              $where " . self::$sql_group_by . "
              $orderby $limit";
    }
    /**
    * @param array $categsId of (int)id
    * @param (optional) array $notCategsId of (int)id
    * @return array(count,torrentsId)
    */
    public static function getByCategTags($categsId, $notCategtagsId = null) {
        $tags_where = array('$all'=> $categsId);

        if ($notCategtagsId != null) {
            $tags_where += array('$not' => array('$in' => $notCategtagsId));
        }

        $mongo_where = array('tags'=> $tags_where);

        return self::_getByCategTags($mongo_where);
    }

    public static function getByCategTagsAndIds($categsId,$torrents_ids) {
        $mongo_where = array('tags'=> array('$all'=> $categsId), '_id'=> array('$in'=> $torrents_ids ) );
        return self::_getByCategTags($mongo_where);
    }

    protected static function _getByCategTags($mongo_where) {
      $mongo = getMongoDb();
      $count = mem2_get($mongo_where);
      if ($count == false) {
          $count = $mongo->torrents->find( $mongo_where )->count();
          mem2_set($mongo_where, $count, 60);
      }
      if (is_numeric($count) && $count > 0) {
            $rows = $mongo->torrents->find( $mongo_where,
                                       array('_id'=>1) )->
                                       sort( array('_id'=>-1) )->
                                       skip( self::$skip )->
                                       limit( self::$torrentsperpage );
            $torrents_id = array();
            if ($rows->hasNext() ) {
                foreach($rows AS $row) {
                    $torrents_id[] = $row['_id'];
                }
            }
            return array($count,$torrents_id);
      }
    }

    public static function mkMagnetLink($torrentId, $infoHash, $tFileName) {
        $passkeyQParam = User::getPasskeyForDownloadWithQueryParam();

        $announce_url = get_announce_url($torrentId) . $passkeyQParam;
        $info_hash_sha1 = unpack("H*",$infoHash);
        $info_hash_sha1 = $info_hash_sha1[1];

        return sprintf('magnet:?xt=urn:btih:%s&dn=%s&tr=%s&tr=%s&tr=%s&tr=%s"',
            rawurlencode($info_hash_sha1),
            rawurlencode($tFileName),
            rawurlencode($announce_url),
            rawurlencode('udp://tracker.publicbt.com:80/announce'),
            rawurlencode('udp://tracker.openbittorrent.com:80/announce')
            //rawurlencode('http://' . $passkeyQParam)
        );
    }

    public static function torrentDescriptionArray($torrent_id) {
        $description = self::torrentDetailsWithDescriptionArray($torrent_id);
        return $row['descr_ar_object'];
    }

    public static function torrentDetailsWithDescriptionArray($torrent_id) {
        $row = fetchRow('
            SELECT td.id, td.descr_ar, t.category
            FROM torrents_details AS td
            LEFT JOIN torrents AS t ON t.id = td.id
            WHERE td.id = :id',

            array("id" => $torrent_id)
        );
        $row['descr_ar_object'] = unserialize($row['descr_ar']);
        return $row;
    }

    /**
     * Get the Categtags
     * @param  Int $torrent_id
     * @return Array[CategTag]
     */
    public static function torrentCategTags($torrent_id) {
        $row = fetchRow('
            SELECT GROUP_CONCAT(catetag) AS catetags
            FROM torrents
            LEFT JOIN torrents_catetags_index ON torrents.id = torrents_catetags_index.torrent
            WHERE torrents.id = :id',

            array("id" => $torrent_id) );
        $categtags = explode(',', $row['catetags']);

        $categtags_arr = array();

        foreach($categtags AS $categtag) {
            $categtag = new CategTag($categtag);
            if ($categtag->isEmpty()) continue;
            $categtags_arr[] = $categtag;
        }

        return $categtags_arr;
    }

    /**
     * Is the specific categtagId inside the array
     * @param  Array[CategTag]  $categtags
     * @param  Int  $categtagId Categtag we are looking for
     * @return boolean
     */
    public static function hasCategtag($categtags, $categtagId) {
      foreach ($categtags AS $loopId=>$categtag ) {
        if ($categtag->id == $categtagId) return true;
      }
      return false;
    }

    /**
     * Count number of occurencies of categtag inside categtags
     * @param  Array[CategTag] $categtags
     * @param  Int $categtagId Categtag we are looking for
     * @return Int             Number of occurences
     */
    public static function countCategtagWithParent($categtags, $categtagId) {
        $found = 0;
        foreach ($categtags AS $loopId=>$categtag) {
            if ($categtag->tag['father'] == $categtagId) {
                $found++;
            }
        }
        return $found;
    }

    public static function getTopTorrents() {
        $res = q("
                SELECT " . self::$sql_columns . ",
                    torrents.seeders + torrents.leechers + torrents.thanks + (torrents.comments / 5) AS rating,
                    torrents_imdb.imdb_tt
                FROM torrents
                " . self::$sql_joins . "
                WHERE torrents.added >= NOW() - INTERVAL 1 day AND category != 6 AND category != 5 AND torrents.size > 73400320
                      AND (torrents.moder_status='verificat' OR torrents.moder_status='neverificat' OR torrents.moder_status IS NULL)
                " . self::$sql_group_by . "
                ORDER BY rating DESC LIMIT 20
                ");
        $top = array();
        //This will be ussed in Torrenttable
        while ($row = mysql_fetch_assoc($res)) {
            $top[] = $row;
        }
        return array_slice($top, 0, 5);
    }

    /**
     * [allCategories description]
     * @param Array $order Id of categories
     */
    public static function allCategories($order) {
      $categories = fetchAll("SELECT id, name FROM categories order by name");
      $ordereded = array();
      $rest = array();


      foreach ($order as $orderId) {
        foreach ($categories as $category) {
          if ($category['id'] == $orderId) {
            $ordereded[] = $category;
          }
        }
      }

      foreach ($categories as $category) {
        if (array_search($category['id'], $order) === FALSE)
          $rest[] = $category;
      }

      return array_merge($ordereded, $rest);
    }
}


class Torrents_Reports {
    public static $signal_reasons = array(
      "virus"     =>"Virus",
      "bad_desc"  => "Torrentul nu coresponde descrierii",
      "bad_desc"  => "Descriere incorecta",
      "insuficient_desc" => "Descriere insuficienta",
      "insuficient_desc" => "Incalca reguli",
      "other" => "Altceva"
    );
    public static $signal_levels = array(
      "1" => "Foarte minor",
      "2" => "Minor",
      "3" => "Mediu",
      "4" => "Grav",
      "5" => "Foarte grav"
    );

    private static $countNonSolved_key = "torrents_reports_countNonSolved";

    public static function countNonSolved() {
      $query = "SELECT COUNT(*) FROM torrent_reports WHERE solved = 'no'";
      return fetchOne_memcache_with_key($query, self::$countNonSolved_key);
    }

    public static function cleanCounters() {
      clean_memcache_with_key(self::$countNonSolved_key);
    }
}

class Torrent_Dht {

  public static function markForDhtUpdateIfNeeded($torrent_row) {
    $updateOnlyHoursOld   = 24;
    $updateOnlySecondsOld = $updateOnlyHoursOld * 60 * 60;

    $error                        = !isset($torrent_row['dht_peers_job_started']) ||
                                    !isset($torrent_row['dht_peers_update_scheduled']);
    $dhtPeersJobStartedSecondsAgo = time() - strtotime($torrent_row['dht_peers_job_started']);
    $noUpdateIsScheduled          = $torrent_row['dht_peers_update_scheduled'] != 'yes';
    $expiredDht                   = $dhtPeersJobStartedSecondsAgo > $updateOnlySecondsOld;

    $needToUpdate                 = $expiredDht && $noUpdateIsScheduled && $error == false;

    if ($needToUpdate === false) {
      return false;
    }

    $torrent_id                   = $torrent_row['id'];

    q('UPDATE torrents
       SET torrents.dht_peers_update_scheduled = "yes"
       WHERE torrents.id                =  :id   AND
             dht_peers_update_scheduled != "yes" AND
             dht_peers_job_started      <  NOW() - INTERVAL ' . $updateOnlyHoursOld . ' HOUR',
       array('id' => $torrent_id)
     );

    cleanTorrentCache($torrent_id);
  }
}
