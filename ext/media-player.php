<?php
/**
 * Name: Media Player
 * Description: Play media on your site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Ext_Media_Player extends HOCWP_Theme_Extension {
	protected static $instance;

	public static function get_instance() {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public $hide_source = true;

	public function __construct() {
		if ( self::$instance instanceof self ) {
			return;
		}

		$this->folder_name = 'media-player';

		parent::__construct( __FILE__ );

		add_action( 'init', array( $this, 'init_action' ) );

		if ( is_admin() ) {

		} else {
			add_action( 'template_redirect', array( $this, 'template_redirect_action' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts_action' ) );
		}
	}

	public function option_tabs( $tabs ) {
		$tabs[ $this->option_name ] = array(
			'text' => __( 'Media Player', 'sb-core' ),
			'icon' => '<span class="dashicons dashicons-controls-play"></span>'
		);

		return $tabs;
	}

	public function option_sections() {
		$sections = array();

		if ( 1 == HT_Util()->get_theme_option( 'jwplayer', '', $this->option_name ) ) {
			$sections[] = array(
				'id'    => 'jwplayer-section',
				'title' => __( 'JW Player Configuration', 'sb-core' ),
				'tab'   => $this->option_name
			);
		}

		return $sections;
	}

	public function option_fields() {
		$fields = array();

		$args = array();

		$field    = hocwp_theme_create_setting_field( 'background', __( 'Background', 'sb-core' ), 'media_upload', $args, 'positive_number', $this->option_name );
		$fields[] = $field;

		$args['type']  = 'checkbox';
		$args['label'] = __( 'Using JW Player as your main player?', 'sb-core' );

		$field    = hocwp_theme_create_setting_field( 'jwplayer', __( 'Use JW Player', 'sb-core' ), 'input', $args, 'boolean', $this->option_name );
		$fields[] = $field;

		$args = array();

		$field    = hocwp_theme_create_setting_field( 'jwplayer_key', _x( 'Key', 'jw player', 'sb-core' ), 'input', $args, 'string', $this->option_name, 'jwplayer-section' );
		$fields[] = $field;

		return $fields;
	}

	public function option_scripts() {
		HT_Util()->enqueue_media();
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
			wp_enqueue_style( 'wp-mediaelement' );
			wp_enqueue_script( 'wp-mediaelement' );
			wp_enqueue_script( HOCWP_Theme()->get_textdomain() );
		}
	}

	public function use_jwplayer() {
		$jwplayer = HT_Util()->get_theme_option( 'jwplayer', '', $this->option_name );

		return ( 1 == $jwplayer );
	}

	public function play( $args = array() ) {
		$defaults = array(
			'thumbnail' => '',
			'loading'   => __( 'Loading mirror...', 'sb-core' ),
			'src'       => ''
		);

		$args = wp_parse_args( $args, $defaults );

		if ( $this->hide_source ) {
			$args['src'] = '';
		}
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
				$src = '';
			}

			if ( false !== strpos( $src, 'iframe' ) ) {
				echo $src;
			} else {
				$oembed = ! ( $this->use_jwplayer() ) ? wp_oembed_get( $src ) : '';

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

					$oembed = '<iframe width="640" height="360" src="' . $url . '" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
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
		$res  = wp_remote_post( $tmp );
		$body = wp_remote_retrieve_body( $res );
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

	public function get_facebook_url( $url ) {
		$key = md5( $url );
		$tmp = $this->get_cached_url( $key );

		if ( empty( $tmp ) ) {
			$tmp = $this->get_facebook_url_onsite( $url );

			if ( empty( $tmp ) ) {
				$tmp  = 'http://www.tubeoffline.com/downloadFrom.php?host=Facebook&video=' . $url;
				$res  = wp_remote_get( $tmp );
				$body = wp_remote_retrieve_body( $res );
				$dom  = new DomDocument();
				$save = false;

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

							if ( ! empty( $href ) && false !== strpos( $href, 't42.9040-2' ) ) {
								$url  = $href;
								$save = true;
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
							$url  = $href;
							$save = true;
							break;
						}
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

		return $url;
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