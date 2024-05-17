<?php
/**
 * Name: Media Player
 * Description: Play media on your site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_Ext_Media_Player' ) ) {
	final class HOCWP_Ext_Media_Player extends HOCWP_Theme_Extension {
		protected static $instance;
		protected $players;
		protected $player;

		public static function get_instance() {
			if ( ! self::$instance instanceof self ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public $hide_source = true;
		public $inline_player = false;

		public $width = 640;
		public $height = 360;

		public function __construct() {
			if ( self::$instance instanceof self ) {
				return;
			}

			$this->players = array(
				'jwplayer'           => 'JW Player',
				'mediaelementplayer' => 'MediaElementPlayer',
				'dplayer'            => 'DPlayer'
			);

			$this->folder_name = 'media-player';
			$this->folder_url  = HOCWP_EXT_URL . '/ext';

			parent::__construct( __FILE__ );

			$inline = $this->get_option( 'inline_player' );

			$this->inline_player = ( 1 == $inline );

			add_action( 'init', array( $this, 'init_action' ) );

			if ( is_admin() ) {

			} else {
				add_action( 'template_redirect', array( $this, 'template_redirect_action' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts_action' ) );
			}

			$this->player = HT_Util()->get_theme_option( 'player', 'jwplayer', $this->option_name );

			if ( empty( $this->player ) ) {
				$this->player = 'jwplayer';
			}

			add_filter( 'hocwp_theme_extension_media_player_pre_facebook_direct_url', array(
				$this,
				'get_facebook_video_direct_link'
			), 10, 2 );

			add_shortcode( 'hocwp_media_player', array( $this, 'shortcode_func' ) );
		}

		public function get_facebook_video_direct_link( $direct, $url ) {
			if ( ! empty( $url ) ) {
				$video_id = $this->get_facebook_video_id( $url );

				if ( is_numeric( $video_id ) ) {
					$tmp = $this->get_facebook_video_graph( $video_id . '?fields=source,picture' );

					if ( isset( $tmp->source ) && ! empty( $tmp->source ) ) {
						$direct = $tmp->source;
					}
				}
			}

			return $direct;
		}

		public function option_tabs( $tabs ) {
			$tabs[ $this->option_name ] = array(
				'text' => __( 'Media Player', 'sb-core' ),
				'icon' => '<span class="dashicons dashicons-controls-play"></span>'
			);

			return $tabs;
		}

		public function get_player_name() {
			return $this->player;
		}

		public function option_sections() {
			$sections = array();

			if ( $this->use_jwplayer() ) {
				$sections[] = array(
					'id'       => 'jwplayer-section',
					'title'    => __( 'JW Player Configuration', 'sb-core' ),
					'tab'      => $this->option_name,
					'callback' => array( $this, 'section_jwplayer_callback' )
				);
			}

			return $sections;
		}

		public function section_jwplayer_callback() {
			echo wpautop( __( 'Provide informations for player working. JW Player is the most powerful & flexible video platform powered by the fastest, most-used HTML5 online video player.', 'sb-core' ) );
		}

		public function option_fields() {
			$fields = array();

			$args = array();

			$field    = hocwp_theme_create_setting_field( 'background', __( 'Background', 'sb-core' ), 'media_upload', $args, 'positive_number', $this->option_name );
			$fields[] = $field;

			$args['type']  = 'checkbox';
			$args['label'] = __( 'By default, the media will be played via iframe. But you can play media instantly by checking this box.', 'sb-core' );

			$field    = hocwp_theme_create_setting_field( 'inline_player', __( 'Inline Player', 'sb-core' ), 'input', $args, 'boolean', $this->option_name );
			$fields[] = $field;

			$args = array(
				'options' => $this->players,
				'class'   => 'regular-text'
			);

			$field    = hocwp_theme_create_setting_field( 'player', __( 'Player', 'sb-core' ), 'select', $args, 'string', $this->option_name );
			$fields[] = $field;

			$args = array();

			$field    = hocwp_theme_create_setting_field( 'jwplayer_key', _x( 'Key', 'jw player', 'sb-core' ), 'input', $args, 'string', $this->option_name, 'jwplayer-section' );
			$fields[] = $field;

			$field    = hocwp_theme_create_setting_field( 'player_library_url', __( 'Player Library URL', 'sb-core' ), 'input', $args, 'url', $this->option_name, 'jwplayer-section' );
			$fields[] = $field;

			$skins_dir = HOCWP_EXT_PATH . '/lib/jwplayer/skins';

			if ( is_dir( $skins_dir ) ) {
				$files = scandir( $skins_dir );
				unset( $files[0], $files[1] );

				if ( HT()->array_has_value( $files ) ) {
					$opts = array(
						__( '-- Choose skin --', 'sb-core' )
					);

					foreach ( $files as $file ) {
						if ( '.' == $file || '..' == $file || false !== strpos( $file, '.min' ) ) {
							continue;
						}

						$info = pathinfo( $file );

						$opts[ $info['filename'] ] = ucfirst( $info['filename'] );
					}

					$fields[] = array(
						'id'      => 'jwplayer_skin',
						'title'   => __( 'Player Skin', 'sb-core' ),
						'tab'     => $this->option_name,
						'section' => 'jwplayer-section',
						'args'    => array(
							'type'          => 'string',
							'callback'      => array( 'HOCWP_Theme_HTML_Field', 'select' ),
							'callback_args' => array(
								'options' => $opts
							)
						)
					);
				}
			}

			return $fields;
		}

		public function option_scripts() {
			if ( function_exists( 'HT_Enqueue' ) ) {
				HT_Enqueue()->media_upload();
			} else {
				HT_Util()->enqueue_media();
			}
		}

		public function get_background_url() {
			$id = HT_Util()->get_theme_option( 'background', '', $this->option_name );

			$url = '';

			if ( ! empty( $id ) ) {
				$url = wp_get_attachment_image_url( $id, 'full' );
			}

			return apply_filters( 'hocwp_theme_extension_media_player_background', $url );
		}

		public function init_action() {
			add_rewrite_endpoint( 'player', EP_ROOT );
		}

		public function template_redirect_action() {
			global $wp_query;

			if ( isset( $wp_query->query_vars['player'] ) ) {
				show_admin_bar( false );
				include $this->basedir . '/media-player/player.php';
				exit;
			}
		}

		public function wp_enqueue_scripts_action() {
			if ( defined( 'HTE_MEDIA_PLAYER' ) && HTE_MEDIA_PLAYER ) {
				wp_enqueue_script( 'jquery' );

				$player = get_query_var( 'player' );

				if ( empty( $player ) ) {
					$player = $this->player;
				}

				if ( 'mediaelementplayer' == $player ) {
					wp_enqueue_style( 'wp-mediaelement' );
					wp_enqueue_script( 'wp-mediaelement' );
				}

				if ( 'dplayer' == $player ) {
					wp_enqueue_style( 'dplayer-style', HOCWP_EXT_URL . '/lib/dplayer/1.25.0/DPlayer.min.css' );
				}

				wp_enqueue_script( HOCWP_Theme()->get_textdomain() );
			}
		}

		public function use_jwplayer() {
			$jwplayer = HT_Util()->get_theme_option( 'jwplayer', '', $this->option_name );

			if ( 'jwplayer' == $this->player ) {
				$jwplayer = 1;
			}

			return ( 1 == $jwplayer );
		}

		public function get_embed_video_url( $url ) {
			$url   = str_replace( 'embed|', 'HOCWP_SPLIT', $url );
			$url   = str_replace( '|embed', 'HOCWP_SPLIT', $url );
			$parts = explode( 'HOCWP_SPLIT', $url );
			$parts = array_filter( $parts );

			$url = array_shift( $parts );

			while ( false === strpos( $url, 'http' ) && false === strpos( $url, 'www' ) && HT()->array_has_value( $parts ) ) {
				$url = array_shift( $parts );
			}

			$url = esc_url( $url );

			$domain = HT()->get_domain_name( $url, true );

			switch ( $domain ) {
				case 'youtube.com':
					$url = str_replace( 'watch?v=', 'embed/', $url );
					break;
			}

			return $url;
		}

		public function shortcode_func( $atts = array() ) {
			$atts = shortcode_atts( array(
				'file'        => '',
				'image'       => '',
				'hide_source' => 0,
				'autostart'   => 0,
				'add_tag'     => '',
				'html_tag'    => 'video',
				'width'       => $this->width,
				'height'      => $this->height
			), $atts );

			ob_start();

			$this->play( array(
				'src'         => $atts['file'],
				'thumbnail'   => $atts['image'],
				'hide_source' => ( $atts['hide_source'] && 1 == $atts['hide_source'] ) ? true : false,
				'autostart'   => $atts['autostart'],
				'add_tag'     => $atts['add_tag'],
				'html_tag'    => $atts['html_tag'],
				'width'       => $atts['width'],
				'height'      => $atts['height']
			) );

			return ob_get_clean();
		}

		public function play( $args = array() ) {
			$defaults = array(
				'thumbnail'   => '',
				'loading'     => __( 'Loading mirror...', 'sb-core' ),
				'src'         => '',
				'player'      => $this->player,
				'hide_source' => $this->hide_source,
				'autostart'   => 0,
				'add_tag'     => '',
				'html_tag'    => 'video',
				'width'       => $this->width,
				'height'      => $this->height
			);

			$args = wp_parse_args( $args, $defaults );

			$hide_source = $args['hide_source'];
			?>
			<div class="mirror-video embed-responsive main-player embed-responsive-16by9">
				<div class="film-grain"></div>
				<?php
				if ( ! empty( $args['thumbnail'] ) ) {
					?>
					<div class="episode-cover video-cover"
					     style="background: transparent url('<?php echo $args['thumbnail']; ?>') no-repeat center center; background-size: cover;">
					</div>
					<?php
				}
				?>
				<div class="alert alert-info loading-mirror" aria-hidden="true" style="display: none">
					<i class="fa fa-info-circle" aria-hidden="true"></i> <?php echo $args['loading']; ?>
				</div>
				<?php
				$src = $args['src'];

				if ( is_numeric( $src ) || '0' == $src || '1' == $src ) {
					$src = '';
				}

				$iframe = false;
				$embed  = false;

				if ( is_array( $src ) ) {
					if ( isset( $src['iframe'] ) ) {
						$iframe = (bool) $src['iframe'];
					}

					if ( isset( $src['embed'] ) ) {
						$embed = (bool) $src['embed'];
					}

					if ( isset( $src['file'] ) ) {
						$src = $src['file'];
					} else {
						$src = current( $src );
					}
				}

				// Check if source is an iframe.
				if ( $iframe || false !== strpos( $src, '<iframe' ) ) {
					echo $src;
				} else {
					$oembed = '';

					// Check if source is embed.
					if ( $embed || ( false !== strpos( $src, 'embed|' ) || false !== strpos( $src, '|embed' ) ) ) {
						$src = $this->get_embed_video_url( $src );

						$domain = HT()->get_domain_name( $src, true );

						if ( 'www.facebook.com' == $domain || 'facebook.com' == $domain ) {
							$base_url = 'https://www.facebook.com/plugins/video.php';

							$params = array(
								'href'      => $src,
								'height'    => 280,
								'show_text' => 'false',
								'width'     => 500,
								'appId'     => HT_Options()->get_tab( 'facebook_app_id', '', 'social' ),
								'mute'      => 0
							);

							$params = apply_filters( 'hocwp_theme_extension_media_player_facebook_embed_params', $params, $this );

							$base_url = add_query_arg( $params, $base_url );

							$src = $base_url;
						}

						if ( ! empty( $src ) ) {
							$oembed = '<iframe width="' . $this->width . '" height="' . $this->height . '" src="' . $src . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
						}
					} else {
						// Hide video source then get it later via AJAX.
						if ( $hide_source ) {
							$args['src'] = '';
						} else {
							$oembed = ! ( $this->use_jwplayer() ) ? wp_oembed_get( $src ) : '';
						}

						if ( empty( $oembed ) ) {
							$url = home_url( 'player' );

							$params = array(
								'src'       => $src,
								'nonce'     => wp_create_nonce(),
								'autostart' => $args['autostart'],
								'add_tag'   => $args['add_tag'],
								'html_tag'  => $args['html_tag']
							);

							$params = wp_parse_args( $params, $args );

							if ( $hide_source ) {
								unset( $params['src'] );
							}

							if ( $this->inline_player ) {
								$this->html( $params );
							} else {
								$params = array_map( 'htmlentities', $params );
								$params = array_map( 'urlencode', $params );
								$url    = add_query_arg( $params, $url );

								$oembed = '<iframe width="' . $this->width . '" height="' . $this->height . '" src="' . $url . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
							}
						}
					}

					echo $oembed;
				}
				?>
			</div>
			<?php
		}

		public function html( $args = array() ) {
			if ( empty( $args ) ) {
				$args = $_GET;
			}

			$src = isset( $args['src'] ) ? $args['src'] : '';

			if ( ( empty( $src ) || is_numeric( $src ) || '0' == $src || '1' == $src ) && ! HTE_Media_Player()->hide_source ) {
				return;
			}

			$post_id = isset( $args['post_id'] ) ? $args['post_id'] : '';

			if ( empty( $src ) && isset( $args['source_key'] ) ) {
				$src = get_post_meta( $post_id, $args['source_key'], true );
			}

			$domain = HT()->get_domain_name( $src );

			$thumbnail = isset( $args['thumbnail'] ) ? $args['thumbnail'] : '';

			switch ( $domain ) {
				case 'www.drive.google.com':
				case 'drive.google.com':
					$src = HTE_Media_Player()->get_google_drive_url( $src );
					break;
				case 'www.facebook.com':
				case 'facebook.com':
					$src = HTE_Media_Player()->get_facebook_url( $src );
					break;
				case 'www.lotus.vn':
				case 'lotus.vn':
					$src = HTE_Media_Player()->get_lotus_url( $src );
					break;
			}

			$poster = HTE_Media_Player()->get_background_url();

			if ( empty( $thumbnail ) && HT()->is_positive_number( $post_id ) ) {
				$thumbnail = get_post_meta( $post_id, '_thumbnail_url', true );
			}

			if ( ! empty( $thumbnail ) ) {
				$poster = $thumbnail;
			}

			$player = isset( $args['player'] ) ? $args['player'] : 'jwplayer';
			?>
			<div class="embedded"
			     style="position: absolute; left: 0; top: 0; z-index: 9999; right: 0; bottom: 0; width: 100%; height: 100%;">
				<?php
				$src = apply_filters( 'hocwp_theme_extension_media_player_pre_source', $src, $args );

				$html = apply_filters( 'hocwp_theme_extension_media_player_html', '', $src, $poster, $post_id );

				if ( empty( $html ) ) {
					$params = array(
						'file'      => $src,
						'image'     => $poster,
						'autostart' => isset( $args['autostart'] ) ? $args['autostart'] : 0,
						'add_tag'   => isset( $args['add_tag'] ) ? $args['add_tag'] : '',
						'tag'       => isset( $args['tag'] ) ? $args['tag'] : '',
						'type'      => isset( $args['type'] ) ? $args['type'] : 'video/mp4'
					);

					if ( 'mediaelementplayer' == $player ) {
						HTE_Media_Player()->play_with_mediaelementplayer( $params );
					} elseif ( 'jwplayer' == $player ) {
						HTE_Media_Player()->play_with_jwplayer( $params );
					} elseif ( 'dplayer' == $player ) {
						HTE_Media_Player()->play_with_dplayer( $params );
					}
				}

				do_action( 'hocwp_theme_extension_media_player_load_' . $player, $src, $poster, $post_id );
				do_action( 'hocwp_theme_extension_media_player_load_player', $src, $poster, $post_id );
				?>
			</div>
			<?php
		}

		private function cache_url( $key, $value ) {
			set_transient( $key, $value, 12 * HOUR_IN_SECONDS );
		}

		private function get_cached_url( $key ) {
			return get_transient( $key );
		}

		public function get_google_drive_url( $url, $api_key = '' ) {
			if ( empty( $api_key ) ) {
				$api_key = HT_Util()->get_theme_option( 'google_api_key', '', 'social' );
			}

			if ( ! empty( $api_key ) ) {
				$url   = esc_url_raw( $url );
				$parts = parse_url( $url );

				if ( isset( $parts['query'] ) ) {
					parse_str( $parts['query'], $query );
				}

				$id = '';

				if ( isset( $query['id'] ) ) {
					$id = $query['id'];
				} else {
					$parts = explode( '/', $url );
					$key   = array_search( 'd', $parts );

					if ( is_int( $key ) && isset( $parts[ $key + 1 ] ) ) {
						$id = $parts[ $key + 1 ];
					}
				}

				if ( empty( $id ) ) {
					$last = array_pop( $parts );
					$id   = remove_query_arg( 'e', $last );
				}

				if ( ! empty( $id ) ) {
					$url = 'https://www.googleapis.com/drive/v3/files/' . $id . '?alt=media&key=' . $api_key;
				}
			}

			return $url;
		}

		private function get_facebook_url_onsite( $url ) {
			$tmp  = $url;
			$res  = wp_remote_get( $tmp );
			$body = wp_remote_retrieve_body( $res );

			if ( empty( $body ) ) {
				return $url;
			}

			$dom  = new DomDocument();
			$save = false;

			$internalErrors = libxml_use_internal_errors( true );
			$dom->loadHTML( $body );
			libxml_use_internal_errors( $internalErrors );
			$scripts = $dom->getElementsByTagName( 'script' );

			foreach ( $scripts as $script ) {
				$html = $dom->saveHTML( $script );

				if ( false !== strpos( $html, '.mp4' ) ) {
					$html = strstr( $html, 'videoData:[{' );
					$html = strstr( $html, '}],', true );
					$html = str_replace( 'videoData:[', '', $html );
					$html .= '}';
					preg_match_all( '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $html, $matches );
					$tmp     = '';
					$matches = $matches[0];

					foreach ( (array) $matches as $link ) {
						if ( false === strpos( $link, 'rl' ) && false === strpos( $link, 'vabr' ) && false !== strpos( $link, 't42.9040-2' ) ) {
							$tmp = $link;
							break;
						}
					}

					if ( empty( $tmp ) ) {
						foreach ( (array) $matches as $link ) {
							if ( false == strpos( $link, 'rl' ) && false == strpos( $link, 'vabr' ) ) {
								$tmp = $link;
								break;
							}
						}
					}

					if ( empty( $tmp ) ) {
						$tmp = isset( $matches[1] ) ? $matches[1] : array_shift( $matches );
					}

					if ( ! empty( $tmp ) ) {
						$url = $tmp;
					}
				}
			}

			return $url;
		}

		public function get_lotus_video_source_html( $url, $key = '' ) {
			if ( empty( $key ) ) {
				$key = md5( $url );
			}

			$tr_name = $key . '_video_source';

			if ( false === ( $html = get_transient( $tr_name ) ) ) {
				$tmp = wp_remote_get( $url );
				$tmp = wp_remote_retrieve_body( $tmp );

				if ( ! empty( $tmp ) ) {
					$pos = strpos( $tmp, 'class="videoplayerDetail"' );

					if ( $pos ) {
						$tmp = substr( $tmp, $pos - 5 );
						$tmp = substr( $tmp, 0, strpos( $tmp, '</div>' ) );
						$tmp .= '</div>';

						$pos = strpos( $tmp, 'data-file="' );

						if ( $pos ) {
							$html = $tmp;
							set_transient( $tr_name, $html, DAY_IN_SECONDS );
						}
					}
				}
			}

			return $html;
		}

		public function get_lotus_url( $url ) {
			$tmp = apply_filters( 'hocwp_theme_extension_media_player_pre_lotus_direct_url', '', $url );

			if ( ! empty( $tmp ) ) {
				return $tmp;
			}

			$current = $url;

			$key = md5( $url );

			$tmp = $this->get_cached_url( $key );

			if ( empty( $tmp ) || $tmp == $url ) {
				$html = $this->get_lotus_video_source_html( $url, $key );

				$pos = strpos( $html, 'data-file="' );

				if ( $pos ) {
					$tmp = substr( $html, $pos );
					$tmp = substr( $tmp, 0, strpos( $tmp, '.mp4' ) );
					$tmp = str_replace( 'data-file="', '', $tmp );
					$tmp .= '.mp4';

					if ( ! empty( $tmp ) ) {
						$url = $tmp;
						$this->cache_url( $key, $url );
					}
				}
			} else {
				$url = $tmp;
			}

			return apply_filters( 'hocwp_theme_extension_media_player_lotus_direct_url', $url, $current );
		}

		public function get_facebook_url_fbdown( $url ) {
			$args = array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(),
				'body'        => array( 'URLz' => $url ),
				'cookies'     => array()
			);

			$response = wp_remote_post( 'https://www.fbdown.net/download.php', $args );

			if ( ! is_wp_error( $response ) ) {
				$body = wp_remote_retrieve_body( $response );
				$dom  = new DomDocument();

				$internalErrors = libxml_use_internal_errors( true );
				$dom->loadHTML( $body );
				libxml_use_internal_errors( $internalErrors );
				$down = $dom->getElementById( 'result' );

				if ( $down ) {
					$rows = $down->getElementsByTagName( 'div' );
					$rows = iterator_to_array( $rows );
					$data = array();

					foreach ( $rows as $row ) {
						$data[] = $dom->saveHTML( $row );
					}

					array_shift( $data );
					array_pop( $data );

					$results = array();

					foreach ( $data as $row ) {
						$dom->loadHTML( $row );
						$links = $dom->getElementsByTagName( 'a' );

						foreach ( $links as $link ) {
							$href = $link->getAttribute( 'href' );

							if ( $this->is_facebook_direct_link( $href ) ) {
								$key = md5( $href );

								if ( ! isset( $results[ $key ] ) ) {
									$results[ $key ] = $href;
								}
							}
						}
					}

					if ( HT()->array_has_value( $results ) ) {
						$url = array_pop( $results );
					}
				}
			}

			return $url;
		}

		public function get_facebook_url_tubeoffline( $url ) {
			$tmp  = 'http://www.tubeoffline.com/downloadFrom.php?host=Facebook&video=' . $url;
			$res  = wp_remote_get( $tmp );
			$body = wp_remote_retrieve_body( $res );

			if ( ! empty( $body ) ) {
				$dom = new DomDocument();

				$internalErrors = libxml_use_internal_errors( true );
				$dom->loadHTML( $body );
				libxml_use_internal_errors( $internalErrors );
				$down = $dom->getElementById( 'videoDownload' );

				if ( $down ) {
					$rows = $down->getElementsByTagName( 'tr' );
					$rows = iterator_to_array( $rows );
					$data = array();

					foreach ( $rows as $row ) {
						$data[] = $dom->saveHTML( $row );
					}

					array_shift( $data );
					array_pop( $data );
					$row = isset( $data[2] ) ? $data[2] : '';

					if ( empty( $row ) ) {
						foreach ( $data as $row ) {
							$dom->loadHTML( $row );
							$links = $dom->getElementsByTagName( 'a' );

							foreach ( $links as $link ) {
								$href = $link->getAttribute( 'href' );

								if ( $this->is_facebook_direct_link( $href ) ) {
									$url = $href;
									break;
								}
							}
						}
					} else {
						$dom->loadHTML( $row );
						$links = $dom->getElementsByTagName( 'a' );

						foreach ( $links as $link ) {
							$href = $link->getAttribute( 'href' );

							if ( ! empty( $href ) ) {
								$url = $href;
								break;
							}
						}
					}
				}
			}

			return $url;
		}

		public function is_facebook_direct_link( $url ) {
			if ( ! empty( $url ) ) {
				if ( false !== strpos( $url, 't42.9040-2' ) || ( false !== strpos( $url, 'fbcdn.net' ) ) ) {
					return true;
				}
			}

			return false;
		}

		public function get_facebook_url( $url ) {
			$tmp = apply_filters( 'hocwp_theme_extension_media_player_pre_facebook_direct_url', '', $url );

			if ( ! empty( $tmp ) ) {
				return $tmp;
			}

			$current = $url;

			$key = md5( $url );
			$bk  = $url;
			$tmp = $this->get_cached_url( $key );

			if ( empty( $tmp ) || $tmp == $url ) {
				$tmp = $this->get_facebook_url_fbdown( $url );

				if ( empty( $tmp ) || $tmp == $url ) {
					$save = false;

					$tmp = $this->get_facebook_url_tubeoffline( $url );

					if ( $tmp != $url ) {
						$url  = $tmp;
						$save = true;
					} else {
						$tmp = $this->get_facebook_url_onsite( $url );

						if ( $tmp != $url ) {
							$url  = $tmp;
							$save = true;
						}
					}

					if ( $save ) {
						$this->cache_url( $key, $url );
					}
				} else {
					$url = $tmp;
				}
			} else {
				$url = $tmp;
			}

			if ( $url == $bk ) {
				$video_id = $this->get_facebook_video_id( $url );

				if ( is_numeric( $video_id ) ) {
					$tmp = $this->get_facebook_video_graph( $video_id );

					if ( isset( $tmp->source ) && ! empty( $tmp->source ) ) {
						$url = $tmp->source;
						$this->cache_url( $key, $url );
					}
				}
			}

			return apply_filters( 'hocwp_theme_extension_media_player_facebook_direct_url', $url, $current );
		}

		public function get_facebook_video_graph( $video_id ) {
			return $this->facebook_graph( $video_id );
		}

		public function get_facebook_video_embed_url( $url ) {
			$url = trailingslashit( $url );
			preg_match( '/https:\/\/www.facebook.com\/(.*)\/videos\/(.*)\/(.*)\/(.*)/U', $url, $matches );

			if ( isset( $matches[4] ) ) {
				$id = $matches[3];
			} else {
				preg_match( '/https:\/\/www.facebook.com\/(.*)\/videos\/(.*)\/(.*)/U', $url, $matches );

				if ( isset( $matches[3] ) ) {
					$id = $matches[2];
				} else {
					preg_match( '/https:\/\/www.facebook.com\/video\.php\?v\=(.*)/', $url, $matches );
					$id = $matches[1];
					$id = substr( $id, 0, - 1 );
				}
			}

			$embed = 'https://www.facebook.com/video/embed?video_id=' . $id;

			return $embed;
		}

		public function get_facebook_video_id( $url ) {
			if ( ! empty( $url ) ) {
				if ( is_numeric( $url ) ) {
					return $url;
				}

				$url   = untrailingslashit( $url );
				$parts = explode( '/videos/', $url );

				if ( isset( $parts[1] ) && is_numeric( $parts[1] ) ) {
					return $parts[1];
				}
			}

			return '';
		}

		public function facebook_graph( $slug, $access_token = '' ) {
			if ( empty( $access_token ) ) {
				$access_token = HT_Util()->get_theme_option( 'facebook_access_token', '', 'social' );
			}

			if ( ! empty( $access_token ) ) {
				$base = 'https://graph.facebook.com/' . $slug;
				$base = add_query_arg( 'access_token', $access_token, $base );

				$data = wp_remote_get( $base );

				if ( ! empty( $data ) && ! is_wp_error( $data ) ) {
					$data = wp_remote_retrieve_body( $data );

					return json_decode( $data );
				}
			}

			return '';
		}

		public function get_facebook_video_thumbnail_url( $video_id ) {
			if ( ! is_numeric( $video_id ) ) {
				$video_id = $this->get_facebook_video_id( $video_id );
			}

			$result = $this->facebook_graph( $video_id . '/thumbnails' );

			if ( isset( $result->data ) && HT()->array_has_value( $result->data ) ) {
				return $result->data[0]->uri;
			}

			return '';
		}

		public function get_youtube_video_id( $url ) {
			$id = '';

			if ( false !== strpos( $url, '?v=' ) ) {
				$parts = parse_url( $url );

				if ( isset( $parts['query'] ) ) {
					parse_str( $parts['query'], $query );

					$id = isset( $query['v'] ) ? $query['v'] : '';
				}
			} elseif ( false !== strpos( $url, '/embed/' ) ) {
				$parts = explode( '/embed/', $url );
				$id    = array_pop( $parts );
			} elseif ( false !== strpos( $url, 'youtu.be/' ) ) {
				$parts = explode( 'youtu.be/', $url );
				$id    = array_pop( $parts );
			}

			return $id;
		}

		public function get_youtube_video_thumbnail_url( $video_id ) {
			if ( false !== strpos( $video_id, 'http' ) || false !== strpos( $video_id, 'www' ) || false !== strpos( $video_id, 'youtu' ) ) {
				$video_id = $this->get_youtube_video_id( $video_id );
			}

			return 'http://img.youtube.com/vi/' . $video_id . '/0.jpg';
		}

		public function get_lotus_video_thumbnail_url( $url ) {
			$html = $this->get_lotus_video_source_html( $url );

			$pos = strpos( $html, 'data-thumb="' );

			if ( $pos ) {
				$html = substr( $html, $pos );
				$html = str_replace( 'data-thumb="', '', $html );
				$html = substr( $html, 0, strpos( $html, '"' ) );

				if ( ! empty( $html ) ) {
					return $html;
				}
			}

			return '';
		}

		public function get_video_thumbnail_url( $url ) {
			$domain = HT()->get_domain_name( $url, true );
			$result = '';

			switch ( $domain ) {
				case 'youtu.be':
				case 'youtube.com':
					$result = $this->get_youtube_video_thumbnail_url( $url );
					break;
				case 'facebook.com':
					$result = $this->get_facebook_video_thumbnail_url( $url );
					break;
				case 'lotus.vn':
					$result = $this->get_lotus_video_thumbnail_url( $url );
					break;
			}

			return $result;
		}

		private function video_or_audio_html( $args = array() ) {
			$args = apply_filters( 'hocwp_theme_extension_media_player_pre_player_args', $args );

			$src = isset( $args['file'] ) ? $args['file'] : '';

			$autostart = isset( $args['autostart'] ) ? $args['autostart'] : 0;

			$tag = isset( $args['html_tag'] ) ? $args['html_tag'] : '';

			if ( 'video' != $tag && 'audio' != $tag ) {
				$tag = 'video';
			}

			$id = isset( $args['id'] ) ? $args['id'] : 'videoPlayer';

			$inner = '';

			if ( HT()->array_has_value( $src ) ) {
				foreach ( (array) $src as $type => $url ) {
					$source = new HOCWP_Theme_HTML_Tag( 'source' );
					$source->add_attribute( 'type', $type );
					$source->add_attribute( 'src', $url );
					$inner .= $source->build();
				}
			} else {
				$source = new HOCWP_Theme_HTML_Tag( 'source' );
				$source->add_attribute( 'src', $src );
				$inner .= $source->build();
			}

			$html = new HOCWP_Theme_HTML_Tag( $tag );
			$html->add_attribute( 'id', $id );

			$controls = isset( $args['controls'] ) ? $args['controls'] : true;

			if ( $controls ) {
				$html->add_attribute( 'controls', true );
			}

			$loop = isset( $args['loop'] ) ? $args['loop'] : '';

			if ( $loop ) {
				$html->add_attribute( 'loop', true );
			}

			$muted = isset( $args['muted'] ) ? $args['muted'] : '';

			if ( $muted ) {
				$html->add_attribute( 'muted', true );
			}

			$html->add_attribute( 'style', 'display: none; position: absolute; left: 0; top: 0; right: 0; bottom: 0; width: 100%; height: 100%;' );
			$html->set_text( $inner );

			if ( 1 == $autostart ) {
				$html->add_attribute( 'autoplay', true );
			}

			$image = isset( $args['image'] ) ? $args['image'] : '';

			if ( ! empty( $image ) ) {
				$html->add_attribute( 'poster', $image );
			}

			$html->output();
		}

		public function play_with_mediaelementplayer( $args = array() ) {
			$image = isset( $args['image'] ) ? $args['image'] : '';

			$this->video_or_audio_html( $args );

			$id = isset( $args['id'] ) ? $args['id'] : 'videoPlayer';

			$settings = apply_filters( 'hocwp_theme_extension_media_player_pre_mediaelementplayer_settings', array(), $args );
			?>
			<script>
				(function ($) {
					if ($.fn.mediaelementplayer) {
						var settings = {
								videoWidth: "100%",
								videoHeight: "100%",
								enableAutosize: true,
								controls: true,
								success: function (mediaElement, originalNode, instance) {
									instance.setPoster("<?php echo $image; ?>");
									// Set player style display block.
									$(mediaElement).show().children().show();
								}
							},
							customSettings = <?php echo json_encode( $settings ); ?>;

						if ("object" == typeof customSettings) {
							settings = $.extend(settings, customSettings);
						}

						$("#<?php echo $id; ?>").mediaelementplayer(settings);
					}
				})(jQuery);
			</script>
			<?php
		}

		public function play_with_dplayer( $args = array() ) {
			$lib = HOCWP_EXT_URL . '/lib/dplayer/1.25.0/DPlayer.min.js';
			$id  = isset( $args['id'] ) ? $args['id'] : 'videoPlayer';

			$src   = isset( $args['file'] ) ? $args['file'] : '';
			$image = isset( $args['image'] ) ? $args['image'] : '';

			$autostart = isset( $args['autostart'] ) ? $args['autostart'] : 0;

			$this->video_or_audio_html( $args );

			$settings = apply_filters( 'hocwp_theme_extension_media_player_pre_dplayer_settings', array(), $args );
			?>
			<script src="<?php echo $lib; ?>"></script>
			<script>
				if ("undefined" != typeof DPlayer) {
					(function ($) {
						var autostart = parseInt(<?php echo $autostart; ?>),
							element = document.getElementById("<?php echo $id; ?>"),
							settings = {
								container: element,
								screenshot: true,
								autoplay: (1 === autostart),
								video: {
									url: "<?php echo $src; ?>",
									pic: "<?php echo $image; ?>"
								}
							},
							customSettings = <?php echo json_encode( $settings ); ?>;

						if ("object" == typeof customSettings) {
							settings = $.extend(settings, customSettings);
						}

						const dp = new DPlayer(settings);

						dp.on("canplay", function () {
							// Set player style display block.
							element.style.display = "block";
						});
					})(jQuery);
				}
			</script>
			<?php
		}

		public function play_with_jwplayer( $args = array() ) {
			$key   = isset( $_GET['key'] ) ? $_GET['key'] : $this->get_option( 'jwplayer_key' );
			$src   = isset( $args['file'] ) ? $args['file'] : '';
			$image = isset( $args['image'] ) ? $args['image'] : '';
			$lib   = $this->get_option( 'player_library_url' );

			$autostart = isset( $args['autostart'] ) ? $args['autostart'] : 0;

			if ( empty( $lib ) ) {
				$lib = HOCWP_EXT_URL . '/lib/jwplayer/jwplayer.js';
			}

			if ( ! empty( $lib ) ) {
				?>
				<script src="<?php echo $lib; ?>"></script>
				<?php
			}

			if ( ! empty( $key ) ) {
				?>
				<script>
					if ("undefined" !== typeof jwplayer) {
						jwplayer.key = "<?php echo $key; ?>";
					}
				</script>
				<?php
			}

			$skin = $this->get_option( 'jwplayer_skin' );

			if ( empty( $skin ) ) {
				$skin = 'seven';
			}

			$add_tag = '';

			if ( function_exists( 'HTE_Ads' ) ) {
				$add_tag = isset( $args['add_tag'] ) ? $args['add_tag'] : '';
				$object  = '';

				if ( HT()->is_positive_number( $add_tag ) ) {
					$object = get_post( $add_tag );
				} elseif ( 'random' == $add_tag ) {
					$query = HTE_Ads()->query_vast_vpaid( array( 'random' => true ) );

					if ( $query->have_posts() ) {
						$object = current( $query->posts );
					}
				}

				if ( $object instanceof WP_Post && HTE_Ads()->post_type == $object->post_type ) {
					$add_tag = get_post_meta( $object->ID, 'vast_vpaid_url', true );
				}

				if ( 'random' == $add_tag || is_wp_error( $add_tag ) || is_numeric( $add_tag ) ) {
					$add_tag = '';
				}

				if ( false === strpos( $add_tag, '.' ) && false === strpos( $add_tag, '/' ) ) {
					$add_tag = '';
				}

				if ( empty( $add_tag ) ) {
					$add_tag = HTE_Ads()->get_option( 'video_ad_tag_url' );
				}

				if ( ! empty( $add_tag ) ) {
					$add_tag = array(
						'client'        => 'googima',
						'tag'           => $add_tag,
						'skipoffset'    => 5,
						'vpaidmode'     => 'insecure',
						'vpaidcontrols' => true
					);
				}
			}

			$this->video_or_audio_html( $args );

			$id = isset( $args['id'] ) ? $args['id'] : 'videoPlayer';

			$type = isset( $args['type'] ) ? $args['type'] : 'video/mp4';

			$settings = apply_filters( 'hocwp_theme_extension_media_player_pre_jwplayer_settings', array(), $args );
			?>
			<script>
				(function ($) {
					if ("undefined" !== typeof jwplayer) {
						var autostart = parseInt(<?php echo $autostart; ?>),
							advertising = <?php echo json_encode( $add_tag ); ?>,
							playerSettings = {
								file: "<?php echo $src; ?>",
								image: "<?php echo $image; ?>",
								autostart: (1 === autostart),
								displayPlaybackLabel: true,
								primary: "html5",
								skin: {
									name: "<?php echo $skin; ?>"
								},
								aspectratio: "16:9",
								width: "100%",
								height: "100%",
								stretching: "uniform",
								type: "<?php echo $type; ?>"
							},
							customSettings = <?php echo json_encode( $settings ); ?>;

						if ("object" == typeof advertising) {
							playerSettings.advertising = advertising;
						}

						if ("object" == typeof customSettings) {
							playerSettings = $.extend(playerSettings, customSettings);
						}

						// Setup the player
						const player = jwplayer("<?php echo $id; ?>").setup(playerSettings);

						player.on("ready", function () {
							$(player.getContainer()).show();
						});
					}
				})(jQuery);
			</script>
			<?php
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HTE_Media_Player()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HTE_Media_Player() {
	return HOCWP_Ext_Media_Player::get_instance();
}