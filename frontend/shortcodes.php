<?php
function pc_quote_history_shortcode() {
    if (!is_user_logged_in()) return '<p>Debes estar logueado para ver tus cotizaciones.</p>';

    $user_id = get_current_user_id();
    $quotes = get_posts([
        'post_type' => 'pc_quote',
        'author' => $user_id,
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    if (!$quotes) return '<p>No tienes cotizaciones registradas.</p>';

    $html = '<h3>Historial de Cotizaciones</h3>';
    foreach ($quotes as $quote) {
        $products = json_decode(get_post_meta($quote->ID, 'products', true), true);
        $html .= '<div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">';
        $html .= '<strong>Fecha:</strong> ' . $quote->post_date . '<br>';
        $html .= '<strong>Customer ID:</strong> ' . get_post_meta($quote->ID, 'customer_id', true) . '<br>';
        $html .= '<strong>Email:</strong> ' . get_post_meta($quote->ID, 'email', true) . '<br>';
        $html .= '<strong>Tier:</strong> ' . get_post_meta($quote->ID, 'tier', true) . '<br><br>';
        foreach ($products as $product) {
            $html .= '<strong>SKU:</strong> ' . $product['sku'] . '<br>';
            $html .= '<strong>Precio:</strong> ' . $product['total_price'] . '<br>';
            $html .= '<ul>';
            foreach ($product['configuration'] as $opt) {
                $html .= '<li>' . esc_html($opt['name']) . ': ' . esc_html($opt['label']) . ' (' . esc_html($opt['price']) . ')</li>';
            }
            $html .= '</ul><hr>';
        }
        $html .= '</div>';
    }
    return $html;
}
add_shortcode('pc_quote_history', 'pc_quote_history_shortcode');

function pc_render_configurator() {
    if (!is_user_logged_in()) {
        return '<p>Debes iniciar sesión para acceder al configurador.</p>';
    }

    ob_start();
    ?>
    <div id="pc-configurator-root"></div>
    <script>
        const pc_ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";
        const pc_nonce = "<?php echo wp_create_nonce('pc_configurator_nonce'); ?>";
    </script>
    <?php
    wp_enqueue_script('pc-configurator-frontend');
    wp_enqueue_style('pc-configurator-frontend');
    return ob_get_clean();
}
add_shortcode('pc_configurator', 'pc_render_configurator');

function pc_render_cart_shortcode() {
    ob_start();
    ?>
    <div id="pc-cart-root">
        <p>Cargando carrito...</p>
    </div>
    <script>
        const pc_ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>";
    </script>
    <?php
    wp_enqueue_script('pc-frontend');
    return ob_get_clean();
}
add_shortcode('pc_cart', 'pc_render_cart_shortcode');