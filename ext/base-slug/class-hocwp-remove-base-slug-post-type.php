<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Remove_Base_Slug_Post_Type extends HOCWP_Remove_Base_Slug {

	public function __construct( $post_type ) {
		$this->set_name( $post_type );
		parent::__construct();
	}

	public function get_object() {
		$this->object = get_post_type_object( $this->name );
	}

	public function rewrite_rules_filter() {
		$rules = array();

		$args  = array(
			'posts_per_page' => - 1,
			'post_type'      => $this->name,
			'tax_query'      => array(
				array(
					'key'     => 'base_removed',
					'value'   => 1,
					'compare' => '!=',
					'type'    => 'numeric'
				)
			)
		);

		$query = new WP_Query( $args );
		$lists = $query->posts;

		foreach ( $lists as $obj ) {
			$rules[ '(' . $obj->post_name . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$' ] = 'index.php?' . $this->query_var . '=$matches[1]&feed=$matches[2]';

			$rules[ '(' . $obj->post_name . ')/page/?([0-9]{1,})/?$' ] = 'index.php?' . $this->query_var . '=$matches[1]&paged=$matches[2]';

			$rules[ '(' . $obj->post_name . ')/?$' ] = 'index.php?' . $this->query_var . '=$matches[1]';
			update_term_meta( $obj->ID, 'base_removed', 1 );
		}

		$slug = trim( $this->rewrite_slug, '/' );

		$rules[ $slug . '/(.*)$' ] = 'index.php?' . $this->query_var_redirect . '=$matches[1]';

		return $rules;
	}

	public function post_type_link( $post_link, $post, $leavename ) {
		if ( $post->post_type == $this->name ) {

			if ( ! empty( $this->rewrite_slug ) ) {
				$post_link = str_replace( '/' . $this->rewrite_slug . '/', '/', $post_link );
			}

		}

		return $post_link;
	}

	public function init() {
		$this->core_init();
		add_filter( $this->name . '_rewrite_rules', array( $this, 'rewrite_rules_filter' ) );
		add_filter( 'post_type_link', array( $this, 'post_type_link' ), 10, 3 );
		add_action( 'save_post_' . $this->name, array( $this, 'flush_rewrite_rules' ) );
	}
}