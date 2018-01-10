<?php
/**
 * @var string $clientKet
 * @var Jigoshop\Entity\Order $order
 * @var string $notifyUrl
 */
?>
<script type='text/javascript'>
    window.onload = function() {
        Worldpay.useTemplateForm({
            'clientKey':'<?= $clientKey; ?>',
            'saveButton': false,
            'paymentSection':'paymentSection',
            'display':'inline',
            'reusable': true,
            'callback': function(obj) {
                if (obj && obj.token) {
                    var _el = document.createElement('input');
                    _el.value = obj.token;
                    _el.type = 'hidden';
                    _el.name = 'jigoshop_order[worldpay][token]';
                    _el.id = 'worldpay_token';
                    document.getElementById('checkout').appendChild(_el);
                    jQuery('#checkout button[type="submit"]').click();
                }
            }
        });
    };
    jQuery('#checkout').on('click', 'button[type="submit"]', function(event){
        if(jQuery('#payment-worldpay input').is(':checked')) {
            if(jQuery('#worldpay_token').length == 0) {
                Worldpay.submitTemplateForm();
                event.preventDefault();
            }
        }
    });
</script>
<div id='paymentSection'></div>
