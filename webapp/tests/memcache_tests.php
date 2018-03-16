<?php
require_once "../include/bittorrent.php";

require_once '../vendor/autoload.php';

require_once('../vendor/simpletest/simpletest/autorun.php');

class MemcacheTests extends UnitTestCase {

    function testSqlQuery() {
        mem_set("lolo_9000", "toto", 100);

        $test_key = "linksantiflood_99";
        mem_delete($test_key);
        $this->assertTrue(false === mem_get($test_key));
        mem_set($test_key, 0, 1000);
        mem_increment($test_key);
        $this->assertEqual(1,mem_get($test_key));
    }

}
