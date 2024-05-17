<?php
/*
 * Name: VIP Management
 * Description: Add VIP functions for managing VIP users and VIP content.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_EXT_VIP_Management' ) ) {
	class HOCWP_EXT_VIP_Management extends HOCWP_Theme_Extension {
		protected static $instance;

		public static function get_instance() {
			if ( ! self::$instance instanceof self ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public $gallery_upload_limit = 5;

		public function __construct() {
			if ( self::$instance instanceof self ) {
				return;
			}

			parent::__construct( __FILE__ );

			if ( is_admin() ) {
				$tab = new HOCWP_Theme_Admin_Setting_Tab( 'vip', __( 'VIP Management', 'sb-core' ), '<span class="dashicons dashicons-awards"></span>' );

				$args = array(
					'class' => 'small-text',
					'type'  => 'number'
				);

				$tab->add_field_array( array(
					'id'    => 'coin_rate',
					'title' => __( 'Coin Rate', 'sb-core' ),
					'args'  => array(
						'type'          => 'string',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
						'callback_args' => $args,
						'description'   => __( 'Conversion rate $1 into coin. For example: $1 = 100.', 'sb-core' )
					)
				) );

				$args = array(
					'class' => 'small-text',
					'type'  => 'number'
				);

				$tab->add_field_array( array(
					'id'    => 'post_price',
					'title' => __( 'Post Price', 'sb-core' ),
					'args'  => array(
						'type'          => 'string',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
						'callback_args' => $args,
						'description'   => __( 'VIP post costs by day.', 'sb-core' )
					)
				) );

				$args = array(
					'type' => 'checkbox',
					'text' => __( 'Allow user add new VIP post then pay later.', 'sb-core' )
				);

				$tab->add_field_array( array(
					'id'    => 'pay_later',
					'title' => __( 'Pay Later', 'sb-core' ),
					'args'  => array(
						'type'          => 'boolean',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
						'callback_args' => $args
					)
				) );

				$args['text'] = __( 'Add button for user upload gallery images.', 'sb-core' );

				$tab->add_field_array( array(
					'id'    => 'upload_gallery',
					'title' => __( 'Upload Gallery', 'sb-core' ),
					'args'  => array(
						'type'          => 'boolean',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
						'callback_args' => $args
					)
				) );

				$args['text'] = __( 'Auto mark post as VIP content when changing expiry date.', 'sb-core' );

				$tab->add_field_array( array(
					'id'    => 'auto_vip_post',
					'title' => __( 'Auto VIP Post', 'sb-core' ),
					'args'  => array(
						'type'          => 'boolean',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
						'callback_args' => $args
					)
				) );

				$secs = array(
					'title'       => __( 'Custom Messages', 'sb-core' ),
					'description' => __( 'Change messages on add new post form.', 'sb-core' )
				);

				$tab->add_section( 'custom_messages', $secs );

				$tab->add_field_array( array(
					'id'      => 'normal_post_description',
					'title'   => __( 'Normal Post Description', 'sb-core' ),
					'section' => 'custom_messages',
					'args'    => array(
						'type'          => 'html',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'editor' ),
						'callback_args' => $args
					)
				) );

				$tab->add_field_array( array(
					'id'      => 'vip_post_description',
					'title'   => __( 'VIP Post Description', 'sb-core' ),
					'section' => 'custom_messages',
					'args'    => array(
						'type'          => 'html',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'editor' ),
						'callback_args' => $args
					)
				) );

				$tab->add_field_array( array(
					'id'      => 'confirm_post_notice',
					'title'   => __( 'Confirm Post Notice', 'sb-core' ),
					'section' => 'custom_messages',
					'args'    => array(
						'type'          => 'html',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'editor' ),
						'callback_args' => $args
					)
				) );

				$tab->add_field_array( array(
					'id'      => 'vip_post_added_message',
					'title'   => __( 'VIP Post Added Message', 'sb-core' ),
					'section' => 'custom_messages',
					'args'    => array(
						'type'          => 'html',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'editor' ),
						'callback_args' => $args
					)
				) );

				$tab->add_field_array( array(
					'id'      => 'vip_post_content_info_message',
					'title'   => __( 'VIP Post Content Info', 'sb-core' ),
					'section' => 'custom_messages',
					'args'    => array(
						'type'          => 'html',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'editor' ),
						'callback_args' => $args
					)
				) );

				add_action( 'load-post.php', array( $this, 'meta_post' ) );
				add_action( 'load-post-new.php', array( $this, 'meta_post' ) );
				add_action( 'save_post', array( $this, 'save_post_action' ), 999 );
			} else {
				add_action( 'wp', array( $this, 'wp_action' ) );

				add_filter( 'hocwp_theme_extension_add_post_frontend_form_html', array(
					$this,
					'add_vip_post_form'
				), 10, 2 );

				add_action( 'hocwp_theme_extension_add_post_frontend_post_added_form', array(
					$this,
					'post_added_form'
				) );

				add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts_action' ) );

				add_filter( 'hocwp_theme_extension_add_post_frontend_check_before_submit_post', array(
					$this,
					'check_post_before_submit'
				) );

				add_action( 'hocwp_theme_extension_add_post_frontend_post_added', array( $this, 'post_added_meta' ) );
			}

			add_action( 'pre_get_posts', array( $this, 'pre_get_posts_action' ) );
		}

		public function is_vip_post( $post_id = null ) {
			if ( ! HT()->is_positive_number( $post_id ) ) {
				$post_id = get_the_ID();
			}

			$vip = get_post_meta( $post_id, 'vip', true );

			if ( 1 == $vip ) {
				$current = current_time( 'timestamp' );

				$expired = get_post_meta( $post_id, 'vip_expired', true );

				if ( $expired >= $current ) {
					return true;
				}
			}

			return false;
		}

		public function cancel_vip_post( $post_id, $vip = null ) {
			if ( null == $vip ) {
				$vip = get_post_meta( $post_id, 'vip', true );
			}

			if ( 1 == $vip ) {
				$current = current_time( 'timestamp' );

				$expired = get_post_meta( $post_id, 'vip_expired', true );

				if ( is_numeric( $expired ) && $expired < $current ) {
					update_post_meta( $post_id, 'vip', - 1 );

					if ( isset( $_REQUEST['action'] ) && 'editpost' == $_REQUEST['action'] ) {
						unset( $_POST['vip'] );
					}
				}
			}
		}

		public function wp_action() {
			if ( is_single() || is_page() || is_singular() ) {
				$post_id = get_the_ID();

				$this->cancel_vip_post( $post_id );
			}
		}

		public function author_contact_fields() {
			$fields = array(
				'NameContact'    => array(
					'type'     => 'text',
					'required' => true,
					'label'    => __( 'Your name', 'sb-core' )
				),
				'AddressContact' => array(
					'type'     => 'text',
					'required' => false,
					'label'    => __( 'Address', 'sb-core' )
				),
				'PhoneContact'   => array(
					'type'     => 'text',
					'required' => false,
					'label'    => __( 'Phone', 'sb-core' )
				),
				'MobileContact'  => array(
					'type'     => 'text',
					'required' => true,
					'label'    => __( 'Mobile', 'sb-core' )
				),
				'EmailContact'   => array(
					'type'     => 'email',
					'required' => true,
					'label'    => __( 'Email', 'sb-core' )
				)
			);

			return apply_filters( 'hocwp_theme_extension_vip_author_contact_fields', $fields );
		}

		public function get_author_contact_default_value( $key, $data = array() ) {
			$value = isset( $data[ $key ] ) ? $data[ $key ] : '';

			return apply_filters( 'hocwp_theme_extension_vip_author_contact_default_value', $value, $key, $data );
		}

		public function vip_post_query_args( $query_vars = null ) {
			$meta_query = isset( $query_vars['meta_query'] ) ? $query_vars['meta_query'] : '';

			if ( ! is_array( $meta_query ) ) {
				$meta_query = array();
			}

			$meta_query['relation'] = 'AND';

			$now = current_time( 'timestamp' );

			$meta_query[] = array(
				'relation' => 'AND',
				array(
					'key'   => 'vip',
					'type'  => 'NUMERIC',
					'value' => 1
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => 'vip_expired',
						'type'    => 'NUMERIC',
						'value'   => $now,
						'compare' => '>='
					),
					array(
						'key'     => 'vip_expired',
						'compare' => 'NOT EXISTS'
					)
				)
			);

			$query_vars['meta_query'] = $meta_query;

			unset( $meta_query, $now );

			return $query_vars;
		}

		public function pre_get_posts_action( $query ) {
			if ( $query instanceof WP_Query ) {
				if ( ! is_admin() ) {
					$feed = $query->get( 'feed' );

					// Allow RSS Feed query VIP posts.
					if ( 'feed' == $feed ) {
						$type = isset( $_GET['type'] ) ? $_GET['type'] : '';
						$type = strtolower( $type );

						if ( 'vip' == $type ) {
							$meta_query = $query->get( 'meta_query' );

							if ( ! is_array( $meta_query ) ) {
								$meta_query = array();
							}

							$meta_query['relation'] = 'AND';

							$meta_query[] = array(
								'key'   => 'vip',
								'type'  => 'NUMERIC',
								'value' => 1
							);

							$query->set( 'meta_query', $meta_query );
						}
					} else {
						if ( isset( $query->query['vip_first'] ) ) {
							remove_action( 'pre_get_posts', array( $this, 'pre_get_posts_action' ) );

							$query_vars = $query->query;

							$meta_query = isset( $query_vars['meta_query'] ) ? $query_vars['meta_query'] : '';

							if ( ! is_array( $meta_query ) ) {
								$meta_query = array();
							}

							$query_vars['fields']  = 'ids';
							$query_vars['orderby'] = 'date';
							$query_vars['order']   = 'desc';

							$bk_meta_query = $meta_query;

							$query_vars = $this->vip_post_query_args( $query_vars );

							$tmp_query = new WP_Query( $query_vars );

							$ppp = $query->get( 'posts_per_page' );

							if ( ! is_numeric( $ppp ) ) {
								$ppp = HT_Util()->get_posts_per_page( is_home() );
							}

							$posts = array();

							if ( $tmp_query->have_posts() ) {
								$posts = $tmp_query->get_posts();
							}

							$count = count( $posts );

							if ( $count < $ppp ) {
								$tmp_ppp = $ppp - $count;

								$meta_query = $bk_meta_query;

								$query_vars['posts_per_page'] = $tmp_ppp;

								$meta_query['relation'] = 'and';

								$meta_query[] = array(
									'key'   => 'featured',
									'value' => 1
								);

								$query_vars['meta_query'] = $meta_query;

								if ( HT()->array_has_value( $posts ) ) {
									$query_vars['post__not_in'] = $posts;
								}

								$tmp_query = new WP_Query( $query_vars );

								if ( $tmp_query->have_posts() ) {
									$posts = array_merge( $posts, $tmp_query->get_posts() );

									$posts = array_filter( $posts );
									$posts = array_unique( $posts );
								}
							}

							$count = count( $posts );

							if ( $count < $ppp ) {
								$tmp_ppp = $ppp - $count;

								$meta_query = $bk_meta_query;

								$query_vars['posts_per_page'] = $tmp_ppp;

								$query_vars['meta_query'] = $meta_query;

								if ( HT()->array_has_value( $posts ) ) {
									$query_vars['post__not_in'] = $posts;
								}

								$tmp_query = new WP_Query( $query_vars );

								if ( $tmp_query->have_posts() ) {
									$posts = array_merge( $posts, $tmp_query->get_posts() );

									$posts = array_filter( $posts );
									$posts = array_unique( $posts );
								}
							}

							if ( HT()->array_has_value( $posts ) ) {
								$query->set( 'post__in', $posts );
								$query->set( 'orderby', 'post__in' );
							}

							unset( $query_vars, $meta_query, $bk_meta_query, $tmp_ppp, $tmp_query, $count );

							add_action( 'pre_get_posts', array( $this, 'pre_get_posts_action' ) );
						}
					}
				}
			}
		}

		public function post_added_form() {
			include $this->folder_path . '/form-add-post-success.php';
		}

		public function can_upload_gallery() {
			return (bool) HT_Options()->get_tab( 'upload_gallery', '', 'vip' );
		}

		public function post_added_meta( $post_id ) {
			$now = current_time( 'timestamp' );

			$data = array(
				'ID' => $post_id
			);

			$start_date = isset( $_POST['StartDate'] ) ? $_POST['StartDate'] : '';
			$start_date = HT()->string_to_datetime( $start_date, 'Y-m-d H:i:s' );

			if ( strtotime( $start_date ) > $now ) {
				$data['post_date']     = $start_date;
				$data['post_data_gmt'] = $start_date;
				$data['post_status']   = 'future';
				$data['edit_date']     = true;
			}

			$end_date = isset( $_POST['EndDate'] ) ? $_POST['EndDate'] : '';
			$end_date = HT()->string_to_datetime( $end_date, 'Y-m-d' );

			update_post_meta( $post_id, 'vip_expired', strtotime( $end_date ) );

			$type_of_post = isset( $_POST['type_of_post'] ) ? $_POST['type_of_post'] : '';

			if ( 'vip' == $type_of_post ) {
				update_post_meta( $post_id, 'vip', 1 );
			}

			if ( $this->can_upload_gallery() ) {
				$gallery = isset( $_FILES['post_gallery'] ) ? $_FILES['post_gallery'] : '';

				if ( HT()->array_has_value( $gallery ) ) {
					$name     = isset( $gallery['name'] ) ? $gallery['name'] : '';
					$tmp_name = isset( $gallery['tmp_name'] ) ? $gallery['tmp_name'] : '';

					$ids = '';

					if ( HT()->array_has_value( $name ) ) {
						foreach ( (array) $name as $key => $in ) {
							$upload = HT_Util()->upload_file( $in, @file_get_contents( $tmp_name[ $key ] ) );

							if ( HT()->array_has_value( $upload ) && isset( $upload['id'] ) && HT()->is_positive_number( $upload['id'] ) ) {
								$ids .= $upload['id'] . ',';
							}
						}
					} else {
						$upload = HT_Util()->upload_file( $name, @file_get_contents( $tmp_name ) );

						if ( HT()->array_has_value( $upload ) && isset( $upload['id'] ) && HT()->is_positive_number( $upload['id'] ) ) {
							$ids .= $upload['id'] . ',';
						}
					}

					$ids = trim( $ids, ',' );

					if ( ! empty( $gallery ) && ! empty( $ids ) ) {
						$gallery = '[gallery size="full" ids="' . $ids . '"]';
						update_post_meta( $post_id, 'gallery', $gallery );
					}
				}
			}

			$pay_later = HT_Options()->get_tab( 'pay_later', '', 'vip' );
			$pay_later = absint( $pay_later );

			$total_cost = isset( $_POST['total_cost'] ) ? $_POST['total_cost'] : '';
			$total_cost = floatval( $total_cost );

			if ( 1 != $pay_later && is_user_logged_in() ) {
				$user = wp_get_current_user();

				$coin = get_user_meta( $user->ID, 'coin', true );
				$coin = floatval( $coin );

				$cost = HTE_VIP_Management()->get_vip_post_price();

				if ( HT()->is_positive_number( $cost ) && $coin >= $cost ) {
					$coin_rate = HT_Options()->get_tab( 'coin_rate', '', 'vip' );
					$coin_rate = floatval( $coin_rate );

					$cost = $total_cost * $coin_rate;

					$coin -= $cost;

					update_user_meta( $user->ID, 'coin', $coin );
				}
			}

			$contact_name    = isset( $_POST['NameContact'] ) ? $_POST['NameContact'] : '';
			$contact_address = isset( $_POST['AddressContact'] ) ? $_POST['AddressContact'] : '';
			$contact_phone   = isset( $_POST['PhoneContact'] ) ? $_POST['PhoneContact'] : '';
			$contact_mobile  = isset( $_POST['MobileContact'] ) ? $_POST['MobileContact'] : '';
			$contact_email   = isset( $_POST['EmailContact'] ) ? $_POST['EmailContact'] : '';

			update_post_meta( $post_id, 'vip_contact_name', $contact_name );
			update_post_meta( $post_id, 'vip_contact_address', $contact_address );
			update_post_meta( $post_id, 'vip_contact_phone', $contact_phone );
			update_post_meta( $post_id, 'vip_contact_mobile', $contact_mobile );

			if ( is_email( $contact_email ) ) {
				update_post_meta( $post_id, 'vip_contact_email', $contact_email );
			}

			update_post_meta( $post_id, 'vip_cost', $total_cost );

			wp_update_post( $data );
		}

		public function save_post_action( $post_id ) {
			if ( ! HT_Util()->can_save_post( $post_id, 'vip-content-information', 'vip-content-information_nonce' ) ) {
				return;
			}

			$auto_vip_post = $this->get_option( 'auto_vip_post' );

			if ( isset( $_POST['vip_expired'] ) ) {
				$vip_expired = $_POST['vip_expired'];

				if ( ! empty( $vip_expired ) && ( ! isset( $_POST['add_vip_day'] ) || empty( $_POST['add_vip_day'] ) ) ) {
					$now = current_time( 'timestamp' );

					$vip_expired = HT()->string_to_datetime( $vip_expired );
					$vip_expired = strtotime( $vip_expired );

					if ( $vip_expired > $now && 1 == $auto_vip_post ) {
						update_post_meta( $post_id, 'vip', 1 );
					}
				}
			}

			if ( isset( $_POST['add_vip_day'] ) && ! empty( $_POST['add_vip_day'] ) ) {
				global $hocwp_theme;

				$options    = $hocwp_theme->options;
				$post_price = isset( $options['vip']['post_price'] ) ? $options['vip']['post_price'] : '';
				$post_price = absint( $post_price );

				$obj    = get_post( $post_id );
				$author = $obj->post_author;

				$coin = get_user_meta( $author, 'coin', true );
				$coin = absint( $coin );

				$day  = absint( $_POST['add_vip_day'] );
				$cost = $day * $post_price;

				if ( $coin < $post_price || $coin < $cost ) {
					$params = array(
						'message' => __( 'You do not have enough coin to add more day for VIP content.', 'sb-core' ),
						'type'    => 'error',
						'echo'    => false
					);

					$msg = HT_Util()->admin_notice( $params );
					set_transient( 'hocwp_add_vip_post_day_message', $msg );
				} else {
					$now = current_time( 'timestamp' );

					$vip_expired = get_post_meta( $post_id, 'vip_expired', true );

					if ( ! is_numeric( $vip_expired ) || $vip_expired < $now ) {
						$vip_expired = $now;
					}

					$vip_expired = strtotime( '+' . $day . ' day', $vip_expired );

					$res = update_post_meta( $post_id, 'vip_expired', $vip_expired );

					if ( $res ) {
						if ( 1 == $auto_vip_post ) {
							update_post_meta( $post_id, 'vip', 1 );
						}

						$coin -= $cost;
						update_user_meta( $author, 'coin', $coin );

						$params = array(
							'message' => sprintf( __( 'You have added %s more VIP days for this post.', 'sb-core' ), $day ),
							'type'    => 'success',
							'echo'    => false
						);

						$msg = HT_Util()->admin_notice( $params );
						set_transient( 'hocwp_add_vip_post_day_message', $msg );
					}
				}
			}

			$this->cancel_vip_post( $post_id );
		}

		public function check_post_before_submit( $result ) {
			$type_of_post = isset( $_POST['type_of_post'] ) ? $_POST['type_of_post'] : '';

			if ( 'vip' == $type_of_post ) {
				if ( is_user_logged_in() ) {
					$user = wp_get_current_user();

					$pay_later = HT_Options()->get_tab( 'pay_later', '', 'vip' );
					$pay_later = absint( $pay_later );

					if ( 1 != $pay_later ) {
						$coin = get_user_meta( $user->ID, 'coin', true );
						$coin = floatval( $coin );
						$cost = HTE_VIP_Management()->get_vip_post_price();

						if ( HT()->is_positive_number( $cost ) && $coin < $cost ) {
							$result = new WP_Error( 'not_enough_coin', __( 'You do not have enough coin to post VIP content.', 'sb-core' ) );
						}
					}
				}
			}

			return $result;
		}

		public function get_vip_post_price( $return = 'coin' ) {
			$post_price = HT_Options()->get_tab( 'post_price', '', 'vip' );

			$post_price = floatval( $post_price );

			if ( 'coin' !== $return ) {
				$coin_rate = HT_Options()->get_tab( 'coin_rate', '', 'vip' );

				if ( is_numeric( $coin_rate ) ) {
					$post_price /= $coin_rate;
				}
			}

			$post_price = floatval( $post_price );

			return $post_price;
		}

		public function wp_enqueue_scripts_action() {
			if ( function_exists( 'HTE_Add_Post_Frontend' ) ) {
				$page = HT_Util()->get_theme_option_page( 'new_post_page', 'add_post_frontend' );

				if ( HT_Options()->check_page_valid( $page ) && is_page( $page->ID ) ) {
					wp_enqueue_style( 'hte-vip-form-add-post-style', SB_Core()->url . '/css/vip-form-add-post.css' );

					wp_enqueue_script( 'hte-vip-form-add-post', SB_Core()->url . '/js/vip-form-add-post.js', array(
						'jquery',
						'hocwp-theme'
					), false, true );

					HT_Enqueue()->datepicker();

					$l10n = array(
						'l10n' => array(
							'not_enough_coin_message'     => sprintf( __( 'You must have at least %s coin to add VIP content. Please add more coin.', 'sb-core' ), $this->get_vip_post_price() ),
							'limit_gallery_message'       => sprintf( __( 'You can only upload %d gallery images each post.', 'sb-core' ), $this->gallery_upload_limit ),
							'confirm_submit_post_message' => _x( 'Are you sure you want to submit this post?', 'add vip post', 'sb-core' )
						)
					);

					wp_localize_script( 'hte-vip-form-add-post', 'hteVIP', $l10n );
				}
			}
		}

		public function add_vip_post_form( $html, $post_added ) {
			if ( empty( $html ) ) {
				ob_start();

				if ( $post_added ) {
					include $this->folder_path . '/form-add-post-success.php';
				} else {
					include $this->folder_path . '/form-add-post.php';
				}

				$html = ob_get_clean();
			}

			return $html;
		}

		public function meta_post() {
			global $hocwp_theme;

			$options    = $hocwp_theme->options;
			$post_price = isset( $options['vip']['post_price'] ) ? $options['vip']['post_price'] : '';
			$post_price = absint( $post_price );

			if ( 0 < $post_price ) {
				$meta = new HOCWP_Theme_Meta_Post();

				if ( function_exists( 'hocwp_add_post_frontend_post_type' ) ) {
					$post_types = hocwp_add_post_frontend_post_type();
				} else {
					$post_types = array( 'post' );
				}

				$meta->set_post_types( $post_types );
				$meta->form_table = true;

				$meta->set_title( __( 'VIP Content Information', 'sb-core' ) );
				$meta->set_id( 'vip-content-information' );

				$meta->add_field( new HOCWP_Theme_Meta_Field( 'vip', __( 'VIP:', 'sb-core' ), 'input', array(
					'type' => 'checkbox',
					'text' => __( 'Mark this post as VIP content.', 'sb-core' )
				) ) );

				$args = array( 'autocomlete' => 'off', 'class' => 'regular-text' );

				$field = hocwp_theme_create_meta_field( 'vip_expired', __( 'Expiry day:', 'sb-core' ), 'datetime_picker', $args, 'timestamp' );
				$meta->add_field( $field );

				$args = array(
					'type'        => 'number',
					'description' => sprintf( __( 'Add more day for your VIP content. %s coins per day.', 'sb-core' ), $post_price ),
					'value'       => '',
					'class'       => 'regular-text'
				);

				$field = hocwp_theme_create_meta_field( 'add_vip_day', __( 'Add more day:', 'sb-core' ), 'input', $args, 'positive_integer' );
				$meta->add_field( $field );

				$meta = new HOCWP_Theme_Meta_Post();
				$meta->set_post_types( $post_types );
				$meta->form_table = true;

				$meta->set_title( __( 'VIP Contact Information', 'sb-core' ) );
				$meta->set_id( 'vip-contact-information' );

				$meta->add_field( new HOCWP_Theme_Meta_Field( 'vip_contact_name', __( 'Full Name:', 'sb-core' ) ) );
				$meta->add_field( new HOCWP_Theme_Meta_Field( 'vip_contact_address', __( 'Address:', 'sb-core' ) ) );
				$meta->add_field( new HOCWP_Theme_Meta_Field( 'vip_contact_phone', __( 'Phone:', 'sb-core' ) ) );
				$meta->add_field( new HOCWP_Theme_Meta_Field( 'vip_contact_mobile', __( 'Mobile:', 'sb-core' ) ) );
				$meta->add_field( new HOCWP_Theme_Meta_Field( 'vip_contact_email', __( 'Email:', 'sb-core' ), 'input', array( 'type' => 'email' ) ) );
				$meta->add_field( new HOCWP_Theme_Meta_Field( 'vip_cost', __( 'Total Cost:', 'sb-core' ), 'input', array( 'type' => 'number' ) ) );
			}
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HTE_VIP_Management()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HTE_VIP_Management() {
	return HOCWP_EXT_VIP_Management::get_instance();
}