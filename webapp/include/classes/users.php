<?php
class Users {
    public static function isLogged() {
        global $CURUSER;
        return isset($CURUSER['id']) && $CURUSER['id']>0;
    }

    public static function getById($id) {
        return fetchRow_memcache( sqlEscapeBind(
          "SELECT users.*, u_du.uploaded, u_du.downloaded, users_inbox.received, users_inbox.sended, users_inbox.unread
        FROM users
        LEFT JOIN users_down_up AS u_du ON users.id = u_du.id
        LEFT JOIN users_inbox ON users.id = users_inbox.id
        WHERE users.id = :id", array('id'=>$id)), 300 );
    }
}
