<?php
/*
 * Name: Classifieds
 * Description: Using for real estate and classifieds websites.
 * Requires core: 6.6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_EXT_Classifieds' ) ) {
	class HOCWP_EXT_Classifieds extends HOCWP_Theme_Extension {
		protected static $instance;

		public $type_taxonomy = 'classifieds_type';

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

			if ( ! defined( 'HOCWP_THEME_CORE_VERSION' ) || version_compare( HOCWP_THEME_CORE_VERSION, '6.5.4', '<' ) ) {
				add_action( 'admin_notices', array( $this, 'upgrade_theme_core_notice' ) );

				return;
			}

			add_action( 'init', array( $this, 'init_action' ) );

			add_filter( 'hocwp_theme_custom_post_types', array( $this, 'post_types_filter' ), 0 );
			add_filter( 'hocwp_theme_custom_taxonomies', array( $this, 'taxonomies_filter' ), 0 );

			if ( is_admin() ) {
				$tab = new HOCWP_Theme_Admin_Setting_Tab( 'classifieds', __( 'Classifieds', 'sb-core' ), '<span class="dashicons dashicons-editor-ul"></span>' );

				$args = array(
					'type' => 'checkbox',
					'text' => __( 'Use category as location.', 'sb-core' )
				);

				$tab->add_field_array( array(
					'id'    => 'category_as_location',
					'title' => __( 'Category as Location', 'sb-core' ),
					'args'  => array(
						'default'       => 1,
						'type'          => 'boolean',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
						'callback_args' => $args
					)
				) );

				add_action( 'load-post.php', array( $this, 'meta_post' ) );
				add_action( 'load-post-new.php', array( $this, 'meta_post' ) );
				add_action( 'restrict_manage_posts', array( $this, 'admin_posts_table_filter' ) );
			} else {
				add_action( 'hte_add_post_frontend_form_middle', array( $this, 'add_post_form_middle' ) );
				add_action( 'hocwp_theme_extension_add_post_frontend_post_added', array( $this, 'post_added_meta' ) );
			}

			add_action( 'pre_get_posts', array( $this, 'pre_get_posts_action' ) );
		}

		public function admin_posts_table_filter( $post_type ) {
			$taxonomies = get_object_taxonomies( $post_type );

			if ( HT()->array_has_value( $taxonomies ) && in_array( $this->type_taxonomy, $taxonomies ) ) {
				$tax = get_taxonomy( $this->type_taxonomy );
				?>
                <label for="filter-by-<?php echo $this->type_taxonomy; ?>"
                       class="screen-reader-text"><?php printf( __( 'Filter by %s', 'sb-core' ), $tax->labels->singular_name ); ?></label>
				<?php
				$selected = isset( $_GET['c_type'] ) ? $_GET['c_type'] : '';

				$args = array(
					'taxonomy'        => $this->type_taxonomy,
					'name'            => 'c_type',
					'id'              => 'filter-by-' . $this->type_taxonomy,
					'show_option_all' => sprintf( __( 'Filter by %s', 'sb-core' ), $tax->labels->singular_name ),
					'selected'        => $selected
				);

				wp_dropdown_categories( $args );
			}
		}

		public function post_added_meta( $post_id ) {
			$google_maps = isset( $_POST['google_maps'] ) ? $_POST['google_maps'] : '';
			update_post_meta( $post_id, 'google_maps', $google_maps );

			$custom_location = isset( $_POST['custom_location'] ) ? $_POST['custom_location'] : '';
			update_post_meta( $post_id, 'address', $custom_location );
		}

		public function add_post_form_middle() {
			if ( function_exists( 'HTE_VIP_Management' ) ) {
				?>
                <div class="form-group">
                    <label class="control-label col-md-3"><?php _e( 'Maps:', 'sb-core' ); ?></label>

                    <div class="col-md-9">
						<?php
						$args = array(
							'name'      => 'google_maps',
							'draggable' => true
						);

						$lng = HT_Options()->get_tab( 'default_lng', '', 'google_maps' );
						$lat = HT_Options()->get_tab( 'default_lat', '', 'google_maps' );

						if ( ! empty( $lat ) ) {
							$args['latitude'] = $lat;
						}

						if ( ! empty( $lng ) ) {
							$args['longitude'] = $lng;
						}

						HT_HTML_Field()->google_maps( $args );
						?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="customLocation"
                           class="control-label col-md-3"><?php _e( 'Custom Location:', 'sb-core' ); ?></label>

                    <div class="col-md-9">
                        <input id="customLocation" name="custom_location" type="text" data-for-maps="google_maps_marker"
                               class="form-control">
                    </div>
                </div>
				<?php
			} else {
				?>
                <div class="form-group">
                    <label class="control-label"><?php _e( 'Maps:', 'sb-core' ); ?></label>
					<?php
					$args = array(
						'name'      => 'google_maps',
						'draggable' => true
					);

					$lng = HT_Options()->get_tab( 'default_lng', '', 'google_maps' );
					$lat = HT_Options()->get_tab( 'default_lat', '', 'google_maps' );

					if ( ! empty( $lat ) ) {
						$args['latitude'] = $lat;
					}

					if ( ! empty( $lng ) ) {
						$args['longitude'] = $lng;
					}

					HT_HTML_Field()->google_maps( $args );
					?>
                </div>
                <div class="form-group">
                    <label class="control-label"><?php _e( 'Custom Location:', 'sb-core' ); ?></label>
                    <input id="customLocation" name="custom_location" type="text" data-for-maps="google_maps_marker"
                           class="form-control">
                </div>
				<?php
			}
		}

		public function pre_get_posts_action( $query ) {
			if ( $query instanceof WP_Query && $query->is_main_query() ) {
				if ( is_admin() ) {
					$c_type = isset( $_GET['c_type'] ) ? $_GET['c_type'] : '';

					if ( HT()->is_positive_number( $c_type ) ) {
						$tax_query = $query->get( 'tax_query' );

						if ( ! is_array( $tax_query ) ) {
							$tax_query = array();
						}

						$tax_query['relation'] = 'AND';

						$tax_query[] = array(
							'taxonomy' => $this->type_taxonomy,
							'field'    => 'term_id',
							'terms'    => array( $c_type )
						);

						$query->set( 'tax_query', $tax_query );
					}
				} else {
					if ( is_search() ) {
						$type     = HT()->get_value_in_array( $_REQUEST, 'type' );
						$province = HT()->get_value_in_array( $_REQUEST, 'province' );
						$district = HT()->get_value_in_array( $_REQUEST, 'district' );
						$ward     = HT()->get_value_in_array( $_REQUEST, 'ward' );
						$street   = HT()->get_value_in_array( $_REQUEST, 'street' );
						$price    = HT()->get_value_in_array( $_REQUEST, 'price' );
						$acreage  = HT()->get_value_in_array( $_REQUEST, 'acreage' );
						$object   = HT()->get_value_in_array( $_REQUEST, 'object' );
						$salary   = HT()->get_value_in_array( $_REQUEST, 'salary' );
						$location = HT()->get_value_in_array( $_REQUEST, 'location' );

						$tax_query = $query->get( 'tax_query' );

						if ( ! is_array( $tax_query ) ) {
							$tax_query = array(
								'relation' => 'AND'
							);
						}

						if ( HT()->is_positive_number( $type ) ) {
							$tax_item = array(
								'taxonomy' => $this->type_taxonomy,
								'field'    => 'id',
								'terms'    => $type
							);

							HT_Sanitize()->tax_query( $tax_item, $tax_query );
						}

						if ( HT()->is_positive_number( $location ) ) {
							$tax_item = array(
								'taxonomy' => 'category',
								'field'    => 'id',
								'terms'    => $location
							);

							HT_Sanitize()->tax_query( $tax_item, $tax_query );
						}

						if ( HT()->is_positive_number( $province ) ) {
							$tax_item = array(
								'taxonomy' => 'category',
								'field'    => 'id',
								'terms'    => $province
							);

							HT_Sanitize()->tax_query( $tax_item, $tax_query );
						}

						if ( HT()->is_positive_number( $district ) ) {
							$tax_item = array(
								'taxonomy' => 'category',
								'field'    => 'id',
								'terms'    => $district
							);

							HT_Sanitize()->tax_query( $tax_item, $tax_query );
						}

						if ( HT()->is_positive_number( $ward ) ) {
							$tax_item = array(
								'taxonomy' => 'category',
								'field'    => 'id',
								'terms'    => $ward
							);

							HT_Sanitize()->tax_query( $tax_item, $tax_query );
						}

						if ( HT()->is_positive_number( $street ) ) {
							$tax_item = array(
								'taxonomy' => 'category',
								'field'    => 'id',
								'terms'    => $street
							);

							HT_Sanitize()->tax_query( $tax_item, $tax_query );
						}

						unset( $query->query['price'] );
						unset( $query->query_vars['price'] );

						if ( HT()->is_positive_number( $price ) ) {
							$tax_item = array(
								'taxonomy' => 'price',
								'field'    => 'id',
								'terms'    => $price
							);

							HT_Sanitize()->tax_query( $tax_item, $tax_query );
						}

						unset( $query->query['acreage'] );
						unset( $query->query_vars['acreage'] );

						if ( HT()->is_positive_number( $acreage ) ) {
							$tax_item = array(
								'taxonomy' => 'acreage',
								'field'    => 'id',
								'terms'    => $acreage
							);

							HT_Sanitize()->tax_query( $tax_item, $tax_query );
						}

						if ( HT()->is_positive_number( $object ) ) {
							$tax_item = array(
								'taxonomy' => 'classifieds_object',
								'field'    => 'id',
								'terms'    => $object
							);

							HT_Sanitize()->tax_query( $tax_item, $tax_query );
						}

						unset( $query->query['salary'] );
						unset( $query->query_vars['salary'] );

						if ( HT()->is_positive_number( $salary ) ) {
							$tax_item = array(
								'taxonomy' => 'salary',
								'field'    => 'id',
								'terms'    => $salary
							);

							HT_Sanitize()->tax_query( $tax_item, $tax_query );
						}

						$tax_query['relation'] = 'AND';

						$query->set( 'tax_query', $tax_query );
						$query->set( 'post_type', 'post' );
					}
				}
			}
		}

		public function category_as_location() {
			$cat_as_location = HT_Options()->get_tab( 'category_as_location', 1, 'classifieds' );

			return ( 1 == $cat_as_location ) ? true : false;
		}

		public function init_action() {
			if ( $this->category_as_location() ) {
				global $wp_taxonomies;

				$taxonomy_name = 'category';

				if ( isset( $wp_taxonomies[ $taxonomy_name ] ) ) {
					$name = _x( 'Locations', 'post location', 'sb-core' );

					$singular_name = _x( 'Location', 'post location', 'sb-core' );

					$labels = HT_Util()->taxonomy_labels( $name, $singular_name, $name );

					$wp_taxonomies[ $taxonomy_name ]->label  = $name;
					$wp_taxonomies[ $taxonomy_name ]->labels = (object) $labels;
				}
			}
		}

		public function meta_post() {
			$meta = new HOCWP_Theme_Meta_Post();

			$meta->add_post_type( 'post' );

			$meta->set_title( __( 'General Information', 'sb-core' ) );
			$meta->set_id( 'classifieds_general_information' );

			$meta->form_table = true;

			$args = array(
				'data-for-maps' => 'google_maps_marker'
			);

			$meta->add_field( new HOCWP_Theme_Meta_Field( 'address', __( 'Address:', 'sb-core' ), 'input', $args ) );

			$enable = apply_filters( 'hocwp_theme_extension_classifieds_use_meta_price', false );

			if ( $enable ) {
				$meta->add_field( new HOCWP_Theme_Meta_Field( 'price', __( 'Price:', 'sb-core' ) ) );
			}

			$meta->add_field( new HOCWP_Theme_Meta_Field( 'phone', __( 'Phone:', 'sb-core' ) ) );
			$meta->add_field( new HOCWP_Theme_Meta_Field( 'email', __( 'Email:', 'sb-core' ), 'input', array( 'type' => 'email' ) ) );

			$enable = apply_filters( 'hocwp_theme_extension_classifieds_use_meta_acreage', false );

			if ( $enable ) {
				$meta->add_field( new HOCWP_Theme_Meta_Field( 'acreage', __( 'Acreage:', 'sb-core' ) ) );
			}

			hocwp_theme_meta_box_editor_gallery( array( 'post_type' => 'post' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		}

		public function admin_enqueue_scripts() {
			HT_Enqueue()->google_maps();
		}

		public function upgrade_theme_core_notice() {
			if ( function_exists( 'HT_Admin' ) && method_exists( HT_Admin(), 'skip_admin_notices' ) && HT_Admin()->skip_admin_notices() ) {
				return;
			}

			$args = array(
				'type'    => 'warning',
				'message' => sprintf( __( '<strong>Warning:</strong> Extension <code>%s</code> requires theme core version as least %s.', 'sb-core' ), $this->name, '6.5.4' )
			);

			HT_Admin()->admin_notice( $args );
		}

		public function post_types_filter( $post_types ) {
			$args = array(
				'name'              => __( 'News', 'sb-core' ),
				'show_in_admin_bar' => true,
				'supports'          => array( 'editor', 'thumbnail', 'comments' )
			);

			$post_types['news'] = $args;

			return $post_types;
		}

		public function taxonomies_filter( $taxonomies ) {
			$args = array(
				'name'              => __( 'Classifieds Types', 'sb-core' ),
				'singular_name'     => __( 'Classifieds Type', 'sb-core' ),
				'menu_name'         => __( 'Types', 'sb-core' ),
				'post_type'         => 'post',
				'hierarchical'      => true,
				'show_admin_column' => true
			);

			$taxonomies[ $this->type_taxonomy ] = $args;

			$args = array(
				'name'              => __( 'Price', 'sb-core' ),
				'show_admin_column' => false,
				'post_type'         => 'post',
				'hierarchical'      => true
			);

			$taxonomies['price'] = $args;

			$args = array(
				'name'              => __( 'Acreages', 'sb-core' ),
				'singular_name'     => __( 'Acreage', 'sb-core' ),
				'show_admin_column' => false,
				'post_type'         => 'post',
				'hierarchical'      => true
			);

			$taxonomies['acreage'] = $args;

			$args = array(
				'name'          => __( 'Salaries', 'sb-core' ),
				'singular_name' => __( 'Salary', 'sb-core' ),
				'post_type'     => 'post',
				'hierarchical'  => true
			);

			$taxonomies['salary'] = $args;

			$args = array(
				'name'          => __( 'Currency Units', 'sb-core' ),
				'singular_name' => __( 'Currency Unit', 'sb-core' ),
				'menu_name'     => __( 'Units', 'sb-core' ),
				'post_type'     => 'post',
				'hierarchical'  => true
			);

			$taxonomies['currency_unit'] = $args;

			$args = array(
				'name'              => __( 'News Categories', 'sb-core' ),
				'singular_name'     => __( 'News Category', 'sb-core' ),
				'menu_name'         => __( 'Categories', 'sb-core' ),
				'post_type'         => 'news',
				'hierarchical'      => true,
				'show_admin_column' => true
			);

			$taxonomies['news_cat'] = $args;

			$args = array(
				'name'          => __( 'News Tags', 'sb-core' ),
				'singular_name' => __( 'News Tag', 'sb-core' ),
				'menu_name'     => __( 'Tags', 'sb-core' ),
				'post_type'     => 'news',
				'hierarchical'  => false
			);

			$taxonomies['news_tag'] = $args;

			return $taxonomies;
		}

		public function get_price( $post_id = null ) {
			$post_id = HT_Util()->return_post( $post_id, 'post_id' );

			$price = get_post_meta( $post_id, 'price', true );

			if ( empty( $price ) ) {
				$price = HT_Util()->get_first_term( $post_id, 'price' );

				if ( $price instanceof WP_Term ) {
					$price = $price->name;
				} else {
					$price = __( 'Agreed price', 'sb-core' );
				}
			}

			return apply_filters( 'hocwp_theme_extension_classifieds_price', $price, $post_id );
		}

		public function get_administrative_boundary( $post_id = null, $only_province = false, $reverse = false ) {
			$post_id = HT_Util()->return_post( $post_id, 'post_id' );

			$result = get_post_meta( $post_id, 'address', true );

			if ( empty( $result ) && $this->category_as_location() ) {
				$object = new HOCWP_Theme_Post( $post_id );
				$terms  = $object->get_ancestor_terms();

				if ( HT()->array_has_value( $terms ) ) {
					if ( $only_province ) {
						$parent = array_pop( $terms );
						$result = $parent->name;
					} else {
						if ( ! $reverse ) {
							$terms = array_reverse( $terms );
						}

						foreach ( $terms as $term ) {
							$result .= $term->name . ', ';
						}

						$result = rtrim( $result, ', ' );
					}
				}
			}

			return $result;
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HTE_Classifieds()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HTE_Classifieds() {
	return HOCWP_EXT_Classifieds::get_instance();
}