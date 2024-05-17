<?php
/*
 * Name: Anime
 * Description: Create anime site.
 * Requires core: 6.6.2
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

		public $viewing_movie_details = null;

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

			$add = apply_filters( 'hocwp_theme_extension_anime_use_title_prefix', true );

			if ( $add ) {
				add_filter( 'the_title', array( $this, 'the_title' ), 10, 2 );
			}

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

					$tab->add_field_array( new HOCWP_Theme_Admin_Setting_Field( 'servers', __( 'Servers', 'sb-core' ), 'input', array( 'description' => __( 'The server name for movie source. Each server separates by commas.', 'sb-core' ) ), 'string', 'anime' ) );
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

				add_filter( 'manage_' . $this->episode_post_type . '_posts_columns', array(
					$this,
					'manage_episode_custom_columns'
				) );

				add_filter( 'manage_' . $this->get_post_type() . '_posts_columns', array(
					$this,
					'anime_posts_custom_columns'
				) );

				add_filter( 'manage_edit-' . $this->episode_post_type . '_sortable_columns', array(
					$this,
					'manage_episode_custom_sortable_columns'
				) );

				add_action( 'manage_' . $this->episode_post_type . '_posts_custom_column', array(
					$this,
					'manage_episode_posts_custom_column_action'
				), 10, 2 );

				add_action( 'manage_' . $this->get_post_type() . '_posts_custom_column', array(
					$this,
					'manage_anime_posts_custom_column_action'
				), 10, 2 );

				add_action( 'pre_get_posts', array( $this, 'pre_get_posts_action' ) );

				add_action( 'page_attributes_misc_attributes', array(
					$this,
					'page_attributes_misc_attributes_action'
				) );

				add_action( 'load-post.php', array( $this, 'meta_boxes' ) );
				add_action( 'load-post-new.php', array( $this, 'meta_boxes' ) );
			} else {
				add_action( 'wp', array( $this, 'wp_action' ) );
				add_filter( 'get_pagenum_link', array( $this, 'get_pagenum_link_filter' ) );
				add_filter( 'paginate_links', array( $this, 'paginate_links_filter' ) );
				add_filter( 'hocwp_theme_pagination_first_item_url', array( $this, 'pagination_first_item_url' ) );
				add_filter( 'get_edit_post_link', array( $this, 'edit_single_episode_link' ), 10, 2 );
				add_filter( 'document_title_parts', array( $this, 'document_title_parts_filter' ), 99 );
				add_filter( 'wpseo_title', array( $this, 'wpseo_title_filter' ) );
				add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 99 );
				add_filter( 'posts_where', array( $this, 'posts_where' ) );
				add_filter( 'template_include', array( $this, 'template_include' ), 99999 );
				add_filter( 'body_class', array( $this, 'body_classes' ) );
			}

			add_filter( 'post_type_link', array( $this, 'post_type_link' ), 10, 2 );
			add_shortcode( 'hte_anime_list', array( $this, 'shortcode_anime_list' ) );
			add_shortcode( 'hte_anime_search_advanced', array( $this, 'shortcode_anime_search_advanced' ) );
			add_shortcode( 'hte_anime_release_schedule', array( $this, 'shortcode_anime_release_schedule' ) );
		}

		public function shortcode_anime_release_schedule( $atts = array() ) {
			$atts = shortcode_atts( array(
				'type'      => 'all',
				'title'     => '',
				'post_type' => $this->get_post_type()
			), $atts );

			$args = array(
				'post_type'      => $atts['post_type'],
				'posts_per_page' => - 1,
				'post_status'    => array( 'publish', 'future', 'private' ),
				'order'          => 'ASC'
			);

			$timezone_string = get_option( 'timezone_string' );

			if ( ! empty( $timezone_string ) ) {
				date_default_timezone_set( $timezone_string );
			}

			$day_of_week = date( 'N' );

			$days = array(
				1 => array( 'monday', __( 'Monday', 'sb-core' ) ),
				2 => array( 'tuesday', __( 'Tuesday', 'sb-core' ) ),
				3 => array( 'wednesday', __( 'Wednesday', 'sb-core' ) ),
				4 => array( 'thursday', __( 'Thursday', 'sb-core' ) ),
				5 => array( 'friday', __( 'Friday', 'sb-core' ) ),
				6 => array( 'saturday', __( 'Saturday', 'sb-core' ) ),
				7 => array( 'sunday', __( 'Sunday', 'sb-core' ) )
			);

			if ( 'today' == $atts['type'] ) {
				$days = array(
					$day_of_week => $days[ $day_of_week ]
				);
			}

			ob_start();

			echo '<div class="clearfix"></div>';

			foreach ( $days as $day => $data ) {
				$name = $data[0];

				$timestamp = strtotime( $name . ' this week' );

				$args['date_query'] = array(
					array(
						'year'  => date( 'Y', $timestamp ),
						'month' => date( 'm', $timestamp ),
						'day'   => date( 'd', $timestamp )
					)
				);

				$query = new WP_Query( $args );

				if ( $query->have_posts() ) {
					$text = $data[1];

					$class = 'schedule-day-of-week weekday';

					if ( $day == $day_of_week ) {
						if ( 'today' != $atts['type'] ) {
							$text .= ' (' . __( 'Today', 'sb-core' ) . ')';
							$class .= ' current-day schedule-today';
						} else {
							$text = $atts['title'];

							if ( empty( $text ) ) {
								$text = __( 'Today\'s Schedule', 'sb-core' );
							}
						}
					}

					HT()->wrap_text( $text, '<h2 class="' . $class . '">', '</h2>', true );

					while ( $query->have_posts() ) {
						$query->the_post();
						$object = get_post( get_the_ID() );

						if ( $atts['post_type'] == $this->episode_post_type ) {
							if ( 1 > $object->post_parent ) {
								continue;
							}

							$parent = get_post( $object->post_parent );

							if ( ! ( $parent instanceof WP_Post ) && $parent->post_type != $this->get_post_type() ) {
								continue;
							}
							?>
							<p class="schedule-page-item clearfix">
								<a title="<?php _e( 'See all releases for this post', 'sb-core' ); ?>"
								   href="<?php echo get_permalink( $parent ); ?>"><?php echo $parent->post_title; ?></a>
							<span
								class="schedule-time fr"><?php echo date( 'H:i', strtotime( $object->post_date ) ); ?></span>
							</p>
							<?php
						} else {
							?>
							<p class="schedule-page-item clearfix">
								<a title="<?php _e( 'See all releases for this post', 'sb-core' ); ?>"
								   href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							<span
								class="schedule-time fr"
								title="<?php the_date(); ?>"><?php echo date( 'H:i', strtotime( $object->post_date ) ); ?></span>
							</p>
							<?php
						}
					}

					wp_reset_postdata();
				}
			}

			if ( 'today' != $atts['type'] ) {
				$args['date_query'] = array(
					array(
						'column' => 'post_date',
						'after'  => 'sunday this week',
					)
				);

				$query = new WP_Query( $args );

				if ( $query->have_posts() ) {
					$text = __( 'To be scheduled', 'sb-core' );

					$class = 'schedule-day-of-week weekday';

					HT()->wrap_text( $text, '<h2 class="' . $class . '">', '</h2>', true );

					while ( $query->have_posts() ) {
						$query->the_post();
						$object = get_post( get_the_ID() );

						if ( 1 > $object->post_parent ) {
							continue;
						}

						$parent = get_post( $object->post_parent );

						if ( ! ( $parent instanceof WP_Post ) && $parent->post_type != $this->get_post_type() ) {
							continue;
						}
						?>
						<p class="schedule-page-item clearfix">
							<a title="<?php _e( 'See all releases for this post', 'sb-core' ); ?>"
							   href="<?php echo get_permalink( $parent ); ?>"><?php echo $parent->post_title; ?></a>
							<span
								class="schedule-time fr"><?php echo date( HOCWP_Theme()->get_date_format() . ' - H:i A', strtotime( $object->post_date ) ); ?></span>
						</p>
						<?php
					}

					wp_reset_postdata();
				}
			}

			return ob_get_clean();
		}

		public function body_classes( $classes ) {
			if ( $object = $this->is_viewing_movie() ) {
				unset( $classes[ array_search( 'home', $classes ) ] );
				unset( $classes[ array_search( 'blog', $classes ) ] );

				$classes[] = 'view-movie';

				$classes[] = 'view-' . $object->post_name;
				$classes[] = 'single single-' . $object->post_type . ' postid-' . $object->ID;
			}

			return $classes;
		}

		public function template_include( $template ) {
			global $wp_query;

			if ( isset( $wp_query->query[ $this->get_view_movie_endpoint() ] ) ) {
				// Load custom template from theme first.
				$new_template = locate_template( array( 'custom/views/module-view-movie.php' ) );

				if ( '' != $new_template ) {
					return $new_template;
				}

				$new_template = $this->folder_path . '/view-movie.php';

				if ( file_exists( $new_template ) ) {
					return $new_template;
				}
			}

			return $template;
		}

		/**
		 * Filters the WHERE clause of the query.
		 *
		 * @param $where
		 *
		 * @return string
		 */
		public function posts_where( $where ) {
			if ( isset( $_GET['letter'] ) && ! empty( $_GET['letter'] ) && is_page() ) {
				if ( false !== strpos( $where, 'post_type = \'' . $this->get_post_type() . '\'' ) ) {
					global $wpdb;

					if ( '.' == $_GET['letter'] ) {
						$where .= $wpdb->prepare( " AND $wpdb->posts.post_title NOT REGEXP '%s'", '^[[:alpha:]]' );
					} else {
						$where .= $wpdb->prepare( " AND $wpdb->posts.post_title LIKE '%s'", $_GET['letter'] . '%' );
					}
				}
			}

			return $where;
		}

		public function is_page_for_viewing_anime() {
			return ( ( is_single() && ! is_page() ) || is_singular( $this->get_post_type() ) || is_home() );
		}

		public function get_viewing_movie_details() {
			if ( null != $this->viewing_movie_details ) {
				return $this->viewing_movie_details;
			}

			if ( $this->is_page_for_viewing_anime() ) {
				$anime = '';

				$endpoint = get_query_var( $this->get_view_movie_endpoint() );

				if ( is_singular( $this->get_post_type() ) ) {
					$anime = get_post( get_the_ID() );
				} else {
					$anime = get_page_by_path( $endpoint, OBJECT, $this->get_post_type() );
				}

				if ( false !== strpos( $endpoint, '/' ) || false !== strpos( $endpoint, '?' ) ) {
					if ( false !== strpos( $endpoint, '/' ) ) {
						$parts = explode( '/', $endpoint );
					} else {
						$parts = explode( '?', $endpoint );
					}

					foreach ( $parts as $key => $part ) {
						$part = ltrim( $part, '/' );
						$part = untrailingslashit( $part );

						$parts[ $key ] = $part;
					}

					if ( ! $this->is_anime( $anime ) ) {
						$anime = get_page_by_path( $parts[0], OBJECT, $this->get_post_type() );
					}

					if ( $this->is_anime( $anime ) ) {
						$id = isset( $_GET['id'] ) ? $_GET['id'] : '';

						if ( HT()->is_positive_number( $id ) ) {
							$episode = get_post( $id );

							if ( $this->is_episode( $episode ) ) {
								$this->viewing_movie_details = array( $anime, $episode );

								return $this->viewing_movie_details;
							}
						}

						if ( isset( $parts[1] ) ) {
							$key = $parts[1];

							if ( false !== strpos( $key, 'f' ) ) {
								$key = ltrim( $key, 'f' );

								$episode = get_post( $key );

								if ( $this->is_episode( $episode ) ) {
									$this->viewing_movie_details = array( $anime, $episode );

									return $this->viewing_movie_details;
								}
							}
						}

						$this->viewing_movie_details = array( $anime );

						return $this->viewing_movie_details;
					}
				} else {
					$id = isset( $_GET['id'] ) ? $_GET['id'] : '';

					if ( HT()->is_positive_number( $id ) ) {
						$episode = get_post( $id );

						if ( $this->is_episode( $episode ) ) {
							if ( ! $this->is_anime( $anime ) && HT()->is_positive_number( $episode->post_parent ) ) {
								$obj = get_post( $episode->post_parent );

								if ( $this->is_anime( $obj ) ) {
									$anime = $obj;
								}
							}

							$this->viewing_movie_details = array( $anime, $episode );

							return $this->viewing_movie_details;
						}
					}

					if ( $this->is_anime( $anime ) ) {
						$this->viewing_movie_details = array( $anime );

						return $this->viewing_movie_details;
					}
				}
			}

			return null;
		}

		/**
		 * Check if is view movie page.
		 *
		 * @param null|WP_Query $query
		 *
		 * @return bool|WP_Post
		 */
		public function is_viewing_movie( $query = null ) {
			if ( ! ( $query instanceof WP_Query ) ) {
				$query = $GLOBALS['wp_query'];
			}

			if ( $this->is_page_for_viewing_anime() ) {
				if ( isset( $query->query[ $this->get_view_movie_endpoint() ] ) ) {
					$anime = $this->get_current_viewing_anime();

					if ( $this->is_anime( $anime ) ) {
						return $anime;
					}
				}
			}

			return false;
		}

		public function get_current_viewing_anime() {
			$details = $this->get_viewing_movie_details();

			if ( $this->is_anime( $details ) ) {
				return $details;
			}

			if ( HT()->array_has_value( $details ) ) {
				$anime = $details[0];

				if ( $this->is_anime( $anime ) ) {
					return $anime;
				}
			}

			return null;
		}

		public function pre_get_posts( $query ) {
			if ( $query instanceof WP_Query ) {
				if ( $query->is_main_query() ) {
					if ( is_page() ) {
						unset( $query->query['s'], $query->query_vars['s'] );
					} elseif ( is_home() ) {
						if ( isset( $query->query[ $this->get_view_movie_endpoint() ] ) ) {
							$anime = $this->get_current_viewing_anime();

							if ( $this->is_anime( $anime ) ) {
								$query->set( 'name', $anime->post_name );
								$query->set( $anime->post_type, $anime->post_name );
								$query->set( 'post_type', $anime->post_type );

								$query->queried_object    = $anime;
								$query->queried_object_id = $anime->ID;
								$query->is_singular       = true;
							}
						}
					}
				} else {
					$letter = isset( $_GET['letter'] ) ? $_GET['letter'] : '';

					if ( ! empty( $letter ) ) {
						$query->set( 'orderby', 'title' );
						$query->set( 'order', 'ASC' );
					}

					$sort = isset( $_GET['sort'] ) ? $_GET['sort'] : '';

					switch ( $sort ) {
						case 'title_asc':
							$query->set( 'orderby', 'title' );
							$query->set( 'order', 'ASC' );
							break;
						case 'title_desc':
							$query->set( 'orderby', 'title' );
							$query->set( 'order', 'DESC' );
							break;
						case 'date_asc':
							$query->set( 'orderby', 'date' );
							$query->set( 'order', 'ASC' );
							break;
						case 'date_desc':
							$query->set( 'orderby', 'date' );
							$query->set( 'order', 'DESC' );
							break;
					}
				}
			}
		}

		public function shortcode_anime_search_advanced( $atts = array() ) {
			$atts = shortcode_atts( array(
				'posts_per_page' => HT_Util()->get_posts_per_page()
			), $atts );

			$s = isset( $_GET['s'] ) ? $_GET['s'] : '';

			$args = array(
				'post_type'       => $this->get_post_type(),
				'posts_per_page'  => $atts['posts_per_page'],
				'paged'           => HT_Util()->get_paged(),
				'advanced_search' => true
			);

			if ( ! empty( $s ) ) {
				$args['s'] = $s;
			}

			$taxs = get_object_taxonomies( $this->get_post_type(), OBJECT );

			$tax_query = array();

			foreach ( $taxs as $taxonomy ) {
				$key = 'a_' . $taxonomy->name;

				if ( isset( $_GET[ $key ] ) && HT()->is_positive_number( $_GET[ $key ] ) ) {
					$tax_query[] = array(
						'taxonomy' => $taxonomy->name,
						'field'    => 'term_id',
						'terms'    => array( $_GET[ $key ] )
					);
				}
			}

			if ( HT()->array_has_value( $tax_query ) ) {
				$tax_query['relation'] = 'AND';
				$args['tax_query']     = $tax_query;
			}

			$query = new WP_Query( $args );

			$html = apply_filters( 'hocwp_theme_extension_anime_search_advanced_html', '', $query, $atts );

			if ( ! empty( $html ) ) {
				return $html;
			}

			$letter = isset( $_GET['letter'] ) ? $_GET['letter'] : '';

			$url = get_the_permalink();
			$url = trailingslashit( $url );

			$class = '';

			ob_start();
			?>
			<form class="advanced-search-form" method="get">
				<?php
				if ( is_page() ) {
					if ( ! empty( $letter ) ) {
						?>
						<input type="hidden" name="letter" value="<?php echo $letter; ?>">
						<?php
					}
					?>
					<div class="form-group">
						<ul class="list-inline list-unstyled list-chars list-letters">
							<?php
							if ( '.' == $letter ) {
								$class = 'current';
							}
							?>
							<li class="labels">
								<span><?php _e( 'Posts title starts with:', 'sb-core' ); ?></span>
							</li>
							<li class="<?php echo $class; ?>">
								<a href="<?php echo $url; ?>?letter=.">#</a>
							</li>
							<?php
							foreach ( range( 'A', 'Z' ) as $char ) {
								$class = '';

								if ( $char == $letter ) {
									$class = 'current';
								}
								?>
								<li class="<?php echo $class; ?>">
									<a href="<?php echo $url; ?>?letter=<?php echo $char; ?>"><?php echo $char; ?></a>
								</li>
								<?php
							}
							?>
						</ul>
					</div>
					<?php
				}
				?>
				<div class="form-group">
					<label for="search-keywords"><?php _e( 'Keywords:', 'sb-core' ); ?></label>

					<div class="row medium">
						<div class="col-md-9">
							<input id="search-keywords" type="search" name="s" class="form-control"
							       value="<?php echo esc_attr( $s ); ?>">
						</div>
						<div class="col-md-3">
							<div class="row medium">
								<div class="col-md-6">
									<button type="submit" class="w-full"><?php _e( 'Search', 'sb-core' ); ?></button>
								</div>
								<div class="col-md-6">
									<button type="reset"
									        class="btn btn-success w-full"
									        onclick="return window.location.href=window.location.href.replace(window.location.search,'')"><?php _ex( 'Reset', 'form reset', 'sb-core' ); ?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="form-group">
					<h3><?php _e( 'Search options', 'sb-core' ); ?></h3>
				</div>
				<div class="form-group">
					<?php
					if ( HT()->array_has_value( $taxs ) ) {
						$count  = count( $taxs );
						$column = 12 / ( $count + 1 );
						$column = intval( $column );

						if ( 1 > $column ) {
							$column = 12;
						}

						$count = 0;
						?>
						<div class="row medium">
							<?php
							foreach ( $taxs as $taxonomy ) {
								?>
								<div class="col-md-<?php echo $column; ?> <?php echo $taxonomy->name; ?>">
									<label
										for="<?php echo $taxonomy->name; ?>"><?php echo $taxonomy->labels->singular_name; ?></label>
									<?php
									$name = 'a_' . $taxonomy->name;

									$args = array(
										'taxonomy'        => $taxonomy->name,
										'hide_empty'      => false,
										'orderby'         => 'name',
										'id'              => $taxonomy->name,
										'name'            => $name,
										'class'           => 'form-control',
										'selected'        => isset( $_GET[ $name ] ) ? $_GET[ $name ] : 0,
										'show_option_all' => sprintf( __( 'All %s', 'sb-core' ), $taxonomy->labels->singular_name )
									);

									wp_dropdown_categories( $args );
									?>
								</div>
								<?php
								$count += $column;
							}

							$column = ( 12 - $count );

							if ( 1 > $column ) {
								$column = 12;
							}

							$sorts = array(
								''           => __( 'Default sorting', 'sb-core' ),
								'title_asc'  => __( 'Sort by title: A to Z', 'sb-core' ),
								'title_desc' => __( 'Sort by title: Z to A', 'sb-core' ),
								'date_asc'   => __( 'Sort by date: old to new', 'sb-core' ),
								'date_desc'  => __( 'Sort by date: new to old', 'sb-core' )
							);
							?>
							<div class="col-md-<?php echo $column; ?> sort">
								<label for="sort"><?php _e( 'Sort', 'sb-core' ); ?></label>
								<select id="sort" name="sort" class="form-control">
									<?php
									$current_sort = isset( $_GET['sort'] ) ? $_GET['sort'] : '';

									foreach ( $sorts as $key => $sort ) {
										?>
										<option
											value="<?php echo $key; ?>"<?php selected( $key, $current_sort ); ?>><?php echo $sort; ?></option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</form>
			<?php

			$html = apply_filters( 'hocwp_theme_extension_anime_search_advanced_loop_html', '', $query, $atts );

			if ( ! empty( $html ) ) {
				echo $html;
			} else {
				do_action( 'hocwp_theme_loop', $query );
			}

			return ob_get_clean();
		}

		public function shortcode_anime_list( $atts = array() ) {
			$atts = shortcode_atts( array(
				'posts_per_page' => HT_Util()->get_posts_per_page()
			), $atts );

			$args = array(
				'post_type'      => $this->get_post_type(),
				'posts_per_page' => $atts['posts_per_page'],
				'paged'          => HT_Util()->get_paged()
			);

			$query = new WP_Query( $args );

			$html = apply_filters( 'hocwp_theme_extension_anime_list_html', '', $query, $atts );

			if ( ! empty( $html ) ) {
				return $html;
			}

			ob_start();
			do_action( 'hocwp_theme_loop', $query );

			return ob_get_clean();
		}

		public function build_server_meta_key( $server ) {
			$name = sanitize_title( $server );
			$name = str_replace( '-', '_', $name );

			return 'server_' . $name;
		}

		public function meta_boxes( $post ) {
			$post_id = HT_Admin()->get_current_post_id( $post );

			$meta = new HOCWP_Theme_Meta_Post();
			$meta->add_post_type( $this->get_post_type() );
			$meta->form_table = true;

			$field = new HOCWP_Theme_Meta_Field( 'different_name', __( 'Different Name', 'sb-core' ) );
			$meta->add_field( $field );

			$field = new HOCWP_Theme_Meta_Field( 'release_date', __( 'Release Date', 'sb-core' ) );
			$meta->add_field( $field );

			$field = new HOCWP_Theme_Meta_Field( 'episode_number', __( 'Episodes Number', 'sb-core' ), 'input', array( 'type' => 'number' ) );
			$meta->add_field( $field );

			do_action_ref_array( 'hocwp_theme_extension_anime_additional_meta', array( &$meta ) );

			$meta = new HOCWP_Theme_Meta_Post();
			$meta->add_post_type( $this->get_post_type() );

			$meta->set_id( 'list-episodes-box' );
			$meta->set_title( __( 'List Episodes', 'sb-core' ) );

			$meta->set_callback( array( $this, 'list_episode_meta_box' ) );

			$servers = $this->get_movie_servers( $post_id );

			if ( HT()->array_has_value( $servers ) ) {
				$meta = new HOCWP_Theme_Meta_Post();
				$meta->add_post_type( $this->episode_post_type );

				$meta->set_id( 'movie-custom-sources-box' );
				$meta->set_title( __( 'Movie Custom Sources Name', 'sb-core' ) );

				$meta->set_priority( 'high' );

				$meta->add_field( new HOCWP_Theme_Meta_Field( 'anime_custom_servers', __( 'Custom servers:', 'sb-core' ), 'input', array( 'description' => __( 'The custom server name for movie source. Each server separates by commas.', 'sb-core' ) ) ) );

				$meta = new HOCWP_Theme_Meta_Post();
				$meta->add_post_type( $this->episode_post_type );

				$meta->set_id( 'movie-servers-box' );
				$meta->set_title( __( 'Movie Sources', 'sb-core' ) );

				$meta->form_table = true;

				$meta->set_priority( 'high' );

				foreach ( $servers as $server ) {
					$meta->add_field( new HOCWP_Theme_Meta_Field( $this->build_server_meta_key( $server ), $server ) );
				}
			}
		}

		public function get_movie_servers( $post_id = null ) {
			$servers = $this->get_option( 'servers' );
			$servers = explode( ',', $servers );

			if ( HT()->is_positive_number( $post_id ) ) {
				$custom = get_post_meta( $post_id, 'anime_custom_servers', true );

				if ( ! empty( $custom ) ) {
					$custom  = explode( ',', $custom );
					$servers = array_merge( $servers, $custom );
				}
			}

			$servers = array_map( 'trim', $servers );
			$servers = array_unique( $servers );
			$servers = array_filter( $servers );

			return $servers;
		}

		public function list_episode_meta_box( $post ) {
			$post_id = isset( $_GET['post'] ) ? $_GET['post'] : '';

			if ( $post instanceof WP_Post ) {
				$post_id = $post->ID;
			}

			if ( ! HT()->is_positive_number( $post_id ) ) {
				echo wpautop( __( 'No episodes found!', 'sb-core' ) );

				return;
			}

			$args = array(
				'post_parent'    => $post_id,
				'post_type'      => $this->episode_post_type,
				'posts_per_page' => - 1,
				'order'          => 'asc'
			);

			$query = new WP_Query( $args );

			$add_new = admin_url( 'post-new.php?post_type=episode' );
			$add_new = add_query_arg( 'post_parent', $post_id, $add_new );

			if ( $query->have_posts() ) {
				?>
				<div class="list-episodes">
					<?php
					while ( $query->have_posts() ) {
						$query->the_post();
						?>
						<a class="button"
						   href="<?php echo get_edit_post_link( get_the_ID() ); ?>"
						   title="<?php the_title(); ?>"><?php the_title(); ?></a>
						<?php
					}

					wp_reset_postdata();
					?>
					<a href="<?php echo $add_new; ?>"
					   class="button button-primary"><?php _e( 'Add new episode', 'sb-core' ); ?></a>
				</div>
				<?php
			} else {
				echo wpautop( __( 'No episodes found!', 'sb-core' ) );
				?>
				<a href="<?php echo $add_new; ?>"
				   class="button button-primary"><?php _e( 'Add new episode', 'sb-core' ); ?></a>
				<?php
			}
		}

		public function get_post_type() {
			$pt = $this->get_option( 'post_type' );

			if ( empty( $pt ) ) {
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
			if ( is_singular( $this->get_post_type() ) ) {
				$episode = $this->get_current_episode();

				if ( $this->is_episode( $episode ) ) {
					$title = isset( $parts['title'] ) ? $parts['title'] : '';

					$title = $this->build_episode_document_title( $title, $episode );

					$parts['title'] = $title;

					unset( $title );
				}

				unset( $episode );
			}

			if ( $object = $this->is_viewing_movie() ) {
				$parts['title'] = $object->post_title;
				unset( $parts['tagline'] );
			}

			return $parts;
		}

		public function edit_single_episode_link( $link, $post_id ) {
			if ( is_single( $post_id ) || $this->is_viewing_movie() ) {
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

		public function is_anime( $post ) {
			return ( $post instanceof WP_Post && $this->get_post_type() == $post->post_type );
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

			if ( $post->post_type != $this->get_post_type() && $this->episode_post_type != $post->post_type ) {
				return;
			}

			$prefix = get_post_meta( $post->ID, 'prefix', true );
			$suffix = get_post_meta( $post->ID, 'suffix', true );
			$anime  = get_post( $post->post_parent );

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
			$columns = HT()->insert_to_array( $columns, __( 'Thumbnail', 'sb-core' ), 'after_head', 'thumbnail' );

			//$columns = HT()->insert_to_array( $columns, __( 'Parent Thumbnail', 'sb-core' ), 'after_head', 'parent_thumbnail' );

			return $columns;
		}

		public function anime_posts_custom_columns( $columns ) {
			$columns = HT()->insert_to_array( $columns, __( 'Thumbnail', 'sb-core' ), 'after_head', 'thumbnail' );

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
			} elseif ( 'parent_thumbnail' == $column ) {
				$obj = get_post( $post_id );

				if ( HT()->is_positive_number( $obj->post_parent ) ) {
					$parent = get_post( $obj->post_parent );

					if ( $this->is_anime( $parent ) ) {
						echo get_the_post_thumbnail( $parent->ID, array( 300, 200 ) );
					}
				}
			} elseif ( 'thumbnail' == $column ) {
				echo get_the_post_thumbnail( $post_id, array( 300, 200 ) );
			}
		}

		public function manage_anime_posts_custom_column_action( $column, $post_id ) {
			if ( 'thumbnail' == $column ) {
				echo get_the_post_thumbnail( $post_id, array( 300, 200 ) );
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

		public function get_current_episode( $get_first = false ) {
			if ( $this->is_viewing_movie() ) {
				$details = $this->get_viewing_movie_details();

				if ( HT()->array_has_value( $details ) && isset( $details[1] ) && $this->is_episode( $details[1] ) ) {
					return $details[1];
				}

				if ( $get_first ) {
					$anime = $this->get_current_viewing_anime();

					if ( $this->is_anime( $anime ) ) {
						$query = $this->get_episodes( $anime->ID );

						if ( $query->have_posts() ) {
							return $query->post;
						}
					}
				}
			}

			return null;
		}

		public function wp_action() {
			if ( ( is_single() && ! is_page() ) || is_singular( $this->get_post_type() ) ) {
				global $wp_query;

				if ( isset( $wp_query->query_vars[ $this->get_view_movie_endpoint() ] ) ) {
					$episode = $this->get_current_episode( true );

					if ( ! $this->is_episode( $episode ) && is_singular( $this->get_post_type() ) ) {
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
			} elseif ( is_home() ) {
				global $wp_query;

				if ( isset( $wp_query->query[ $this->get_view_movie_endpoint() ] ) && ! $this->is_viewing_movie() ) {
					$episode = $this->get_current_episode( true );

					if ( ! $this->is_episode( $episode ) ) {
						wp_redirect( home_url() );
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
				$url .= $this->get_view_movie_endpoint() . '/';

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
			if ( function_exists( 'HT_Admin' ) && method_exists( HT_Admin(), 'skip_admin_notices' ) && HT_Admin()->skip_admin_notices() ) {
				return;
			}

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

			if ( HT_Admin()->is_admin_page( array( 'post.php', 'post-new.php', 'edit.php' ) ) ) {
				wp_enqueue_style( 'hte-anime-style', HOCWP_EXT_URL . '/css/admin-anime.css' );
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
				'labels'       => array(
					'name'          => _x( 'Types', 'taxonomy', 'sb-core' ),
					'singular_name' => _x( 'Type', 'taxonomy', 'sb-core' ),
					'menu_name'     => _x( 'Types', 'taxonomy', 'sb-core' )
				),
				'rewrite'      => array(
					'slug' => 'type'
				),
				'hierarchical' => true
			);

			$this->taxonomies_args['type'] = array(
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

		public function get_view_movie_endpoint() {
			$endpoint = apply_filters( 'hocwp_theme_extension_anime_view_movie_endpoint', $this->episode_post_type );
			$endpoint = untrailingslashit( $endpoint );
			$endpoint = ltrim( $endpoint, '/' );

			return $endpoint;
		}

		public function get_view_movie_url( $post_id = null, $ep_id = null ) {
			$object = HT_Util()->return_post( $post_id );

			$url = home_url( $this->get_view_movie_endpoint() );
			$url = trailingslashit( $url ) . $object->post_name;

			if ( HT()->is_positive_number( $ep_id ) ) {
				$url = trailingslashit( $url ) . $ep_id;
			}

			return trailingslashit( $url );
		}

		public function register_post_type_and_taxonomy() {
			add_rewrite_endpoint( $this->get_view_movie_endpoint(), EP_ALL );

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