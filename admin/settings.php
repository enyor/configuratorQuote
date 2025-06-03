<?php
function pc_register_settings_menu() {
    add_menu_page('Product Configurator', 'Product Configurator', 'manage_options', 'pc-settings', 'pc_render_settings_page');
}
add_action('admin_menu', 'pc_register_settings_menu');

function pc_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configuración de Product Configurator</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('pc_settings_group');
            do_settings_sections('pc-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function pc_register_settings() {
    register_setting('pc_settings_group', 'pc_request_quote_url');
    add_settings_section('pc_section', 'Ajustes Generales', null, 'pc-settings');
    add_settings_field('pc_request_quote_url', 'Request Quote URL', 'pc_request_quote_url_callback', 'pc-settings', 'pc_section');
}
add_action('admin_init', 'pc_register_settings');

function pc_request_quote_url_callback() {
    $value = get_option('pc_request_quote_url', '');
    echo '<input type="text" name="pc_request_quote_url" value="' . esc_attr($value) . '" class="regular-text" />';
}