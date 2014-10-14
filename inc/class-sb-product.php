<?php
class SB_Product {
    public static function get_category_thumbnail_url($cat) {
        $thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
        return wp_get_attachment_url( $thumbnail_id );
    }

    public static function get_categories($args = array()) {
        return get_terms('product_cat', $args);
    }
}