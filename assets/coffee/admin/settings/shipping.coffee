class Shipping
  ruleCount: 0

  constructor: ->
    @ruleCount = jQuery('#advanced-flat-rate li.list-group-item').length
    jQuery('div.advanced_flat_rate_countries_field').show()
    jQuery('#advanced_flat_rate_available_for').on 'change', @toggleSpecificCountires
    jQuery('#advanced-flat-rate').on 'click', '.add-rate', (event) =>
      @addRate(event)
    jQuery('#advanced-flat-rate').on 'click', '.toggle-rate', @toggleRate
    jQuery('#advanced-flat-rate').on 'click', '.remove-rate', @removeRate
    jQuery('#advanced-flat-rate').on 'keyup', '.input-label, .input-cost', @updateTitle
    jQuery('#advanced-flat-rate').on 'change', 'select.country-select', @updateStates

  updateTitle: (event) ->
    $rule = jQuery(event.target).closest 'li'
    label = $rule.find('.input-label').val()
    cost = $rule.find('.input-cost').val()
    $rule.find('span.title').html label + ' - ' + cost

  updateStates: (event) ->
    country = jQuery(event.target).val()
    jQuery.ajax(
      url: jigoshop.getAjaxUrl()
      type: 'POST'
      dataType: 'JSON'
      data:
        action: 'jigoshop.ajax',
        service: 'jigoshop.ajax.get_states',
        country: country
    ).done (response) ->
      if response.success? and response.success
        $stateSelect = jQuery(event.target).closest('li').find('select.states-select')
        $stateSelect.find('option').remove()
        for key of response.states
          element = response.states[key]
          $stateSelect.append jQuery('<option></option>').attr('value', key).text(element)
        $stateSelect.select2()

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
