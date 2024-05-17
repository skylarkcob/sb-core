<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_woocommerce_tab( $tabs ) {
	$tabs['woocommerce'] = array(
		'text' => __( 'WooCommerce', 'sb-core' ),
		'icon' => '<span class="dashicons dashicons-products"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_woocommerce_tab' );

function hocwp_theme_settings_page_woocommerce_field() {
	$options = HT_Util()->get_theme_options( 'woocommerce' );

	$fields = array();

	$fields[] = array(
		'tab'     => 'woocommerce',
		'section' => 'default',
		'id'      => 'products_per_page',
		'title'   => __( 'Products Per Page', 'sb-core' ),
		'args'    => array(
			'label_for'     => true,
			'default'       => $GLOBALS['hocwp_theme']->defaults['posts_per_page'],
			'callback_args' => array(
				'class' => 'small-text',
				'type'  => 'number'
			)
		)
	);

	$args = array(
		'class'       => 'medium-text',
		'description' => __( 'This text will be used if the product has no price.', 'sb-core' )
	);

	$field    = hocwp_theme_create_setting_field( 'no_price_text', __( 'No Price Text', 'sb-core' ), 'input', $args, 'string', 'woocommerce' );
	$fields[] = $field;

	$args = array(
		'class' => 'medium-text',
		'type'  => 'number'
	);

	$field    = hocwp_theme_create_setting_field( 'usd_vnd_rate', __( 'USD to Vietnam Dong Rate', 'sb-core' ), 'input', $args, 'numeric', 'woocommerce' );
	$fields[] = $field;

	$comment = HT_Options()->get_tab( 'comment_system', '', 'discussion' );

	if ( 'default' != $comment ) {
		$args = array(
			'type' => 'checkbox',
			'text' => __( 'Use custom comment system for product?', 'sb-core' )
		);

		$field    = hocwp_theme_create_setting_field( 'custom_comment', __( 'Custom Comment', 'sb-core' ), 'input', $args, 'boolean', 'woocommerce' );
		$fields[] = $field;

		$args['text'] = __( 'Replace review form with custom comment form.', 'sb-core' );

		$field    = hocwp_theme_create_setting_field( 'replace_review', __( 'Replace Review', 'sb-core' ), 'input', $args, 'boolean', 'woocommerce' );
		$fields[] = $field;
	}

	$args = array(
		'type' => 'checkbox',
		'text' => __( 'Replace price reduction with the percentage reduction.', 'sb-core' )
	);

	$field    = new HOCWP_Theme_Admin_Setting_Field( 'onsale_percent', __( 'Onsale Percentage', 'sb-core' ), 'input', $args, 'boolean', 'woocommerce' );
	$fields[] = $field;

	$args = array(
		'type' => 'checkbox',
		'text' => __( 'Add plus <code>+</code> and minus <code>-</code> buttons to the quantity input on the product page.', 'sb-core' )
	);

	$field    = new HOCWP_Theme_Admin_Setting_Field( 'plus_minus_quantity', __( 'Plus Minus Quantity', 'sb-core' ), 'input', $args, 'boolean', 'woocommerce' );
	$fields[] = $field;

	$args['text'] = __( 'Add <code>Buy now</code> button to the right of <code>Add to cart</code> button.', 'sb-core' );

	$field    = new HOCWP_Theme_Admin_Setting_Field( 'add_buy_now_button', __( 'Buy Now Button', 'sb-core' ), 'input', $args, 'boolean', 'woocommerce' );
	$fields[] = $field;

	$sizes = HTE_WooCommerce()->get_wc_image_sizes();

	$args = array();

	foreach ( $sizes as $key => $size ) {
		$title = str_replace( '_', ' ', $key );
		$title = str_replace( '-', ' ', $title );
		$title = ucwords( $title );

		$args['default'] = $size;

		$field    = new HOCWP_Theme_Admin_Setting_Field( $key, $title, 'image_size', $args, 'array', 'woocommerce', 'image_sizes' );
		$fields[] = $field;
	}

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_woocommerce_settings_field', 'hocwp_theme_settings_page_woocommerce_field' );

function hocwp_theme_settings_page_woocommerce_section() {
	$sections = array();

	$sections['image_sizes'] = array(
		'tab'   => 'woocommerce',
		'id'    => 'image_sizes',
		'title' => __( 'Image Sizes', 'sb-core' )
	);

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_woocommerce_settings_section', 'hocwp_theme_settings_page_woocommerce_section' );

function hocwp_theme_sanitize_option_woocommerce_filter( $input ) {
	$sizes = HTE_WooCommerce()->get_wc_image_sizes();

	foreach ( $sizes as $key => $size ) {
		$input[ $key ]['crop'] = $input[ $key ]['crop'] ?? 0;
	}

	return $input;
}

add_filter( 'hocwp_theme_sanitize_option_woocommerce', 'hocwp_theme_sanitize_option_woocommerce_filter' );