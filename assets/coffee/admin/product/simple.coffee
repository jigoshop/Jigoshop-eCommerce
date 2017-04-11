class AdminProductSimple
  constructor: ->
    jQuery('#product-type').on 'change', @removeParameters
    jQuery('#product-variations > li').on 'change', '.variation-type', @removeVariationParameters

  removeParameters: (event) ->
    $item = jQuery(event.target)
    if $item.val() == 'simple'
      jQuery('.product_regular_price_field').slideDown()
      jQuery('.product_regular_price_field').find('.not-active').removeClass 'not-active'

  removeVariationParameters: (event) ->
    $item = jQuery(event.target)
    $parent = $item.closest('li.variation')
    if $item.val() == 'simple'
      jQuery('.product-simple', $parent).slideDown()
    else
      jQuery('.product-simple', $parent).slideUp()

jQuery ->
  new AdminProductSimple()
