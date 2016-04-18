var AdminProductSimple;

AdminProductSimple = (function() {
  function AdminProductSimple() {
    jQuery('#product-type').on('change', this.removeParameters);
  }

  AdminProductSimple.prototype.removeParameters = function(event) {
    var $item;
    $item = jQuery(event.target);
    if ($item.val() === 'simple') {
      return jQuery('.product_regular_price_field').slideDown();
    }
  };

  return AdminProductSimple;

})();

jQuery(function() {
  return new AdminProductSimple();
});
