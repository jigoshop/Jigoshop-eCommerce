class AdminProductSimple
  constructor: ->
    jQuery('#product-type').on 'change', @removeParameters

  removeParameters: (event) ->
    $item = jQuery(event.target)
    if $item.val() == 'simple'
      jQuery('.product_regular_price_field').slideDown()
      jQuery('.product_regular_price_field').find('.not-active').removeClass 'not-active'

jQuery ->
  new AdminProductSimple()
