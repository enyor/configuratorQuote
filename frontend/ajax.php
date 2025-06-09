<?php
add_action('wp_ajax_pc_send_quote', 'pc_send_quote');

function pc_send_quote() {
    $data = $_POST['data'];
    $endpoint = get_option('pc_request_quote_url');
    $post_author = isset($data['author']) ? intval($data['author']) : get_current_user_id();
    $author_user = get_user_by('id', $post_author);
    $author_email = $author_user ? $author_user->user_email : '';

    $payload = [
        'customer_id' => sanitize_text_field($data['customer_id']),
        'email' => sanitize_email($data['email']),
        'phone' => sanitize_text_field($data['phone']),
        'tier' => sanitize_text_field($data['tier']),
        'post_author' => $post_author,
        'author_email'  => sanitize_email($author_email),
        'products' => $data['products'],
        'payment_terms' => sanitize_text_field($data['payment_terms'])
    ];

    $post_id = wp_insert_post([
        'post_type' => 'pc_quote',
        'post_title' => 'Quote - ' . current_time('mysql'),
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
        'meta_input' => [
            'customer_id' => $payload['customer_id'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'tier' => $payload['tier'],
            'payment_terms' => $payload['payment_terms'],
            'products' => wp_json_encode($payload['products']),
        ]
    ]);
    $rownum = 1;
    foreach ($payload['products'] as &$product) {
        $product['qty'] = isset($product['qty']) ? intval($product['qty']) : 1;
        $product['line'] = $rownum;

        $total = 0;
        foreach ($product['configuration'] as $opt) {
            $total += floatval($opt['price']);
        }

        // Aplicar descuento por Tier
        $tier = strtolower($payload['tier']);
        $discounts = [
            'tier 1'   => 0.32,
            'tier 2'   => 0.25,
            'tier 3'   => 0.15,
            'no tier'  => 0
        ];
        $discount = $discounts[$tier] ?? 0;
        $discounted = $total * (1 - $discount);

        $product['total_price'] = round($discounted, 2); // precio final con descuento
        $product['raw_price'] = round($total, 2);        // precio sin descuento
        $product['discount_percent'] = $discount * 100;

        $lines = [];
        
        foreach ($product['configuration'] as $opt) {
            $label = str_replace(['\"', '"'], 'in.', $opt['label']);
            $lines[] = "{$opt['name']}: {$label}";
        }
        $product['description'] = implode("\\n", $lines); // descripción para ese producto
        $rownum++;
    }
    unset($product);

    if ($endpoint) {
        $response = wp_remote_post($endpoint, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($payload),
            'timeout' => 10,
        ]);
    }

    wp_send_json_success(['message' => 'Quote saved and sent.']);
}

add_action('wp_ajax_pc_get_config', 'pc_get_config');
function pc_get_config() {
    check_ajax_referer('pc_configurator_nonce', 'nonce');

    // Obtener el primer producto (ejemplo simple)
    $products = get_posts([
        'post_type' => 'pc_product',
        'posts_per_page' => 1,
    ]);

    if (!$products) wp_send_json_error();

    $data = get_post_meta($products[0]->ID, '_pc_characteristics', true);
    wp_send_json_success($data);
}

add_action('wp_ajax_pc_get_products', 'pc_get_products');
function pc_get_products() {
    check_ajax_referer('pc_configurator_nonce', 'nonce');

    $products = get_posts([
        'post_type' => 'pc_product',
        'post_status' => 'publish',
        'numberposts' => -1
    ]);

    if (!$products) {
        wp_send_json_error(['message' => 'No configurable products found.']);
    }

    $result = [];

    foreach ($products as $product) {
        $characteristics = get_post_meta($product->ID, '_pc_characteristics', true);
        if (!$characteristics) continue;

        $result[] = [
            'ID' => $product->ID,
            'title' => get_the_title($product),
            'base_sku' => get_post_meta($product->ID, '_pc_base_sku', true),
            'characteristics' => $characteristics,
        ];
    }

    wp_send_json_success($result);
}