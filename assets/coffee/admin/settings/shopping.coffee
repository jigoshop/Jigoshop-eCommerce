class ShoppingSettings
  constructor: ->
    jQuery('#restrict_selling_locations').on 'switchChange.bootstrapSwitch', @toggleSellingLocations
    jQuery('#selling_locations').show().closest('div.form-group').show()

  toggleSellingLocations: ->
    jQuery('#selling_locations').closest('tr').toggle()

jQuery ->
  new ShoppingSettings()