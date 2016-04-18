var AdminCoupon;

AdminCoupon = (function() {
  AdminCoupon.prototype.params = {
    ajax: ''
  };

  function AdminCoupon(params) {
    this.params = params;
    jigoshop.ajaxSearch(jQuery('#jigoshop_coupon_products'), {
      action: 'jigoshop.admin.product.find',
      ajax: this.params.ajax
    });
    jigoshop.ajaxSearch(jQuery('#jigoshop_coupon_excluded_products'), {
      action: 'jigoshop.admin.product.find',
      ajax: this.params.ajax
    });
    jigoshop.ajaxSearch(jQuery('#jigoshop_coupon_categories'), {
      action: 'jigoshop.admin.coupon.find_category',
      ajax: this.params.ajax
    });
    jigoshop.ajaxSearch(jQuery('#jigoshop_coupon_excluded_categories'), {
      action: 'jigoshop.admin.coupon.find_category',
      ajax: this.params.ajax
    });
  }

  return AdminCoupon;

})();

jQuery(function() {
  return new AdminCoupon(jigoshop_admin_coupon);
});
