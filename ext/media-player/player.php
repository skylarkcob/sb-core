<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$nonce = isset( $_GET['nonce'] ) ? $_GET['nonce'] : '';

if ( ( ! wp_verify_nonce( $nonce ) || ! function_exists( 'HTE_Media_Player' ) ) && ! HOCWP_THEME_DEVELOPING ) {
	return;
}

if ( ! defined( 'HTE_MEDIA_PLAYER' ) ) {
	define( 'HTE_MEDIA_PLAYER', true );
}
?>
<!DOCTYPE html>
<html style="margin: 0 !important; padding: 0 !important;">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<?php wp_head(); ?>
	<style>
		html,
		body {
			background: #000;
			padding: 0;
			margin: 0;
			height: 100%;
			width: 100%;
			overflow: hidden;
		}

		#videoPlayer,
		body > .embedded,
		body {
			position: fixed;
			z-index: 9999;
			left: 0;
			right: 0;
			top: 0;
			bottom: 0;
			width: 100%;
			height: 100%;
		}
	</style>
</head>
<body>
<?php HTE_Media_Player()->html(); ?> ?>
</body>
</html>