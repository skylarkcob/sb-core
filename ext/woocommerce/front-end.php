<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_wc_pre_get_posts( WP_Query $query ) {
	if ( $query instanceof WP_Query ) {
		if ( $query->is_main_query() ) {
			if ( function_exists( 'is_woocommerce' ) && is_post_type_archive( 'product' ) || is_tax() || is_search() ) {
				if ( is_woocommerce() ) {
					$ppp = hocwp_theme_get_option( 'products_per_page', $GLOBALS['hocwp_theme']->defaults['posts_per_page'], 'woocommerce' );
					$query->set( 'posts_per_page', $ppp );
					$query->set( 'post_type', 'product' );
				} elseif ( is_search() ) {
					$query->set( 'post_type', 'product' );
				}
			}
		}
	}
}

if ( ! is_admin() ) {
	add_action( 'pre_get_posts', 'hocwp_theme_wc_pre_get_posts' );
}

function hocwp_theme_wc_enqueue_scripts() {
	wp_enqueue_style( 'hocwp-theme-woocommerce-style', HOCWP_EXT_URL . '/css/woocommerce' . HOCWP_THEME_CSS_SUFFIX );
}

add_action( 'wp_enqueue_scripts', 'hocwp_theme_wc_enqueue_scripts' );

function hocwp_woocommerce_after_add_to_cart_quantity_action() {
	$plus_minus = HTE_WooCommerce()->get_option( 'plus_minus_quantity' );

	if ( $plus_minus ) {
		$si = get_post_meta( get_the_ID(), '_sold_individually', true );

		$plus_minus = ! ( 1 == $si || 'yes' == $si );
	}

	if ( $plus_minus ) {
		echo '<button type="button" class="plus" >+</button>';
		?>
        <script>
            jQuery(document).ready(function ($) {
                $("form.cart").on("click", "button.plus, button.minus", function () {
                    var qty = $(this).closest('form.cart').find('.qty'),
                        val = parseFloat(qty.val()),
                        max = parseFloat(qty.attr("max")),
                        min = parseFloat(qty.attr("min")),
                        step = parseFloat(qty.attr("step"));

                    if ($(this).is(".plus")) {
                        if (max && (max <= val)) {
                            qty.val(max);
                        } else {
                            qty.val(val + step);
                        }
                    } else {
                        if (min && (min >= val)) {
                            qty.val(min);
                        } else if (val > 1) {
                            qty.val(val - step);
                        }
                    }
                });
            });
        </script>
		<?php
	}
}

add_action( 'woocommerce_after_add_to_cart_quantity', 'hocwp_woocommerce_after_add_to_cart_quantity_action' );

function hocwp_woocommerce_before_add_to_cart_quantity_action() {
	$plus_minus = HTE_WooCommerce()->get_option( 'plus_minus_quantity' );

	if ( $plus_minus ) {
		$si = get_post_meta( get_the_ID(), '_sold_individually', true );

		$plus_minus = ! ( 1 == $si || 'yes' == $si );
	}

	if ( $plus_minus ) {
		echo '<button type="button" class="minus" >-</button>';
	}
}

add_action( 'woocommerce_before_add_to_cart_quantity', 'hocwp_woocommerce_before_add_to_cart_quantity_action' );

