jQuery(document).ready(function($) {
  var changeStatus;
  changeStatus = function(order_id, status) {
    var params;
    params = jigoshop_admin_orders_list;
    return $.ajax({
      url: params['ajax'],
      type: 'post',
      dataType: 'json',
      data: {
        action: params['module'],
        orderId: order_id,
        status: status
      }
    }).done(function(data) {
      if (data.success === true) {
        return location.reload();
      } else {
        return alert(data.msg);
      }
    }).fail(function(data) {
      return alert(params['ajax_error']);
    });
  };
  return $('.btn-status').click(function() {
    return changeStatus($(this).data('order_id'), $(this).data('status_to'));
  });
});
