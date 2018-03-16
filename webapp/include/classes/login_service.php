<?php

class LoginService {
  private static $MAX_ATEMPTS_CAPTCHA = 7;

  public static function may_captcha_be_required($ip) {
    $have_tried_many_times_ip = self::tried_many_times_ip($ip);
    $is_cookie_not_set = false;

    return array(
      'result' => $have_tried_many_times_ip || $is_cookie_not_set,
      'reason' => "have_tried_many_times_ip $have_tried_many_times_ip, is_cookie_not_set $is_cookie_not_set"
    );
  }

  public static function is_captcha_required_verbose($ip, $username, $password) {
    $password_is_weak = PasswordStrengthEvaluator::is_weak($password);
    $tried_many_times_username = self::tried_many_times_username($username);

    $may_captcha_be_required = self::may_captcha_be_required($ip);
    $may_captcha_be_required_result = $may_captcha_be_required['result'];
    $may_captcha_be_required_reason = $may_captcha_be_required['reason'];

    if ($password_is_weak || $tried_many_times_username || $may_captcha_be_required_result) {
      $reason = "password_is_weak: $password_is_weak,
      tried_many_times_username: $tried_many_times_username,
      $may_captcha_be_required_reason";

      return array("result"=>true, "reason"=>$reason);
    } else {
      return array("result"=>false,"reason"=>"");
    }
  }

  public static function is_captcha_required($ip, $username, $password) {
    $verbose = self::is_captcha_required_verbose($ip, $username, $password);
    return $verbose["result"];
  }

  private static function tried_many_times_ip($ip) {
    return get_login_attempt_faild($ip) >= self::$MAX_ATEMPTS_CAPTCHA;
  }

  private static function tried_many_times_username($username) {
    return get_login_attempt_faild($username) >= self::$MAX_ATEMPTS_CAPTCHA;
  }

  public static function verify_captcha($captcha_response) {
    global $google_captcha_secret;

    $data = array("secret"=>$google_captcha_secret, "response"=>$captcha_response);
    $response = self::post_data("https://www.google.com/recaptcha/api/siteverify", $data);
    $decoded = json_decode($response);
    if ($decoded !== null && $decoded !== false && is_object($decoded)) {
      return $decoded->success;
    }
    return false;
  }

  public static function post_data($url, $data) {
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
  }

}