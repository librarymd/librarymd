<?php

class Sql_Query {
    private $sql = "";
    private $where = array();

    public function __construct($sql) {
        $this->sql = $sql;
    }
    public function where($sql_part, $params) {
        $this->where[] = sqlEscapeBind($sql_part, $params);
    }
    public function sql() {
        $where_sql = '';
        if (count($this->where) > 0) {
            $where_sql = " WHERE " . join(" AND ", $this->where);
        }
        return $this->sql . $where_sql;
    }
}