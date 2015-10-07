var AdminProductDownloadable;

AdminProductDownloadable = (function() {
  function AdminProductDownloadable() {
    jQuery('#product-variations > li').on('change', '.variation-type', this.removeVariationParameters);
  }

  AdminProductDownloadable.prototype.removeVariationParameters = function(event) {
    var $item, $parent;
    $item = jQuery(event.target);
    $parent = $item.closest('li.variation');
    if ($item.val() === 'downloadable') {
      return jQuery('.product-downloadable', $parent).slideDown();
    } else {
      return jQuery('.product-downloadable', $parent).slideUp();
    }
  };

  return AdminProductDownloadable;

})();

jQuery(function() {
  return new AdminProductDownloadable();
});
