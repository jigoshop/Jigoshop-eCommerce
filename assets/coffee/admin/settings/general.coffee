class GeneralSettings
  params:
    states: {}
    currency: {}

  constructor: (@params) ->
    jQuery('#show_message').on 'switchChange.bootstrapSwitch', @toggleCustomMessage
    jQuery('#custom_message').show().closest('div.form-group').show()

    jQuery('select#country').on 'change', @updateStateField
    jQuery('select#currency')
      .on 'change', @updateCurrencyPositionField
      .change()
    @updateFields()

  updateStateField: (event) =>
    $country = jQuery(event.target)
    $states = jQuery('input#state')
    country = $country.val()
    if @params.states[country]?
      @_attachSelectField($states, @params.states[country])
    else
      @_attachTextField($states)

  updateCurrencyPositionField: (event) =>
    currency = jQuery(event.target).val()
    $position = jQuery('input#currency_position')
    @_attachSelectField($position, @params.currency[currency])

  toggleCustomMessage: ->
    jQuery('#custom_message').closest('tr').toggle()

  updateFields: ->
    jQuery('select#country').change()

  ###
  Attaches Select2 to provided field with proper states to select
  ###
  _attachSelectField: ($field, states) ->
    $field.select2
      data: states
      multiple: false

  ###
  Attaches simple text field to write a state
  ###
  _attachTextField: ($field) ->
    $field.select2('destroy')

jQuery () ->
  new GeneralSettings(jigoshop_admin_general)

