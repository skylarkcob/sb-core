<?php
defined( 'ABSPATH' ) || exit;

$action = $_GET['action'] ?? '';
?>
<div class="page-account">
    <div class="container">
		<?php
		if ( is_user_logged_in() ) {
			include __DIR__ . '/page-edit-profile.php';
		} else {
			if ( 'register' == $action ) {
				include __DIR__ . '/page-register.php';
			} elseif ( 'lostpassword' == $action ) {
				include __DIR__ . '/page-lostpassword.php';
			} else {
				include __DIR__ . '/page-login.php';
			}
		}
		?>
    </div>
</div>