<?php
/*
Plugin Name: Product Configurator
Description: Plugin para configurar productos personalizados con cotización.
Version: 1.0
Author: Frank_lions
*/

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/post-types.php';
require_once plugin_dir_path(__FILE__) . 'admin/settings.php';
require_once plugin_dir_path(__FILE__) . 'frontend/ajax.php';
require_once plugin_dir_path(__FILE__) . 'frontend/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'admin/meta-boxes.php';

function pc_enqueue_scripts() {
    if (is_user_logged_in()) {
        wp_enqueue_script('pc-frontend', plugin_dir_url(__FILE__) . 'frontend/js/frontend.js', ['jquery'], '1.0', true);
        wp_localize_script('pc-frontend', 'pc_ajax', [
            'ajax_url' => admin_url('admin-ajax.php')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'pc_enqueue_scripts');

add_action('wp_enqueue_scripts', function () {
    wp_register_script(
        'pc-configurator-frontend',
        plugin_dir_url(__FILE__) . 'assets/js/configurator.js',
        [],
        '1.0',
        true
    );

    wp_register_style(
        'pc-configurator-frontend',
        plugin_dir_url(__FILE__) . 'assets/css/configurator.css'
    );
});

add_action('wp_login_failed', function($username) {
    $referrer = wp_get_referer();
    if (!empty($referrer) && !str_contains($referrer, 'wp-login') && !str_contains($referrer, 'login=failed')) {
        wp_redirect(add_query_arg('login', 'failed', $referrer));
        exit;
    }
});