<?php
/*
 * Name: WooCommerce
 * Description: Add more functionality for your shop site which runs base on WooCommerce Plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_theme_woocommerce_activated' ) ) {
	function hocwp_theme_woocommerce_activated() {
		return class_exists( 'WC_Product' );
	}
}

if ( ! function_exists( 'hocwp_theme_load_extension_woocommerce' ) ) {
	function hocwp_theme_load_extension_woocommerce() {
		$load = apply_filters( 'hocwp_theme_load_extension_woocommerce', HT_extension()->is_active( __FILE__ ) );

		return $load;
	}
}

$load = hocwp_theme_load_extension_woocommerce();

if ( ! function_exists( 'hocwp_ext_wc_require_plugins' ) ) {
	function hocwp_ext_wc_require_plugins( $plugins ) {
		if ( ! in_array( 'woocommerce', $plugins ) ) {
			$plugins[] = 'woocommerce';
		}

		return $plugins;
	}
}

add_filter( 'hocwp_theme_required_plugins', 'hocwp_ext_wc_require_plugins' );

if ( ! $load || ! hocwp_theme_woocommerce_activated() ) {
	return;
}

if ( ! class_exists( 'HOCWP_EXT_WooCommerce' ) ) {
	class HOCWP_EXT_WooCommerce extends HOCWP_Theme_Extension {
		protected static $instance;

		public static function get_instance() {
			if ( ! ( self::$instance instanceof self ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			if ( self::$instance instanceof self ) {
				return;
			}

			parent::__construct( __FILE__ );

			add_action( 'after_setup_theme', array( $this, 'after_setup_theme_action' ), 999 );

			require_once dirname( __FILE__ ) . '/woocommerce/woocommerce.php';

			if ( ! is_admin() || HOCWP_THEME_DOING_AJAX ) {
				$custom_comment = $this->get_option( 'custom_comment' );

				if ( 1 == $custom_comment ) {
					add_filter( 'woocommerce_product_tabs', array( $this, 'woocommerce_product_tabs_filter' ), 99 );
					$replace_review = $this->get_option( 'replace_review' );

					if ( 1 == $replace_review ) {
						add_action( 'woocommerce_after_single_product_summary', array(
							$this,
							'woocommerce_after_single_product_summary_action'
						), 9 );
					}
				}
			}

			if ( ! is_admin() ) {
				add_filter( 'body_class', array( $this, 'body_class_filter' ) );

				add_filter( 'woocommerce_output_related_products_args', array(
					$this,
					'output_related_products_args_filter'
				) );
			}

			add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'add_to_cart_fragments_filter' ), 99999 );

			$keys = array(
				'woocommerce_shop_page_id',
				'woocommerce_cart_page_id',
				'woocommerce_checkout_page_id',
				'woocommerce_myaccount_page_id',
				'woocommerce_edit_address_page_id',
				'woocommerce_view_order_page_id',
				'woocommerce_change_password_page_id',
				'woocommerce_logout_page_id'
			);

			foreach ( $keys as $item ) {
				$item = str_replace( 'woocommerce_', '', $item );
				$item = str_replace( '_page_id', '', $item );
				add_filter( 'woocommerce_get_' . $item . '_page_id', array( $this, 'pll_page_id_filter' ), 999 );
			}
		}

		public function pll_page_id_filter( $page_id ) {
			if ( function_exists( 'pll_get_post' ) ) {
				$page_id = pll_get_post( $page_id );
			}

			return $page_id;
		}

		public function cart_link() {
			?>
            <a class="cart-contents" href="<?php echo esc_url( wc_get_cart_url() ); ?>"
               title="<?php esc_attr_e( 'View your shopping cart', 'sb-core' ); ?>">
				<?php
				ob_start();
				$count = WC()->cart->get_cart_contents_count();

				$item_count_text = sprintf( _nx( '%d item', '%d items', $count, 'product', 'sb-core' ), $count );
				?>
                <span class="amount"><?php echo wp_kses_data( WC()->cart->get_cart_subtotal() ); ?></span>
                <span class="count"><?php echo esc_html( $item_count_text ); ?></span>
                <span class="count-number"><?php echo number_format( $count ); ?></span>
				<?php
				$cart_content = apply_filters( 'sb_core_ext_woocommerce_mini_cart_content', ob_get_clean() );

				echo $cart_content;
				?>
            </a>
			<?php
		}

		public function header_cart( $callback = '' ) {
			if ( ! class_exists( 'WC_Widget_Cart' ) ) {
				return;
			}

			if ( is_cart() ) {
				$class = 'current-menu-item';
			} else {
				$class = '';
			}
			?>
            <ul id="site-header-cart" class="site-header-cart">
                <li class="<?php echo esc_attr( $class ); ?>">
					<?php
					if ( is_callable( $callback ) ) {
						call_user_func( $callback );
					} else {
						$this->cart_link();
					}
					?>
                </li>
                <li>
					<?php
					$instance = array(
						'title' => '',
					);

					the_widget( 'WC_Widget_Cart', $instance );
					?>
                </li>
            </ul>
			<?php
		}

		public function add_to_cart_fragments_filter( $fragments ) {
			ob_start();
			$this->cart_link();
			$fragments['a.cart-contents']    = ob_get_clean();
			$fragments['span.custom-cart-count'] = '<span class="custom-cart-count">' . WC()->cart->get_cart_contents_count() . '</span>';

			return $fragments;
		}

		public function body_class_filter( $classes ) {
			$classes[] = 'woocommerce-active';

			return $classes;
		}

		public function output_related_products_args_filter( $args ) {
			$defaults = array(
				'posts_per_page' => 3,
				'columns'        => 3,
			);

			return wp_parse_args( $defaults, $args );
		}

		public function after_setup_theme_action() {
			$args = array(
				'thumbnail_image_width' => 150,
				'single_image_width'    => 300,
				'product_grid'          => array(
					'default_rows'    => 3,
					'min_rows'        => 1,
					'default_columns' => 4,
					'min_columns'     => 1,
					'max_columns'     => 6,
				),
			);

			$args = apply_filters( 'hocwp_theme_extension_woocommerce_support_args', $args );

			add_theme_support( 'woocommerce', $args );

			add_theme_support( 'wc-product-gallery-zoom' );
			add_theme_support( 'wc-product-gallery-lightbox' );
			add_theme_support( 'wc-product-gallery-slider' );
		}

		public function woocommerce_after_single_product_summary_action() {
			echo '<div class="clearfix"></div><div class="custom-comments-box clearfix">';
			$this->comment_form_callback();
			echo '</div>';
		}

		public function woocommerce_product_tabs_filter( $tabs ) {
			$replace_review = $this->get_option( 'replace_review' );

			if ( 1 != $replace_review ) {
				$comment = HT_Options()->get_tab( 'comment_system', '', 'discussion' );

				if ( 'facebook' == $comment ) {
					$tabs['facebook_comment'] = array(
						'title'    => __( 'Facebook Comments', 'sb-core' ),
						'callback' => array( $this, 'comment_form_callback' )
					);
				}
			} else {
				unset( $tabs['reviews'] );
			}

			return $tabs;
		}

		public function comment_form_callback() {
			hocwp_theme_comments_template();
		}

		public function get_attribute_taxonomies() {
			global $wpdb;

			$sql = "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name != '' ORDER BY attribute_name ASC";

			return $wpdb->get_results( $sql );
		}

		public function get_wc_image_sizes() {
			$sizes = HT_Util()->get_image_sizes();

			if ( HT()->array_has_value( $sizes ) ) {
				foreach ( $sizes as $key => $size ) {
					if ( false === strpos( $key, 'woocommerce' ) && false === strpos( $key, 'shop' ) ) {
						unset( $sizes[ $key ] );
					}
				}
			}

			return $sizes;
		}
	}
}

function HTE_WooCommerce() {
	return HOCWP_EXT_WooCommerce::get_instance();
}

HTE_WooCommerce();