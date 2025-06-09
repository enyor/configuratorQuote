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
            <button id="pc-clear-cart" class="pc-button">Clean Cart</button>
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
            <select id="pc-payment-terms">
                <option value="TBD" selected>TBD - To Be Determined</option>
                <option value=".5%">0.5% 10, Net 30</option>
                <option value="1%">1/15, Net 30</option>
                <option value="2%">2/10, Net 30</option>
                <option value="51P">5th 1st Prox</option>
                <option value="52P">5th 2nd Prox</option>
                <option value="53P">5th 3rd Prox</option>
                <option value="54P">5th 4th Prox</option>
                <option value="ADV">Pay in Advance</option>
                <option value="D50">50% Down, Net 30 on Balance</option>
                <option value="E30">Net 30 End of Month</option>
                <option value="IMM">Immediate Pay</option>
                <option value="N12">Net 120</option>
                <option value="N15">Net 15</option>
                <option value="N20">Net 20</option>
                <option value="N30">Net 30</option>
                <option value="N35">Net 35</option>
                <option value="N37">Net 37</option>
                <option value="N45">Net 45</option>
                <option value="N5">Net 5</option>
                <option value="N50">Net 50</option>
                <option value="N60">Net 60</option>
                <option value="N70">Net 70</option>
                <option value="N75">Net 75</option>
                <option value="N8">Net 8</option>
                <option value="N80">Net 80</option>
                <option value="N85">Net 85</option>
                <option value="N90">Net 90</option>
            </select><br><br>
            <button id="pc-send-quote" class="pc-button">Send Request Quote</button>
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
        let $btn = $(this);
        let customer_id = $('#pc-customer-id').val();
        let email = $('#pc-email').val();
        let tier = $('#pc-tier').val();
        let phone = $('#pc-phone').val();
        let author = typeof pc_current_user_id !== 'undefined' ? pc_current_user_id : null;
        let payment_terms = $('#pc-payment-terms').val();

        if (!customer_id || !email || !tier ||!phone) {
            alert('Complete all fields');
            return;
        }

        $btn.prop('disabled', true).text('Sending...');

        $.post(pc_ajax_url, {
            action: 'pc_send_quote',
            data: { customer_id, email, phone, tier, products, author, payment_terms }
        }, function(response) {
            alert(response.data.message);
            localStorage.removeItem('pc_cart');
            products = [];
            renderCart();
        }).fail(() => {
            alert('Error sending the quote.');
        }).always(() => {
            $btn.prop('disabled', false).text('Send Request Quote');
        });
    });
});
