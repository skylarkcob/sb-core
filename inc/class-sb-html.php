<?php
class SB_HTML {
    var $self_closers = null;
    var $name = null;
    var $attributes = null;

    public function __construct($name) {
        $this->name = strtolower($name);
        $this->init();
    }

    private function init() {
        $this->self_closers = array('input', 'img', 'hr', 'br', 'meta', 'link');
        $this->attributes = array();
    }

    public function get_attribute($attribute_name) {
        if($this->is_attribute_exists($attribute_name)) {
            return $this->attributes[$attribute_name];
        }
        return null;
    }

    public function set_attribute($attribute_name, $value) {
        if(!empty($value)) {
            $this->attributes[$attribute_name] = $value;
        }
    }

    public function set_class($class) {
        $this->set_attribute('class', $class);
    }

    public function set_html($value) {
        $this->set_attribute('text', $value);
    }

    public function set_text($value) {
        $this->set_html($value);
    }

    public function set_attribute_array($attributes) {
        if(is_array($attributes)) {
            $this->attributes = $attributes;
        }
    }

    public function remove_attribute($attribute_name) {
        if($this->is_attribute_exists($attribute_name)) {
            unset($this->attributes[$attribute_name]);
        }
    }

    public function remove_all_attribute() {
        $this->attributes = array();
    }

    private function make_outlink_nofollow() {
        if('a' == $this->name) {
            $href = $this->get_attribute('href');
            if(!empty($href)) {
                if(!SB_WP::is_my_domain_url($href)) {
                    $this->set_attribute('rel', 'nofollow');
                    $this->set_attribute('target', '_blank');
                }
            }
        }
    }

    private function check_html() {
        $this->make_outlink_nofollow();
    }

    public function build() {
        $this->check_html();
        $result = '<'.$this->name;
        foreach($this->attributes as $key => $value) {
            if($key != 'text') {
                $result .= sprintf(' %1$s="%2$s"', $key, $value);
            }
        }
        $result .= '>';
        if(!in_array($this->name, $this->self_closers)) {
            $result .= $this->get_attribute('text');
            $result .= sprintf('</%s>', $this->name);
        }
        return $result;
    }

    public function output() {
        echo $this->build();
    }

    public function is_attribute_exists($attribute_name) {
        return array_key_exists($attribute_name, $this->attributes);
    }

    public static function before_mail_message() {
        return SB_Mail::before();
    }

    public static function after_mail_message() {
        return SB_Mail::after();
    }

    public static function build_mail_body($message) {
        $result = self::before_mail_message();
        $result .= $message;
        $result .= self::after_mail_message();
        return $result;
    }
}