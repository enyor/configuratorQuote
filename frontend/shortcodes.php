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
        $customer_id = esc_html(get_post_meta($quote->ID, 'customer_id', true));
        $email = esc_html(get_post_meta($quote->ID, 'email', true));
        $tier = esc_html(get_post_meta($quote->ID, 'tier', true));
        $date = esc_html($quote->post_date);

        $html .= "<div style='border:1px solid #ccc; padding:15px; margin-bottom:20px;'>";
        $html .= "<strong>Fecha:</strong> {$date}<br>";
        $html .= "<strong>Customer ID:</strong> {$customer_id}<br>";
        $html .= "<strong>Email:</strong> {$email}<br>";
        $html .= "<strong>Tier:</strong> {$tier}<br><br>";

        $html .= "<table style='width:100%; border-collapse:collapse;'>";
        $html .= "<thead><tr style='border-bottom:1px solid #ccc;'>
                    <th style='text-align:left;'>ITEM</th>
                    <th style='text-align:left;'>REMARKS WITH PRODUCT</th>
                    <th style='text-align:left;'>QTY</th>
                  </tr></thead><tbody>";

        foreach ($products as $index => $product) {
            $product_name = esc_html($product['title'] ?? 'Producto sin título'); // Valor estático por ahora, si guardas el título, cámbialo aquí.
            $sku = esc_html($product['sku']);
            $total_price = esc_html($product['total_price']);
            $qty = isset($product['qty']) ? intval($product['qty']) : 1;
            $remark = isset($product['remark']) ? esc_html($product['remark']) : '';
            $config_lines = '';
            foreach ($product['configuration'] as $opt) {
                $label = esc_html($opt['label']);
                $name = esc_html($opt['name']);
                $price = esc_html($opt['price']);
                $config_lines .= "{$name}: {$label}<br>";
            }

            $html .= "<tr style='border-bottom:1px solid #eee;'>
                        <td style='padding:10px; vertical-align:top;'>
                            <strong>{$product_name}</strong><br>
                            <small>SKU: {$sku}</small><br><br>
                            {$config_lines}
                        </td>
                        <td style='padding:10px; vertical-align:top;'>
                            {$remark}
                        </td>
                        <td style='padding:10px; vertical-align:top;'>
                            {$qty}
                        </td>
                      </tr>";
        }

        $html .= "</tbody></table></div>";
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