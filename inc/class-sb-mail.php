<?php
defined('ABSPATH') OR exit;

class SB_Mail {
    public static function set_html_content_type() {
        return 'text/html';
    }

    public static function send($to, $subject, $message, $headers = '', $attachments = '') {
        $done = wp_mail($to, $subject, $message, $headers, $attachments);
        return $done;
    }

    public static function send_html($to, $subject, $message, $headers = '', $attachments = '') {
        add_filter( 'wp_mail_content_type', array('SB_Mail', 'set_html_content_type') );
        $result = self::send($to, $subject, $message, $headers, $attachments);
        remove_filter( 'wp_mail_content_type', array('SB_Mail', 'set_html_content_type') );
        return $result;
    }

    public static function notify_user_for_comment_approved($comment) {
        if($comment) {
            $post = get_post($comment->comment_post_ID);
            if($post) {
                $subject = sprintf(__('Your comment on %s is approved', 'sb-core'), $post->post_title);
                $body = sprintf(sprintf('<p>%s,</p>', __('Dear %s', 'sb-core')), $comment->comment_author);
                $body .= sprintf(sprintf('<p>%s</p>', __('Your comment on %s is approved. You can click these links below for more detail.', 'sb-core')), $post->post_title);
                $body .= sprintf(sprintf('<p>%s</p>', __('Post link: %s', 'sb-core')), get_permalink($post));
                $body .= sprintf(sprintf('<p>%s</p>', __('Comment link: %s', 'sb-core')), get_comment_link($comment));
                $body = SB_HTML::build_mail_body($body);
                self::send_html_mail($comment->comment_author_email, $subject, $body);
            }
        }
    }
}