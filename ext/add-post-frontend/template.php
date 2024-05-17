<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$allow_guest_posting = HT_Options()->get_tab( 'allow_guest_posting', '', 'add_post_frontend' );

if ( 1 != $allow_guest_posting ) {
	if ( ! is_user_logged_in() ) {
		$url = wp_login_url( get_the_permalink() );
		?>
        <p class="alert alert-warning"><?php _e( 'Please login to use this function.', 'sb-core' ); ?></p>
        <script>
            window.location.href = "<?php echo $url; ?>";
        </script>
		<?php
		return;
	} else {
		if ( ! current_user_can( 'edit_posts' ) ) {
			$url = home_url();
			?>
            <p class="alert alert-warning"><?php _e( 'Please login to use this function.', 'sb-core' ); ?></p>
            <script>
                window.location.href = "<?php echo $url; ?>";
            </script>
			<?php
			return;
		}
	}
}

global $hocwp_theme;

$options    = $hocwp_theme->options;
$post_price = isset( $options['vip']['post_price'] ) ? $options['vip']['post_price'] : '';
$post_price = absint( $post_price );

$post_type = hocwp_add_post_frontend_post_type();

if ( is_string( $post_type ) && ! post_type_exists( $post_type ) ) {
	return;
}

if ( is_array( $post_type ) ) {
	foreach ( $post_type as $key => $type_name ) {
		if ( ! post_type_exists( $type_name ) ) {
			unset( $post_type[ $key ] );
		}
	}
}

if ( ! HT()->array_has_value( $post_type ) ) {
	return;
}

$first_post_type = ( is_array( $post_type ) ) ? current( $post_type ) : $post_type;

if ( ! post_type_exists( $first_post_type ) ) {
	return;
}

if ( isset( $_POST['add_post_type'] ) || ( isset( $_GET['add_post_type'] ) && post_type_exists( $_GET['add_post_type'] ) ) ) {
	$first_post_type = $_REQUEST['add_post_type'];
}

$taxonomies = get_object_taxonomies( $first_post_type, OBJECT );

$tags = array();

HTE_Add_Post_Frontend()->filter_combined_disabled_taxonomies( $taxonomies, $tags );

$title        = '';
$post_content = '';
$messages     = array();

$post_added = false;

$post_id = '';

