<?php
add_action('add_meta_boxes', function() {
    add_meta_box(
        'pc_product_characteristics',
        'Características del Producto',
        'pc_render_characteristics_metabox',
        'pc_product',
        'normal',
        'high'
    );
});

function pc_render_characteristics_metabox($post) {
    wp_nonce_field('pc_save_characteristics', 'pc_characteristics_nonce');
    $data = get_post_meta($post->ID, '_pc_characteristics', true) ?: [];
    $base_sku = get_post_meta($post->ID, '_pc_base_sku', true);

    echo '<label><strong>SKU Base del Producto:</strong></label><br>';
    echo '<input type="text" name="pc_base_sku" value="' . esc_attr($base_sku) . '" class="widefat" /><br><br>';

    echo '<div id="pc-characteristics-wrapper">';
    echo '<button type="button" class="button" id="add-characteristic">Agregar Característica</button>';
    echo '<div id="characteristics-list">';

    foreach ($data as $index => $char) {
        echo '<div class="char-block">';
        echo '<input type="text" name="characteristics[' . $index . '][name]" value="' . esc_attr($char['name']) . '" placeholder="Nombre de la característica">';
        echo '<div class="items-list">';
        foreach ($char['items'] as $i => $item) {
            echo '<div class="item-block">';
            echo '<input type="text" name="characteristics[' . $index . '][items][' . $i . '][name]" value="' . esc_attr($item['name']) . '" placeholder="Nombre">';
            echo '<input type="text" name="characteristics[' . $index . '][items][' . $i . '][sku]" value="' . esc_attr($item['sku']) . '" placeholder="SKU">';
            echo '<input type="number" step="0.01" name="characteristics[' . $index . '][items][' . $i . '][price]" value="' . esc_attr($item['price']) . '" placeholder="Precio">';
            echo '</div>';
        }
        echo '</div>';
        echo '<button type="button" class="button add-item">Agregar Item</button>';
        echo '</div>';
    }

    echo '</div></div>';
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        let charCount = document.querySelectorAll('.char-block').length;

        document.getElementById('add-characteristic').addEventListener('click', () => {
            const list = document.getElementById('characteristics-list');
            const html = `
                <div class="char-block">
                    <input type="text" name="characteristics[${charCount}][name]" placeholder="Nombre de la característica">
                    <div class="items-list"></div>
                    <button type="button" class="button add-item">Agregar Item</button>
                </div>`;
            list.insertAdjacentHTML('beforeend', html);
            charCount++;
        });

        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-item')) {
                const parent = e.target.closest('.char-block');
                const index = Array.from(document.querySelectorAll('.char-block')).indexOf(parent);
                const items = parent.querySelectorAll('.item-block').length;
                const html = `
                    <div class="item-block">
                        <input type="text" name="characteristics[${index}][items][${items}][name]" placeholder="Nombre">
                        <input type="text" name="characteristics[${index}][items][${items}][sku]" placeholder="SKU">
                        <input type="number" step="0.01" name="characteristics[${index}][items][${items}][price]" placeholder="Precio">
                    </div>`;
                parent.querySelector('.items-list').insertAdjacentHTML('beforeend', html);
            }
        });
    });
    </script>
    <style>
        .char-block, .item-block { margin: 10px 0; padding: 10px; border: 1px solid #ccc; }
        input { margin: 4px 0; display: block; width: 100%; }
    </style>
    <?php
}

add_action('save_post_pc_product', function($post_id) {
    if (!isset($_POST['pc_characteristics_nonce']) || !wp_verify_nonce($_POST['pc_characteristics_nonce'], 'pc_save_characteristics')) {
        return;
    }
    $data = $_POST['characteristics'] ?? [];
    update_post_meta($post_id, '_pc_characteristics', $data);
    if (isset($_POST['pc_base_sku'])) {
        update_post_meta($post_id, '_pc_base_sku', sanitize_text_field($_POST['pc_base_sku']));
    }
});
