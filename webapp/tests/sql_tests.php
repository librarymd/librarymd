<?php
require "../include/bittorrent.php";

require '../vendor/autoload.php';

require_once('../vendor/simpletest/simpletest/autorun.php');

class TestOfSql extends UnitTestCase {

    function testSqlQuery() {
        $query_str = "SELECT * FROM users";
        $query = new Sql_Query($query_str);
        $this->assertTrue( $query->sql() == $query_str );
        $query->where("id = :id", array("id"=>"1"));
        $this->assertTrue( $query->sql() == "$query_str WHERE id = '1'");
        $query->where("id = :id", array("id"=>"2"));
        $this->assertTrue( $query->sql() == "$query_str WHERE id = '1' AND id = '2'");
    }

}
