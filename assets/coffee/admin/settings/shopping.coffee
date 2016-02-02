class ShoppingSettings
  constructor: ->
    jQuery('#restrict_selling_locations').on 'switchChange.bootstrapSwitch', @toggleSellingLocations
    jQuery('#selling_locations').show().closest('div.form-group').show()
    jQuery('#enable_verification_message').on 'switchChange.bootstrapSwitch', @toggleVerificationMessage
    jQuery('#verification_message').show().closest('div.form-group').show()

  toggleSellingLocations: ->
    jQuery('#selling_locations').closest('tr').toggle()

  toggleVerificationMessage: ->
    jQuery('#verification_message').closest('tr').toggle()

jQuery ->
  new ShoppingSettings()