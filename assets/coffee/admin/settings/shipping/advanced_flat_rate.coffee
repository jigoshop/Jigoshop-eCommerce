class ShippingAdvancedFlatRate
  ruleCount: 0

  constructor: ->
    @ruleCount = jQuery('#advanced-flat-rate li.list-group-item').length
    jQuery('#advanced_flat_rate_available_for').on('change', @toggleSpecificCountires).trigger('change')
    jQuery('#advanced-flat-rate').on( 'click', '.add-rate', (event) =>
      @addRate(event))
    .on('click', '.toggle-rate', @toggleRate)
    .on('click', '.remove-rate', @removeRate)
    .on('change', '.input-label, .input-cost', @updateTitle)
    .on('switchChange.bootstrapSwitch', 'input.rest-of-the-world', @toggleLocationFields)
    jQuery('input.rest-of-the-world').trigger 'switchChange'
    jQuery('#advanced-flat-rate ul').sortable
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
    $rule = jQuery(event.target).parents('li')
    label = $rule.find('input.input-label').val()
    cost = $rule.find('input.input-cost').val()
    $rule.find('span.title').text(label + ' - ' + cost)

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
        onText: jigoshop_settings.i18n.yes
        offText: jigoshop_settings.i18n.no

  toggleSpecificCountires: (event) ->
    if jQuery(event.target).val() == 'specific'
      jQuery('.mfp-content .advanced_flat_rate_countries_field').slideDown()
    else
      jQuery('.mfp-content .advanced_flat_rate_countries_field').slideUp()

  toggleRate: (event) ->
    $item = jQuery(event.target)
    jQuery('.list-group-item-text', $item.closest('li')).slideToggle () ->
      jQuery('span', $item).toggleClass('glyphicon-collapse-down').toggleClass('glyphicon-collapse-up')

  removeRate: (event) ->
    $item = jQuery(event.target).closest('li')
    $item.slideUp 300, () ->
      $item.remove()

jQuery () ->
  new ShippingAdvancedFlatRate()
