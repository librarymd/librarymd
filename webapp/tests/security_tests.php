<?php
require_once "../include/bittorrent.php";

require_once '../vendor/autoload.php';

require_once('../vendor/simpletest/simpletest/autorun.php');

class TestOfSecurity extends UnitTestCase {

    function testHmacSession() {
        global $application_secret;

        $test_val = "|&z";

        $hmac_data = new Hmac_Session();
        $hmac_data->set("id", $test_val);
        $hmac_data->set("id2", $test_val);
        $hmac_data->setKey("abc");
        $exported = $hmac_data->export();

        $hmac_data_tested = new Hmac_Session();
        $this->assertTrue( $hmac_data_tested->load($exported) );
        $this->assertTrue( $hmac_data_tested->get("id") === $test_val );
        $this->assertTrue( $hmac_data_tested->get("id2") === $test_val );
        $this->assertTrue( $hmac_data_tested->verifyKey("abc") );

    }

    function testSqlEscapeBind() {
      $binded = sqlEscapeBind("SELECT * FROM username=:username OR username=:username", array("username" => 'username'));
      $this->assertEqual($binded, "SELECT * FROM username='username' OR username='username'");

      $binded = sqlEscapeBind("SELECT * FROM username=:username OR username=:username2",
        array("username" => ':username2--', 'username2' => 'malicious')
      );
      $this->assertEqual($binded, "SELECT * FROM username=':username2--' OR username='malicious'");

      $binded = sqlEscapeBind("SELECT * FROM username=:username",
        array("username" => "'lo")
      );

      $this->assertEqual($binded, "SELECT * FROM username='\'lo'");

      $binded = sqlEscapeBind("SELECT * FROM username=?",array("uzer'"));
      $this->assertEqual($binded, "SELECT * FROM username='uzer\''");
    }



}
