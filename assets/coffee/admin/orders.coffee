class AdminOrders
  constructor: ->
    jQuery('.column-status').on 'click', '.btn-status', @changeStatus
  changeStatus: (event) ->
    $parent = jQuery(event.target).parent()
    orderId = jQuery(event.target).data 'order_id'
    status = jQuery(event.target).data 'status_to'
    if orderId and status
      jigoshop.block $parent,
        overlayCSS:
          backgroundColor: '#fff'
      jQuery.ajax(
        url: jigoshop.getAjaxUrl()
        type: 'post'
        dataType: 'json'
        data:
          action: 'jigoshop.admin.orders.change_status'
          orderId: orderId
          status: status
      ).done (result) ->
        if result.success
          $parent.html result.html
        else
          alert result.error
        jigoshop.unblock $parent
jQuery ->
  new AdminOrders()