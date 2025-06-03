jQuery(document).ready(function($) {
    $('#pc-send-quote').on('click', function(e) {
        e.preventDefault();
        let customer_id = $('#pc-customer-id').val();
        let email = $('#pc-email').val();
        let tier = $('#pc-tier').val();
        let products = JSON.parse(localStorage.getItem('pc_cart')) || [];

        $.post(pc_ajax.ajax_url, {
            action: 'pc_send_quote',
            data: { customer_id, email, tier, products }
        }, function(response) {
            alert(response.data.message);
            localStorage.removeItem('pc_cart');
        });
    });
});