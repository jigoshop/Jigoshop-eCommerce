class AdminCoupon

  constructor: ->
    jigoshop.ajaxSearch jQuery('#jigoshop_coupon_products'), {
      action: 'jigoshop.admin.product.find'
    }
    jigoshop.ajaxSearch jQuery('#jigoshop_coupon_excluded_products'), {
      action: 'jigoshop.admin.product.find'
    }
    jigoshop.ajaxSearch jQuery('#jigoshop_coupon_categories'), {
      action: 'jigoshop.admin.coupon.find_category'
    }
    jigoshop.ajaxSearch jQuery('#jigoshop_coupon_excluded_categories'), {
      action: 'jigoshop.admin.coupon.find_category'
    }

jQuery ->
  new AdminCoupon()
