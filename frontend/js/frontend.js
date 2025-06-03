jQuery(document).ready(function($) {
    const root = $('#pc-cart-root');
    if (!root.length) return;

    let products = JSON.parse(localStorage.getItem('pc_cart')) || [];

    function renderCart() {
        if (products.length === 0) {
            root.html('<p>Tu carrito está vacío.</p>');
            return;
        }

        let html = '<h3>Carrito de Cotización</h3><ul>';
        products.forEach((p, index) => {
            html += '<li><strong>Producto #' + (index + 1) + '</strong><br>SKU: ' + p.sku + '<br>Precio Total: ' + p.total_price + '<ul>';
            p.configuration.forEach(opt => {
                html += '<li>' + opt.name + ': ' + opt.label + ' (' + opt.price + ')</li>';
            });
            html += '</ul></li>';
        });
        html += '</ul>';

        html += `
            <button id="pc-clear-cart">Vaciar Carrito</button>
            <h4>Enviar Cotización</h4>
            <input type="text" id="pc-customer-id" placeholder="Customer ID"><br>
            <input type="email" id="pc-email" placeholder="Email"><br>
            <select id="pc-tier">
                <option value="">Selecciona Tier</option>
                <option value="Tier 1">Tier 1</option>
                <option value="Tier 2">Tier 2</option>
                <option value="Tier 3">Tier 3</option>
                <option value="Tier 4">Tier 4</option>
            </select><br><br>
            <button id="pc-send-quote">Send Request Quote</button>
        `;
        root.html(html);
    }

    renderCart();

    root.on('click', '#pc-clear-cart', function() {
        localStorage.removeItem('pc_cart');
        products = [];
        renderCart();
    });

    root.on('click', '#pc-send-quote', function(e) {
        e.preventDefault();
        let customer_id = $('#pc-customer-id').val();
        let email = $('#pc-email').val();
        let tier = $('#pc-tier').val();

        if (!customer_id || !email || !tier) {
            alert('Completa todos los campos');
            return;
        }

        $.post(pc_ajax_url, {
            action: 'pc_send_quote',
            data: { customer_id, email, tier, products }
        }, function(response) {
            alert(response.data.message);
            localStorage.removeItem('pc_cart');
            renderCart();
        });
    });
});