function hocwp_wc_after_add_to_cart_button_action() {
	$add_buy_now_button = HTE_WooCommerce()->get_option( 'add_buy_now_button' );

	if ( $add_buy_now_button ) {
		$url = '';

		if ( function_exists( 'wc_get_checkout_url' ) ) {
			$url = wc_get_checkout_url();
		}
		?>
        <a href="#" class="btn btn-warning buy-now"><?php _e( 'Buy now', 'sb-core' ); ?></a>
        <script>
            jQuery(document).ready(function ($) {
                var body = $("body");

                (function () {
                    var btnAddCart = $("form.cart *[type='submit'][name='add-to-cart'], form.cart *[type='submit'].single_add_to_cart_button");

                    if (btnAddCart && btnAddCart.length) {
                        var btnBuyNow = body.find(".btn.buy-now"),
                            variationId = body.find("input[name='variation_id']");

                        if (variationId && variationId.length) {
                            var id = parseInt(variationId.val());

                            if (1 > id) {
                                btnBuyNow.addClass("disabled");
                            }

                            variationId.on("change", function (e) {
                                id = parseInt($(this).val());

                                if (0 < id) {
                                    btnBuyNow.removeClass("disabled");
                                } else {
                                    btnBuyNow.addClass("disabled");
                                }
                            });
                        }

                        body.on("click", "a.btn.buy-now", function (e) {
                            e.preventDefault();

                            var p_id = parseInt(btnAddCart.attr("value"));

                            if (!$.isNumeric(p_id) || 1 > p_id) {
                                if (variationId && variationId.length) {
                                    p_id = parseInt(variationId.val());
                                }
                            }

                            $(this).addClass("disabled");
                            $(document.body).css({cursor: "wait"});

                            var url = hocwpTheme.homeUrl + "wp/?post_type=product&quantity=1&add-to-cart=" + p_id;

                            body.find(".variations select").each(function () {
                                var select = $(this),
                                    name = select.attr("data-attribute_name"),
                                    value = select.val();

                                if ($.trim(name) && $.trim(value)) {
                                    url += "&";
                                    url += name;
                                    url += "=";
                                    url += value;
                                }
                            });

                            $.get(url, function () {
                                $(this).removeClass("disabled");
                                $(document.body).css({cursor: "default"});

                                var checkoutUrl = "<?php echo $url; ?>";

                                if ($.trim(checkoutUrl)) {
                                    window.location.href = checkoutUrl;
                                }
                            });
                        });
                    }
                })();
            });
        </script>
		<?php
	}
}

add_action( 'woocommerce_after_add_to_cart_button', 'hocwp_wc_after_add_to_cart_button_action' );

function hocwp_woocommerce_review_order_before_submit_action() {
	?>
    <a href="<?php echo esc_url( wc_get_cart_url() ); ?>"
       class="btn btn-success btn-back-cart"><?php _e( 'Back to shopping cart', 'sb-core' ); ?></a>
	<?php
}

add_action( 'woocommerce_review_order_before_submit', 'hocwp_woocommerce_review_order_before_submit_action' );

function hocwp_woocommerce_proceed_to_checkout_action() {
	?>
    <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>"
       class="btn btn-success btn-continue-shopping"><?php _e( 'Continue shopping', 'sb-core' ); ?></a>
	<?php
}

add_action( 'woocommerce_proceed_to_checkout', 'hocwp_woocommerce_proceed_to_checkout_action' );

function hocwp_woocommerce_get_price_html_filter( $price, $product ) {
	if ( $product instanceof WC_Product ) {
		if ( '' === $product->get_price() || 0 == $product->get_price() ) {
			$price = '<span class="woocommerce-Price-amount amount">' . HTE_WooCommerce()->get_option( 'no_price_text' ) . '</span>';
		}
	}

	return $price;
}

add_filter( 'woocommerce_get_price_html', 'hocwp_woocommerce_get_price_html_filter', 99, 2 );

function hocwp_wc_body_class_filter( $classes ) {
	$plus_minus = HTE_WooCommerce()->get_option( 'plus_minus_quantity' );

	if ( $plus_minus ) {
		$si = get_post_meta( get_the_ID(), '_sold_individually', true );

		$plus_minus = ! ( 1 == $si || 'yes' == $si );
	}

	if ( $plus_minus ) {
		$classes[] = 'plus-minus-quantity';
	}

	return $classes;
}

add_filter( 'body_class', 'hocwp_wc_body_class_filter' );

