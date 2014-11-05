<?php
class SB_Product {
    public static function get_category_thumbnail_url($cat) {
        $thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
        return wp_get_attachment_url( $thumbnail_id );
    }

    public static function get_categories($args = array()) {
        return get_terms('product_cat', $args);
    }

    private $product;

    public function __construct($id = null) {
        if($id && class_exists('WC_Product')) {
            $this->product = new WC_Product($id);
        }
        return $this->product;
    }

    public function get_sku() {
        return $this->product->get_sku();
    }

    public function get_add_to_cart_button($args = array()) {
        $defaults = array('id' => $this->product->id, 'sku' => $this->get_sku());
        extract( $defaults, EXTR_SKIP );
        if(empty($id) || !is_numeric($id)) {
            $id = get_the_ID();
        }
        if(!empty($id) && is_numeric($id)) {
            $price = $this->get_price(array('id' => $id));
            $style = '';
            if($price == 0) {
                $style = ' pointer-events: none; cursor: default;';
                $style = trim($style);
            }
            $class = '';
            if(0 == $this->get_price()) {
                $class = ' please-call';
            }
            return '<div class="custom-add-to-cart'.$class.'">'.do_shortcode('[add_to_cart id="'.$id.'" sku="'.$sku.'" style="'.$style.'"]').'</div>';
        }
        return '';
    }

    public function the_add_to_cart_button($args = array()) {
        echo $this->get_add_to_cart_button($args);
    }

    public static function get_checkout_uri() {
        global $woocommerce;
        return $woocommerce->cart->get_checkout_url();
    }

    public static function the_checkout_uri() {
        echo SB_Product::get_checkout_uri();
    }

    public static function get_cart_uri() {
        global $woocommerce;
        return $woocommerce->cart->get_cart_url();
    }

    public static function get_cart() {
        return '<a class="cart-content" href="'.self::get_cart_uri().'" title="Thông tin giỏ hàng"><span class="product-number">'.sprintf(_n('%d sản phẩm', '%d sản phẩm', self::count_cart(), 'sbwp'), self::count_cart()).'</span><span class="sep"> - </span>'.self::cart_total().'</a>';
    }

    public static function the_cart() {
        echo '<div class="cart-group">';
        do_action('sbwp_cart_before');
        echo self::get_cart();
        do_action('sbwp_cart_after');
        echo '</div>';
    }

    public static function count_cart() {
        global $woocommerce;
        return $woocommerce->cart->cart_contents_count;
    }

    public static function cart_total() {
        global $woocommerce;
        return $woocommerce->cart->get_cart_total();
    }

    public function get_price($type = "price") {
        $product = $this->product;
        $price = 0;
        switch($type) {
            case 'sale':
                $price = $product->get_sale_price();
                break;
            case 'regular':
                $price = $product->get_regular_price();
                break;
            default:
                $price = $product->price;
        }
        if(!is_numeric($price)) {
            $price = 0;
        }
        return $price;
    }

    public function price() {
        $price = $this->get_price();
        if(0 == $price) {
            echo '<span class="price"><span class="no-price call">Tạm hết hàng</span></span>';
        }
        else {
            $sale = $this->get_price('sale');
            if($sale > 0) {
                $price = $this->get_price('regular');
                $sale = '<ins><span class="amount">'.number_format($sale).' đ</span></ins>';
                $price = '<del><span class="amount">'.number_format($price).' đ</span></del>';
                $price = $price.' '.$sale;
            }
            else {
                $price = $this->get_price();
                $price = '<ins><span class="amount">'.number_format($price).' đ</span></ins>';
            }
            echo '<span class="price">'.trim($price).'</span>';
        }
    }

    public function is_sale() {
        $price = $this->get_price('sale');
        if($price > 0) {
            $regular = $this->get_price('regular');
            if($price < $regular) {
                return true;
            }
        }
        return false;
    }

    public function sale_percentage() {
        echo '-'.SB_PHP::percentage($this->get_price('sale'), $this->get_price('regular'), 2).'%';
    }

    public static function count_product() {
        $products = new WP_Query(array("post_type" => "product", "posts_per_page" => -1));
        return $products->post_count;
    }

    public static function get($args = array()) {
        return self::get_product($args);
    }