if ( isset( $_POST['add_post_type'] ) ) {
	if ( ! HT_Util()->verify_nonce() ) {
		$messages[] = '<p class="alert alert-danger">' . __( 'Invalid form data.', 'sb-core' ) . '</p>';
	} else {
		$title = isset( $_POST['add_post_title'] ) ? $_POST['add_post_title'] : '';

		$post_content = isset( $_POST['add_post_content'] ) ? $_POST['add_post_content'] : '';
		$post_content = stripslashes( $post_content );
		$post_content = str_replace( '\\', '', $post_content );

		if ( ! empty( $title ) && ! empty( $post_content ) ) {
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
			} else {
				$user_id = 0;
			}

			$enough = true;

			$enough = apply_filters( 'hocwp_theme_extension_add_post_frontend_check_before_submit_post', $enough );

			if ( is_bool( $enough ) && $enough ) {
				$data = array(
					'post_type'    => $_POST['add_post_type'],
					'post_title'   => $title,
					'post_content' => $post_content,
					'post_status'  => 'pending',
					'post_author'  => $user_id
				);

				$errors = apply_filters( 'hocwp_theme_extension_add_post_frontend_insert_post_errors', '', $data );

				if ( $errors instanceof WP_Error && $errors->get_error_code() ) {
					foreach ( $errors->get_error_messages() as $mesg ) {
						$messages[] = '<p class="alert alert-danger">' . $mesg . '</p>';
					}
				} else {
					$id = wp_insert_post( $data, true );

					if ( $id instanceof WP_Error ) {
						$messages[] = '<p class="alert alert-danger">' . $id->get_error_message() . '</p>';
					} else {
						$post_added = true;
						$post_id    = $id;

						if ( is_user_logged_in() ) {
							$msg = sprintf( __( 'Your post has been added successfully. You can <a href="%s">click here</a> to update it.', 'sb-core' ), get_edit_post_link( $id ) );
						} else {
							$msg = __( 'Your post has been added successfully. We will check and approve your post soon.', 'sb-core' );
						}

						$messages[] = '<p class="alert alert-success">' . $msg . '</p>';

						foreach ( $taxonomies as $taxonomy ) {
							$name = 'add_' . $taxonomy->name;

							if ( isset( $_POST[ $name ] ) ) {
								$term_id  = absint( $_POST[ $name ] );
								$term_ids = array();

								if ( HT()->is_positive_number( $term_id ) ) {
									$term_ids = array( $term_id );
								} elseif ( ! empty( $_POST[ $name ] ) ) {
									$exists = term_exists( $_POST[ $name ], $taxonomy->name );

									if ( ! is_array( $exists ) || ! isset( $exists['term_id'] ) || ! HT()->is_positive_number( $exists['term_id'] ) ) {
										$exists = wp_insert_term( $_POST[ $name ], $taxonomy->name );
									}

									if ( is_array( $exists ) && isset( $exists['term_id'] ) && HT()->is_positive_number( $exists['term_id'] ) ) {
										$term_id  = $exists['term_id'];
										$term_ids = array( $term_id );
									}
								}

								if ( HT()->array_has_value( $term_ids ) ) {
									if ( 'category' == $taxonomy->name && function_exists( 'HTE_Classifieds' ) && HTE_Classifieds()->category_as_location() ) {
										$term = get_term( $term_id, $taxonomy->name );

										while ( $term instanceof WP_Term && HT()->is_positive_number( $term->parent ) ) {
											$term_ids[] = $term->parent;
											$term       = get_term( $term->parent, $term->taxonomy );
										}
									}

									wp_set_post_terms( $id, $term_ids, $taxonomy->name );
								}
							}
						}

						foreach ( $tags as $taxonomy ) {
							$name = 'add_' . $taxonomy->name;

							if ( isset( $_POST[ $name ] ) ) {
								$tag_name = $_POST[ $name ];

								if ( ! empty( $tag_name ) ) {
									$tag_name = trim( $tag_name );
									$tag_name = rtrim( $tag_name, ',' );
									wp_set_post_terms( $id, $tag_name, $taxonomy->name );
								}
							}
						}

						if ( isset( $_POST['combined_taxonomy_term'] ) ) {
							$part = $_POST['combined_taxonomy_term'];
							$part = explode( '@', $part );

							if ( is_array( $part ) && 2 == count( $part ) && taxonomy_exists( $part[0] ) ) {
								$cbt_id = $part[1];

								if ( HT()->is_positive_number( $cbt_id ) ) {
									$cbt = get_term( $cbt_id, $part[0] );

									if ( $cbt instanceof WP_Term ) {
										wp_set_post_terms( $id, array( $cbt_id ), $cbt->taxonomy );
									}
								}
							} elseif ( isset( $_POST['combined_taxonomy_name'] ) ) {
								$term_name = $_POST['combined_taxonomy_term'];

								if ( ! empty( $term_name ) ) {
									$tax_name = $_POST['combined_taxonomy_name'];

									if ( ! empty( $tax_name ) ) {
										$exists = term_exists( $term_name, $tax_name );

										if ( ! is_array( $exists ) || ! isset( $exists['term_id'] ) || ! HT()->is_positive_number( $exists['term_id'] ) ) {
											$exists = wp_insert_term( $term_name, $tax_name );
										}

										if ( is_array( $exists ) && isset( $exists['term_id'] ) && HT()->is_positive_number( $exists['term_id'] ) ) {
											wp_set_post_terms( $post_id, array( $exists['term_id'] ), $tax_name );
										}
									}
								}
							}
						}

						$thumbnail = isset( $_FILES['post_thumbnail'] ) ? $_FILES['post_thumbnail'] : '';

						if ( HT()->array_has_value( $thumbnail ) ) {
							$thumbnail = HT_Util()->upload_file( $thumbnail['name'], @file_get_contents( $thumbnail['tmp_name'] ) );

							if ( HT()->array_has_value( $thumbnail ) && isset( $thumbnail['id'] ) && HT()->is_positive_number( $thumbnail['id'] ) ) {
								set_post_thumbnail( $id, $thumbnail['id'] );
							}
						}

						$title = '';

						$post_content = '';

						do_action( 'hocwp_theme_extension_add_post_frontend_post_added', $id );

						$_POST['post_id'] = $id;
					}
				}
			} else {
				if ( $enough instanceof WP_Error ) {
					foreach ( $enough->get_error_messages() as $msg ) {
						$messages[] = '<p class="alert alert-danger">' . $msg . '</p>';
					}
				} else {
					if ( ! is_string( $enough ) ) {
						$enough = __( 'An error has occurred, please try again or contact the administrator.', 'sb-core' );
					}

					$messages[] = '<p class="alert alert-danger">' . $enough . '</p>';
				}
			}
		} else {
			$messages[] = '<p class="alert alert-danger">' . __( 'Post title and post content must not be empty.', 'sb-core' ) . '</p>';
		}
	}
}

