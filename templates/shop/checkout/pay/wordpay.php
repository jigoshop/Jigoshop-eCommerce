<?php
/**
 *
 */
?>
<script type='text/javascript'>
    window.onload = function() {
        Worldpay.useTemplateForm({
            'clientKey':'your-test-client-key',
            'form':'paymentForm',
            'paymentSection':'paymentSection',
            'display':'inline',
            'reusable':true,
            'callback': function(obj) {
                if (obj && obj.token) {
                    var _el = document.createElement('input');
                    _el.value = obj.token;
                    _el.type = 'hidden';
                    _el.name = 'token';
                    document.getElementById('paymentForm').appendChild(_el);
                    //document.getElementById('paymentForm').submit();
                }
            }
        });
    }
</script>
<form action="<?= $notifyUrl; ?>" id="paymentForm" method="post">
    <!-- all other fields you want to collect, e.g. name and shipping address -->
    <div id='paymentSection'></div>
    <div>
        <input type="submit" value="Place Order" onclick="Worldpay.submitTemplateForm()" />
    </div>
</form>
