<?php
/*
 * Name: Trending
 * Description: Trending helps viewers see what's happening on your website.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_EXT_Trending' ) ) {
	class HOCWP_EXT_Trending extends HOCWP_Theme_Extension {
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

			add_filter( 'hocwp_theme_custom_taxonomies', array( $this, 'custom_taxonomies_filter' ) );
			add_action( 'init', array( $this, 'init_action' ) );

			add_filter( 'pre_comment_approved', array( $this, 'pre_comment_approved_filter' ), 10, 2 );
			add_action( 'wp', array( $this, 'wp_action' ) );

			add_action( 'admin_init', array( $this, 'admin_init_action' ) );
		}

		protected $table_name = 'hw_trending';

		public function custom_taxonomies_filter( $taxonomies ) {
			$pa = array(
				'public'   => true,
				'_builtin' => false
			);

			$post_types = get_post_types( $pa );

			$post_types[] = 'post';
			$post_types[] = 'page';

			$args = array(
				'name'      => __( 'Trending', 'sb-core' ),
				'post_type' => $post_types
			);

			$args = HT_Util()->taxonomy_args( $args );

			$taxonomies['trending'] = $args;

			return $taxonomies;
		}

		public function init_action() {

		}

		public function admin_init_action() {
			global $pagenow, $plugin_page;

			if ( 'themes.php' == $pagenow && HOCWP_Theme()->get_prefix() == $plugin_page ) {
				$tab = HT()->get_method_value( 'tab', 'GET' );

				if ( 'extension' == $tab ) {
					$this->database_table_init();
					flush_rewrite_rules();
				}
			}
		}

		public function wp_action() {
			if ( is_singular() || is_single() || is_page() ) {
				$this->insert_simple();
			}
		}

		public function pre_comment_approved_filter( $approved, $commentdata ) {
			$post_id = HT()->get_value_in_array( $commentdata, 'comment_post_ID' );

			if ( HT()->is_positive_number( $post_id ) ) {
				$this->insert( array( 'post_id' => $post_id, 'action' => 'comment' ) );
			}

			return $approved;
		}

		public function database_table_init() {
			global $wpdb;

			$table_name = $wpdb->prefix . $this->table_name;

			$sql = "ID bigint(20) unsigned NOT NULL auto_increment,
		        post_id bigint(20) unsigned NOT NULL default '0',
		        post_date datetime NOT NULL default '0000-00-00 00:00:00',
		        post_type varchar(20) NOT NULL default 'post',
		        action varchar(20) NOT NULL default '',
		        PRIMARY KEY (ID),
		        KEY post_id (post_id)";

			HT_Util()->create_database_table( $table_name, $sql );
		}

		public function insert_simple( $post_id = null, $action = 'view' ) {
			if ( null == $post_id || ! HT()->is_positive_number( $post_id ) ) {
				$post_id = get_the_ID();
			}

			$this->insert( array( 'post_id' => $post_id, 'action' => $action ) );
		}

		public function insert( $args = array() ) {
			if ( ! is_array( $args ) && is_numeric( $args ) ) {
				$args = array( 'post_id' => $args );
			}

			$post_id = HT()->get_value_in_array( $args, 'post_id' );

			if ( HT()->is_positive_number( $post_id ) ) {
				global $wpdb;

				$datetime = current_time( 'mysql' );

				$post   = get_post( $post_id );
				$action = HT()->get_value_in_array( $args, 'action', 'view' );

				if ( empty( $action ) ) {
					$action = 'view';
				}

				$table_name = $wpdb->prefix . $this->table_name;

				if ( ! HT_util()->is_database_table_exists( $table_name ) ) {
					return;
				}

				$trending_day = HT()->get_value_in_array( $args, 'trending_day', 7 );

				$trending_day = absint( apply_filters( 'hocwp_theme_extension_trending_interval', $trending_day ) );

				$sql = "DELETE FROM $table_name WHERE UNIX_TIMESTAMP(post_date) < UNIX_TIMESTAMP(DATE_SUB('$datetime', INTERVAL $trending_day DAY))";

				$wpdb->query( $sql );

				$sql = "INSERT INTO $table_name (post_id, post_date, post_type, action)";

				$sql .= " VALUES ('$post_id', '$datetime', '$post->post_type', '$action')";

				$wpdb->query( $sql );
			}
		}

		public function get( $post_type = null, $number = false ) {
			global $wpdb;
			$table_name = $wpdb->prefix . $this->table_name;

			$sql = "SELECT post_id, COUNT(post_id) as count FROM $table_name";

			if ( null != $post_type ) {
				if ( is_array( $post_type ) ) {
					$sql .= " WHERE";

					foreach ( $post_type as $type ) {
						$sql .= " post_type = '$type' OR";
					}

					$sql = trim( $sql, ' OR' );
				} elseif ( ! empty( $post_type ) ) {
					$sql .= " WHERE post_type = '$post_type'";
				}
			}

			$sql .= " GROUP BY post_id ORDER BY count DESC";

			if ( is_numeric( $number ) ) {
				$sql .= ' LIMIT ' . $number;
			}

			$result = $wpdb->get_results( $sql );

			return $result;
		}

		public function get_post_ids( $post_type = null, $number = false ) {
			$trends = $this->get( $post_type, $number );

			$post_ids = array();

			if ( HT()->array_has_value( $trends ) ) {
				foreach ( $trends as $trend ) {
					$post_ids[] = $trend->post_id;
				}
			}

			return $post_ids;
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HTE_Trending()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HTE_Trending() {
	return HOCWP_EXT_Trending::get_instance();
}