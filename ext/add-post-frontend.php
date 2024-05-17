<?php
/*
 * Name: Add Post Frontend
 * Description: Allow registered users add new post from frontend.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_theme_load_extension_add_post_frontend' ) ) {
	function hocwp_theme_load_extension_add_post_frontend() {
		return apply_filters( 'hocwp_theme_load_extension_add_post_frontend', HT_extension()->is_active( __FILE__ ) );
	}
}

$load = hocwp_theme_load_extension_add_post_frontend();

if ( ! $load ) {
	return;
}

require dirname( __FILE__ ) . '/add-post-frontend/add-post-frontend.php';

if ( ! class_exists( 'HOCWP_EXT_Add_Post_Frontend' ) ) {
	class HOCWP_EXT_Add_Post_Frontend extends HOCWP_Theme_Extension {
		protected static $instance;

		public static function get_instance() {
			if ( ! self::$instance instanceof self ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			if ( self::$instance instanceof self ) {
				return;
			}

			parent::__construct( __FILE__ );

			if ( is_admin() ) {
				add_action( 'wp_loaded', function () {
					$tab = new HOCWP_Theme_Admin_Setting_Tab( 'add_post_frontend', __( 'Add Post Frontend', 'sb-core' ), '<span class="dashicons dashicons-admin-post"></span>' );
					$tab->load_style( 'chosen-style' );
					$tab->load_script( 'chosen-select' );

					$args = array(
						'type' => 'checkbox',
						'text' => __( 'Allow guest add new post without login.', 'sb-core' )
					);

					$tab->add_field_array( array(
						'id'    => 'allow_guest_posting',
						'title' => __( 'Guest Posting', 'sb-core' ),
						'args'  => array(
							'type'          => 'boolean',
							'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
							'callback_args' => $args
						)
					) );

					$args = array(
						'public'          => true,
						'capability_type' => 'post'
					);

					$post_types = get_post_types( $args, OBJECT );

					$options = array();

					foreach ( $post_types as $post_type_object ) {
						if ( $post_type_object instanceof WP_Post_Type && 'attachment' != $post_type_object->name ) {
							$options[ $post_type_object->name ] = $post_type_object->labels->singular_name . ' (' . $post_type_object->name . ')';
						}
					}

					$args = array(
						'options'  => $options,
						'multiple' => 'multiple'
					);

					$tab->add_field_array( array(
						'id'    => 'post_type',
						'title' => __( 'Post Type', 'sb-core' ),
						'args'  => array(
							'type'          => 'default',
							'callback'      => array( 'HOCWP_Theme_HTML_Field', 'chosen' ),
							'description'   => __( 'Post type for the user to choose when posting.', 'sb-core' ),
							'callback_args' => $args
						)
					) );

					$args = array(
						'class' => 'regular-text'
					);

					$field = hocwp_theme_create_setting_field( 'new_post_page', __( 'Add Post Page', 'sb-core' ), 'select_page', $args, 'numeric', 'add_post_frontend' );
					$tab->add_field_array( $field );

					$args['text'] = __( 'Add button for user upload thumbnail image.', 'sb-core' );
					$args['type'] = 'checkbox';

					$tab->add_field_array( array(
						'id'    => 'upload_thumbnail',
						'title' => __( 'Upload Thumbnail', 'sb-core' ),
						'args'  => array(
							'type'          => 'boolean',
							'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
							'callback_args' => $args
						)
					) );

					$args['text'] = __( 'Allow user inserts media to content editor.', 'sb-core' );

					$tab->add_field_array( array(
						'id'    => 'insert_media',
						'title' => __( 'Insert Content Media', 'sb-core' ),
						'args'  => array(
							'type'          => 'boolean',
							'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
							'callback_args' => $args
						)
					) );


					$args['text'] = __( 'Use CAPTCHA to protect your site against spam.', 'sb-core' );

					$tab->add_field_array( array(
						'id'    => 'captcha',
						'title' => __( 'CAPTCHA', 'sb-core' ),
						'args'  => array(
							'type'          => 'boolean',
							'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
							'callback_args' => $args
						)
					) );

					$taxs = $this->get_taxonomies();

					$options = array();

					$hrt_opts = array();

					foreach ( $taxs as $object ) {
						if ( $object instanceof WP_Taxonomy ) {
							$options[ $object->name ] = $object->labels->singular_name . ' (' . $object->name . ')';

							if ( $object->hierarchical ) {
								$hrt_opts[ $object->name ] = $object->labels->singular_name . ' (' . $object->name . ')';
							}
						}
					}

					$args = array(
						'options'  => $options,
						'multiple' => 'multiple'
					);

					$tab->add_field_array( array(
						'id'    => 'disabled_taxonomies',
						'title' => __( 'Disabled Taxonomies', 'sb-core' ),
						'args'  => array(
							'type'          => 'default',
							'callback'      => array( 'HOCWP_Theme_HTML_Field', 'chosen' ),
							'description'   => __( 'Exclude these taxonomy types when posting.', 'sb-core' ),
							'callback_args' => $args
						)
					) );

					$tab->add_field_array( array(
						'id'    => 'combined_taxonomies',
						'title' => __( 'Combined Taxonomies', 'sb-core' ),
						'args'  => array(
							'type'          => 'default',
							'callback'      => array( 'HOCWP_Theme_HTML_Field', 'chosen' ),
							'description'   => __( 'Users can only select one of these taxonomy types when posting.', 'sb-core' ),
							'callback_args' => $args
						)
					) );

					$tab->add_field_array( array(
						'id'    => 'required_taxonomies',
						'title' => __( 'Required Taxonomies', 'sb-core' ),
						'args'  => array(
							'type'          => 'default',
							'callback'      => array( 'HOCWP_Theme_HTML_Field', 'chosen' ),
							'description'   => __( 'Users must choose at least one term in these taxonomies when posting.', 'sb-core' ),
							'callback_args' => $args
						)
					) );

					$args['options'] = $hrt_opts;

					$tab->add_field_array( array(
						'id'    => 'manually_taxonomies',
						'title' => __( 'Manually Taxonomies', 'sb-core' ),
						'args'  => array(
							'type'          => 'default',
							'callback'      => array( 'HOCWP_Theme_HTML_Field', 'chosen' ),
							'description'   => __( 'Users can select exists term or add new term manually for these taxonomies.', 'sb-core' ),
							'callback_args' => $args
						)
					) );

					$tab->add_field_array( array(
						'id'    => 'once_hierarchical_taxonomies',
						'title' => __( 'Once Hierarchical Taxonomies', 'sb-core' ),
						'args'  => array(
							'type'          => 'default',
							'callback'      => array( 'HOCWP_Theme_HTML_Field', 'chosen' ),
							'description'   => __( 'Usually, users can select multiple terms for hierarchical taxonomies (like categories). Now you can choose these taxonomies which allows users to select only one term.', 'sb-core' ),
							'callback_args' => $args
						)
					) );

					$field = new HOCWP_Theme_Admin_Setting_Field( 'redirect_seconds', __( 'Redirect Seconds', 'sb-core' ), 'input', array(
						'type'        => 'number',
						'class'       => 'medium-text',
						'description' => __( 'The system will redirect the user to another address after the post has been posted.', 'sb-core' )
					), 'positive_integer', 'add_post_frontend' );

					$tab->add_field_array( $field );

					$field = new HOCWP_Theme_Admin_Setting_Field( 'redirect_url', __( 'Redirect URL', 'sb-core' ), 'input', array(
						'type'        => 'text',
						'class'       => 'regular-text',
						'description' => __( 'The system will redirect the user to this URL after the post has been posted.', 'sb-core' )
					), 'string', 'add_post_frontend' );

					$tab->add_field_array( $field );
				} );

				add_action( 'wp_ajax_hte_add_post_frontend_change_post_type', array(
					$this,
					'change_post_type_ajax_callback'
				) );

				add_action( 'wp_ajax_nopriv_hte_add_post_frontend_change_post_type', array(
					$this,
					'change_post_type_ajax_callback'
				) );

				add_action( 'admin_notices', array( $this, 'admin_notices_action' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 999 );
				add_filter( 'bulk_actions-edit-post', array( $this, 'post_bulk_actions_filter' ) );
				add_filter( 'handle_bulk_actions-edit-post', array( $this, 'handle_post_bulk_action' ), 10, 3 );
			} else {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

				add_filter( 'hocwp_theme_extension_add_post_frontend_insert_post_errors', array(
					$this,
					'check_data_before_submit_post'
				) );
			}

			add_shortcode( 'hte_add_post_frontend', array( $this, 'shortcode' ) );

			add_action( 'save_post', array( $this, 'save_post' ) );
		}

		public function handle_post_bulk_action( $redirect_to, $doaction, $post_ids ) {
			if ( 'publish' == $doaction && HT()->array_has_value( $post_ids ) ) {
				foreach ( $post_ids as $post_id ) {
					$data = array(
						'ID'          => $post_id,
						'post_status' => 'publish'
					);

					wp_update_post( $data );
				}

				$redirect_to = add_query_arg( 'published_posts', count( $post_ids ), $redirect_to );
			}

			return $redirect_to;
		}

		public function post_bulk_actions_filter( $actions ) {
			$post_status = isset( $_GET['post_status'] ) ? $_GET['post_status'] : '';

			if ( 'pending' == $post_status ) {
				$actions['publish'] = _x( 'Publish', 'publish post', 'sb-core' );
			}

			return $actions;
		}

		public function admin_scripts() {
			if ( is_admin_bar_showing() ) {
				wp_enqueue_style( 'hocwp-theme-user-logged-in-style', HOCWP_Theme()->core_url . '/css/user-logged-in' . HOCWP_THEME_CSS_SUFFIX );
			}
		}

		public function save_post( $post_id ) {
			$taxs = $this->get_once_hierarchical_taxonomies();

			if ( HT()->array_has_value( $taxs ) ) {
				foreach ( $taxs as $taxonomy ) {
					if ( is_taxonomy_hierarchical( $taxonomy ) ) {
						$term_ids = isset( $_POST['tax_input'][ $taxonomy ] ) ? $_POST['tax_input'][ $taxonomy ] : '';

						if ( HT()->array_has_value( $term_ids ) ) {
							$term_ids = array_unique( $term_ids );
							$term_ids = array_filter( $term_ids );

							if ( 1 < count( $term_ids ) ) {
								$term_id = array_shift( $term_ids );

								while ( ! HT()->is_positive_number( $term_id ) && HT()->array_has_value( $term_ids ) ) {
									$term_id = array_shift( $term_ids );
								}

								if ( HT()->is_positive_number( $term_id ) ) {
									wp_set_object_terms( $post_id, array( absint( $term_id ) ), $taxonomy );
								}
							}
						}
					}
				}
			}
		}

		public function content_editor( $post_content, $args = array() ) {
			$defaults = array(
				'media_buttons' => false,
				'textarea_rows' => 18,
				'textarea_name' => 'add_post_content'
			);

			if ( 1 == HT_Options()->get_tab( 'insert_media', '', 'add_post_frontend' ) ) {
				unset( $defaults['media_buttons'] );
			}

			$args = wp_parse_args( $args, $defaults );

			$args = apply_filters( 'hocwp_theme_extension_add_post_frontend_editor_args', $args );

			wp_editor( $post_content, 'post_content', $args );
		}

		public function check_data_before_submit_post( $errors ) {
			if ( $this->use_captcha() && HT_CAPTCHA()->check_config_valid() ) {
				$response = HT_CAPTCHA()->check_valid();

				$errors = HT_CAPTCHA()->control_captcha_errors( $response, $errors );
			}

			return $errors;
		}

		public function check_captcha_config() {
			return HT_CAPTCHA()->check_config_valid();
		}

		public function admin_notices_action() {
			if ( function_exists( 'HT_Admin' ) && method_exists( HT_Admin(), 'skip_admin_notices' ) && HT_Admin()->skip_admin_notices() ) {
				return;
			}

			if ( isset( $_REQUEST['published_posts'] ) ) {
				$count = absint( $_REQUEST['published_posts'] );

				if ( HT()->is_positive_number( $count ) ) {
					global $post_type;

					if ( empty( $post_type ) ) {
						$post_type = 'post';
					}

					$object = get_post_type_object( $post_type );

					$args = array(
						'type'    => 'success',
						'message' => sprintf( __( '%d %s has/have been published successfully.', 'sb-core' ), $count, $object->labels->singular_name )
					);

					HT_Util()->admin_notice( $args );
				}
			}

			if ( $this->use_captcha() ) {
				if ( ! HT_CAPTCHA()->check_config_valid() ) {
					$msg = sprintf( __( 'You must fully input settings in <a href="%s">Social tab</a> for Add Post Frontend extension works normally.', 'sb-core' ), admin_url( 'themes.php?page=hocwp_theme&tab=social' ) );

					$args = array(
						'type'    => 'warning',
						'message' => sprintf( '<strong>%s</strong> %s', __( 'Add Post Frontend:', 'sb-core' ), $msg )
					);

					HT_Util()->admin_notice( $args );
				}
			}
		}

		public function use_captcha() {
			return (bool) HT_Options()->get_tab( 'captcha', '', 'add_post_frontend' );
		}

		public function generate_preview_taxs( $tax_args = array() ) {
			$combined_taxonomies = $this->get_combined_taxonomies();

			$post_type = $tax_args['object_type'] ?? '';

			if ( is_array( $post_type ) ) {
				$post_type = current( $post_type );
			}

			$taxonomies = get_object_taxonomies( $post_type, OBJECT );

			$tags = null;

			HTE_Add_Post_Frontend()->filter_combined_disabled_taxonomies( $taxonomies, $tags );

			if ( HT()->array_has_value( $combined_taxonomies ) ) {
				$combined_taxonomies = array_map( 'get_taxonomy', $combined_taxonomies );

				$id    = 'taxonomy-';
				$label = '';

				foreach ( $combined_taxonomies as $taxonomy ) {
					if ( $taxonomy instanceof WP_Taxonomy && in_array( $post_type, $taxonomy->object_type ) ) {
						$id    .= $taxonomy->name . '-';
						$label .= $taxonomy->labels->singular_name . '/';
					}
				}

				$id    = rtrim( $id, '-' );
				$label = rtrim( $label, '/' );

				if ( ! empty( $label ) ) {
					ob_start();
					?>
                    <label
                            class="control-label col-md-3 col-xs-3"><?php echo $label; ?>:</label>

                    <div class="col-md-3 col-xs-9">
                        <span id="temp_<?php echo $id; ?>"></span>
                    </div>
					<?php
					$taxonomies[] = ob_get_clean();
				}
			}

			if ( HT()->array_has_value( $taxonomies ) ) {
				$chunks = array_chunk( $taxonomies, 2 );

				foreach ( $chunks as $taxonomies ) {
					?>
                    <div class="form-group">
						<?php
						foreach ( $taxonomies as $taxonomy ) {
							if ( $taxonomy instanceof WP_Taxonomy ) {
								?>
                                <label
                                        class="control-label col-md-3 col-xs-3"><?php echo $taxonomy->labels->singular_name; ?>
                                    :</label>

                                <div class="col-md-3 col-xs-9">
                                    <span id="temp_taxonomy-<?php echo $taxonomy->name; ?>"></span>
                                </div>
								<?php
							} elseif ( is_string( $taxonomy ) ) {
								echo $taxonomy;
							}
						}
						?>
                    </div>
					<?php
				}
			}
		}

		public function filter_combined_disabled_taxonomies( &$taxonomies, &$tags ) {
			if ( HT()->array_has_value( $taxonomies ) ) {
				$disabled_taxonomies = HTE_Add_Post_Frontend()->get_disabled_taxonomies();
				$combined_taxonomies = HTE_Add_Post_Frontend()->get_combined_taxonomies();

				foreach ( $taxonomies as $key => $object ) {
					if ( $object instanceof WP_Taxonomy && ( in_array( $object->name, $disabled_taxonomies ) || in_array( $object->name, $combined_taxonomies ) ) ) {
						unset( $taxonomies[ $key ] );
					}
				}

				if ( is_array( $tags ) ) {
					foreach ( $taxonomies as $key => $taxonomy ) {
						if ( $taxonomy instanceof WP_Taxonomy && ! $taxonomy->hierarchical ) {
							$tags[] = $taxonomy;
							unset( $taxonomies[ $key ] );
						}
					}
				}
			}
		}

		/**
		 * Generate the select box for user can choose Combined Taxonomies Terms.
		 *
		 * @param $taxonomies
		 * @param array $args
		 */
		public function hierarchical_combined_taxonomy_terms_select( $taxonomies, $args = array() ) {
			$id      = 'taxonomy-';
			$options = '';
			$label   = '';

			$post_type = isset( $args['object_type'] ) ? $args['object_type'] : '';

			if ( is_array( $post_type ) ) {
				$post_type = current( $post_type );
			}

			$manually = isset( $args['manually'] ) ? $args['manually'] : '';

			foreach ( $taxonomies as $taxonomy ) {
				if ( $taxonomy instanceof WP_Taxonomy && in_array( $post_type, $taxonomy->object_type ) ) {
					$tq_args = array(
						'taxonomy'   => $taxonomy->name,
						'hide_empty' => false
					);

					$query = new WP_Term_Query( $tq_args );

					$terms = $query->get_terms();

					if ( HT()->array_has_value( $terms ) ) {
						$id    .= $taxonomy->name . '-';
						$label .= $taxonomy->labels->singular_name . '/';

						$opt = new HOCWP_Theme_HTML_Tag( 'optgroup' );
						$opt->add_attribute( 'label', $taxonomy->labels->singular_name . ' (' . $taxonomy->name . ')' );
						$opt->add_attribute( 'for-taxonomy', $taxonomy->name );

						$option = '';

						foreach ( $terms as $term ) {
							if ( $term instanceof WP_Term ) {
								$option .= '<option value="' . $taxonomy->name . '@' . $term->term_id . '">' . $term->name . '</option>';
							}
						}

						if ( $manually ) {
							$options .= $option;
						} else {
							$opt->set_text( $option );

							$options .= $opt->build();
						}
					}
				}
			}

			$id    = rtrim( $id, '-' );
			$label = rtrim( $label, '/' );

			if ( empty( $label ) ) {
				return;
			}

			$class = 'control-label';

			$right_label = isset( $args['right_label'] ) ? (bool) $args['right_label'] : false;

			$list_even = ( 0 == ( count( $taxonomies ) % 2 ) );

			$even = isset( $args['even'] ) ? (bool) $args['even'] : $list_even;

			$cb_taxs = $this->get_combined_taxonomies();

			$force_odd = false;

			if ( 1 < count( $cb_taxs ) ) {
				$mn_taxs = $this->get_manually_taxonomies();

				$count_mn_cb = 0;

				foreach ( $mn_taxs as $tax ) {
					if ( in_array( $tax, $cb_taxs ) ) {
						$count_mn_cb ++;
					}
				}

				if ( 2 <= $count_mn_cb ) {
					$force_odd = true;
				}
			}

			if ( $right_label ) {
				if ( $even && ! $force_odd ) {
					$class .= ' col-md-6';
				} else {
					$class .= ' col-md-3';
				}
			}

			if ( $right_label && $even ) {
				echo '<div class="form-group">';
			}
			?>
            <label for="<?php echo $id; ?>" class="<?php echo $class; ?>">
                <span><?php echo $label; ?>:</span>
            </label>
			<?php
			if ( $right_label ) {
				if ( $even && ! $force_odd ) {
					echo '<div class="col-md-6">';
				} else {
					echo '<div class="col-md-9">';
				}
			}

			if ( $manually && 1 < count( $taxonomies ) ) {
				?>
                <div class="row">
                    <div class="col-md-6">
                        <label style="width: 100%; font-weight: 400;">
                            <select id="combined_taxonomy_name" name="combined_taxonomy_name" class="form-control">
                                <option value=""></option>
								<?php
								foreach ( $taxonomies as $tax ) {
									if ( $tax instanceof WP_Taxonomy ) {
										?>
                                        <option
                                                value="<?php echo $tax->name; ?>"><?php echo $tax->labels->singular_name; ?></option>
										<?php
									}
								}
								?>
                            </select>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <select id="<?php echo $id; ?>" name="combined_taxonomy_term" class="form-control"
                                data-combobox="1">
                            <option value=""></option>
							<?php echo $options; ?>
                        </select>
                    </div>
                </div>
				<?php
			} else {
				$atts = '';

				if ( $manually ) {
					$atts = ' data-combobox="1"';
				}
				?>
                <select id="<?php echo $id; ?>" name="combined_taxonomy_term" class="form-control"<?php echo $atts; ?>>
                    <option value=""></option>
					<?php echo $options; ?>
                </select>
				<?php
			}
			?>
            <div class="help-block with-errors"></div>
			<?php
			if ( $right_label ) {
				echo '</div>';
			}

			if ( $right_label && $even ) {
				echo '</div>';
			}
		}

		/**
		 * Get all terms in combined taxonomies, generate html and add to list taxonomy object.
		 *
		 * @param $taxonomies
		 * @param $post_type
		 * @param array $args
		 */
		public function add_combined_taxonomies_to_list( &$taxonomies, $post_type, $args = array() ) {
			$combined_taxonomies = HTE_Add_Post_Frontend()->get_combined_taxonomies();

			if ( HT()->array_has_value( $combined_taxonomies ) ) {
				if ( ! isset( $args['object_type'] ) ) {
					$args['object_type'] = $post_type;
				}

				$taxs = array_map( 'get_taxonomy', $combined_taxonomies );

				$man_taxs = array();

				foreach ( $taxs as $key => $tax ) {
					$is_man = is_object( $tax ) && isset( $tax->name ) && in_array( $tax->name, $this->get_manually_taxonomies() );

					if ( ! ( $tax instanceof WP_Taxonomy ) || ! in_array( $post_type, $tax->object_type ) || $is_man ) {
						if ( $is_man && in_array( $post_type, $tax->object_type ) ) {
							$man_taxs[] = $tax;
						}

						unset( $taxs[ $key ] );
					}
				}

				if ( HT()->array_has_value( $taxs ) ) {
					$list_even = ( 0 == ( count( $taxonomies ) % 2 ) );

					$args['even'] = ! $list_even;

					$args['combined'] = true;

					ob_start();
					HTE_Add_Post_Frontend()->hierarchical_combined_taxonomy_terms_select( $taxs, $args );
					$taxonomies[] = ob_get_clean();
				}

				if ( HT()->array_has_value( $man_taxs ) ) {
					$list_even = ( 0 == ( count( $taxonomies ) % 2 ) );

					if ( isset( $args['right_label'] ) && (bool) $args['right_label'] ) {
						$list_even = ! $list_even;
					}

					$args['even'] = $list_even;

					$args['combined'] = true;

					$args['manually'] = true;

					ob_start();
					HTE_Add_Post_Frontend()->hierarchical_combined_taxonomy_terms_select( $man_taxs, $args );
					$taxonomies[] = ob_get_clean();
				}
			}
		}

		public function add_manually_taxonomies_to_list( &$taxonomies, $post_type, $args = array() ) {
			$taxonomies = HTE_Add_Post_Frontend()->get_manually_taxonomies();

			if ( HT()->array_has_value( $taxonomies ) ) {
				if ( ! isset( $args['object_type'] ) ) {
					$args['object_type'] = $post_type;
				}

				$taxs = array_map( 'get_taxonomy', $taxonomies );

				foreach ( $taxs as $key => $tax ) {
					if ( ! ( $tax instanceof WP_Taxonomy ) || ! in_array( $post_type, $tax->object_type ) ) {
						unset( $taxs[ $key ] );
					}
				}

				if ( HT()->array_has_value( $taxs ) ) {
					$list_even = ( 0 == ( count( $taxonomies ) % 2 ) );

					$args['even'] = ! $list_even;

					$args['combined'] = true;

					ob_start();
					HTE_Add_Post_Frontend()->hierarchical_combined_taxonomy_terms_select( $taxs, $args );
					$taxonomies[] = ob_get_clean();
				}
			}
		}

		public function taxonomy_form_group_html( $taxonomies, $args = array() ) {
			if ( HT()->array_has_value( $taxonomies ) ) {
				$even = ( 0 == ( count( $taxonomies ) % 2 ) );

				$required_taxonomies = HTE_Add_Post_Frontend()->get_required_taxonomies();

				$chunks = array_chunk( $taxonomies, 2 );
				$last   = array_pop( $chunks );

				$count_last = count( $last );

				$even = false;

				if ( 2 == $count_last ) {
					$chunks[] = $last;
					$even     = true;
				}

				$last_chunk = null;

				if ( $even ) {
					$cb_taxs = $this->get_combined_taxonomies();

					if ( 1 < count( $cb_taxs ) ) {
						$mn_taxs = $this->get_manually_taxonomies();

						$count_mn_cb = 0;

						foreach ( $mn_taxs as $tax ) {
							if ( in_array( $tax, $cb_taxs ) ) {
								$count_mn_cb ++;
							}
						}

						if ( 2 <= $count_mn_cb ) {
							$last_chunk = array_pop( $chunks );
						}
					}
				}

				$right_label = isset( $args['right_label'] ) ? (bool) $args['right_label'] : false;

				foreach ( $chunks as $taxonomies ) {
					?>
                    <div class="row">
						<?php
						foreach ( $taxonomies as $taxonomy ) {
							if ( $taxonomy instanceof WP_Taxonomy ) {
								$box_args = array(
									'right_label' => $right_label,
									'even'        => true
								);

								if ( in_array( $taxonomy->name, $required_taxonomies ) ) {
									$box_args['required'] = true;
								}
								?>
                                <div class="col-sm-6 col-xs-12">
									<?php HTE_Add_Post_Frontend()->taxonomy_form_control( $taxonomy, $box_args ); ?>
                                </div>
								<?php
							} elseif ( is_string( $taxonomy ) && ! empty( $taxonomy ) ) {
								?>
                                <div class="col-sm-6 col-xs-12">
									<?php
									if ( $right_label && $even ) {
										//echo '<div class="form-group">';
									}

									echo $taxonomy;

									if ( $right_label && $even ) {
										//echo '</div>';
									}
									?>
                                </div>
								<?php
							}
						}
						?>
                    </div>
					<?php
				}

				$has_cb_mn = HT()->array_has_value( $last_chunk );

				if ( 2 != $count_last || $has_cb_mn ) {
					if ( $has_cb_mn ) {
						$last = $last_chunk;
					}

					foreach ( $last as $taxonomy ) {
						if ( $taxonomy instanceof WP_Taxonomy ) {
							$box_args = array(
								'right_label' => $right_label,
								'even'        => $even
							);

							if ( in_array( $taxonomy->name, $required_taxonomies ) ) {
								$box_args['required'] = true;
							}

							if ( $has_cb_mn ) {
								$box_args['force_odd'] = true;
							}

							HTE_Add_Post_Frontend()->taxonomy_form_control( $taxonomy, $box_args );
						} else {
							if ( ! $has_cb_mn ) {
								?>
                                <div class="form-group">
									<?php echo $taxonomy; ?>
                                </div>
								<?php
							} else {
								echo $taxonomy;
							}
						}
					}
				}
			}
		}

		public function taxonomy_form_control( $taxonomy, $args = array() ) {
			if ( $taxonomy instanceof WP_Taxonomy ) {
				$req      = '';
				$box_args = array();

				$required = isset( $args['required'] ) ? (bool) $args['required'] : false;

				if ( $required ) {
					$req = sprintf( ' (%s)', HT()->required_mark() );

					$box_args['required'] = true;
				}

				$class = 'control-label';

				$right_label = isset( $args['right_label'] ) ? (bool) $args['right_label'] : false;

				$box_args['right_label'] = $right_label;

				$even = isset( $args['even'] ) ? $args['even'] : true;

				$force_odd = isset( $args['force_odd'] ) ? (bool) $args['force_odd'] : false;

				if ( $right_label ) {
					if ( $even && ! $force_odd ) {
						$class .= ' col-md-6';
					} else {
						$class .= ' col-md-3';
					}
				}

				if ( $right_label ) {
					if ( $even && ! $force_odd ) {
						$box_args['before'] = '<div class="col-md-6">';
					} else {
						$box_args['before'] = '<div class="col-md-9">';
					}

					$box_args['after'] = '</div>';
				}
				?>
                <div class="form-group">
                    <label for="taxonomy-<?php echo $taxonomy->name; ?>" class="<?php echo $class; ?>">
						<span><?php echo esc_html( $taxonomy->labels->singular_name ) . $req; ?>
							:</span>
                    </label>
					<?php HTE_Add_Post_Frontend()->hierarchical_taxonomy_terms_select( $taxonomy, $box_args ); ?>
                </div>
				<?php
			}
		}

		public function hierarchical_taxonomy_terms_select( $taxonomy, $args = array() ) {
			if ( $taxonomy instanceof WP_Taxonomy ) {
				$required = isset( $args['required'] ) ? $args['required'] : false;

				if ( ! $required ) {
					$required = in_array( $taxonomy->name, $this->get_required_taxonomies() );
				}

				$tq_args = array(
					'taxonomy'   => $taxonomy->name,
					'hide_empty' => false,
					'fields'     => 'names'
				);

				$query = new WP_Term_Query( $tq_args );
				$terms = $query->get_terms();

				if ( $taxonomy->hierarchical && ( ! is_array( $terms ) || 1 > count( $terms ) ) ) {
					return;
				}

				$name = 'add_' . $taxonomy->name;

				$before = HT()->get_value_in_array( $args, 'before' );

				echo $before;

				if ( $taxonomy->hierarchical ) {
					$manually = in_array( $taxonomy->name, $this->get_manually_taxonomies() );

					$dc_args = array(
						'taxonomy'        => $taxonomy->name,
						'name'            => esc_attr( $name ),
						'id'              => 'taxonomy-' . $taxonomy->name,
						'class'           => 'form-control',
						'hierarchical'    => 1,
						'depth'           => 5,
						'hide_empty'      => false,
						'show_option_all' => esc_html( sprintf( __( '-- Choose %s --', 'sb-core' ), $taxonomy->labels->singular_name ) ),
						'echo'            => false,
						'selected'        => isset( $args['selected'] ) ? absint( $args['selected'] ) : ''
					);

					if ( $manually ) {
						unset( $dc_args['show_option_all'], $dc_args['show_option_none'] );
					}

					$select = wp_dropdown_categories( $dc_args );

					$search = '<select';

					$replace = '<select required="required"';

					if ( $required ) {
						$select = str_replace( $search, $replace, $select );
					}

					$replace = '<select data-taxonomy="' . $taxonomy->name . '"';

					$replace .= ' data-hierarchical="' . HT()->bool_to_int( $taxonomy->hierarchical ) . '"';

					if ( $manually ) {
						$replace .= ' data-combobox="1"';
						$replace .= ' data-show-items-text="' . __( 'Show all items', 'sb-core' ) . '"';
					}

					$select = str_replace( $search, $replace, $select );

					echo $select;
					echo '<div class="help-block with-errors"></div>';
				} else {
					?>
                    <input name="<?php echo esc_attr( $name ); ?>" id="taxonomy-<?php echo $taxonomy->name; ?>"
                           class="form-control nonhierarchical-taxonomy" data-autocomplete="1"
                           data-taxonomy="<?php echo $taxonomy->name; ?>"
                           placeholder=""<?php HT()->checked_selected_helper( true, $required, true, 'required' ); ?>>
                    <p class="description"><?php _e( 'Each value separated by commas.', 'sb-core' ); ?></p>
                    <div class="help-block with-errors"></div>
					<?php
				}

				$after = HT()->get_value_in_array( $args, 'after' );

				echo $after;
			}
		}

		public function post_type_form_control( $post_types ) {
			if ( is_array( $post_types ) ) {
				echo '<select name="add_post_type" id="add-post-type" class="form-control">';

				foreach ( $post_types as $type ) {
					$selected = ( isset( $_REQUEST['add_post_type'] ) && $type == $_REQUEST['add_post_type'] ) ? true : false;
					$object   = get_post_type_object( $type );

					if ( $object instanceof WP_Post_Type ) {
						?>
                        <option
                                value="<?php echo $type; ?>"<?php selected( $selected, true ); ?>><?php echo $object->labels->singular_name; ?>
                            (<?php echo $type; ?>)
                        </option>
						<?php
					}
				}

				echo '</select>';
				echo '<div class="help-block with-errors"></div>';
			} else {
				?>
                <input type="hidden" name="add_post_type" value="<?php echo esc_attr( $post_types ); ?>">
				<?php
			}
		}

		public function change_post_type_ajax_callback() {
			$post_type = HT()->get_method_value( 'post_type' );

			if ( post_type_exists( $post_type ) ) {
				$right_label = HT()->get_method_value( 'right_label' );

				$taxonomies = get_object_taxonomies( $post_type, OBJECT );

				$tags = array();

				$this->filter_combined_disabled_taxonomies( $taxonomies, $tags );

				$data = array(
					'hierarchical'      => '',
					'none_hierarchical' => '',
					'preview_taxs'      => ''
				);

				$args = array(
					'right_label' => $right_label,
					'object_type' => $post_type
				);

				HTE_Add_Post_Frontend()->add_combined_taxonomies_to_list( $taxonomies, $post_type, $args );

				ob_start();

				$this->taxonomy_form_group_html( $taxonomies, $args );

				$data['hierarchical'] = ob_get_clean();

				ob_start();

				$this->taxonomy_form_group_html( $tags, $args );

				$data['none_hierarchical'] = ob_get_clean();

				ob_start();

				$this->generate_preview_taxs( array( 'object_type' => $post_type ) );

				$data['preview_taxs'] = ob_get_clean();

				wp_send_json_success( $data );
			}

			wp_send_json_error();
		}

		public function enqueue_scripts() {
			if ( is_admin_bar_showing() ) {
				wp_enqueue_style( 'hocwp-theme-user-logged-in-style', HOCWP_Theme()->core_url . '/css/user-logged-in' . HOCWP_THEME_CSS_SUFFIX );
			}

			$page = HT_Util()->get_theme_option_page( 'new_post_page', 'add_post_frontend' );

			if ( HT_Options()->check_page_valid( $page ) && is_page( $page->ID ) ) {
				wp_enqueue_style( 'hte-add-post-frontend-style', SB_Core()->url . '/css/add-post-frontend' . HOCWP_THEME_CSS_SUFFIX );

				wp_enqueue_script( 'hte-add-post-frontend', SB_Core()->url . '/js/add-post-frontend' . HOCWP_THEME_JS_SUFFIX, array(
					'jquery',
					'hocwp-theme'
				), false, true );

				$l10n = array(
					'l10n' => array(
						'invalidImageMessage' => __( 'Please select PNG or JPEG image only.', 'sb-core' )
					)
				);

				wp_localize_script( 'hte-add-post-frontend', 'hteAddPostFrontend', $l10n );

				HT_Enqueue()->combobox();
				HT_Enqueue()->autocomplete();
			}
		}

		public function get_taxonomies( $args = array() ) {
			$defaults = array(
				'public' => true
			);

			$args = wp_parse_args( $args, $defaults );

			$taxs = get_taxonomies( $args, OBJECT );

			return $taxs;
		}

		private function get_taxonomies_option_array( $option_name ) {
			$taxs = HT_Options()->get_tab( $option_name, '', 'add_post_frontend' );

			if ( ! is_array( $taxs ) ) {
				$taxs = array();
			}

			return $taxs;
		}

		public function get_required_taxonomies() {
			return $this->get_taxonomies_option_array( 'required_taxonomies' );
		}

		public function get_disabled_taxonomies() {
			return $this->get_taxonomies_option_array( 'disabled_taxonomies' );
		}

		public function get_combined_taxonomies() {
			return $this->get_taxonomies_option_array( 'combined_taxonomies' );
		}

		public function get_manually_taxonomies() {
			return $this->get_taxonomies_option_array( 'manually_taxonomies' );
		}

		public function get_once_hierarchical_taxonomies() {
			return $this->get_taxonomies_option_array( 'once_hierarchical_taxonomies' );
		}

		public function can_upload_thumbnail() {
			return (bool) HT_Options()->get_tab( 'upload_thumbnail', '', 'add_post_frontend' );
		}

		public function form_control_thumbnail() {
			?>
            <div class="upload-group">
                <div class="btn btn-primary image-button">
                    <span><i class="fa fa-cloud-upload"></i> <?php _e( 'Upload image', 'sb-core' ); ?></span>
                    <input type="file" id="post_thumbnail" name="post_thumbnail" style="display: none;"
                           accept="image/jpeg, image/png">
                </div>

                <div class="wrap-main-image wrap-image">
                </div>
                <div class="wrap-loader">
                    <div class="loader loader-primary"></div>
                </div>
            </div>
			<?php
		}

		public function shortcode( $atts = array(), $content = null ) {
			$atts = shortcode_atts( array(), $atts );

			ob_start();
			include $this->folder_path . '/template.php';

			return ob_get_clean();
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HTE_Add_Post_Frontend()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HTE_Add_Post_Frontend() {
	return HOCWP_EXT_Add_Post_Frontend::get_instance();
}