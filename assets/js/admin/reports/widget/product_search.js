var ProductSearch;

ProductSearch = (function() {
  ProductSearch.prototype.params = {
    ajax: ''
  };

  function ProductSearch(params) {
    this.params = params;
    jigoshop.ajaxSearch(jQuery('#jigoshop_find_products'), {
      action: 'jigoshop.admin.product.find',
      ajax: this.params.ajax
    });
  }

  return ProductSearch;

})();

jQuery(function() {
  return new ProductSearch(jigoshop_admin_reports_widget_product_search);
});
