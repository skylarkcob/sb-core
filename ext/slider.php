<?php
/*
 * Name: Slider
 * Description: Add images or content slider.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_EXT_Slider' ) ) {
	class HOCWP_EXT_Slider extends HOCWP_Theme_Extension {
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

			add_action( 'init', array( $this, 'init_action' ) );

			add_shortcode( 'hocwp_slider', array( $this, 'shortcode_slider_func' ) );

			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'admin_menu_action' ), 99 );
				add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes_action' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_action' ) );
				add_action( 'save_post', array( $this, 'save_post_action' ) );
				add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'admin_columns_filter' ) );
				add_action( 'manage_' . $this->post_type . '_posts_custom_column', array(
					$this,
					'admin_columns_content_action'
				), 10, 2 );
			}
		}

		public function admin_columns_filter( $columns ) {
			HT()->insert_to_array( $columns, __( 'Shortcode', 'sb-core' ), 'before_last', 'shortcode' );

			return $columns;
		}

		public function admin_columns_content_action( $column, $post_id ) {
			if ( 'shortcode' == $column ) {
				printf( '<input type="text" value="%s" class="regular-text" onfocus="this.select();" onmouseup="return false;" readonly>', esc_attr( $this->generate_shortcode( $post_id, get_the_title( $post_id ) ) ) );
			}
		}

		public $post_type = 'hocwp_slider';
		public $mt_slider_items = 'slider_items';
		public $mt_slider_settings = 'slider_settings';

		public $slide_settings = array(
			'width'             => 1350,
			'height'            => 800,
			'arrows'            => 1,
			'autoplay'          => 0,
			'infinity'          => 0,
			'navigation'        => 'hidden',
			'link_image'        => 0,
			'adaptive_height'   => 0,
			'autoplay_speed'    => 3000,
			'advanced_settings' => '',
			'slides_per_view'   => 1
		);

		public function shortcode_slider_func( $atts = array() ) {
			$atts = shortcode_atts( array(
				'title'    => '',
				'id'       => '',
				'settings' => ''
			), $atts );

			$title = $atts['title'];
			$id    = $atts['id'];

			if ( 'publish' != get_post_status( $id ) ) {
				return '';
			}

			if ( empty( $title ) ) {
				$title = get_the_title( $id );
			}

			$slider = get_post( $id );

			if ( ! ( $slider instanceof WP_Post ) || $this->post_type != $slider->post_type ) {
				return '';
			}

			$slider_items = get_post_meta( $slider->ID, $this->mt_slider_items, true );

			if ( ! HT()->is_array_has_value( $slider_items ) ) {
				return '';
			}

			$settings = $atts['settings'];

			if ( ! empty( $settings ) ) {
				$settings = HT()->json_string_to_array( $settings );
			}

			if ( ! is_array( $settings ) ) {
				$settings = array();
			}

			$defaults = $this->get_slide_settings( $id );
			$settings = wp_parse_args( $settings, $defaults );

			$advanced_settings = $settings['advanced_settings'] ?? '';

			unset( $settings['advanced_settings'] );
			$slides_per_view = '';

			if ( ! empty( $advanced_settings ) ) {
				$advanced_settings = HT()->json_string_to_array( $advanced_settings );
				$slides_per_view   = $advanced_settings['slidesPerView'] ?? '';
			}

			if ( ! is_array( $advanced_settings ) ) {
				$advanced_settings = array();
			}

			if ( empty( $slides_per_view ) ) {
				$slides_per_view = $settings['slides_per_view'] ?? '';
			}

			ob_start();
			?>
            <div id="Slider<?php echo esc_attr( $id ); ?>" class="hocwp-slider position-relative slider-items"
                 data-title="<?php echo esc_attr( $title ); ?>"
                 data-settings="<?php echo esc_attr( json_encode( $settings ) ); ?>"
                 data-count="<?php echo esc_attr( count( $slider_items ) ); ?>"
                 data-advanced-settings="<?php echo esc_attr( json_encode( $advanced_settings ) ); ?>"
                 data-slides-per-view="<?php echo esc_attr( $slides_per_view ); ?>">
                <div class="swiper-wrapper">
					<?php
					foreach ( $slider_items as $item ) {
						$image_id     = $item['image_id'] ?? '';
						$post_excerpt = $item['post_excerpt'] ?? '';
						$url          = $item['url'] ?? '';
						$post_excerpt = str_replace( '%url%', $url, $post_excerpt );
						$thumbnail    = $item['thumbnail_id'] ?? '';

						if ( HT_Media()->exists( $image_id ) ) {
							?>
                            <div class="slider-item swiper-slide"
                                 data-image-url="<?php echo esc_attr( wp_get_original_image_url( $image_id ) ); ?>"
                                 data-thumbnail-url="<?php echo esc_attr( wp_get_original_image_url( $thumbnail ) ); ?>">
								<?php
								if ( $settings['width'] || $settings['height'] ) {
									$size = array(
										$settings['width'],
										$settings['height']
									);
								} else {
									$size = 'full';
								}

								$image = wp_get_attachment_image( $image_id, $size );

								$image = apply_filters( 'hocwp_theme_slider_image_html', $image, $image_id, $id, $thumbnail );

								if ( ! empty( $url ) && 1 == $settings['link_image'] ) {
									$image = sprintf( '<a href="%s" title="%s">%s</a>', esc_attr( $url ), esc_attr( get_the_title( $image_id ) ), $image );
								}

								echo $image;
								?>
                                <div class="details"><?php echo wpautop( do_shortcode( $post_excerpt ) ); ?></div>
                            </div>
							<?php
						}
					}
					?>
                </div>
                <!-- If we need pagination -->
                <div class="swiper-pagination"></div>

                <!-- If we need navigation buttons -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>

                <!-- If we need scrollbar -->
                <div class="swiper-scrollbar"></div>
            </div>
			<?php

			return ob_get_clean();
		}

		public function save_post_action( $post_id ) {
			if ( ! wp_is_post_revision( $post_id ) && current_user_can( 'edit_posts' ) ) {
				if ( get_post_type( $post_id ) == $this->post_type ) {
					if ( HT()->array_has_value( $_POST ) ) {
						$slide_items = $_POST[ $this->mt_slider_items ] ?? '';
						update_post_meta( $post_id, $this->mt_slider_items, $slide_items );

						$settings = $_POST[ $this->mt_slider_settings ] ?? '';

						if ( ! is_array( $settings ) ) {
							$settings = array();
						}

						$settings['arrows']          = isset( $_POST[ $this->mt_slider_settings ]['arrows'] ) ? 1 : 0;
						$settings['link_image']      = isset( $_POST[ $this->mt_slider_settings ]['link_image'] ) ? 1 : 0;
						$settings['autoplay']        = isset( $_POST[ $this->mt_slider_settings ]['autoplay'] ) ? 1 : 0;
						$settings['infinity']        = isset( $_POST[ $this->mt_slider_settings ]['infinity'] ) ? 1 : 0;
						$settings['adaptive_height'] = isset( $_POST[ $this->mt_slider_settings ]['adaptive_height'] ) ? 1 : 0;

						update_post_meta( $post_id, $this->mt_slider_settings, $settings );
					}
				}
			}
		}

		public function admin_enqueue_scripts_action() {
			global $pagenow;

			if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {
				$pt = HT_Admin()->get_current_post_type();

				if ( $pt == $this->post_type ) {
					wp_enqueue_media();
					HT_Enqueue()->sortable();
					wp_enqueue_style( 'hte-slider-style', SB_Core()->url . '/css/admin-slider.css' );
					wp_enqueue_script( 'hte-slider', SB_CORE()->url . '/js/admin-slider.js', array( 'jquery' ), false, true );

					$l10n = array(
						'slideItem' => $this->slide_item_html(),
						'text'      => array(
							'add_media_title' => __( 'Add Slide Images', 'sb-core' )
						)
					);

					wp_localize_script( 'hte-slider', 'HTESlider', $l10n );
				}
			}
		}

		public function admin_menu_action() {
			$title = __( 'Slider', 'sb-core' );
			add_theme_page( $title, $title, 'manage_options', 'edit.php?post_type=' . $this->post_type );
		}

		public function init_action() {
			$args = array(
				'labels'       => array(
					'name' => __( 'Slider', 'sb-core' )
				),
				'private'      => true,
				'show_in_menu' => false,
				'supports'     => array( 'title' )
			);

			$args = HT_Util()->post_type_args( $args );

			register_post_type( $this->post_type, $args );
		}

		public function add_meta_boxes_action( $post_type ) {
			if ( $this->post_type == $post_type ) {
				add_meta_box( 'slide-settings', __( 'Settings', 'sb-core' ), array(
					$this,
					'meta_box_slide_settings'
				), $post_type, 'side' );

				add_meta_box( 'copy-shortcode', __( 'How to use', 'sb-core' ), array(
					$this,
					'meta_box_copy_shortcode'
				), $post_type, 'side' );

				add_meta_box( 'copy-advanced-settings', __( 'Advanced Settings', 'sb-core' ), array(
					$this,
					'meta_box_advanced_settings'
				), $post_type, 'side' );

				add_meta_box( 'manage-slides', __( 'Slide Items', 'sb-core' ), array(
					$this,
					'meta_box_slide_items'
				), $post_type );
			}
		}

		public function get_slide_settings( $slide_id ) {
			$settings = get_post_meta( $slide_id, $this->mt_slider_settings, true );

			if ( ! is_array( $settings ) ) {
				$settings = array();
			}

			return wp_parse_args( $settings, $this->slide_settings );
		}

		public function meta_box_slide_settings( $post ) {
			if ( $post instanceof WP_Post ) {
				$settings = $this->get_slide_settings( $post->ID );
				?>
                <div class="hocwp-theme">
                    <table class="slide-settings form-table">
                        <tbody>
                        <tr>
                            <td>
                                <label for="settings_slides_per_view"><?php _e( 'Slides Per View', 'sb-core' ); ?></label>
                            </td>
                            <td>
                                <input id="settings_slides_per_view" type="number" min="1" max="" step="1"
                                       class="small-text"
                                       name="<?php echo esc_attr( $this->mt_slider_settings ); ?>[slides_per_view]"
                                       value="<?php echo esc_attr( $settings['slides_per_view'] ); ?>"
                                       title="<?php esc_attr_e( 'Slide items per view', 'sb-core' ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="settings_width"><?php _e( 'Width', 'sb-core' ); ?></label>
                            </td>
                            <td>
                                <input id="settings_width" type="number" min="0" max="9999" step="1" class="small-text"
                                       name="<?php echo esc_attr( $this->mt_slider_settings ); ?>[width]"
                                       value="<?php echo esc_attr( $settings['width'] ); ?>"
                                       title="<?php esc_attr_e( 'Slide width', 'sb-core' ); ?>"><span>&nbsp;px</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="settings_height"><?php _e( 'Height', 'sb-core' ); ?></label>
                            </td>
                            <td>
                                <input id="settings_height" type="number" min="0" max="9999" step="1" class="small-text"
                                       name="<?php echo esc_attr( $this->mt_slider_settings ); ?>[height]"
                                       value="<?php echo esc_attr( $settings['height'] ); ?>"
                                       title="<?php esc_attr_e( 'Slide height', 'sb-core' ); ?>"><span>&nbsp;px</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="settings_link_image"><?php _e( 'Link Image', 'sb-core' ); ?></label>
                            </td>
                            <td>
                                <input id="settings_link_image" type="checkbox"
                                       name="<?php echo esc_attr( $this->mt_slider_settings ); ?>[link_image]"
                                       title="<?php esc_attr_e( 'Display direction arrows', 'sb-core' ); ?>"<?php checked( 1, $settings['link_image'] ); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="settings_arrow"><?php _e( 'Arrows', 'sb-core' ); ?></label>
                            </td>
                            <td>
                                <input id="settings_arrow" type="checkbox"
                                       name="<?php echo esc_attr( $this->mt_slider_settings ); ?>[arrows]"
                                       title="<?php esc_attr_e( 'Display direction arrows', 'sb-core' ); ?>"<?php checked( 1, $settings['arrows'] ); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="settings_navigation"><?php _e( 'Navigation', 'sb-core' ); ?></label>
                            </td>
                            <td>
								<?php
								$navigations = array(
									'hidden' => __( 'Hidden', 'sb-core' ),
									'dots'   => __( 'Dots', 'sb-core' )
								);
								?>
                                <select name="<?php echo esc_attr( $this->mt_slider_settings ); ?>[navigation]"
                                        id="settings_navigation">
									<?php
									foreach ( $navigations as $key => $text ) {
										?>
                                        <option
                                                value="<?php echo esc_attr( $key ); ?>"<?php selected( $settings['navigation'], $key ); ?>><?php echo $text; ?></option>
										<?php
									}
									?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="settings_autoplay"><?php _e( 'Autoplay', 'sb-core' ); ?></label>
                            </td>
                            <td>
                                <input id="settings_autoplay" type="checkbox"
                                       name="<?php echo esc_attr( $this->mt_slider_settings ); ?>[autoplay]"
                                       title="<?php esc_attr_e( 'Make slider autoplay', 'sb-core' ); ?>"<?php checked( 1, $settings['autoplay'] ); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="settings_autoplay_speed"><?php _e( 'Autoplay Speed', 'sb-core' ); ?></label>
                            </td>
                            <td>
                                <input id="settings_autoplay_speed" type="number" min="1000" max="" step="500"
                                       class="small-text"
                                       name="<?php echo esc_attr( $this->mt_slider_settings ); ?>[autoplay_speed]"
                                       value="<?php echo esc_attr( $settings['autoplay_speed'] ); ?>"
                                       title="<?php esc_attr_e( 'Slide autoplay speed', 'sb-core' ); ?>"><span>&nbsp;ms</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="settings_infinity"><?php _e( 'Infinity', 'sb-core' ); ?></label>
                            </td>
                            <td>
                                <input id="settings_infinity" type="checkbox"
                                       name="<?php echo esc_attr( $this->mt_slider_settings ); ?>[infinity]"
                                       title="<?php esc_attr_e( 'Make slider infinite scrolling', 'sb-core' ); ?>"<?php checked( 1, $settings['infinity'] ); ?>>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label for="settings_adaptive_height"><?php _e( 'Adaptive Height', 'sb-core' ); ?></label>
                            </td>
                            <td>
                                <input id="settings_adaptive_height" type="checkbox"
                                       name="<?php echo esc_attr( $this->mt_slider_settings ); ?>[adaptive_height]"
                                       title="<?php esc_attr_e( 'Auto change slider height', 'sb-core' ); ?>"<?php checked( 1, $settings['adaptive_height'] ); ?>>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
				<?php
			}
		}

		public function meta_box_slide_items( $post ) {
			if ( $post instanceof WP_Post ) {
				$slide_items = get_post_meta( $post->ID, $this->mt_slider_items, true );
				?>
                <div data-slide-id="<?php echo esc_attr( $post->ID ); ?>" class="hocwp-theme slider-items slide-items">
                    <div data-slide-id="<?php echo esc_attr( $post->ID ); ?>" class="items">
						<?php
						if ( HT()->is_array_has_value( $slide_items ) ) {
							$count = 0;

							foreach ( $slide_items as $item ) {
								$image_id     = $item['image_id'] ?? '';
								$post_excerpt = $item['post_excerpt'] ?? '';
								$url          = $item['url'] ?? '';
								$thumbnail    = $item['thumbnail_id'] ?? '';

								$html = $this->slide_item_html( esc_attr( $image_id ), esc_attr( $post_excerpt ), esc_attr( $url ), $thumbnail, $count );

								$html = str_replace( array(
									'%image_url%',
									'%image_title%',
									'%image_id%',
									'%slide_id%',
									'%key_index%'
								), array(
									wp_get_original_image_url( $image_id ),
									get_the_title( $image_id ),
									$image_id,
									$post->ID,
									$count
								), $html );

								echo $html;

								$count ++;
							}
						}
						?>
                    </div>
                    <button data-slide-id="<?php echo esc_attr( $post->ID ); ?>" id="addSlideItems" type="button"
                            class="button"><?php _e( 'Add images', 'sb-core' ); ?></button>
                </div>
				<?php
			}
		}

		private function slide_item_html( $image_id = '', $post_excerpt = '', $url = '', $thumbnail = '', $index = '' ) {
			if ( empty( $image_id ) ) {
				$image_id = '%image_id%';
			}

			if ( ! HT()->is_nonnegative_number( $index ) ) {
				$index = '%key_index%';
			}

			ob_start();
			?>
            <div class="slide-item" data-slide-id="%slide_id%" data-index="<?php echo esc_attr( $index ); ?>">
                <div class="item-header">
                    <h4 class="slide-details"><?php _e( 'Image Slide', 'sb-core' ); ?></h4>
                    <button type="button" data-slide-id="%slide_id%" data-image-id="%image_id%"
                            class="toolbar-button delete-slide alignright tipsy-tooltip-top">
                        <i>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-x">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </i></button>
                    <button type="button" data-slide-type="image" data-image-id="%image_id%" data-slide-id="%slide_id%"
                            class="toolbar-button update-image alignright tipsy-tooltip-top">
                        <i>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" class="feather feather-edit-2">
                                <polygon points="16 3 21 8 8 21 3 21 3 16 16 3"></polygon>
                            </svg>
                        </i></button>
                </div>
                <div class="item-body">
                    <img class="slide-image" alt="%image_title%" title="%image_title%" src="%image_url%"
                         data-image-id="%image_id%">

                    <div class="info">
                        <input class="image-id" type="hidden"
                               name="<?php echo esc_attr( $this->mt_slider_items ); ?>[%key_index%][image_id]"
                               value="<?php echo $image_id; ?>">
                        <textarea name="<?php echo esc_attr( $this->mt_slider_items ); ?>[%key_index%][post_excerpt]"
                                  class="widefat post-excerpt"
                                  placeholder="<?php esc_attr_e( 'Caption', 'sb-core' ); ?>"><?php echo $post_excerpt; ?></textarea>
                        <input type="text" name="<?php echo esc_attr( $this->mt_slider_items ); ?>[%key_index%][url]"
                               class="widefat url"
                               placeholder="<?php esc_attr_e( 'URL', 'sb-core' ); ?>" value="<?php echo $url; ?>">
						<?php
						$class = 'thumbnail';

						if ( HT_Media()->exists( $thumbnail ) ) {
							$class .= ' has-image';
						}
						?>
                        <div class="<?php echo esc_attr( $class ); ?>">
                            <div class="thumb-image">
								<?php
								if ( HT_Media()->exists( $thumbnail ) ) {
									echo wp_get_attachment_image( $thumbnail, 'full' );
								}
								?>
                            </div>
                            <a class="set-image" href="javascript:"
                               title="<?php esc_attr_e( 'Add thumbnail image', 'sb-core' ); ?>"><?php _e( 'Add thumbnail', 'sb-core' ); ?></a>
                            <a class="remove-image" href="javascript:"
                               title="<?php esc_attr_e( 'Remove thumbnail image', 'sb-core' ); ?>"><?php _e( 'Remove thumbnail', 'sb-core' ); ?></a>
                            <input class="thumbnail-id" type="hidden"
                                   name="<?php echo esc_attr( $this->mt_slider_items ); ?>[%key_index%][thumbnail_id]"
                                   value="<?php echo $thumbnail; ?>">
                        </div>
                    </div>
                </div>
            </div>
			<?php
			return ob_get_clean();
		}

		public function generate_shortcode( $post_id, $title ) {
			return sprintf( '[hocwp_slider title="%s" id="%s"]', esc_attr( $title ), esc_attr( $post_id ) );
		}

		public function meta_box_advanced_settings( $post ) {
			if ( ! ( $post instanceof WP_Post ) ) {
				return;
			}

			$settings = $this->get_slide_settings( $post->ID );
			?>
            <div class="hocwp-theme">
                <textarea name="<?php echo esc_attr( $this->mt_slider_settings ); ?>[advanced_settings]" rows="10"
                          class="widefat" placeholder="<?php echo esc_attr( '{
  "breakpoints": {
    "@1.0": {
      "slidesPerView": 3,
      "spaceBetween": 10
    },
    "@1.50": {
      "slidesPerView": 4,
      "spaceBetween": 20
    }
  }
}' ); ?>"><?php echo esc_attr( $settings['advanced_settings'] ); ?></textarea>
                <p class="description"><?php _e( 'Custom advanced settings JSON formatted.', 'sb-core' ); ?></p>
            </div>
			<?php
		}

		public function meta_box_copy_shortcode( $post ) {
			if ( ! ( $post instanceof WP_Post ) ) {
				return;
			}

			$shortcode = $this->generate_shortcode( $post->ID, $post->post_title );

			$text   = __( 'Copy all', 'sb-core' );
			$copied = __( 'Copied!', 'sb-core' );
			?>
            <div class="hocwp-theme">
                <p><?php _e( 'To display your slideshow, add the following shortcode (in orange) to your page. If adding the slideshow to your theme files, additionally include the surrounding PHP code (in gray).', 'sb-core' ); ?></p>
                <input id="copy-shortcode-value" readonly onclick="__copy_shortcode()" class="widefat disabled code"
                       value="<?php echo esc_attr( $shortcode ); ?>" style="cursor: pointer">
                <pre id="copy-all-shortcode-value" dir="ltr" class="text-gray text-sm">&lt;?php echo do_shortcode('<br><div
                            class="text-orange cursor-pointer whitespace-normal inline"
                            onclick="__click_pre_code()"><?php echo $shortcode; ?></div><br>'); ?&gt;</pre>
                <div class="flex mt-4 justify-between">
                    <p class="m-0"><?php _e( 'Click shortcode to copy', 'sb-core' ); ?></p>
                    <button type="button" onclick="__copy_all_shortcode()"
                            data-copied-text="<?php echo esc_attr( $copied ); ?>"
                            data-text="<?php echo esc_attr( $text ); ?>"
                            title="<?php esc_attr_e( 'Copy all code', 'sb-core' ); ?>"
                            class="text-xs flex items-center copy">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                             class="text-gray mr-px rtl:mr-0 rtl:ml-px w-4 inline">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                        </svg>
						<?php echo $text; ?>
                    </button>
                </div>
                <script>
                    function __click_pre_code() {
                        document.getElementById("copy-shortcode-value").click();
                    }

                    function __copy_all_shortcode() {
                        __copy_shortcode("copy-shortcode-value");
                    }

                    function __copy_shortcode(id) {
                        id = id || "copy-shortcode-value";

                        let copyText = document.getElementById(id);

                        copyText.select();
                        copyText.setSelectionRange(0, 99999);

                        document.execCommand("copy");
                    }
                </script>
            </div>
			<?php
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HOCWP_EXT_Slider()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HOCWP_EXT_Slider() {
	return HOCWP_EXT_Slider::get_instance();
}