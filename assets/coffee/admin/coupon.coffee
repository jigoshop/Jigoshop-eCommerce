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

    jQuery('#jigoshop_coupon_type').on('change', @couponTypeChange).trigger('change')

  couponTypeChange: ->
    if(jQuery('#jigoshop_coupon_type').val() == 'fixed_product' || jQuery('#jigoshop_coupon_type').val() == 'percent_product')
      jQuery('#jigoshop_coupon_type_product').slideDown()
    else
      jQuery('#jigoshop_coupon_type_product').slideUp()

jQuery ->
  new AdminCoupon()
