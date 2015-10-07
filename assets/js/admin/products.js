var AdminProducts,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

AdminProducts = (function() {
  AdminProducts.prototype.params = {
    ajax: '',
    i18n: {
      saved: '',
      confirm_remove: '',
      attribute_removed: ''
    }
  };

  function AdminProducts(params) {
    this.params = params;
    this.featureProduct = bind(this.featureProduct, this);
    jQuery('.product-featured').on('click', this.featureProduct);
  }

  AdminProducts.prototype.featureProduct = function(event) {
    var $button;
    event.preventDefault();
    $button = jQuery(event.target).closest('a.product-featured');
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.products.feature_product',
        product_id: $button.data('id')
      }
    }).done(function(data) {
      if ((data.success != null) && data.success) {
        return jQuery('span', $button).toggleClass('glyphicon-star').toggleClass('glyphicon-star-empty');
      } else {
        return addMessage('danger', data.error, 6000);
      }
    });
  };

  return AdminProducts;

})();

jQuery(function() {
  return new AdminProducts(jigoshop_admin_products);
});
