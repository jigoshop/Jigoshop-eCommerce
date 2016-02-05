jQuery(document).ready ($) ->
  changeStatus = undefined

  changeStatus = (order_id, status) ->
    params = undefined
    params = jigoshop_admin_orders_list
    $.ajax(
      url: params['ajax']
      type: 'post'
      dataType: 'json'
      data:
        action: params['module']
        orderId: order_id
        status: status
    ).done((data) ->
      if data.success == true
        location.reload()
      else
        alert data.msg
    ).fail (data) ->
      alert(params['ajax_error'])

  $('.btn-status').click ->
    changeStatus $(this).data('order_id'), $(this).data('status_to')
