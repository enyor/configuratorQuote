<?php
function pc_register_custom_post_types() {
    // Productos configurables
    register_post_type('pc_product', [
        'labels' => [
            'name' => 'Productos Configurables',
            'singular_name' => 'Producto Configurable',
        ],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-cart',
        'supports' => ['title']
    ]);

    // Cotizaciones
    register_post_type('pc_quote', [
        'labels' => [
            'name' => 'Cotizaciones',
            'singular_name' => 'Cotización',
        ],
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-clipboard',
        'supports' => ['title', 'custom-fields']
    ]);
}
add_action('init', 'pc_register_custom_post_types');