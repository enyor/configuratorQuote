jQuery(document).ready(function($) {
    const root = $('#pc-cart-root');
    if (!root.length) return;

    let products = JSON.parse(localStorage.getItem('pc_cart')) || [];

    function renderCart() {
        if (products.length === 0) {
            root.html('<p>Your cart is empty.</p>');
            return;
        }

        let html = `
            <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                <thead>
                    <tr style="border-bottom:1px solid #ccc;">
                        <th style="text-align:left;">ITEM</th>
                        <th style="text-align:left;">REMARKS WITH PRODUCT</th>
                        <th style="text-align:left; width:80px;">QTY</th>
                    </tr>
                </thead>
                <tbody>
        `;

        products.forEach((p, index) => {
            const itemDetails = p.configuration.map(c => `${c.name}: ${c.label}`).join('<br>');
            const warning = p.backorder ? `<div style="background:#fff3cd; color:#856404; padding:10px; border:1px solid #ffeeba; margin-top:10px;">
                ⚠️ We're sending what we have now. The remaining ${p.backorder} item(s) will ship soon as it's back in stock.
            </div>` : '';

            html += `
                <tr style="border-bottom:1px solid #eee;">
                    <td style="vertical-align:top; padding:10px;">
                        <strong>${p.title || 'Producto sin título'}</strong><br>
                        <small>SKU: ${p.sku}</small><br><br>
                        ${itemDetails}
                        ${warning}
                    </td>
                    <td style="padding:10px;">
                        <textarea style="width:100%;" rows="4" class="pc-remark" data-index="${index}">${p.remark || ''}</textarea>
                    </td>
                    <td style="padding:10px;">
                        <input type="number" min="1" value="${p.qty || 1}" class="pc-qty" data-index="${index}" style="width:60px;" />
                    </td>
                </tr>
            `;
        });

        html += `</tbody></table>`;

        html += `
            <button id="pc-clear-cart">Clean Cart</button>
            <h4 style="margin-top:20px;">Enviar Cotización</h4>
            <input type="text" id="pc-customer-id" placeholder="Customer ID"><br><br>
            <input type="email" id="pc-email" placeholder="Email"><br><br>
            <input type="text" id="pc-phone" placeholder="Teléfono"><br><br>
            <select id="pc-tier">
                <option value="">Select Tier</option>
                <option value="No Tier">No Tier</option>
                <option value="Tier 1">Tier 1</option>
                <option value="Tier 2">Tier 2</option>
                <option value="Tier 3">Tier 3</option>
            </select><br><br>
            <button id="pc-send-quote">Send Request Quote</button>
        `;

        root.html(html);
    }

    root.on('change', '.pc-remark', function() {
        const index = $(this).data('index');
        products[index].remark = $(this).val();
        localStorage.setItem('pc_cart', JSON.stringify(products));
    });

    root.on('change', '.pc-qty', function() {
        const index = $(this).data('index');
        products[index].qty = parseInt($(this).val()) || 1;
        localStorage.setItem('pc_cart', JSON.stringify(products));
    });

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
        let phone = $('#pc-phone').val();
        let author = typeof pc_current_user_id !== 'undefined' ? pc_current_user_id : null;

        if (!customer_id || !email || !tier ||!phone) {
            alert('Complete all fields');
            return;
        }

        $.post(pc_ajax_url, {
            action: 'pc_send_quote',
            data: { customer_id, email, phone, tier, products, author }
        }, function(response) {
            alert(response.data.message);
            localStorage.removeItem('pc_cart');
            products = [];
            renderCart();
        });
    });
});
