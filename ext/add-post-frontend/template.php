<?php
if ( ! is_user_logged_in() ) {
	$url = wp_login_url( get_the_permalink() );
	?>
    <script>
        window.location.href = "<?php echo $url; ?>";
    </script>
	<?php
} else {
	if ( ! current_user_can( 'edit_posts' ) ) {
		$url = home_url();
		?>
        <script>
            window.location.href = "<?php echo $url; ?>";
        </script>
		<?php
	}
}
global $hocwp_theme;
$options      = $hocwp_theme->options;
$post_price   = isset( $options['vip']['post_price'] ) ? $options['vip']['post_price'] : '';
$post_price   = absint( $post_price );
$post_type    = hocwp_add_post_frontend_post_type();
$taxonomies   = get_object_taxonomies( $post_type, OBJECT );
$title        = '';
$post_content = '';
$messages     = array();
if ( isset( $_POST['add_post_type'] ) ) {
	if ( ! HT_Util()->verify_nonce() ) {
		$messages[] = '<p class="alert alert-danger">' . __( 'Invalid form data.', 'hocwp-ext' ) . '</p>';
	} else {
		$title        = isset( $_POST['add_post_title'] ) ? $_POST['add_post_title'] : '';
		$post_content = isset( $_POST['add_post_content'] ) ? $_POST['add_post_content'] : '';
		$post_content = stripslashes( $post_content );
		$post_content = str_replace( '\\', '', $post_content );
		if ( ! empty( $title ) && ! empty( $post_content ) ) {
			$user_id = get_current_user_id();
			$enough  = true;
			if ( isset( $_POST['submit_vip'] ) ) {
				$coin = get_user_meta( $user_id, 'coin', true );
				$coin = absint( $coin );
				if ( $coin < $post_price ) {
					$enough = false;
				}
			}
			if ( $enough ) {
				$data = array(
					'post_type'    => $_POST['add_post_type'],
					'post_title'   => $title,
					'post_content' => $post_content,
					'post_status'  => 'pending',
					'post_author'  => $user_id
				);
				$id   = wp_insert_post( $data, true );
				if ( $id instanceof WP_Error ) {
					$messages[] = '<p class="alert alert-danger">' . $id->get_error_message() . '</p>';
				} else {
					$msg = sprintf( __( 'Your post has been added successfully. You can <a href="%s">click here</a> to update it.', 'hocwp-ext' ), get_edit_post_link( $id ) );

					$messages[] = '<p class="alert alert-success">' . $msg . '</p>';
					if ( isset( $_POST['submit_vip'] ) ) {
						$coin -= $post_price;
						update_user_meta( $user_id, 'coin', $coin );
						update_post_meta( $id, 'vip_expired', strtotime( '+1 day' ) );
					}
					foreach ( $taxonomies as $taxonomy ) {
						$name = 'add_' . $taxonomy->name;
						if ( isset( $_POST[ $name ] ) ) {
							$term_id = absint( $_POST[ $name ] );
							if ( HT()->is_positive_number( $term_id ) ) {
								wp_set_post_terms( $id, array( $term_id ), $taxonomy->name );
							}
						}
					}
					$title = '';

					$post_content = '';

					$_POST = array();
				}
			} else {
				$messages[] = '<p class="alert alert-danger">' . __( 'You do not have enough coin to post VIP content.', 'hocwp-ext' ) . '</p>';
			}
		} else {
			$messages[] = '<p class="alert alert-danger">' . __( 'Post title and post content must not be empty.', 'hocwp-ext' ) . '</p>';
		}
	}
}
?>
<div class="add-post-frontend">
    <h1 class="entry-title"><?php the_title(); ?></h1>
    <form method="post" enctype="multipart/form-data">
		<?php
		if ( 0 < count( $messages ) ) {
			foreach ( $messages as $message ) {
				echo $message;
			}
		}
		wp_nonce_field();
		?>
        <div class="form-group">
            <label for="post-title"><?php _e( 'Post title:', 'hocwp-ext' ); ?></label>
            <input type="text" name="add_post_title" id="post-title" class="form-control" value="<?php echo $title; ?>">
        </div>
        <div class="form-group">
			<?php
			$args = array();
			wp_editor( $post_content, 'add_post_content', $args );
			?>
        </div>
		<?php
		foreach ( $taxonomies as $taxonomy ) {
			$args  = array(
				'taxonomy'   => $taxonomy->name,
				'hide_empty' => false
			);
			$query = new WP_Term_Query( $args );
			$terms = $query->get_terms();
			if ( ! is_array( $terms ) || 1 > count( $terms ) ) {
				continue;
			}
			$name = 'add_' . $taxonomy->name;
			?>
            <div class="form-group">
                <label for="taxonomy-<?php echo $taxonomy->name; ?>"><?php echo esc_html( $taxonomy->labels->singular_name ); ?>
                    :</label>
                <select name="<?php echo esc_attr( $name ); ?>"
                        id="taxonomy-<?php echo $taxonomy->name; ?>"
                        class="form-control">
                    <option value=""><?php echo esc_html( sprintf( __( 'Choose %s', 'hocwp-ext' ), $taxonomy->labels->singular_name ) ); ?></option>
					<?php
					foreach ( $terms as $term ) {
						$selected = ( isset( $_POST[ $name ] ) && $term->term_id == $_POST[ $name ] ) ? true : false;
						?>
                        <option value="<?php echo $term->term_id; ?>"<?php selected( $selected, true ); ?>><?php echo $term->name; ?></option>
						<?php
					}
					?>
                </select>
            </div>
			<?php
		}
		?>
        <div class="form-group">
			<?php
			if ( is_array( $post_type ) ) {
				?>
                <label for="add-post-type"><?php _e( 'Post type:', 'hocwp-ext' ); ?>:</label>
                <select name="add_post_type" id="add-post-type"
                        class="form-control">
					<?php
					foreach ( $post_type as $type ) {
						$selected = ( isset( $_POST['add_post_type'] ) && $type == $_POST['add_post_type'] ) ? true : false;
						?>
                        <option value="<?php echo $type; ?>"<?php selected( $selected, true ); ?>><?php echo $type; ?></option>
						<?php
					}
					?>
                </select>
				<?php
			} else {
				?>
                <input type="hidden" name="add_post_type" value="<?php echo esc_attr( $post_type ); ?>">
				<?php
			}
			?>
        </div>
        <div class="form-group text-right">
			<?php
			if ( 0 < $post_price ) {
				$confirm = sprintf( __( 'You will pay %s coins for posting VIP content.', 'hocwp-ext' ), number_format( $post_price ) );
				?>
                <button class="btn btn-warning" name="submit_vip"
                        type="submit"
                        onclick="return (confirm('<?php echo $confirm; ?>')) || false;"><?php _e( 'Add VIP Post', 'hocwp-ext' ); ?></button>
				<?php
			}
			?>
            <button class="btn btn-success" name="submit"
                    type="submit"><?php _e( 'Add Post', 'hocwp-ext' ); ?></button>
            <a href="<?php echo esc_url( home_url() ); ?>"
               class="btn btn-info"><?php _e( 'Back to home', 'hocwp-ext' ); ?></a>
        </div>
    </form>
</div>