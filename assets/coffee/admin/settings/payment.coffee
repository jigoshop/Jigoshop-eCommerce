Payment = undefined
Payment = do ->
  `var Payment`

  Payment = ->
    jQuery('.payment-method-configure').click (e) ->
      targetMethod = undefined
      targetMethod = undefined
      e.preventDefault()
      targetMethod = jQuery(e.target).val()
      if targetMethod != undefined
        jQuery.magnificPopup.open
          mainClass: 'jigoshop'
          closeOnContentClick: false
          closeOnBgClick: false
          closeBtnInside: true
          showCloseBtn: true
          enableEscapeKey: true
          modal: true
          items: src: ''
          type: 'inline'
          callbacks:
            elementParse: (item) ->
              item.src = '<div></div>'
              return
            open: ->
              that = undefined
              jQuery('.mfp-content').empty()
              jQuery('#payment-method-options-' + targetMethod).appendTo '.mfp-content'
              jQuery('.mfp-content input[type="checkbox"]').bootstrapSwitch
                size: 'small'
                onText: 'Yes'
                offText: 'No'
              that = this
              jQuery('.mfp-content .payment-method-close').click (e) ->
                e.preventDefault()
                that.close()
                return
              return
            close: ->
              jQuery('#payment-method-options-' + targetMethod).appendTo '#payment-methods-container'
              return
      return
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
          return
        return
      ), 300
      return
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
          return
        return
      ), 300
      return
    return

  Payment
jQuery ->
  new Payment