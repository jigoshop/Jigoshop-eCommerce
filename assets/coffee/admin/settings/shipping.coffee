class Shipping
  ruleCount: 0

  constructor: ->
    jQuery('.shipping-method-configure').click (e) =>
      targetMethod = jQuery(e.target).val()
      if targetMethod != undefined
        jQuery.magnificPopup.open
          mainClass: 'jigoshop'
          items: src: ''
          type: 'inline'
          callbacks:
            elementParse: (item) ->
              item.src = jQuery('#shipping-method-options-' + targetMethod).html()
            open: =>
              jQuery('.mfp-content input[type="checkbox"]').bootstrapSwitch
                size: 'small'
                onText: 'Yes'
                offText: 'No'
              jQuery('.mfp-content select').each (index, element) ->
                jQuery(element).siblings().remove()
                jQuery(element).select2 'destroy'
                jQuery(element).select2()
              jQuery('.shipping-method-options-save').click ->
                jQuery.magnificPopup.close()

              @initAdvancedFlatRateElements()
            close: ->
              jQuery(@content).find('input[type="checkbox"]').each (index, element) ->
                jQuery(element).bootstrapSwitch 'destroy'
              jQuery(@content).find('select').each (index, element) ->
                jQuery(element).select2 'destroy'
              jQuery('#shipping-method-options-' + targetMethod).html jQuery(@content).get()
              jQuery('.shipping-method-options-save').click()

  initAdvancedFlatRateElements: () ->
    @ruleCount = jQuery('.mfp-content #advanced-flat-rate li.list-group-item').length
    jQuery('.mfp-content div.advanced_flat_rate_countries_field').show()
    jQuery('.mfp-content #advanced_flat_rate_available_for').on('change', @toggleSpecificCountires).trigger('change')
    jQuery('.mfp-content #advanced-flat-rate').on( 'click', '.add-rate', (event) =>
      @addRate(event))
    .on('click', '.toggle-rate', @toggleRate)
    .on('click', '.remove-rate', @removeRate)
    .on('keyup', '.input-label, .input-cost', @updateTitle)
    .on('switchChange.bootstrapSwitch', 'input.rest-of-the-world', @toggleLocationFields)
    jQuery('.mfp-content input.rest-of-the-world').trigger 'switchChange'
    jQuery('.mfp-content #advanced-flat-rate ul').sortable
      handle: ".handle"
      axis: "y"

  toggleLocationFields: (event) ->
    $container = jQuery(event.target).closest('.list-group-item-text')
    $fields = jQuery('div.continents, div.countries, div.states, div.postcode', $container)
    if jQuery(event.target).is ':checked'
      $fields.slideUp()
    else
      $fields.slideDown()

  updateTitle: (event) ->
    $rule = jQuery(event.target).closest 'li'
    label = $rule.find('.input-label').val()
    cost = $rule.find('.input-cost').val()
    $rule.find('span.title').html label + ' - ' + cost

  addRate: (event) ->
    event.preventDefault()
    template = wp.template('advanced-flat-rate')
    @ruleCount++
    jQuery('.mfp-content #advanced-flat-rate ul.list-group').append template
      id: @ruleCount
    jQuery('.mfp-content #advanced-flat-rate ul.list-group li:last select').select2()
    jQuery('.mfp-content').find('input[type="checkbox"]').each (index, element) ->
      jQuery(element).bootstrapSwitch
        size: 'small'
        onText: 'Yes'
        offText: 'No'

  toggleSpecificCountires: (event) ->
    console.log('toggleSpecificCountires')
    if jQuery(event.target).val() == 'specific'
      jQuery('.mfp-content .advanced_flat_rate_countries_field').show()
    else
      jQuery('.mfp-content .advanced_flat_rate_countries_field').hide()

  toggleRate: (event) ->
    $item = jQuery(event.target)
    console.log('toggleRate')
    jQuery('.list-group-item-text', $item.closest('li')).slideToggle () ->
      jQuery('span', $item).toggleClass('glyphicon-collapse-down').toggleClass('glyphicon-collapse-up')

  removeRate: (event) ->
    $item = jQuery(event.target).closest('li')
    $item.slideUp 1000, () ->
      $item.remove()

jQuery () ->
  new Shipping()
