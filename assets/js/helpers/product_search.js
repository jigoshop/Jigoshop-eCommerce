jigoshop.productSearch = function($field, params) {
  if (typeof params.initAction === 'undefined') {
    params.initAction = params.action;
  }
  if (typeof params.multiple === 'undefined') {
    params.multiple = true;
  }
  return $field.select2({
    multiple: params.multiple,
    minimumInputLength: 3,
    ajax: {
      url: params.ajax,
      type: 'post',
      dataType: 'json',
      cache: true,
      data: function(term) {
        return {
          action: params.action,
          query: term
        };
      },
      results: function(data) {
        if ((data.success != null) && data.success) {
          return {
            results: data.results
          };
        } else {
          return jigoshop.addMessage('danger', data.error, 6000);
        }
      }
    },
    initSelection: function(element, callback) {
      return jQuery.ajax({
        url: params.ajax,
        type: 'post',
        dataType: 'json',
        data: {
          action: params.initAction,
          value: jQuery(element).val()
        }
      }).done(function(data) {
        if ((data.success != null) && data.success) {
          return callback(data.results);
        } else {
          return jigoshop.addMessage('danger', data.error, 6000);
        }
      });
    }
  });
};
