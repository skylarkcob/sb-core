<?php
/*
 * Name: Links Manager
 * Description: Re-enable links management functions on WordPress site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_filter( 'pre_option_link_manager_enabled', '__return_true' );

if ( ! class_exists( 'HOCWP_EXT_Bookmark' ) ) {
	class HOCWP_EXT_Bookmark extends HOCWP_Theme_Extension {
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

			global $pagenow;

			if ( is_admin() ) {
				if ( 'link.php' == $pagenow || 'link-add.php' == $pagenow ) {
					add_filter( 'wp_redirect', array( $this, 'wp_redirect_bookmark_add_edit' ), 1, 2 );
					add_action( 'load-link.php', array( $this, 'meta_boxes' ) );
					add_action( 'load-link-add.php', array( $this, 'meta_boxes' ) );
					add_action( 'add_link', array( $this, 'add_link_action' ), 999 );
				} elseif ( 'link-manager.php' == $pagenow ) {
					add_filter( 'manage_link-manager_columns', array( $this, 'manage_columns_filter' ) );
					add_action( 'manage_link_custom_column', array( $this, 'manage_link_custom_column' ), 10, 2 );
					add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
				}
			}
		}

		public function admin_scripts() {
			global $pagenow;

			if ( 'link-manager.php' == $pagenow && defined( 'HOCWP_THEME_CSS_SUFFIX' ) ) {
				wp_enqueue_style( 'link-manager-style', HOCWP_EXT_URL . '/css/link-manager' . HOCWP_THEME_CSS_SUFFIX );
			}
		}

		public function manage_link_custom_column( $column_name, $link_id ) {
			if ( 'thumbnail' == $column_name ) {
				$id = get_post_meta( $link_id, 'thumbnail_id', true );

				if ( HT()->is_positive_number( $id ) ) {
					echo wp_get_attachment_image( $id, 'full' );
				}
			}
		}

		public function add_link_action( $link_id ) {
			$location = admin_url( 'link.php' );

			$params   = array( 'action' => 'edit', 'link_id' => $link_id, 'added' => 'true' );
			$location = add_query_arg( $params, $location );

			wp_redirect( $location );
			exit;
		}

		public function manage_columns_filter( $columns ) {
			$columns['thumbnail'] = __( 'Thumbnail', 'sb-core' );

			return $columns;
		}

		public function meta_boxes() {
			$meta = new HOCWP_Theme_Meta_Bookmark();

			$meta->set_context( 'side' );
			$meta->set_title( __( 'Featured Image', 'sb-core' ) );
			$meta->set_id( 'featured-image-box' );

			$field = hocwp_theme_create_meta_field( 'thumbnail_id', '', 'media_upload' );
			$meta->add_field( $field );
		}

		public function wp_redirect_bookmark_add_edit( $location, $status ) {
			if ( 302 == $status ) {
				$action = isset( $_POST['action'] ) ? $_POST['action'] : '';

				if ( $location == admin_url( 'link-manager.php' ) ) {
					if ( 'save' == $action && isset( $_POST['link_id'] ) && HT()->is_positive_number( $_POST['link_id'] ) ) {
						$location = admin_url( 'link.php' );

						$params = array( 'action' => 'edit', 'link_id' => $_POST['link_id'], 'updated' => 'true' );

						$location = add_query_arg( $params, $location );
					}
				}
			}

			return $location;
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HTE_Bookmark()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HTE_Bookmark() {
	return HOCWP_EXT_Bookmark::get_instance();
}