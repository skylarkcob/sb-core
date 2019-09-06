<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_add_post_frontend_user_profile_fields( $user ) {
	global $hocwp_theme;

	$options    = $hocwp_theme->options;
	$post_price = isset( $options['vip']['post_price'] ) ? $options['vip']['post_price'] : '';
	$post_price = absint( $post_price );

	if ( 0 < $post_price ) {
		$user_id = $user->ID;

		$coin = get_user_meta( $user_id, 'coin', true );
		$coin = absint( $coin );

		$disabled = ( current_user_can( 'edit_users' ) ) ? false : true;
		?>
		<tr class="user-coin-wrap">
			<th>
				<label for="coin"><?php _e( 'Coin', 'sb-core' ); ?></label>
			</th>
			<td>
				<input type="number" name="coin" id="coin" min="0" value="<?php echo esc_attr( $coin ); ?>"
				       class="medium-text"<?php disabled( $disabled, true ); ?>>
			</td>
		</tr>
		<?php
	}
}

add_action( 'hocwp_theme_user_profile_fields', 'hocwp_add_post_frontend_user_profile_fields' );

function hocwp_add_post_frontend_user_profile_updated( $user_id ) {
	if ( current_user_can( 'edit_users' ) ) {
		if ( isset( $_POST['coin'] ) ) {
			update_user_meta( $user_id, 'coin', absint( $_POST['coin'] ) );
		}
	}
}

add_action( 'hocwp_theme_user_profile_updated', 'hocwp_add_post_frontend_user_profile_updated' );