<?php

/**
 * Class User
 * Methods for the current logged user
 */
class User {
    public static $currentUser = array();

    const DOWNLOAD_NO_PASSKEY = 'download_no_passkey';
    const PASSKEY = 'passkey';

    public static function cleanCache($user_id) {
      mem_delete('users_'.$user_id);
    }

    public static function fetchCurrentUser($user_id) {
        $user = mem_get('users_'.$user_id);

        if ($user === FALSE) {
            $row = fetchRow("SELECT users.*, u_du.uploaded, u_du.downloaded, users_inbox.received,
                                    users_inbox.sended, users_inbox.unread,
                                    users_inbox.unread_notifications, users_inbox.last_read_global_notification
                             FROM users
                             LEFT JOIN users_down_up AS u_du ON users.id = u_du.id
                             LEFT JOIN users_inbox ON users.id = users_inbox.id
                             WHERE users.id = :id", array("id" => $user_id) );
            if (!$row) return;
            mem_set('users_'.$user_id, serialize($row), 600);
        } else {
            $row = unserialize($user);
        }

        // Fill the lastest last_browse_see
        $last_browse_see = mem_get('last_browse_see'.$user_id);
        if ($last_browse_see == FALSE) {
            $last_browse_see = fetchOne('SELECT last_browse_see FROM users_hot WHERE id=' . $user_id);
            @mem_set('last_browse_see'.$user_id, $last_browse_see, 86400);
        }
        $row['last_browse_see'] = $last_browse_see;

        if (!strlen($row['browserHash'])) {
            mem_delete('users_'.$user_id);
        }

        $row['fotbalist'] = (have_flag('fotbalist'))?'yes':'no';

        //Translate 1->english, ruled by lang.conf from lang dir
        $row['2letter_lang'] = lang_translator($row['language']);

        return $row;
    }

    public static function hasValidStatus($row) {
        if ($row['enabled'] != 'yes' || $row['status'] != 'confirmed') return false;
        else return true;
    }

    public static function setUserAsCurrent($user) {
        if (self::hasValidStatus($user)) {
            self::$currentUser = $user;
            $GLOBALS['CURUSER'] = $user;
        }
    }

    public static function currentUserId() {
        return self::$currentUser["id"];
    }

    public static function currentUserName() {
        return self::$currentUser["username"];
    }

    public static function id() {
        return self::currentUserId();
    }

    public static function isAuthenticated() {
        return !empty(self::$currentUser) && self::id() > 0;
    }

    public static function currentUserLang() {
        return get_lang();
    }

    public static function lang() {
        return self::currentUserLang();
    }

    public static function data() {
        return self::$currentUser;
    }

    public static function current() {
      return self::data();
    }

    public static function signedUpRecently() {
        if (get_config_variable('general', 'allow_fresh_new_users_write_messages') == true) {
          return false;
        } else {
          $user = self::current();
          $deltaAddedLessThan = (time() - strtotime($user['added'])) < 60*60*24*14;
          return $deltaAddedLessThan;
        }
    }

    public static function haveRightsToPostBan($forum_id) {
        if (get_user_class() >= UC_MODERATOR) {
            return true;
        }
        if (have_flag('forum_moderator')) {
            $moders = Forum::getCatModers($forum_id);
            foreach ($moders as $moder) {
                if ($moder['id'] == self::currentUserId()) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function hasStatus($status) {
        if (!isset(self::$currentUser["class"])) return false;
        return self::$currentUser["class"] >= $status;
    }

    public static function noPasskeyForDownload() {
      $user = self::current();
      return $user[self::DOWNLOAD_NO_PASSKEY] == 1;
    }

    public static function passkeyForDownload() {
      return !self::noPasskeyForDownload();
    }

    public static function getPasskeyForDownload() {
      $user = self::current();
      if (self::passkeyForDownload()) {
        return $user[self::PASSKEY];
      } else {
        return '';
      }
    }

    public static function getPasskeyForDownloadWithQueryParam() {
      return (self::passkeyForDownload() ? '?passkey=' . self::getPasskeyForDownload() : '' );
    }

    public static function findByUsername($username) {
      return fetchRow('SELECT * FROM users WHERE username=:username', array('username'=>$username) );
    }

    public static function getAvatarUrl() {
      $user = self::current();
      list($notused, $notused, $currentUserAvatarUrl) = getAvatarLink(
        $user['id'], $user['avatar'], $user['avatar_version']);
      return $currentUserAvatarUrl;
    }
}


class User_Icons {
  public static $iconFields = array('id', 'name', 'url', 'img');

  // This should be only used for bootstrap, otherwise DB data should be used
  public static $initialIcons = array(
    array("id"=>1, "name" => "Participant la concursul Poetry Contest", "url" => "/forum.php?action=viewforum&forumid=6&subcat=145", "img" => "/pic/user_state/poezii_contest_v2.png"),
    array("id"=>2, "name" => "Participant la concursul Fotografi Amatori", "url" => "/forum.php?action=viewforum&forumid=6&subcat=152", "img" => "/pic/user_state/photo_contest.png"),
    array("id"=>3, "name" => "Designer", "url" => "/forum.php?action=viewtopic&topicid=88147684", "img" => "/pic/user_state/designers_contest.png"),
    array("id"=>4, "name" => "Muzician", "url" => "/forum.php?action=viewtopic&topicid=88147684", "img" => "/pic/user_state/music_contest_v2.png"),
    array("id"=>5, "name" => "Participant la concursul Counter-Strike Masters Tournament", "url" => "/forum.php?action=viewtopic&topicid=88147684", "img" => "/pic/user_state/cs_contest.png"),
    array("id"=>6, "name" => "Cel mai bun bucătar", "url" => "/forum.php?action=viewforum&forumid=6&subcat=154", "img" => "/pic/user_state/chef.png")
  );

  public static function bootstrapIconsDb() {
    Q('INSERT INTO avps (value, arg) VALUES (:value, :name)',
      array('name'=>self::uniqueKeyName(), 'value'=> serialize(self::$initialIcons))
    );
  }

  public static function icons() {
    return self::fetchCached();
  }

  public static function fetchCached() {
    $cached = mem_get_multi_get(self::uniqueKeyName());
    if ($cached == false) {
      $cached = mem_get(self::uniqueKeyName());
    }

    if ($cached == false) {
      $fetched = self::fetch();
      if ($fetched == false) {
        self::bootstrapIconsDb();
      }
      mem_set(self::uniqueKeyName(), $fetched);
      return $fetched;
    }

    return $cached;
  }

  public static function fetch() {
    return unserialize(fetchOne('SELECT value FROM avps WHERE arg=:name', array('name'=>self::uniqueKeyName()) ));
  }

  public static function expireCache() {
    mem_delete(self::uniqueKeyName());
  }

  public static function uniqueKeyName() {
    return "userIconsConfig";
  }

  public static function update($newIconsArray) {
    assert(is_array($newIconsArray));

    $serialized = serialize($newIconsArray);


    Q('UPDATE avps SET value=:value WHERE arg=:name', array('name'=>self::uniqueKeyName(), 'value'=> $serialized) );
    self::expireCache();
  }

  public static function addOne($additionalIcon) {
    assert(is_array($additionalIcon) &&
           isset($additionalIcon[self::$iconFields[0]]) );

    $icons = self::icons();
    $icons[] = $additionalIcon;

    self::update($icons);
  }

  public static function insertEmpty() {
    Q('INSERT IGNORE avps SET arg=:name, value=:value', array('name'=>self::uniqueKeyName(), 'value'=> serialize(array())) );
  }

  // Shortcut function

  public static function getIconById($id) {
    foreach (self::icons() as $icon) {
      if ($icon['id'] == $id)
        return $icon;
    }
  }

  public static function getNameById($id) {
    $icon = self::getIconById($id);
    if (!empty($icon))
      return $icon['name'];
  }

  public static function getInfoUrlById($id) {
    $icon = self::getIconById($id);
    if (!empty($icon)) return $icon['url'];
  }

  public static function getImgUrlById($id) {
    $icon = self::getIconById($id);
    if (!empty($icon)) return $icon['img'];
  }
}

class User_Icon {
  public function __construct($id) {
    $this->id = $id;
  }

  public function name() {
    return User_Icons::getNameById($this->id);
  }

  public function infoUrl() {
    return User_Icons::getInfoUrlById($this->id);
  }

  public function imgUrl() {
    return User_Icons::getImgUrlById($this->id);
  }
}