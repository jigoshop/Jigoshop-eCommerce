class Payment
  processingFeeRulesLastId: 0

  constructor: ->
    jQuery('.payment-method-enable').on 'switchChange.bootstrapSwitch', @toggleEnable
    jQuery('.payment-method-testMode').on 'switchChange.bootstrapSwitch', @toggleTestMode

    @processingFeeRulesLastId = jQuery('#processing-fee-rules').find('tbody').find('tr').length

    jQuery('#processing-fee-rules').find('tbody').find('select').trigger('change')

    jQuery('#processing-fee-add-rule').click(@addProcessingFeeRule)
    @bindProcessingFeeRulesControls()

    jQuery('#processing-fee-rules').find('tbody').sortable()

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

  bindProcessingFeeRulesControls: ->
    jQuery('.processing-fee-remove-rule').click(@removeProcessingFeeRule)

  addProcessingFeeRule: =>
    rule = jigoshop_admin_payment.processingFeeRule.replace(/%RULE_ID%/g, @processingFeeRulesLastId)
    @processingFeeRulesLastId++

    jQuery('#processing-fee-rules').find('tbody').append(rule)
    jQuery('#processing-fee-rules').find('tbody').find('tr').last().find('select').select2()

    @bindProcessingFeeRulesControls()

  removeProcessingFeeRule: (e) ->
    jQuery(e.delegateTarget).parents('tr').remove()

jQuery () ->
  new Payment()