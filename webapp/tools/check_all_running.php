<?php
require '../include/bittorrent.php';
include($WWW_ROOT . './sphinx/sphinxapi.php');

if (isset($_GET["json"]))
  error_reporting(E_ERROR);

function test_smtp($host, $port) {
  $fp = fsockopen($host, $port, $errno, $errstr, 5);
  if ($fp) {
    $data = fgets($fp, 128);
    return $data;
    ob_flush();
    fclose($fp);
  } else {
    return false;
  }
}

function test_dht_server() {
  global $globalDhtClientHost;

  $result = file_get_contents($globalDhtClientHost . '/alive.txt');

  return $result == "OK";
}

function test_sphinx() {
  global $sphinx_host, $sphinx_port;
  $sphinx_index = 'torrents_search';

  $cl = new SphinxClient();
  $cl->SetServer($sphinx_host, $sphinx_port );
  $sphinx_res = $cl->Query("just a test", $sphinx_index );

  if ($sphinx_res === false) {
    return $cl->GetLastError();
  } else {
    return true;
  }
}

class Results {
  public function __init__() {
    $this->result = array();
  }

  const ERROR = "error";
  const OK = "ok";

  const TYPE_SMTP = "smtp";
  const TYPE_DHT_DAEMON = "dhtDaemon";
  const TYPE_SPHINX = "sphinx";

  function error($type, $reason) {
    $this->add_result($type, self::ERROR, $reason);
  }

  function ok($type, $reason) {
    $this->add_result($type, self::OK, $reason);
  }

  function add_result($type, $result, $reason) {
    $this->result[$type] = array(
      "result" => $result,
      "reason" => $reason
    );
  }

  function json() {
    return json_encode($this->result);
  }

  function str() {
    $r = array();
    foreach ($this->result as $k => $v) {
      array_push($r, $k . ": " . $v["result"] . " => " . $v["reason"] );
    }
    return join($r, "<br/>\n");
  }
}

$allResults = new Results();

$result = test_smtp(ini_get("SMTP"), ini_get("smtp_port"));

if ($result === false) {
  $allResults->error(Results::TYPE_SMTP, "Cannot connect to smtp");
} else {
  if (strlen($result) > 10)
    $allResults->ok(Results::TYPE_SMTP, $result);
  else
    $allResults->error(Results::TYPE_SMTP, "not enough recieved data: " . $result);
}

if (test_dht_server()) {
  $allResults->ok(Results::TYPE_DHT_DAEMON, "1");
} else {
  $allResults->error(Results::TYPE_DHT_DAEMON, "0");
}


if (test_sphinx()) {
  $allResults->ok(Results::TYPE_SPHINX, "1");
} else {
  $allResults->error(Results::TYPE_SPHINX, "0");
}

if (isset($_GET['json'])) {
  header('Content-Type: application/json');
  echo $allResults->json();
} else
  echo $allResults->str();