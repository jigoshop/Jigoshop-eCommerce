jigoshop.productSearch = ($field, params) ->
  if typeof params.initAction is 'undefined'
    params.initAction = params.action
  if typeof params.multiple is 'undefined'
    params.multiple = true

  $field.select2
    multiple: params.multiple
    minimumInputLength: 3
    ajax:
      url: params.ajax
      type: 'post'
      dataType: 'json'
      cache: true
      data: (term) ->
        return {
          action: params.action,
          query: term
        }
      results: (data) ->
        if data.success? and data.success
          return {
            results: data.results
          }
        else
          jigoshop.addMessage('danger', data.error, 6000)
    initSelection: (element, callback) ->
      jQuery.ajax
        url: params.ajax
        type: 'post'
        dataType: 'json'
        data:
          action: params.initAction
          value: jQuery(element).val()
      .done (data) ->
        if data.success? and data.success
          callback(data.results)
        else
          jigoshop.addMessage('danger', data.error, 6000)