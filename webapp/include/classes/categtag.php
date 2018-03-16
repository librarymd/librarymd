<?php

class CategTag {
    public $id;
    public $tag;
    private $ancestors;
    private static $_get_all_catetags = null;
    private static $categtagById = null;
    
    function __construct($id) {
        $this->id = $id;
        
        $this->load();
    }
    
    function isEmpty() {
        return empty($this->tag);
    }

    function getName() {
        return $this->tag['name_'.get_lang()];
    }

    function getId() {
        return $this->id;
    }

    public static function getAllCategtagsSql($visible='') {
        $sql_append = '';
        if ($visible != '') {
            $sql_append = sqlEscapeBind(' WHERE visible=:g',array('g'=>$visible));
        }
        return 'SELECT *, name_'.get_lang().' AS name FROM torrents_catetags'.$sql_append . ' ORDER BY orderi,name';
    }

    public static function getAllCategtags($visible='') {
        if (self::$_get_all_catetags != null) {
            return self::$_get_all_catetags;
        }
        $sql = self::getAllCategtagsSql($visible);
        $res = mem2_get($sql);
        if ($res == false) {
            $res = fetchAll($sql);
            mem2_set($sql,serialize($res));
        } else {
            $res = unserialize($res);
        }
        self::$_get_all_catetags = $res;
        return $res;
    }
    
    function load() {
        if (self::$categtagById == null) {
            $rows = self::getAllCategtags();
            self::$categtagById = array_set_index($rows,'id');
        }

        if (!isset(self::$categtagById[$this->id])) return false;

        $this->tag = self::$categtagById[$this->id][0];
        return true;
    }
    
    function getAncestors() {
        global $categtagById;
        if (!empty($this->ancestors))
            return $this->ancestors;
        $ancestors = array();
        
        if ($this->tag['father'] == 0) return $ancestors;
        
        $ancestor = $categtagById[ $this->tag['father'] ][0];
        $ancestors[] = $ancestor;
        
        while( $ancestor['father'] != 0 ) {
            $ancestor = $categtagById[ $ancestor[ 'father' ] ][0];
            $ancestors[] = $ancestor;
        }
        
        $this->ancestors = $ancestors;
        return $ancestors;
    }
    
    function getAcestorsPath() {
        $path = '';
        
        $ancestors = $this->getAncestors();
        
        foreach($ancestors AS $categtag_ancestor) {
            $path .= $categtag_ancestor['name_'.get_lang()].'->';
        }
        
        return $path;
    }
    
}