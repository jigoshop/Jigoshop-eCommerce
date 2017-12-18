class ShippingPayment
  saveSettings: false

  constructor: ->
    jQuery('.shipping-payment-method-configure').click (e) =>
      targetMethod = jQuery(e.delegateTarget).val()
      if targetMethod != undefined
        jQuery.magnificPopup.open
          mainClass: 'jigoshop'
          items: src: ''
          type: 'inline'
          callbacks:
            elementParse: (item) ->
              item.src = jQuery('#shipping-payment-method-options-' + targetMethod).detach()
              jQuery(item.src).css('display', 'block')
            open: =>
              jQuery('.mfp-content input[type="checkbox"]').bootstrapSwitch
                size: 'small'
                onText: 'Yes'
                offText: 'No'
              jQuery('.mfp-content select').each (index, element) ->
                jQuery(element).siblings().remove()
                jQuery(element).select2('destroy')
                jQuery(element).select2()

              jQuery('.mfp-content .shipping-payment-method-options-save').click (e) =>
                @saveSettings = true
                @finalizeChanges(e)
              jQuery('.mfp-content .shipping-payment-method-options-discard').click (e) =>
                @finalizeChanges(e)
            close: =>
              @finalizeChanges(null)

  finalizeChanges: (e) ->
    if(e != null)
      e.preventDefault()

    jQuery('.mfp-content')
    .find('.shipping-payment-method-options-discard, .shipping-payment-method-options-save')
    .attr('disabled', 'disabled')

    if(!@saveSettings)
      location.href = document.URL
      return

    $contents = jQuery('.mfp-content').children('div').clone(true, true)

    $contents.find('select').each (index, element) ->
      selectedValues = jQuery(element).select2('data')
      selectedValuesIds = []

      if(jQuery.isArray(selectedValues))
        jQuery(selectedValues).each (index2, element2) ->
          selectedValuesIds.push(element2.id)
      else
        selectedValuesIds.push(selectedValues.id)

      jQuery(element).val(selectedValuesIds)

    jQuery($contents).appendTo('#shipping-payment-methods-container')
    jQuery('.shipping-payment-method-options-save').parents('form').submit()
jQuery () ->
  new ShippingPayment()