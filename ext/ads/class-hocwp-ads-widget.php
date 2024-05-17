<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Ads_Widget extends WP_Widget {
	public $defaults;

	public function __construct() {
		$this->defaults = array(
			'position' => '',
			'random'   => 0
		);

		$this->defaults = apply_filters( 'hocwp_widget_ads_defaults', $this->defaults, $this );

		$widget_options = array(
			'classname'                   => 'hocwp-ext-ads-widget hocwp-ads-widget',
			'description'                 => _x( 'Display ads by position.', 'widget description', 'sb-core' ),
			'customize_selective_refresh' => true
		);

		$control_options = array(
			'width' => 400
		);

		parent::__construct( 'hocwp_widget_ads', 'HocWP Ads', $widget_options, $control_options );
	}

	public function widget( $args, $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults );

		$instance = apply_filters( 'hocwp_widget_ads_instance', $instance, $args, $this );

		$html = apply_filters( 'hocwp_widget_ads_html', '', $instance, $args, $this );

		if ( empty( $html ) ) {
			$position = $instance['position'];

			$random = $instance['random'];

			if ( 1 == $random ) {
				$random = true;
			}

			$ads = isset( $instance['ads'] ) ? $instance['ads'] : '';
			$ads = get_post( $ads );

			if ( $ads instanceof WP_Post && 'hocwp_ads' == $ads->post_type ) {
				$position = $ads;
			}

			if ( ! function_exists( 'hocwp_ext_ads_display' ) ) {
				require_once HTE_Ads()->folder_path . '/front-end.php';
			}

			ob_start();
			hocwp_ext_ads_display( $position, $random );
			$html = ob_get_clean();
		}

		if ( ! empty( $html ) ) {
			do_action( 'hocwp_theme_widget_before', $args, $instance, $this );
			echo $html;
			do_action( 'hocwp_theme_widget_after', $args, $instance, $this );
		}
	}

	public function form( $instance ) {
		$instance = wp_parse_args( $instance, $this->defaults );

		$positions = hocwp_ext_get_ads_positions();
		array_unshift( $positions, '' );

		$position = $instance['position'];
		$random   = $instance['random'];

		$ads = isset( $instance['ads'] ) ? $instance['ads'] : '';

		do_action( 'hocwp_theme_widget_form_before', $instance, $this );
		?>
        <p>
			<?php
			$args = array(
				'for'  => $this->get_field_id( 'position' ),
				'text' => __( 'Position:', 'sb-core' )
			);

			HT_HTML_Field()->label( $args );

			$args = array(
				'id'      => $this->get_field_id( 'position' ),
				'name'    => $this->get_field_name( 'position' ),
				'options' => $positions,
				'class'   => 'widefat',
				'value'   => $position
			);

			HT_HTML_Field()->select( $args );
			?>
        </p>
        <p>
			<?php
			$args = array(
				'for'  => $this->get_field_id( 'ads' ),
				'text' => __( 'Ads:', 'sb-core' )
			);

			HT_HTML_Field()->label( $args );

			$args = array(
				'id'         => $this->get_field_id( 'ads' ),
				'name'       => $this->get_field_name( 'ads' ),
				'post_type'  => 'hocwp_ads',
				'class'      => 'widefat',
				'value'      => $ads,
				'option_all' => __( '-- Choose ads --', 'sb-core' )
			);

			HT_HTML_Field()->select_post( $args );
			?>
        </p>
        <p>
            <input class="checkbox" type="checkbox"<?php checked( $random, 1 ); ?>
                   id="<?php echo $this->get_field_id( 'random' ); ?>"
                   name="<?php echo $this->get_field_name( 'random' ); ?>"/>
            <label
                    for="<?php echo $this->get_field_id( 'random' ); ?>"><?php _e( 'Displaying ads for this position randomly? If you choose random position, the ads will be displayed randomly by default.', 'sb-core' ); ?></label>
        </p>
		<?php
		do_action( 'hocwp_theme_widget_form_after', $instance, $this );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']    = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['position'] = isset( $new_instance['position'] ) ? sanitize_text_field( $new_instance['position'] ) : '';
		$instance['random']   = isset( $new_instance['random'] ) ? 1 : 0;
		$instance['ads']      = isset( $new_instance['ads'] ) ? absint( $new_instance['ads'] ) : '';

		return $instance;
	}
}