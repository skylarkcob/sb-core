<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Remove_Base_Slug_Taxonomy extends HOCWP_Remove_Base_Slug {

	public function __construct( $taxonomy ) {
		$this->set_name( $taxonomy );
		parent::__construct();
	}

	public function get_object() {
		if ( taxonomy_exists( $this->name ) ) {
			$this->object = get_taxonomy( $this->name );
		}
	}

	public function rewrite_rules_filter() {
		$rules = array();

		$args  = array(
			'hide_empty' => false,
			'taxonomy'   => $this->name,
			'tax_query'  => array(
				array(
					'key'     => 'base_removed',
					'value'   => 1,
					'compare' => '!=',
					'type'    => 'numeric'
				)
			)
		);

		$query = new WP_Term_Query( $args );
		$terms = $query->get_terms();

		foreach ( $terms as $term ) {
			$rules[ '(' . $term->slug . ')/(?:feed/)?(feed|rdf|rss|rss2|atom)/?$' ] = 'index.php?' . $this->query_var . '=$matches[1]&feed=$matches[2]';

			$rules[ '(' . $term->slug . ')/page/?([0-9]{1,})/?$' ] = 'index.php?' . $this->query_var . '=$matches[1]&paged=$matches[2]';

			$rules[ '(' . $term->slug . ')/?$' ] = 'index.php?' . $this->query_var . '=$matches[1]';
			update_term_meta( $term->term_id, 'base_removed', 1 );
		}

		$slug = trim( $this->rewrite_slug, '/' );

		$rules[ $slug . '/(.*)$' ] = 'index.php?' . $this->query_var_redirect . '=$matches[1]';

		return $rules;
	}

	public function term_link_filter( $termlink, $term, $taxonomy ) {
		if ( $taxonomy == $this->name ) {

			if ( ! empty( $this->rewrite_slug ) ) {
				$termlink = str_replace( '/' . $this->rewrite_slug . '/', '/', $termlink );
			}

		}

		return $termlink;
	}

	public function init() {
		$this->core_init();
		add_filter( $this->name . '_rewrite_rules', array( $this, 'rewrite_rules_filter' ) );
		add_filter( 'term_link', array( $this, 'term_link_filter' ), 10, 3 );
		add_action( 'created_' . $this->name, array( $this, 'flush_rewrite_rules' ) );
		add_action( 'edited_' . $this->name, array( $this, 'flush_rewrite_rules' ) );
		add_action( 'delete_' . $this->name, array( $this, 'flush_rewrite_rules' ) );
	}
}