<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$type_of_post = isset( $_POST['type_of_post'] ) ? $_POST['type_of_post'] : '';

if ( 'normal' != $type_of_post ) {
	$vip_post_added_message = HT_Options()->get_tab( 'vip_post_added_message', '', 'vip' );

	if ( ! empty( $vip_post_added_message ) ) {
		$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : '';

		$obj = get_post( $post_id );

		if ( $obj instanceof WP_Post ) {
			$contact_name = isset( $_POST['NameContact'] ) ? $_POST['NameContact'] : '';
			$total_cost   = isset( $_POST['total_cost'] ) ? $_POST['total_cost'] : '';
			$total_cost   = floatval( $total_cost );
			$total_cost   = number_format( $total_cost, 2 );

			$search = array(
				'[FULLNAME]',
				'[POSTTITLE]',
				'[TOTAL]',
				'[POSTID]'
			);

			$replace = array(
				$contact_name,
				get_the_title( $obj ),
				'$' . $total_cost,
				$post_id
			);

			$vip_post_added_message = str_replace( $search, $replace, $vip_post_added_message );
			?>
			<div class="post-added-success">
				<?php echo wpautop( $vip_post_added_message ); ?>
			</div>
			<?php
		}
	}
}