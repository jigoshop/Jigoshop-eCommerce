class ShippingFreeShipping
  constructor: ->
    jQuery('#free_shipping_available_for').on('change', @toggleSpecificCountries).trigger('change')

  toggleSpecificCountries: ->
    if(jQuery('#free_shipping_available_for').val() == 'specific')
      jQuery('#free_shipping_countries').parents('div.free_shipping_countries_field').slideDown()
    else
      jQuery('#free_shipping_countries').parents('div.free_shipping_countries_field').slideUp()

jQuery () ->
  new ShippingFreeShipping