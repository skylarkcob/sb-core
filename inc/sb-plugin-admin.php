<?php
$sb_admin = new SB_Admin();

add_action( 'init', 'sb_core_check_license' );

function is_sb_admin_page() {
    $result = SB_Admin_Custom::is_sb_page();
    return apply_filters( 'sb_admin_page', $result );
}