<?php
/*
 * Name: Google Code Prettify
 * Description: Using Google Code Prettify for displaying source code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_EXT_Google_Code_Prettify' ) ) {
	class HOCWP_EXT_Google_Code_Prettify extends HOCWP_Theme_Extension {
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
				$tab = new HOCWP_Theme_Admin_Setting_Tab( 'google_code_prettify', __( 'Code Prettify', 'sb-core' ), '<span class="dashicons dashicons-editor-paste-word"></span>' );

				$args = array(
					'class'   => 'regular-text',
					'options' => array(
						'default'          => 'Default',
						'desert'           => 'Desert',
						'sunburst'         => 'Sunburst',
						'sons-of-obsidian' => 'Sons-Of-Obsidian',
						'doxy'             => 'Doxy'
					)
				);

				$tab->add_field_array( array(
					'id'    => 'theme',
					'title' => __( 'Theme', 'sb-core' ),
					'args'  => array(
						'type'          => 'string',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'select' ),
						'callback_args' => $args
					)
				) );

				$args = array(
					'class' => 'medium-text',
					'type'  => 'checkbox',
					'label' => __( 'Load script on singular page only?', 'sb-core' )
				);

				$tab->add_field_array( array(
					'id'    => 'only_singular',
					'title' => __( 'Only Singular', 'sb-core' ),
					'args'  => array(
						'type'          => 'boolean',
						'callback'      => array( 'HOCWP_Theme_HTML_Field', 'input' ),
						'default'       => 1,
						'callback_args' => $args
					)
				) );
			} else {
				add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
				add_filter( 'the_content', array( $this, 'the_content_filter' ), 9 );
			}
		}

		public function scripts() {
			$only_singular = HT_Options()->get_tab( 'only_singular', 1, 'google_code_prettify' );

			if ( 1 == $only_singular && ! is_singular() ) {
				return;
			}

			$theme = HT_Options()->get_tab( 'theme', 'default', 'google_code_prettify' );
			$theme = strtolower( $theme );

			wp_enqueue_script( 'google-code-prettify', 'https://rawgit.com/google/code-prettify/master/loader/run_prettify.js?skin=' . $theme, array(), false, true );
		}

		public function the_content_filter( $post_content ) {
			$search = array(
				'[php]',
				'[/php]',
				'[css]',
				'[/css]',
				'[html]',
				'[/html]',
				'[javascript]',
				'[/javascript]',
				'[js]',
				'[/js]',
				'[code]',
				'[/code]'
			);

			$replace = array(
				'<pre class="lang-php prettyprint">',
				'</pre>',
				'<pre class="lang-css prettyprint">',
				'</pre>',
				'<pre class="lang-html prettyprint">',
				'</pre>',
				'<pre class="lang-js prettyprint">',
				'</pre>',
				'<pre class="lang-js prettyprint">',
				'</pre>',
				'<pre class="lang-php prettyprint">',
				'</pre>'
			);

			$post_content = str_replace( $search, $replace, $post_content );

			$post_content = preg_replace( '/<p[^>]*><\\/p[^>]*>/', '', $post_content );

			return $post_content;
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HTE_Google_Code_Prettify()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HTE_Google_Code_Prettify() {
	return HOCWP_EXT_Google_Code_Prettify::get_instance();
}