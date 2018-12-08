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
			$args['label'] = __( 'Using JW Player as your main player?', 'sb-core' );

			$field = hocwp_theme_create_setting_field( 'jwplayer', __( 'Use JW Player', 'sb-core' ), 'input', $args, 'boolean', $this->option_name );
			//$fields[] = $field;

			$args = array(
				'options' => $this->players
			);

			$field    = hocwp_theme_create_setting_field( 'player', __( 'Player', 'sb-core' ), 'select', $args, 'string', $this->option_name );
			$fields[] = $field;

			$args = array();

			$field    = hocwp_theme_create_setting_field( 'jwplayer_key', _x( 'Key', 'jw player', 'sb-core' ), 'input', $args, 'string', $this->option_name, 'jwplayer-section' );
			$fields[] = $field;

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

				if ( 'mediaelementplayer' == $this->player ) {
					wp_enqueue_style( 'wp-mediaelement' );
					wp_enqueue_script( 'wp-mediaelement' );
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

		public function play( $args = array() ) {
			$defaults = array(
				'thumbnail' => '',
				'loading'   => __( 'Loading mirror...', 'sb-core' ),
				'src'       => '',
				'player'    => $this->player
			);

			$args = wp_parse_args( $args, $defaults );
			?>
			<div class="mirror-video embed-responsive main-player embed-responsive-16by9">
				<div class="film-grain"></div>
				<?php
				if ( ! empty( $args['thumbnail'] ) ) {
					?>
					<div class="episode-cover"
					     style="background: transparent url('<?php echo $args['thumbnail']; ?>') no-repeat center center; background-size: cover;">
					</div>
					<?php
				}
				?>
				<div class="alert alert-info loading-mirror" aria-hidden="true">
					<i class="fa fa-info-circle" aria-hidden="true"></i> <?php echo $args['loading']; ?>
				</div>
				<?php
				$src = $args['src'];

				if ( is_array( $src ) ) {
					$src = current( $src );
				}

				if ( false !== strpos( $src, '<iframe' ) ) {
					echo $src;
				} else {
					$oembed = '';

					if ( false !== strpos( $src, 'embed|' ) || false !== strpos( $src, '|embed' ) ) {
						$src = $this->get_embed_video_url( $src );

						if ( ! empty( $src ) ) {
							$oembed = '<iframe width="' . $this->width . '" height="' . $this->height . '" src="' . $src . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
						}
					} else {
						if ( $this->hide_source ) {
							$args['src'] = '';
						} else {
							$oembed = ! ( $this->use_jwplayer() ) ? wp_oembed_get( $src ) : '';
						}

						if ( empty( $oembed ) ) {
							$url = home_url( 'player' );

							$params = array(
								'src'   => $src,
								'nonce' => wp_create_nonce()
							);

							$params = wp_parse_args( $params, $args );

							if ( $this->hide_source ) {
								unset( $params['src'] );
							}

							$url = add_query_arg( $params, $url );

							$oembed = '<iframe width="' . $this->width . '" height="' . $this->height . '" src="' . $url . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
						}
					}

					echo $oembed;
				}
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
				parse_str( $parts['query'], $query );
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

			return $url;
		}

		public function get_facebook_url_tubeoffline( $url ) {
			$tmp  = 'http://www.tubeoffline.com/downloadFrom.php?host=Facebook&video=' . $url;
			$res  = wp_remote_get( $tmp );
			$body = wp_remote_retrieve_body( $res );
			$dom  = new DomDocument();

			$internalErrors = libxml_use_internal_errors( true );
			$dom->loadHTML( $body );
			libxml_use_internal_errors( $internalErrors );
			$down = $dom->getElementById( 'videoDownload' );
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
			$key     = md5( $url );
			$bk      = $url;
			$tmp     = $this->get_cached_url( $key );

			if ( empty( $tmp ) ) {
				$tmp = $this->get_facebook_url_fbdown( $url );

				if ( empty( $tmp ) ) {
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
			}

			return $result;
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