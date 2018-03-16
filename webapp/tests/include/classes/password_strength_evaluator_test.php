<?php
require "../include/classes/password_strength_evaluator.php";

require '../vendor/autoload.php';

require_once('../vendor/simpletest/simpletest/autorun.php');

class TestOfSql extends UnitTestCase {

    function testWeakPassword() {
        $tested_password = "1234";
        $this->assertTrue( PasswordStrengthEvaluator::is_weak($tested_password) === true);
    }

    function testBetterPassword() {
        $tested_password = "something_else";
        $this->assertTrue( PasswordStrengthEvaluator::is_weak($tested_password) === false);
    }

}