$class = 'add-post-form';

if ( $post_added ) {
	$class .= ' added';
}

$required_taxonomies = HTE_Add_Post_Frontend()->get_required_taxonomies();

$html = '';

if ( ! $post_added ) {
	$html = apply_filters( 'hocwp_theme_extension_add_post_frontend_form_html', '', $post_added );
}

$default_html = '';

if ( ! $post_added && empty( $html ) ) {
	ob_start();
	?>
    <div class="form-group">
		<?php
		if ( is_array( $post_type ) ) {
			?>
            <label for="add-post-type"><?php _e( 'Post type:', 'sb-core' ); ?></label>
			<?php
		}

		HTE_Add_Post_Frontend()->post_type_form_control( $post_type );
		?>
    </div>
    <div class="form-group">
        <label
                for="post-title"><?php printf( __( 'Post title (%s):', 'sb-core' ), HT()->required_mark() ); ?></label>
        <input type="text" name="add_post_title" id="post-title" class="form-control"
               value="<?php echo $title; ?>" required>
    </div>
    <div class="form-group">
		<?php HTE_Add_Post_Frontend()->content_editor( $post_content ); ?>
    </div>
    <div class="hierarchical-taxs">
		<?php
		HTE_Add_Post_Frontend()->add_combined_taxonomies_to_list( $taxonomies, $first_post_type );

		HTE_Add_Post_Frontend()->taxonomy_form_group_html( $taxonomies );
		?>
    </div>
    <div class="custom-fields">
		<?php do_action( 'hte_add_post_frontend_form_middle' ); ?>
    </div>
    <div class="none-hierarchical-taxs">
		<?php HTE_Add_Post_Frontend()->taxonomy_form_group_html( $tags ); ?>
    </div>
	<?php
	if ( HTE_Add_Post_Frontend()->can_upload_thumbnail() ) {
		?>
        <div class="form-group">
            <label><?php _e( 'Thumbnail:', 'sb-core' ); ?></label>
			<?php HTE_Add_Post_Frontend()->form_control_thumbnail(); ?>
        </div>
		<?php
	}

	if ( HTE_Add_Post_Frontend()->use_captcha() && HT_CAPTCHA()->check_config_valid() ) {
		?>
        <div class="captcha-box form-group">
			<?php HT_CAPTCHA()->display_html(); ?>
        </div>
		<?php
	}
	?>
    <div class="form-group text-right">
        <button class="btn btn-success" name="submit"
                type="submit"><?php _ex( 'Add Post', 'add post frontend', 'sb-core' ); ?></button>
        <a href="<?php echo esc_url( home_url() ); ?>"
           class="btn btn-info button"><?php _e( 'Back to home', 'sb-core' ); ?></a>
    </div>
	<?php
	$default_html = ob_get_clean();
}
?>
<div class="add-post-frontend">
    <form method="post" class="<?php echo $class; ?>" enctype="multipart/form-data">
		<?php
		if ( 0 < count( $messages ) ) {
			$messages = apply_filters( 'hocwp_theme_extension_add_post_frontend_form_messages', $messages );

			if ( HT()->array_has_value( $messages ) ) {
				foreach ( $messages as $message ) {
					echo $message;
				}
			}
		}

		if ( $post_added ) {
			do_action( 'hocwp_theme_extension_add_post_frontend_post_added_form', $post_id );

			$_POST = array();
			$url   = HT_Options()->get_tab( 'redirect_url', '', 'add_post_frontend' );

			if ( empty( $url ) ) {
				$url = home_url();
			}

			$secs = HT_Options()->get_tab( 'redirect_seconds', '', 'add_post_frontend' );

			if ( ! HT()->is_positive_number( $secs ) ) {
				$secs = 30;
			}

			$secs *= 1000;
			?>
            <script>
                var body = document.getElementsByTagName("body")[0];

                if (-1 === body.className.indexOf("post-added")) {
                    body.className += " post-added";
                }

                setTimeout(function () {
                    window.location.href = "<?php echo $url; ?>";
                }, <?php echo $secs; ?>);
            </script>
			<?php
		} else {
			wp_nonce_field();

			if ( ! empty( $html ) ) {
				echo $html;
			} else {
				echo $default_html;
			}
		}
		?>
    </form>
</div>