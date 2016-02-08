var CheckoutPay;

CheckoutPay = (function() {
  CheckoutPay.prototype.params = {
    assets: ''
  };

  function CheckoutPay(params1) {
    this.params = params1;
    jQuery.fn.payment = this.payment.bind(this, this.params);
    jQuery('#payment-methods').on('change', 'li input[type=radio]', function() {
      jQuery('#payment-methods li > div').slideUp();
      return jQuery('div', jQuery(this).closest('li')).slideDown();
    });
  }

  CheckoutPay.prototype.payment = function(params, options) {
    var settings;
    settings = jQuery.extend({
      redirect: 'Redirecting...',
      message: 'Thank you for your order. We are now redirecting you to make payment.'
    }, options);
    jQuery(document.body).block({
      message: '<img src="' + params.assets + '/images/loading.gif" alt="' + settings.redirect + '" />' + settings.message,
      css: {
        padding: '20px',
        width: 'auto',
        height: 'auto',
        border: '1px solid #83AC31'
      },
      overlayCss: {
        opacity: 0.01
      }
    });
    return this.submit();
  };

  return CheckoutPay;

})();

jQuery(function() {
  return new CheckoutPay(jigoshop_checkout_pay);
});
