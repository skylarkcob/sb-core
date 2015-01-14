<?php
session_start();
$_SESSION['sb_captcha'] = rand(1000, 9999);
?>
<div>
<img alt="" src="captcha.php">
</div>
<?php
echo $_SESSION['sb_captcha'];