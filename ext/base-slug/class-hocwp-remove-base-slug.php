<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class HOCWP_Remove_Base_Slug {
	public $name;
	public $object;
	public $rewrite_slug;
	public $query_var;
	public $query_var_redirect;

	public function __construct() {
		$this->get_object();
		$this->query_var = $this->get_query_var();

		$this->query_var_redirect = $this->query_var . '_redirect';

		$this->rewrite_slug = $this->get_rewrite_slug();
	}

	public function set_name( $name ) {
		$this->name = $name;
	}

	public function get_rewrite_slug() {
		$slug = '';

		if ( is_object( $this->object ) ) {
			$slug = $this->object->rewrite['slug'] ?? '';
		}

		return $slug;
	}

	public function get_query_var() {
		$query_var = '';

		if ( is_object( $this->object ) ) {
			$query_var = $this->object->query_var;
		}

		return $query_var;
	}

	public function add_permastruct( $name, $struct, $args = array() ) {
		global $wp_rewrite;
		$wp_rewrite->add_permastruct( $name, $struct, $args );
	}

	public function init_action() {
		$this->add_permastruct( $this->name, '%' . $this->name . '%' );
	}

	public function query_vars_filter( $query_vars ) {
		$query_vars[] = $this->query_var_redirect;

		return $query_vars;
	}

	public function request_filter( $query_vars ) {
		if ( isset( $query_vars[ $this->query_var_redirect ] ) ) {
			$term_name = user_trailingslashit( $query_vars[ $this->query_var_redirect ], $this->name );

			$term_permalink = home_url( $term_name );
			wp_redirect( $term_permalink, 301 );
			exit;
		}

		return $query_vars;
	}

	public function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

	public function core_init() {
		add_action( 'init', array( $this, 'init_action' ) );
		add_filter( 'query_vars', array( $this, 'query_vars_filter' ) );
		add_filter( 'request', array( $this, 'request_filter' ) );
	}

	abstract function get_object();

	abstract function rewrite_rules_filter();

	abstract function init();
}