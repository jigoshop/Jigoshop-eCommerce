JigoshopHelpers.prototype.ajaxSearch = ($field, params) ->
  if typeof params.initAction is 'undefined'
    params.initAction = params.action
  if typeof params.multiple is 'undefined'
    params.multiple = true
  if typeof params.only_parent is 'undefined'
    params.only_parent = false

  $field.select2
    multiple: params.multiple
    minimumInputLength: 3
    ajax:
      url: jigoshop.getAjaxUrl()
      type: 'post'
      dataType: 'json'
      cache: true
      data: (term) ->
        return {
          action: params.action,
          only_parent: params.only_parent,
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
        url: jigoshop.getAjaxUrl()
        type: 'post'
        dataType: 'json'
        data:
          action: params.initAction
          only_parent: params.only_parent,
          value: jQuery(element).val()
      .done (data) ->
        if data.success? and data.success
          callback(data.results)
        else
          jigoshop.addMessage('danger', data.error, 6000)