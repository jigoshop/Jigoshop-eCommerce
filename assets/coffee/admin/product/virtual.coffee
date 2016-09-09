class AdminProductVirtual
  constructor: ->
    jQuery('#product-type').on 'change', @removeParameters

  removeParameters: (event) ->
    $item = jQuery(event.target)
    if $item.val() == 'virtual'
      jQuery('.product_regular_price_field').slideDown()
      jQuery('.product_regular_price_field').find('.not-active').removeClass 'not-active'

jQuery ->
  new AdminProductVirtual()
