<?php

class Sql_Counter {
  private $sql = "";
  private $limit = "";

  private $key = "";
  private $count = NULL;

  private $state = "query";

  function __construct($sql, $limit = "") {
    $select_str = "SELECT";
    $without_select = $sql;
    if (substr($sql, 0, strlen($select_str)) == $select_str) {
      $without_select = substr($sql, strlen($select_str));
    }

    $this->sql = $without_select;
    $this->limit = $limit;

    $this->init();
  }

  function init() {
    $this->key = "sql_count_" . md5($this->sql);
    $this->count = mem_get($this->key);
  }

  function need_to_calc() {
    return $this->count == NULL;
  }

  function query() {
    $query = $this->sql . " " . $this->limit;
    $with_found = ($this->need_to_calc() ? " SQL_CALC_FOUND_ROWS " : "") . $query;
    $this->state = "count";

    return "SELECT " . $with_found;
  }

  function count() {
    if ($this->state != "count") {
      trigger_error("You must first query");
    }

    if ($this->need_to_calc()) {
      $this->count = q_singleval('SELECT FOUND_ROWS()');
      mem_set($this->key, $this->count, 60);
    }

    return $this->count;
  }
}
