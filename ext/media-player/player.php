<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$nonce = isset( $_GET['nonce'] ) ? $_GET['nonce'] : '';

if ( ( ! wp_verify_nonce( $nonce ) || ! function_exists( 'HTE_Media_Player' ) ) && ! HOCWP_THEME_DEVELOPING ) {
	return;
}

$src = isset( $_GET['src'] ) ? $_GET['src'] : '';

if ( empty( $src ) && ! HTE_Media_Player()->hide_source ) {
	return;
}

$post_id = isset( $_GET['post_id'] ) ? $_GET['post_id'] : '';

$domain = HT()->get_domain_name( $src );

$thumbnail = isset( $_GET['thumbnail'] ) ? $_GET['thumbnail'] : '';

switch ( $domain ) {
	case 'www.drive.google.com':
	case 'drive.google.com':
		$src = HTE_Media_Player()->get_google_drive_url( $src );
		break;
	case 'www.facebook.com':
	case 'facebook.com':
		$src = HTE_Media_Player()->get_facebook_url( $src );
		break;
}

if ( ! defined( 'HTE_MEDIA_PLAYER' ) ) {
	define( 'HTE_MEDIA_PLAYER', true );
}

$poster = HTE_Media_Player()->get_background_url();

if ( empty( $thumbnail ) && HT()->is_positive_number( $post_id ) ) {
	$thumbnail = get_post_meta( $post_id, '_thumbnail_url', true );
}

if ( ! empty( $thumbnail ) ) {
	$poster = $thumbnail;
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
			overflow-x: hidden;
		}
	</style>
</head>
<body>
<div class="embedded">
	<?php
	if ( HT_Util()->get_theme_option( 'jwplayer', '', HTE_Media_Player()->option_name ) ) {
		do_action( 'hocwp_theme_extension_media_player_load_jwplayer', $src, $poster, $post_id );
	} else {
		?>
		<video id="videoPlayer" controls>
			<source src="<?php echo esc_url( $src ); ?>">
		</video>
		<script>
			jQuery(document).ready(function ($) {
				$('#videoPlayer').mediaelementplayer({
					videoWidth: "100%",
					videoHeight: "100%",
					enableAutosize: true,
					controls: true,
					success: function (mediaElement, originalNode, instance) {
						instance.setPoster("<?php echo $poster; ?>");
					}
				});
			});
		</script>
		<?php
	}
	?>
</div>
<?php do_action( 'hocwp_theme_extension_media_player_footer' ); ?>
</body>
</html>