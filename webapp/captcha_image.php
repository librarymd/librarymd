<?php
require 'include/bittorrent.php';

$id = get('id');

if (!strlen($id)) {
	die('um');
}

// Can be used only once
if (mem_get('captcha_'.$id) != false) {
	die('already generated');
}

class CaptchaSecurityImages {

   var $font = 'pic/monofont.ttf';

   function generateCode($characters) {
      /* list all possible characters, similar looking characters and vowels have been removed */
      $possible = '23456789bcdfghjkmnpqrstvwxyz';
      $code = '';
      $i = 0;
      while ($i < $characters) {
         $code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
         $i++;
      }
      return $code;
   }

   function CaptchaSecurityImages($width='120',$height='40',$characters='6') {
      global $id;
      $code = $this->generateCode($characters);

      // Store that in cache
      mem_set('captcha_'.$id, $code, 1200);

      /* font size will be 75% of the image height */
      $font_size = $height * 0.75;
      $image = imagecreate($width, $height) or die('Cannot initialize new GD image stream');
      /* set the colours */
      $background_color = imagecolorallocate($image, 255, 255, 255);
      $text_color = imagecolorallocate($image, 20, rand(40,120), rand(40,120));
      $noise_color = imagecolorallocate($image, rand(40,120), rand(40,120), rand(40,120));
      /* generate random dots in background */
      for( $i=0; $i<($width*$height)/3; $i++ ) {
         imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
      }
      /* generate random lines in background */
      for( $i=0; $i<($width*$height)/150; $i++ ) {
         imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);
      }
      /* create textbox and add text */
      $textbox = imagettfbbox($font_size, 0, $this->font, $code) or die('Error in imagettfbbox function');
      $x = ($width - $textbox[4])/2;
      $y = ($height - $textbox[5])/2;
      imagettftext($image, $font_size, 0, $x, $y, $text_color, $this->font , $code) or die('Error in imagettftext function');
      /* output captcha image to browser */
      imagejpeg($image);
      imagedestroy($image);
   }

}

$width = '180';
$height = '60';
$characters = rand(2,6);

header('Content-Type: image/jpeg');
$captcha = new CaptchaSecurityImages($width,$height,$characters);

?>