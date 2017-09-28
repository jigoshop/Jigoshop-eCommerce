class Payment

  constructor: ->
    jQuery('.payment-method-configure').click (e) ->
      targetMethod = undefined
      e.preventDefault()
      targetMethod = jQuery(e.delegateTarget).val()
      if targetMethod != undefined
        jQuery.magnificPopup.open
          mainClass: 'jigoshop'
          items: src: ''
          type: 'inline'
          callbacks:
            elementParse: (item) ->
              item.src = jQuery('#payment-method-options-' + targetMethod).detach()
              jQuery(item.src).css('display', 'block')
            open: ->
              jQuery('.mfp-content input[type="checkbox"]').bootstrapSwitch
                size: 'small'
                onText: 'Yes'
                offText: 'No'
              jQuery('.mfp-content select').each (index, element) ->
                jQuery(element).siblings().remove()
                jQuery(element).select2 'destroy'
                jQuery(element).select2()
              jQuery('.mfp-content .payment-method-options-save').click (e) ->
                e.preventDefault()
                jQuery.magnificPopup.close()
            close: ->
              jQuery('.mfp-content').find('input[type="checkbox"]').each (index, element) ->
                jQuery(element).bootstrapSwitch 'destroy'
              jQuery('.mfp-content').find('select').each (index, element) ->
                jQuery(element).select2 'destroy'
              contents = jQuery('.mfp-content').children('div').detach()
              jQuery(contents).appendTo('#payment-methods-container')
              jQuery('.payment-method-options-save').click()

    jQuery('.payment-method-enable').on 'switchChange.bootstrapSwitch', (e, state) ->
      targetMethod = undefined
      targetMethod = jQuery(e.target).parents('tr').attr('id')
      setTimeout (->
        jQuery.post ajaxurl, {
          action: 'paymentMethodSaveEnable'
          method: targetMethod
          state: state
        }, ->
          location.href = document.URL
      ), 300
    jQuery('.payment-method-testMode').on 'switchChange.bootstrapSwitch', (e, state) ->
      targetMethod = undefined
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