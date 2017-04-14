class Shipping
  ruleCount: 0

  constructor: ->
    @ruleCount = jQuery('#advanced-flat-rate li.list-group-item').length
    jQuery('div.advanced_flat_rate_countries_field').show()
    jQuery('#advanced_flat_rate_available_for').on 'change', @toggleSpecificCountires
    jQuery('#advanced-flat-rate').on( 'click', '.add-rate', (event) =>
      @addRate(event))
    .on('click', '.toggle-rate', @toggleRate)
    .on('click', '.remove-rate', @removeRate)
    .on('keyup', '.input-label, .input-cost', @updateTitle)
    .on('change', 'input.rest-of-the-world', @toggleLocationFields)
    jQuery('input.rest-of-the-world').trigger 'change'

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
    jQuery('#advanced-flat-rate ul.list-group').append template
      id: @ruleCount
    jQuery('#advanced-flat-rate ul.list-group li:last select').select2()

  toggleSpecificCountires: (event) ->
    if jQuery(event.target).val() == 'specific'
      jQuery('#advanced_flat_rate_countries').closest('tr').show()
    else
      jQuery('#advanced_flat_rate_countries').closest('tr').hide()

  toggleRate: (event) ->
    $item = jQuery(event.target)
    jQuery('.list-group-item-text', $item.closest('li')).slideToggle () ->
      jQuery('span', $item).toggleClass('glyphicon-collapse-down').toggleClass('glyphicon-collapse-up')

  removeRate: (event) ->
    $item = jQuery(event.target).closest('li')
    $item.slideUp 1000, () ->
      $item.remove()

jQuery () ->
  new Shipping()
