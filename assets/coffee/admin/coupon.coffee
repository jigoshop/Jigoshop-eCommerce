class AdminCoupon
  params:
    ajax: ''

  constructor: (@params) ->
    jigoshop.ajaxSearch jQuery('#jigoshop_coupon_products'), {
      action: 'jigoshop.admin.product.find'
      ajax: @params.ajax
    }
    jigoshop.ajaxSearch jQuery('#jigoshop_coupon_excluded_products'), {
      action: 'jigoshop.admin.product.find'
      ajax: @params.ajax
    }
    jigoshop.ajaxSearch jQuery('#jigoshop_coupon_categories'), {
      action: 'jigoshop.admin.coupon.find_category'
      ajax: @params.ajax
    }
    jigoshop.ajaxSearch jQuery('#jigoshop_coupon_excluded_categories'), {
      action: 'jigoshop.admin.coupon.find_category'
      ajax: @params.ajax
    }

jQuery ->
  new AdminCoupon(jigoshop_admin_coupon)
