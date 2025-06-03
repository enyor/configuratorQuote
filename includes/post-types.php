<?php
function pc_register_quote_post_type() {
    register_post_type('pc_quote', [
        'labels' => ['name' => 'Quotes', 'singular_name' => 'Quote'],
        'public' => false,
        'has_archive' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-clipboard',
        'supports' => ['title', 'custom-fields'],
        'capability_type' => 'post',
    ]);
}
add_action('init', 'pc_register_quote_post_type');