<?php

class Security {
    public static function hash($message_str) {
        global $application_secret;
        if (strlen($application_secret) == 0) die('Set $application_secret in include/secrets.php');

        return hash_hmac('sha256', $message_str, $application_secret);
    }
}

/**
 * Data is in clear text + a check sum that only server can generate
 */
class Hmac_Session {
    // @type array
    private $message = array();
    private $hash;
    private $session;
    private $secret_key;

    function set($name, $value) {
        $this->message[$name] = $value;
    }

    function get($name) {
        if (isset($this->message[$name]))
            return $this->message[$name];
    }

    function hash($message_str) {
        return Security::hash($message_str);
    }

    function message_to_str() {
        $parts = array();

        foreach ($this->message as $key => $value) {
             $parts[] = urlencode($key) . "=" . urlencode($value);
        }
        return implode("&", $parts);
    }

    function str_to_message($str) {
        $message = array();
        $parts = explode("&", $str);
        foreach ($parts as $part) {
            $kv = explode("=",$part);
            if (count($kv) != 2) return;
            $message[urldecode($kv[0])] = urldecode($kv[1]);
        }
        return $message;
    }

    function export() {
        $message_str = $this->message_to_str();
        return $this->hash($message_str) . "-" . $message_str;
    }

    /**
     * Load exported message with authentication code
     * @param  String $auth_message
     * @return Boolean               true for auntentication succes, false otherwise
     */
    function load($auth_message) {
        if (strlen($auth_message) > 512) $auth_message = "";
        $this->auth_message = $auth_message;

        if (strlen($auth_message) == 0 || strstr($auth_message, "-") === false) return false;

        $authentication_message = explode("-", $auth_message);
        if (count($authentication_message) != 2) return;

        $authentication = $authentication_message[0];
        $message = $authentication_message[1];

        if ($this->hash($message) !== $authentication)
            return false;

        $this->message = $this->str_to_message($message);
        return true;
    }

    private function keyHashing($secret) {
        return substr(Security::hash($secret), 0, 16);
    }

    function setKey($secret) {
        $this->set("key", $this->keyHashing($secret) );
    }

    function verifyKey($secret) {
        $hash = $this->get("key");
        return $hash === $this->keyHashing($secret);
    }
}