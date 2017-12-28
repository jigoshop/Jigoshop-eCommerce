class Payment

  constructor: ->
    jQuery('.payment-method-enable').on 'switchChange.bootstrapSwitch', @toggleEnable
    jQuery('.payment-method-testMode').on 'switchChange.bootstrapSwitch', @toggleTestMode

  toggleEnable: (e, state) ->
    targetMethod = jQuery(e.target).parents('tr').attr('id')
    setTimeout (->
      jQuery.post ajaxurl, {
        action: 'paymentMethodSaveEnable'
        method: targetMethod
        state: state
      }, ->
        location.href = document.URL
    ), 300

  toggleTestMode: (e, state) ->
    targetMethod = jQuery(e.target).parents('tr').attr('id')
    setTimeout (->
      jQuery.post ajaxurl, {
        action: 'paymentMethodSaveTestMode'
        method: targetMethod
        state: state
      }, ->
        location.href = document.URL
    ), 300

jQuery () ->
  new Payment()