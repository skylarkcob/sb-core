<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class HOCWP_Theme_Streaming_Streamcherry extends HOCWP_Theme_Streaming_Streamango {
	public $pattern = '/^https?:\/\/streamcherry.com\/(f|embed)\/?(.*?)\/(.*)(.mp4)?/';
}