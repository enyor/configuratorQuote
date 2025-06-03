<?php
add_action('wp_ajax_pc_send_quote', 'pc_send_quote');

function pc_send_quote() {
    $data = $_POST['data'];
    $endpoint = get_option('pc_request_quote_url');

    $payload = [
        'customer_id' => sanitize_text_field($data['customer_id']),
        'email' => sanitize_email($data['email']),
        'tier' => sanitize_text_field($data['tier']),
        'products' => $data['products']
    ];

    $post_id = wp_insert_post([
        'post_type' => 'pc_quote',
        'post_title' => 'Quote - ' . current_time('mysql'),
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
        'meta_input' => [
            'customer_id' => $payload['customer_id'],
            'email' => $payload['email'],
            'tier' => $payload['tier'],
            'products' => wp_json_encode($payload['products']),
        ]
    ]);

    if ($endpoint) {
        $response = wp_remote_post($endpoint, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($payload),
            'timeout' => 10,
        ]);
    }

    wp_send_json_success(['message' => 'Cotización guardada y enviada.']);
}

add_action('wp_ajax_pc_get_config', 'pc_get_config');
function pc_get_config() {
    check_ajax_referer('pc_configurator_nonce', 'nonce');

    // Recuperar características del backend
    $features = get_option('pc_features', []);
    $features = array_map(function($f) {
        $f['items'] = isset($f['items']) ? $f['items'] : [];
        return $f;
    }, $features);

    wp_send_json_success($features);
}