function hocwp_wc_theme_localize_script_l10n_filter( $args ) {
	$ajax = get_option( 'woocommerce_enable_ajax_add_to_cart' );

	if ( 'yes' === $ajax ) {
		$args['woocommerce_enable_ajax_add_to_cart'] = $ajax;

		$args['l10n']['addingToCart'] = __( 'Adding to cart', 'sb-core' );
		$args['l10n']['addToCart']    = __( 'Add to cart', 'sb-core' );
		$args['l10n']['addedToCart']  = __( 'Added to cart', 'sb-core' );
	}

	return $args;
}

add_filter( 'hocwp_theme_localize_script_l10n', 'hocwp_wc_theme_localize_script_l10n_filter' );

function hocwp_theme_woocommerce_get_image_size_thumbnail_filter( $size ) {
	$s = HT_Options()->get_tab( 'woocommerce_thumbnail', '', 'woocommerce' );

	if ( HT()->array_has_value( $s ) ) {
		$size = $s;
	}

	return $size;
}

// Size woocommerce_thumbnail
add_filter( 'woocommerce_get_image_size_thumbnail', 'hocwp_theme_woocommerce_get_image_size_thumbnail_filter' );

function hocwp_theme_woocommerce_get_image_size_single_filter( $size ) {
	$s = HT_Options()->get_tab( 'woocommerce_single', '', 'woocommerce' );

	if ( HT()->array_has_value( $s ) ) {
		$size = $s;
	}

	return $size;
}

// Size woocommerce_single
add_filter( 'woocommerce_get_image_size_single', 'hocwp_theme_woocommerce_get_image_size_single_filter' );

function hocwp_theme_woocommerce_get_image_size_gallery_thumbnail_filter( $size ) {
	$s = HT_Options()->get_tab( 'woocommerce_gallery_thumbnail', '', 'woocommerce' );

	if ( HT()->array_has_value( $s ) ) {
		$size = $s;
	}

	return $size;
}

// Size woocommerce_gallery_thumbnail
add_filter( 'woocommerce_get_image_size_gallery_thumbnail', 'hocwp_theme_woocommerce_get_image_size_gallery_thumbnail_filter' );

function hocwp_theme_woocommerce_get_image_size_shop_catalog_filter( $size ) {
	$s = HT_Options()->get_tab( 'shop_catalog', '', 'woocommerce' );

	if ( HT()->array_has_value( $s ) ) {
		$size = $s;
	}

	return $size;
}

// Size shop_catalog
add_filter( 'woocommerce_get_image_size_shop_catalog', 'hocwp_theme_woocommerce_get_image_size_shop_catalog_filter' );

function hocwp_theme_woocommerce_get_image_size_shop_single_filter( $size ) {
	$s = HT_Options()->get_tab( 'shop_single', '', 'woocommerce' );

	if ( HT()->array_has_value( $s ) ) {
		$size = $s;
	}

	return $size;
}

// Size shop_single
add_filter( 'woocommerce_get_image_size_shop_single', 'hocwp_theme_woocommerce_get_image_size_shop_single_filter' );

function hocwp_theme_woocommerce_get_image_size_shop_thumbnail_filter( $size ) {
	$s = HT_Options()->get_tab( 'shop_thumbnail', '', 'woocommerce' );

	if ( HT()->array_has_value( $s ) ) {
		$size = $s;
	}

	return $size;
}

// Size shop_thumbnail
add_filter( 'woocommerce_get_image_size_shop_thumbnail', 'hocwp_theme_woocommerce_get_image_size_shop_thumbnail_filter' );

$value = HTE_WooCommerce()->get_option( 'onsale_percent' );

if ( $value ) {
	add_filter( 'woocommerce_sale_flash', function ( $html, $post, $product ) {
		if ( $post instanceof WP_Post && $product instanceof WC_Product ) {
			$regular = $product->get_regular_price( 'mysql' );
			$sale    = $product->get_sale_price( 'mysql' );
			$percent = HT()->calculate_discount( $regular, $sale, 0 );
			$html    = '<span class="onsale">-' . $percent . '%</span>';
		}

		return $html;
	}, 10, 3 );
}