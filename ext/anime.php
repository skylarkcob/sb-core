<?php
/*
 * Name: Anime
 * Description: Create anime site.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'HOCWP_EXT_Anime' ) ) {
	class HOCWP_EXT_Anime extends HOCWP_Theme_Extension {
		protected static $instance;

		public $taxonomies_args = array();
		public $admin_notices_transient_name = 'hocwp_ext_anime_admin_notices';

		public $order_episode_by = 'menu_order';

		public $episode_post_type = 'episode';

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

			$this->add_required_extension( 'media-player' );
			$this->folder_url = HOCWP_EXT_URL . '/ext';

			parent::__construct( __FILE__ );

			$this->get_taxonomies_args();

			add_action( 'init', array( $this, 'register_post_type_and_taxonomy' ) );

			add_action( 'registered_post_type', array( $this, 'registered_post_type' ) );
			add_filter( 'the_title', array( $this, 'the_title' ), 10, 2 );

			if ( is_admin() ) {
				add_action( 'wp_loaded', function () {
					$tab = new HOCWP_Theme_Admin_Setting_Tab( 'anime', __( 'Anime', 'sb-core' ), '<span class="dashicons dashicons-format-video"></span>' );

					$args = array(
						'public'          => true,
						'capability_type' => 'post'
					);

					$post_types = get_post_types( $args, OBJECT );

					$options = array(
						'' => __( '-- Choose post type --', 'sb-core' )
					);

					foreach ( $post_types as $post_type_object ) {
						if ( $post_type_object instanceof WP_Post_Type && 'attachment' != $post_type_object->name ) {
							$options[ $post_type_object->name ] = $post_type_object->labels->singular_name . ' (' . $post_type_object->name . ')';
						}
					}

					$args = array(
						'options' => $options
					);

					$tab->add_field_array( array(
						'id'    => 'post_type',
						'title' => __( 'Animation Post Type', 'sb-core' ),
						'args'  => array(
							'type'          => 'default',
							'callback'      => array( 'HOCWP_Theme_HTML_Field', 'select' ),
							'description'   => __( 'Post type for using as animation.', 'sb-core' ),
							'callback_args' => $args
						)
					) );
				} );

				add_action( 'admin_notices', array( $this, 'admin_notices' ) );
				add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
				add_action( 'save_post', array( $this, 'save_post' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

				add_filter( 'page_attributes_dropdown_pages_args', array(
					$this,
					'page_attributes_dropdown_pages_args'
				), 99, 2 );

				add_filter( 'page_row_actions', array( $this, 'page_row_actions' ), 10, 2 );
				add_filter( 'post_updated_messages', array( $this, 'post_updated_messages_filter' ) );
				add_filter( 'manage_episode_posts_columns', array( $this, 'manage_episode_custom_columns' ) );

				add_filter( 'manage_edit-episode_sortable_columns', array(
					$this,
					'manage_episode_custom_sortable_columns'
				) );

				add_action( 'manage_episode_posts_custom_column', array(
					$this,
					'manage_episode_posts_custom_column_action'
				), 10, 2 );

				add_action( 'pre_get_posts', array( $this, 'pre_get_posts_action' ) );

				add_action( 'page_attributes_misc_attributes', array(
					$this,
					'page_attributes_misc_attributes_action'
				) );
			} else {
				add_action( 'wp', array( $this, 'wp_action' ) );
				add_filter( 'get_pagenum_link', array( $this, 'get_pagenum_link_filter' ) );
				add_filter( 'paginate_links', array( $this, 'paginate_links_filter' ) );
				add_filter( 'hocwp_theme_pagination_first_item_url', array( $this, 'pagination_first_item_url' ) );
				add_filter( 'get_edit_post_link', array( $this, 'edit_single_episode_link' ), 10, 2 );
				add_filter( 'document_title_parts', array( $this, 'document_title_parts_filter' ), 99 );
				add_filter( 'wpseo_title', array( $this, 'wpseo_title_filter' ) );
			}

			add_filter( 'post_type_link', array( $this, 'post_type_link' ), 10, 2 );
		}

		public function get_post_type() {
			$pt = HT_Options()->get_tab( 'post_type', '', 'anime' );

			if ( ! post_type_exists( $pt ) ) {
				$pt = 'post';
			}

			return $pt;
		}

		private function build_episode_document_title( $title, $episode, $context = 'document' ) {
			if ( $this->is_episode( $episode ) ) {
				$sep = apply_filters( 'document_title_separator', HT_Frontend()->get_separator() );

				$parent = get_post( $episode->post_parent );

				$parts = array(
					'anime_title'   => $parent->post_title,
					'prefix'        => $this->get_episode_prefix( $episode->ID ),
					'episode_title' => $episode->post_title,
					'site_name'     => get_bloginfo( 'name' )
				);

				$parts = apply_filters( 'hocwp_theme_extension_anime_episode_title_parts', $parts, $episode, $context );

				$title = implode( " $sep ", $parts );

				$title = apply_filters( 'hocwp_theme_extension_anime_single_episode_title', $title, $episode, $sep, $context );

				unset( $sep, $parts, $parent );
			}

			return $title;
		}

		public function wpseo_title_filter( $title ) {
			if ( is_singular( 'post' ) ) {
				$episode = $this->get_current_episode();

				$title = $this->build_episode_document_title( $title, $episode );

				unset( $episode );
			}

			return $title;
		}

		public function wpseo_accessible_post_types( $post_types ) {
			$post_types[ $this->episode_post_type ] = $this->episode_post_type;

			return $post_types;
		}

		public function document_title_parts_filter( $parts ) {
			if ( is_singular( 'post' ) ) {
				$episode = $this->get_current_episode();

				if ( $this->is_episode( $episode ) ) {
					$title = isset( $parts['title'] ) ? $parts['title'] : '';

					$title = $this->build_episode_document_title( $title, $episode );

					$parts['title'] = $title;

					unset( $title );
				}

				unset( $episode );
			}

			return $parts;
		}

		public function edit_single_episode_link( $link, $post_id ) {
			if ( is_single( $post_id ) ) {
				$episode = $this->get_current_episode();

				if ( $this->is_episode( $episode ) ) {
					remove_filter( 'get_edit_post_link', array( $this, 'edit_single_episode_link' ) );
					$link = get_edit_post_link( $episode );
					add_filter( 'get_edit_post_link', array( $this, 'edit_single_episode_link' ), 10, 2 );
				}
			}

			return $link;
		}

		public function is_episode( $post ) {
			return ( $post instanceof WP_Post && $this->episode_post_type == $post->post_type );
		}

		public function get_current_anime_episode_list_paged() {
			$ep_list = get_query_var( 'episode-list', 1 );

			if ( ! is_numeric( $ep_list ) ) {
				$ep_list = 1;
			}

			return $ep_list;
		}

		private function replace_episode_link_link( $result ) {
			$ep_list = $this->get_current_anime_episode_list_paged();

			$rp = 'episode-list/' . $ep_list;
			$rp = trailingslashit( $rp );

			$result = str_replace( $rp, '', $result );

			return $result;
		}

		public function pagination_first_item_url( $url ) {
			if ( is_single() && ! is_page() ) {
				$url = $this->replace_episode_link_link( $url );
			}

			return $url;
		}

		public function get_pagenum_link_filter( $result ) {
			if ( is_single() && ! is_page() ) {
				if ( false !== strpos( $result, 'page' ) ) {
					$result = str_replace( '/page/', '/episode-list/', $result );
					$result = $this->replace_episode_link_link( $result );
				}
			}

			return $result;
		}

		public function paginate_links_filter( $result ) {
			if ( is_single() && ! is_page() ) {
				if ( false !== strpos( $result, 'page' ) ) {
					$result = str_replace( '/page/', '/episode-list/', $result );
				}
			}

			return $result;
		}

		public function page_attributes_misc_attributes_action( $post ) {
			if ( ! ( $post instanceof WP_Post ) || 'page' == $post->post_type ) {
				return;
			}

			if ( $post->post_type != $this->get_post_type() ) {
				return;
			}

			$prefix    = get_post_meta( $post->ID, 'prefix', true );
			$suffix    = get_post_meta( $post->ID, 'suffix', true );
			$anime     = get_post( $post->post_parent );
			$post_type = get_post_type_object( $post->post_type );
			?>
			<p class="post-attributes-label-wrapper">
				<label class="post-attributes-label" for="prefix"><?php _e( 'Prefix:', 'sb-core' ); ?></label>
			</p>
			<input name="prefix" type="text" id="prefix" value="<?php echo $prefix; ?>" class="widefat">
			<p class="post-attributes-label-wrapper">
				<label class="post-attributes-label" for="suffix"><?php _e( 'Suffix:', 'sb-core' ); ?></label>
			</p>
			<input name="suffix" type="text" id="suffix" value="<?php echo $suffix; ?>" class="widefat">
			<?php
			if ( $anime instanceof WP_Post ) {
				?>
				<div
					style="padding: 10px; clear: both; border-top: 1px solid #ddd; background: #f5f5f5; margin: 15px -12px -12px;">
					<div style="float: left; line-height: 28px;">
						<a class="edit-anime"
						   href="<?php echo get_edit_post_link( $anime ); ?>"
						   title="<?php printf( __( 'Edit %s', 'sb-core' ), $anime->post_title ); ?>"><?php _e( 'Edit Anime', 'sb-core' ); ?></a>
					</div>
					<div style="text-align: right; float: right; line-height: 23px;">
						<a class="button button-primary button-large"
						   href="<?php echo get_permalink( $post ); ?>"
						   title="<?php printf( __( 'View %s', 'sb-core' ), $post->post_title ); ?>"><?php printf( __( 'View %s', 'sb-core' ), $post_type->labels->singular_name ); ?></a>
					</div>
					<div class="clear"></div>
				</div>
				<?php
			}
		}

		public function manage_episode_custom_columns( $columns ) {
			$columns = HT()->insert_to_array( $columns, __( 'Post parent', 'sb-core' ), 'before_tail', 'post_parent' );

			return $columns;
		}

		public function manage_episode_custom_sortable_columns( $columns ) {
			$columns['post_parent'] = 'post_parent';

			return $columns;
		}

		public function manage_episode_posts_custom_column_action( $column, $post_id ) {
			if ( 'post_parent' == $column ) {
				$obj = get_post( $post_id );

				if ( HT()->is_positive_number( $obj->post_parent ) ) {
					$parent = get_post( $obj->post_parent );

					echo '<a href="' . get_edit_post_link( $parent ) . '">' . $parent->post_title . '</a>';
					echo ' (<a class="" href="' . get_permalink( $parent ) . '">' . __( 'View', 'sb-core' ) . '</a>)';
				}
			}
		}

		public function pre_get_posts_action( $query ) {
			if ( $query instanceof WP_Query && $query->is_main_query() ) {
				$post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

				if ( $this->episode_post_type == $post_type ) {
					$orderby = isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';

					if ( 'post_parent' == $orderby ) {
						$query->set( 'orderby', 'parent' );
					}
				}
			}
		}

		public function post_updated_messages_filter( $messages ) {
			$post_type = HT_Admin()->get_current_post_type();
			$post_id   = HT_Admin()->get_current_post_id();

			if ( $this->episode_post_type == $post_type ) {
				$post_type_object = get_post_type_object( $post_type );

				if ( ! $post_type_object->public && ! $post_type_object->publicly_queryable ) {
					$view_post_link_html = sprintf( ' <a href="%1$s">%2$s</a>',
						esc_url( get_the_permalink( $post_id ) ),
						__( 'View video', 'sb-core' )
					);

					if ( isset( $messages['post'][1] ) ) {
						$messages['post'][1] = $messages['post'][1] . $view_post_link_html;
					}

					if ( isset( $messages['post'][6] ) ) {
						$messages['post'][6] = $messages['post'][6] . $view_post_link_html;
					}
				}
			}

			return $messages;
		}

		public function get_current_episode() {
			if ( is_single() && ! is_page() ) {
				$id      = isset( $_GET['id'] ) ? $_GET['id'] : '';
				$episode = get_post( $id );

				if ( $this->is_episode( $episode ) ) {
					return $episode;
				}

				global $wp_query;

				if ( isset( $wp_query->query_vars[ $this->episode_post_type ] ) ) {
					$viewing = get_query_var( $this->episode_post_type );

					return $this->get_episode( get_the_ID(), $viewing );
				}
			}

			return null;
		}

		public function wp_action() {
			if ( is_single() && ! is_page() ) {
				global $wp_query;

				if ( isset( $wp_query->query_vars[ $this->episode_post_type ] ) ) {
					$viewing = get_query_var( $this->episode_post_type );

					$episode = $this->get_episode( get_the_ID(), $viewing );

					if ( ! ( $episode instanceof WP_Post ) || $this->episode_post_type != $episode->post_type ) {
						wp_redirect( get_the_permalink() );
						exit;
					}
				} elseif ( isset( $wp_query->query_vars['episode-list'] ) ) {
					$ep_list = $this->get_current_anime_episode_list_paged();

					if ( 2 > $ep_list ) {
						wp_redirect( get_the_permalink() );
						exit;
					}
				}
			}
		}

		/**
		 * @param int $parent_id The ID of parent Anime.
		 * @param string $prefix The menu order of episode.
		 *
		 * @param null|int $menu_order
		 *
		 * @return array|null|WP_Post
		 */
		public function get_episode( $parent_id, $prefix, $menu_order = null ) {
			if ( ! is_numeric( $menu_order ) && ! is_string( $prefix ) && ! is_numeric( $prefix ) ) {
				return null;
			}

			if ( '' == $prefix && ! is_numeric( $menu_order ) ) {
				return null;
			}

			$args = array(
				'post_type'   => $this->episode_post_type,
				'post_status' => 'publish',
				'fields'      => 'ids',
				'post_parent' => $parent_id,
				'meta_key'    => 'prefix_slug',
				'meta_value'  => $this->sanitize_episode_slug( $prefix )
			);

			if ( is_numeric( $menu_order ) ) {
				$args['menu_order'] = $menu_order;
			}

			$query = new WP_Query( $args );

			if ( ! $query->have_posts() ) {
				if ( ! is_numeric( $prefix ) && is_numeric( $menu_order ) ) {
					$prefix = $menu_order;
				}

				unset( $args['meta_value'], $args['meta_key'] );
				$args['menu_order'] = $prefix;

				$query = new WP_Query( $args );
			}

			if ( $query->have_posts() ) {
				return get_post( $query->posts[0] );
			}

			return null;
		}

		public function get_episodes( $parent_id, $args = array() ) {
			$defaults = array(
				'post_type'      => $this->episode_post_type,
				'post_parent'    => $parent_id,
				'post_status'    => 'publish',
				'orderby'        => $this->order_episode_by,
				'order'          => 'asc',
				'posts_per_page' => - 1
			);

			$args = wp_parse_args( $args, $defaults );

			$args = apply_filters( 'hocwp_theme_extension_anime_episodes_args', $args );

			return new WP_Query( $args );
		}

		public function post_type_link( $post_link, $post ) {
			if ( $this->episode_post_type == $post->post_type ) {
				remove_filter( 'post_type_link', array( $this, 'post_type_link' ), 10 );
				$post_link = $this->get_episode_permalink( $post );
				add_filter( 'post_type_link', array( $this, 'post_type_link' ), 10, 2 );
			}

			return $post_link;
		}

		public function sanitize_episode_slug( $prefix ) {
			$prefix = str_replace( '.', '-', $prefix );
			$prefix = str_replace( '+', '-', $prefix );
			$prefix = str_replace( ',', '-', $prefix );
			$prefix = sanitize_title( $prefix );

			return $prefix;
		}

		public function get_episode_permalink( $post ) {
			if ( HT()->is_positive_number( $post ) ) {
				$post = get_post( $post );
			}

			if ( ! ( $post instanceof WP_Post ) ) {
				return '';
			}

			if ( $this->is_episode( $post ) ) {
				$url = get_permalink( $post->post_parent );
				$url = trailingslashit( $url );
				$url .= 'episode/';

				$prefix = $this->get_episode_prefix( $post->ID, false );
				$prefix = $this->sanitize_episode_slug( $prefix );
				$url .= $prefix;

				$url = trailingslashit( $url );
				$url = add_query_arg( 'id', $post->ID, $url );
			} else {
				$url = get_permalink( $post );
			}

			return apply_filters( 'hocwp_theme_extension_anime_episode_permalink', $url, $post );
		}

		public function page_row_actions( $actions, $post ) {
			$post_type_object = get_post_type_object( $post->post_type );

			if ( 'publish' == $post->post_status ) {
				if ( $this->episode_post_type == $post->post_type && HT()->is_positive_number( $post->post_parent ) ) {
					if ( ! $post_type_object->public && ! $post_type_object->publicly_queryable ) {
						$actions['view'] = sprintf(
							'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
							$this->get_episode_permalink( $post ),
							esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'sb-core' ), get_the_title( $post ) ) ),
							__( 'View', 'sb-core' )
						);
					}
				} elseif ( 'post' == $post->post_type ) {
					$actions['add_ep'] = sprintf(
						'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
						admin_url( 'post-new.php?post_type=episode&post_parent=' . $post->ID ),
						esc_attr( __( 'Add New Episode', 'sb-core' ) ),
						__( 'Add New Episode', 'sb-core' )
					);
				}
			}

			return $actions;
		}

		public function post_updated_messages( $messages ) {
			$notices = $this->get_admin_notices();

			if ( HT()->array_has_value( $notices ) ) {
				unset( $messages['post'][6] );
			}

			return $messages;
		}

		public function admin_notices() {
			$notices = $this->get_admin_notices();

			if ( HT()->array_has_value( $notices ) ) {
				foreach ( $notices as $notice ) {
					echo $notice;
				}

				delete_transient( $this->admin_notices_transient_name );
				unset( $_GET['action'], $_GET['message'] );
			}
		}

		public function get_episode_prefix( $post_id, $number = true ) {
			$prefix = get_post_meta( $post_id, 'prefix', true );

			if ( $number ) {
				$prefix = HT()->keep_only_number( $prefix, '.,+-' );
				$prefix = trim( $prefix, '.,+-' );
			}

			if ( '' == $prefix ) {
				$obj    = get_post( $post_id );
				$prefix = $obj->menu_order;
			}

			return $prefix;
		}

		public function the_title( $title, $post_id ) {
			if ( $this->episode_post_type == get_post_type( $post_id ) ) {
				$obj = get_post( $post_id );

				$prefix = $this->get_episode_prefix( $post_id );

				$prefix = sprintf( _x( 'Ep. %s', 'episode prefix', 'sb-core' ), $prefix );

				if ( false == strpos( $title, $prefix ) ) {
					$prefix .= ' - ';

					$prefix .= $obj->post_title;

					$title = $prefix;
				}
			}

			return $title;
		}

		public function admin_enqueue_scripts() {
			if ( HT_Admin()->is_admin_page( array( 'post.php', 'post-new.php' ) ) ) {
				if ( function_exists( 'HT_Enqueue' ) ) {
					HT_Enqueue()->chosen();
				} else {
					HT_Util()->enqueue_chosen();
				}
			}
		}

		public function registered_post_type( $post_type ) {
			if ( $this->episode_parent_post_type() == $post_type ) {
				global $wp_post_types;

				if ( isset( $wp_post_types[ $post_type ] ) ) {
					$wp_post_types[ $post_type ]->hierarchical = 1;
				}
			}
		}

		public function episode_parent_post_type() {
			$post_type = $this->get_post_type();

			return apply_filters( 'hocwp_theme_extension_anime_episode_parent_post_type', $post_type, $this );
		}

		public function page_attributes_dropdown_pages_args( $args, $post ) {
			if ( $this->episode_post_type == $post->post_type ) {
				$parent_id = get_post_meta( $post->ID, 'parent_id', true );

				if ( ! HT()->is_positive_number( $parent_id ) ) {
					$parent_id = isset( $_GET['post_parent'] ) ? $_GET['post_parent'] : '';
				}

				if ( empty( $parent_id ) ) {
					$parent_id = $post->post_parent;
				}

				$post_type = $this->episode_parent_post_type();

				$args['post_type']    = $post_type;
				$args['selected']     = $parent_id;
				$args['exclude_tree'] = array();
			}

			return $args;
		}

		public function get_admin_notices() {
			$notices = get_transient( $this->admin_notices_transient_name );

			if ( ! is_array( $notices ) ) {
				$notices = array();
			}

			return $notices;
		}

		public function generate_episode_uniqueue_key( $episode_id, $menu_order, $prefix ) {
			return md5( $episode_id . '-' . $menu_order . '-', $prefix );
		}

		public function save_post( $post_id ) {
			if ( ( HT_Admin()->can_save_post( $post_id, 'add-post' ) || HT_Admin()->can_save_post( $post_id, 'update-post_' . $post_id ) ) && $this->episode_post_type == get_post_type( $post_id ) ) {
				$obj = get_post( $post_id );

				if ( 'trash' == $obj->post_status ) {
					return;
				}

				$post_type_object = get_post_type_object( $obj->post_type );

				$notice = '';

				$order_key = $obj->menu_order;

				if ( ! HT()->is_positive_number( $obj->post_parent ) ) {
					$args = array(
						'message' => sprintf( __( 'Please set parent for this <strong>%s</strong>.', 'sb-core' ), $post_type_object->labels->singular_name ),
						'type'    => 'error'
					);

					$notice = HT_Admin()->admin_notice( $args );
				} else {
					do_action( 'hocwp_ext_anime_save_episode', $post_id, $obj );
				}

				if ( isset( $_POST['prefix'] ) ) {
					$prefix_slug = $this->sanitize_episode_slug( $_POST['prefix'] );
					update_post_meta( $post_id, 'prefix', $_POST['prefix'] );
					update_post_meta( $post_id, 'prefix_slug', $prefix_slug );

					//update_post_meta( $post_id, 'uniqueue_key', $this->generate_episode_uniqueue_key( $post_id, $obj->menu_order, $prefix_slug ) );

					if ( '' != $_POST['prefix'] ) {
						$order_key = $_POST['prefix'];

						$tmp = HT()->keep_only_number( $order_key, '.,+-' );

						if ( null != $tmp && false != $tmp ) {
							$order_key = $tmp;
						}
					}
				}

				$suffix = isset( $_POST['suffix'] ) ? $_POST['suffix'] : '';
				update_post_meta( $post_id, 'suffix', $suffix );

				update_post_meta( $post_id, 'order_key', $order_key );

				if ( ! empty( $notice ) ) {
					$notices = $this->get_admin_notices();

					$notices[] = $notice;
					set_transient( $this->admin_notices_transient_name, $notices );

					$postarr = array(
						'ID'          => $post_id,
						'post_status' => 'draft'
					);

					remove_action( 'save_post', array( $this, 'save_post' ) );
					wp_update_post( $postarr );
				}
			}
		}

		public function get_taxonomies_args() {
			$args = array(
				'labels'       => array(
					'name'          => _x( 'Release Years', 'taxonomy', 'sb-core' ),
					'singular_name' => _x( 'Release Year', 'taxonomy', 'sb-core' ),
					'menu_name'     => _x( 'Years', 'taxonomy', 'sb-core' )
				),
				'rewrite'      => array(
					'slug' => 'release-year'
				),
				'hierarchical' => true
			);

			$this->taxonomies_args['release_year'] = array(
				'post_type' => $this->episode_parent_post_type(),
				'args'      => $args
			);

			$args = array(
				'labels'            => array(
					'name' => _x( 'Status', 'taxonomy', 'sb-core' )
				),
				'rewrite'           => array(
					'slug' => 'status'
				),
				'show_in_nav_menus' => true
			);

			$this->taxonomies_args['status'] = array(
				'post_type' => $this->episode_parent_post_type(),
				'args'      => $args
			);

			$this->taxonomies_args = apply_filters( 'hocwp_theme_anime_taxonomies', $this->taxonomies_args );

			return $this->taxonomies_args;
		}

		public function get_episode_preview( $episode_id ) {
			return wp_get_post_autosave( $episode_id );
		}

		public function register_post_type_and_taxonomy() {
			add_rewrite_endpoint( $this->episode_post_type, EP_PERMALINK );

			$ep_list = $this->episode_post_type . '-list';

			$ep_list = str_replace( '_', '-', $ep_list );

			add_rewrite_endpoint( $ep_list, EP_PERMALINK );

			$args = array(
				'labels'              => array(
					'name'          => _x( 'Episodes', 'post type', 'sb-core' ),
					'singular_name' => _x( 'Episode', 'post type', 'sb-core' )
				),
				'public'              => false,
				'publicly_queryable'  => true,
				'has_archive'         => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'hierarchical'        => true,
				'supports'            => array( 'title', 'page-attributes', 'thumbnail', 'editor' ),
				'menu_position'       => 5
			);

			$args = HT_Util()->post_type_args( $args );

			register_post_type( $this->episode_post_type, $args );

			foreach ( $this->taxonomies_args as $taxonomy => $data ) {
				$post_type = isset( $data['post_type'] ) ? $data['post_type'] : $this->episode_parent_post_type();

				if ( ! empty( $post_type ) ) {
					$args = isset( $data['args'] ) ? $data['args'] : '';

					$args = HT_Util()->taxonomy_args( $args );

					register_taxonomy( $taxonomy, $post_type, $args );
				}
			}
		}
	}
}

global $hocwp_theme;

if ( ! isset( $hocwp_theme->extensions ) || ! is_array( $hocwp_theme->extensions ) ) {
	$hocwp_theme->extensions = array();
}

$extension = HTE_Anime()->get_instance();

$hocwp_theme->extensions[ $extension->basename ] = $extension;

function HTE_Anime() {
	return HOCWP_EXT_Anime::get_instance();
}