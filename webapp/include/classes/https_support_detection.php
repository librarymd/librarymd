<?php

class HttpsSupportDetection {

  private static $HTTPS_ABLE_COOKIE_NAME = "HTTPS_ABLE";
  private static $HTTPS_ABLE_COOKIE_VALUE_ON = "yes";
  private static $HTTPS_ABLE_ATTEMPT_COOKIE_NAME = "HTTPS_ABLE_ATTEMPT";

  public static function https_attempt_number() {
    return (
      isset($_COOKIE[self::$HTTPS_ABLE_ATTEMPT_COOKIE_NAME]) ?
      intval($_COOKIE[self::$HTTPS_ABLE_ATTEMPT_COOKIE_NAME]) :
      0
    );
  }

  public static function didnt_make_too_many_attempts() {
    return self::https_attempt_number() <= 10;
  }

  public static function https_detector_iframe() {
    if (!is_https() && !self::is_https_supported() && self::didnt_make_too_many_attempts()) {
      $onloadJs = (self::https_attempt_number() == 0) ? 'location.reload();' : '';
      echo sprintf('<iframe src="https://%s/check_ssl_support.php" style="visibility: hidden; width: 1px; height: 1px" onLoad="%s"></iframe>', get_normalized_host_name(), $onloadJs);
    }
  }

  public static function increment_https_check_attempts() {
    if (!is_https() && !self::is_https_supported() && self::didnt_make_too_many_attempts()) {
      $https_attempts = self::https_attempt_number() + 1;
      set_cookie_fix_domain('HTTPS_ABLE_ATTEMPT', $https_attempts, time()+60*60*24*30, "/", false, true);
    }
  }

  public static function is_https_supported() {
    return @$_COOKIE[self::$HTTPS_ABLE_COOKIE_NAME] === self::$HTTPS_ABLE_COOKIE_VALUE_ON;
  }

  public static function get_https_enabled_cookie_name() {
    return self::$HTTPS_ABLE_COOKIE_NAME;
  }

  public static function set_https_supported() {
    $expires = time()+60*60*24*1000;
    set_cookie_fix_domain(self::$HTTPS_ABLE_COOKIE_NAME, self::$HTTPS_ABLE_COOKIE_VALUE_ON, $expires, "/", false, false);
  }

}