<?php
session_start();
$captcha = isset($_SESSION['sb_captcha']) ? $_SESSION['sb_captcha'] : '';
$captcha = json_decode($captcha);
$string = isset($captcha->code) ? $captcha->code : '';
$expired = isset($captcha->expire) ? $captcha->expire : '';
$timestamp = strtotime(date('Y-m-d H:i:s'));
header('Content-Type: image/png');
$angle = rand(-60, 60);
while($angle < 10 && $angle > -10) {
	$angle = rand(-60, 60);
}
$bg_captcha = rand(1, 10);
$im = imagecreatefromjpeg('bg-captcha-' . $bg_captcha . '.jpg');
$size = 20;
$white = imagecolorallocate($im, 255, 255, 255);
$grey = imagecolorallocate($im, 128, 128, 128);
$black = imagecolorallocate($im, 0, 0, 0);
$captcha_color = imagecolorallocate($im, 230, 160, 50);
$lighter_grey = imagecolorallocate($im, 230, 230, 230);
$text = $string;
$font_captcha = rand(1, 5);
$font = 'font-captcha-' . $font_captcha . '.ttf';
$pos_x = rand(0, 60);
$pos_y = rand(10, 20);
$temp_text = rand(1000, 9999);
imagettftext($im, $size, $angle, $pos_x, $pos_y, $lighter_grey, $font, $temp_text);
$max_pos_x = 65 - ($font_captcha * 2);
if(strlen($string) > 4) {
	$max_pos_x = 33;
}
$pos_x = rand(0, $max_pos_x);
$pos_y = rand(22, 24);
imagettftext($im, $size, 0, $pos_x, $pos_y, $captcha_color, $font, $text);
imagepng($im);
imagedestroy($im);