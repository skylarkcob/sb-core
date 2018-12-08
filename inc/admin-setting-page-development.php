<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( function_exists( 'hocwp_theme_settings_page_development_tab' ) ) {
	return;
}

function hocwp_theme_settings_page_development_tab( $tabs ) {
	$tabs['development'] = array(
		'text' => __( 'Development', 'sb-core' ),
		'icon' => '<span class="dashicons dashicons-hammer"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_development_tab' );

global $hocwp_theme;

if ( ! is_object( $hocwp_theme ) || ! is_object( $hocwp_theme->option ) || 'development' != $hocwp_theme->option->tab ) {
	return;
}

function hocwp_theme_settings_page_development_section() {
	$sections = array();

	return $sections;
}

add_filter( 'hocwp_theme_settings_page_development_settings_section', 'hocwp_theme_settings_page_development_section' );

function hocwp_theme_settings_page_development_field() {
	$fields = array();

	$field = array(
		'id'    => 'compress_css_and_js',
		'title' => 'Compress CSS and JS',
		'tab'   => 'development',
		'args'  => array(
			'type'          => 'boolean',
			'callback_args' => array(
				'type'  => 'checkbox',
				'label' => 'Minify all CSS and Javascript files'
			),
			'action'        => 'hocwp_theme_admin_settings_page_development_compress_css_and_js'
		)
	);

	$field = array(
		'id'    => 'publish_release',
		'title' => 'Compress Theme as ZIP File',
		'tab'   => 'development',
		'args'  => array(
			'type'          => 'boolean',
			'label_for'     => true,
			'callback_args' => array(
				'type'  => 'checkbox',
				'label' => 'After building environment, theme will be compressed for release.'
			)
		)
	);

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_development_settings_field', 'hocwp_theme_settings_page_development_field' );

function _hocwp_theme_admin_settings_page_list_css_and_js_files_checkbox( $path ) {
	if ( ! is_dir( $path ) ) {
		return;
	}
	$fieldset = '<fieldset class="directory" data-path="' . md5( $path ) . '">';
	echo $fieldset;
	$files = scandir( $path );
	$label = new HOCWP_Theme_HTML_Tag( 'label' );
	$label->add_attribute( 'for', md5( $path ) );
	$input = new HOCWP_Theme_HTML_Tag( 'input' );
	$input->add_attribute( 'id', md5( $path ) );
	$input->add_attribute( 'type', 'checkbox' );
	$input->add_attribute( 'class', 'path' );
	$strong = new HOCWP_Theme_HTML_Tag( 'strong' );
	$strong->set_text( $path );
	$input->set_text( $strong );
	$input->add_attribute( 'value', esc_attr( $path ) );
	$label->set_text( $input->build() );
	$label->output();
	echo '</fieldset>';
	$fieldset = '<fieldset data-path="' . md5( $path ) . '">';
	foreach ( $files as $file ) {
		if ( ! _hocwp_theme_is_css_or_js_file( $file ) ) {
			continue;
		}
		echo $fieldset;
		$label = new HOCWP_Theme_HTML_Tag( 'label' );
		$file  = trailingslashit( $path ) . $file;
		$label->add_attribute( 'for', md5( $file ) );
		$input = new HOCWP_Theme_HTML_Tag( 'input' );
		$input->add_attribute( 'id', md5( $file ) );
		$input->add_attribute( 'type', 'checkbox' );
		$input->add_attribute( 'class', 'path' );
		$input->set_text( $file );
		$input->add_attribute( 'value', esc_attr( $file ) );
		$label->set_text( $input->build() );
		$label->add_attribute( 'style', 'color: #bbb' );
		$label->output();
		echo '</fieldset>';
	}
}

function hocwp_theme_admin_settings_page_development_compress_css_and_js_callback() {
	global $hocwp_theme;
	$paths = $hocwp_theme->defaults['compress_css_and_js_paths'];
	foreach ( $paths as $path ) {
		$css = $path . '/css';
		_hocwp_theme_admin_settings_page_list_css_and_js_files_checkbox( $css );
		$js = $path . '/js';
		_hocwp_theme_admin_settings_page_list_css_and_js_files_checkbox( $js );
	}
	?>
	<script>
		jQuery(document).ready(function ($) {
			$('.compress_css_and_js .directory input[type="checkbox"]').on('change', function () {
				var $element = $(this),
					$fieldset = $element.closest('fieldset'),
					path = $fieldset.attr('data-path');
				document.getSelection().removeAllRanges();
				if ($.trim(path)) {
					var $sames = $element.closest('td').find('fieldset[data-path="' + path + '"] input');
					if ($element.is(':checked')) {
						$sames.prop('checked', true);
					} else {
						$sames.prop('checked', false);
					}
				}
			});
		});
	</script>
	<?php
}

add_action( 'hocwp_theme_admin_settings_page_development_compress_css_and_js', 'hocwp_theme_admin_settings_page_development_compress_css_and_js_callback' );

function hocwp_theme_settings_page_development_submit_text( $args ) {
	$args['text']       = 'Execute';
	$args['attributes'] = array(
		'id'               => 'execute_development',
		'data-ajax-button' => 1
	);

	return $args;
}

add_filter( 'hocwp_theme_settings_page_development_submit_button_args', 'hocwp_theme_settings_page_development_submit_text' );