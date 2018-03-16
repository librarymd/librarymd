<?php
class Forum {
    public static function getForums($userlang, $id=null) {
        $query = new Sql_Query("SELECT id, name_$userlang AS name, minclassread FROM forums");
        if ($id != null) {
            $query->where("id = :id", array("id"=>$id));
        }
        return fetchAll($query->sql());
    }

    public static function getForumId($id, $userlang) {
        $forum = self::getForums($userlang, $id);
        return $forum[0];
    }

    public static function getCatModers($forumid = 0, $moderator_type = '') {
        $sql_part = '';
        if ($moderator_type != '') {
            $sql_part = sqlEscapeBind(" AND statut=:type", array( "type"=>$moderator_type) );
        }

        $catModers = fetchAll("
            SELECT fm.statut, u.username, u.id
            FROM forum_moderators AS fm
            LEFT JOIN users AS u ON (u.id=fm.user_id)
            WHERE forum_category_id=:id" . $sql_part, array('id' => (int)$forumid) );

        return $catModers;
    }

    public static function getForumsAsModerator($user_id, $userlang='ro') {
        return fetchAll(
            "SELECT fm.statut, f.name_ro, f.name_ru, f.id, name_$userlang AS name
            FROM forum_moderators AS fm
            LEFT JOIN forums AS f ON (fm.forum_category_id = f.id)
            WHERE fm.user_id=:id",
            array('id'=>$user_id));
    }



}