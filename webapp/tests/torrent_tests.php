<?php
require "../include/bittorrent.php";

require '../vendor/autoload.php';

require_once('../vendor/simpletest/simpletest/autorun.php');

class TestOfTorrents extends UnitTestCase {

    function testIsHandlingBase32() {
      $magnet = 'magnet:?xt=urn:btih:K3CC5I4JSFYKPNSG6NTPEZ7RUXANISSD&dn=This%20Is%20Test%2028.0.0.137&tr=';

      $result = magnet_extract_hashinfo($magnet);

      $this->assertEqual($result['infohash'], "56c42ea3899170a7b646f366f267f1a5c0d44a43");
      $this->assertEqual($result['filename'], 'This Is Test 28.0.0.137');
    }

    function testIsHandlingBase16() {
      $magnet = 'magnet:?xt=urn:btih:9f9165d9a281a9b8e782cd5176bbcc8256fd1870&dn=Ubuntu+16.04.1+LTS+Desktop+64-bit&tr=';

      $result = magnet_extract_hashinfo($magnet);

      $this->assertEqual($result['infohash'], "9f9165d9a281a9b8e782cd5176bbcc8256fd1870");
      $this->assertEqual($result['filename'], 'Ubuntu 16.04.1 LTS Desktop 64-bit');
    }

}
