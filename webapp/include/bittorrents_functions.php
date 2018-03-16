<?php

function get_normalized_host_name() {
  return get_normalized_host_name_value($_SERVER['HTTP_HOST']);
}

function get_normalized_host_name_value($hostname) {
    $cleaned_hostname = str_replace( array('http://','https://','www.'), array('','',''), strtolower($hostname));
    $hostname_without_path = explode('/',$cleaned_hostname);
    return $hostname_without_path[0];
}

function is_https() {
  return @$_SERVER['HTTP_HTTPS'] == "on" || @$_SERVER["HTTP_X_FORWARDED_PROTO"] == 'https';
}

function get_server_protocol() {
  return is_https() ? "https" : "http";
}
