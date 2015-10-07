var AdminProductExternal;

AdminProductExternal = (function() {
  function AdminProductExternal() {
    jQuery('#product-type').on('change', this.removeParameters);
    jQuery('#product-variations > li').on('change', '.variation-type', this.removeVariationParameters);
  }

  AdminProductExternal.prototype.removeParameters = function(event) {
    var $item;
    $item = jQuery(event.target);
    if ($item.val() === 'external') {
      jQuery('.product_regular_price_field').slideDown();
      return jQuery('.product-external').slideDown();
    } else {
      return jQuery('.product-external').slideUp();
    }
  };

  AdminProductExternal.prototype.removeVariationParameters = function(event) {
    var $item, $parent;
    $item = jQuery(event.target);
    $parent = $item.closest('li.variation');
    if ($item.val() === 'external') {
      return jQuery('.product-external', $parent).slideDown();
    } else {
      return jQuery('.product-external', $parent).slideUp();
    }
  };

  return AdminProductExternal;

})();

jQuery(function() {
  return new AdminProductExternal();
});
