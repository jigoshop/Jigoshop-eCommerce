class ShippingFlatRate
  constructor: ->
    jQuery('#flat_rate_available_for').on('change', @toggleSpecificCountries).trigger('change')

  toggleSpecificCountries: ->
    if(jQuery('#flat_rate_available_for').val() == 'specific')
      jQuery('#flat_rate_countries').parents('div.flat_rate_countries_field').slideDown()
    else
      jQuery('#flat_rate_countries').parents('div.flat_rate_countries_field').slideUp()
jQuery () ->
  new ShippingFlatRate