    public static function get_product($args = array()) {
        $defaults = array('posts_per_page' => 8, 'offset' => 0, 'type' => 'recent', 'params' => array());
        $args = wp_parse_args($args, $defaults);
        extract($args, EXTR_SKIP);
        $defaults = array(
            'post_type'			=> 'product',
            'posts_per_page'	=> $posts_per_page,
            'offset'			=> $offset
        );
        switch($type) {
            case 'most_view':
                $args = array(
                    'meta_key'	=> 'views',
                    'order_by'	=> 'meta_value'
                );
                break;
            case 'best_seller':
                $args = array(
                    'meta_key'			=> 'total_sales',
                    'orderby'			=> 'meta_value'
                );
                break;
            case 'sale':
                $args = array(
                    'meta_query'		=> array(
                        'relation' => 'OR',
                        array(
                            'key'           => '_sale_price',
                            'value'         => 0,
                            'compare'       => '>',
                            'type'          => 'numeric'
                        ),
                        array(
                            'key'           => '_min_variation_sale_price',
                            'value'         => 0,
                            'compare'       => '>',
                            'type'          => 'numeric'
                        )
                    )
                );
                break;
            case 'sale_slide':
                $args = array(
                    'meta_query'		=> array(
                        'relation' => 'AND',
                        array(
                            'key'           => '_sale_price',
                            'value'         => 0,
                            'compare'       => '>',
                            'type'          => 'numeric'
                        ),
                        array(
                            'key'           => 'wpcf-product-slide',
                            'value'         => 1,
                            'compare'       => '=',
                            'type'          => 'numeric'
                        )
                    )
                );
                break;
            case 'most_comment':
                $args = array(
                    'orderby'			=> 'comment_count'
                );
                break;
            default:
                $args = array();
        }
        $args = array_merge($defaults, $args);
        if(count($params) > 0) {
            $args = array_merge($params, $args);
        }
        return new WP_Query($args);
    }

    public static function get_account_uri() {
        $myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
        if ( $myaccount_page_id ) {
            $myaccount_page_url = get_permalink( $myaccount_page_id );
        }
        return $myaccount_page_url;
    }

    public static function get_store_uri() {
        return get_permalink( woocommerce_get_page_id( 'shop' ) );
    }

    public static function get_payment_uri() {
        $payment_page = get_permalink( woocommerce_get_page_id( 'pay' ) );
        if ( get_option( 'woocommerce_force_ssl_checkout' ) == 'yes' ) $payment_page = str_replace( 'http:', 'https:', $payment_page );
        return $payment_page;
    }

    public static function get_category_thumbnail_uri($cat) {
        $thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
        return wp_get_attachment_url( $thumbnail_id );
    }

    public static function get_category($args = array()) {
        return get_terms('product_cat', $args);
    }

    public static function get_category_parent($args = array()) {
        $defaults = array(
            'parent'	=> 0
        );
        $args = wp_parse_args($args , $defaults);
        return self::get_category($args);
    }

    public static function get_by_category($cat, $args = array()) {
        $defaults = array(
            'posts_per_page'	=> 8,
            'params'			=> array(
                'tax_query'		=> array(
                    array(
                        'taxonomy'	=> 'product_cat',
                        'field'		=> 'id',
                        'terms'		=> $cat->term_id
                    )
                )
            )
        );
        $args = wp_parse_args($args, $defaults);
        $product = new WP_Query($args);
        return $product;
    }

    public function get_bulk_discount() {
        $result = array();
        $quantity = 5;
        $discount = get_post_meta($this->product->id, 'wpcf-product-quantity-5-price', true);
        if(!empty($quantity) && !empty($discount)) {
            $bulk_discount = new SB_Product_Discount($quantity, $discount);
            array_push($result, $bulk_discount);
        }
        $quantity = 10;
        $discount = get_post_meta($this->product->id, 'wpcf-product-quantity-10-price', true);
        if(!empty($quantity) && !empty($discount)) {
            $bulk_discount = new SB_Product_Discount($quantity, $discount);
            array_push($result, $bulk_discount);
        }
        $quantity = 15;
        $discount = get_post_meta($this->product->id, 'wpcf-product-quantity-15-price', true);
        if(!empty($quantity) && !empty($discount)) {
            $bulk_discount = new SB_Product_Discount($quantity, $discount);
            array_push($result, $bulk_discount);
        }
        $quantity = 20;
        $discount = get_post_meta($this->product->id, 'wpcf-product-quantity-20-price', true);
        if(!empty($quantity) && !empty($discount)) {
            $bulk_discount = new SB_Product_Discount($quantity, $discount);
            array_push($result, $bulk_discount);
        }
        $quantity = 25;
        $discount = get_post_meta($this->product->id, 'wpcf-product-quantity-25-price', true);
        if(!empty($quantity) && !empty($discount)) {
            $bulk_discount = new SB_Product_Discount($quantity, $discount);
            array_push($result, $bulk_discount);
        }
        return $result;
    }
}

class SB_Product_Discount {
    public $quantity;
    public $discount;
    public function __construct($quantity, $discount) {
        $this->quantity = $quantity;
        $this->discount = $discount;
    }
}