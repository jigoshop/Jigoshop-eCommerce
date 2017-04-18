class AdminProductDownloadable
  constructor: ->
    jQuery('#product-type').on 'change', @removeParameters
    jQuery('#product-variations > li').on 'change', '.variation-type', @removeVariationParameters

  removeParameters: (event) ->
    $item = jQuery(event.target)
    if $item.val() == 'downloadable'
      jQuery('li.download').show()
      jQuery('li.download').removeClass 'not-active'
    else
      jQuery('li.download').hide()
      jQuery('li.download').not('not-active').addClass 'not-active'

  removeVariationParameters: (event) ->
    $item = jQuery(event.target)
    $parent = $item.closest('li.variation')
    if $item.val() == 'downloadable'
      jQuery('.product-downloadable', $parent).slideDown()
    else
      jQuery('.product-downloadable', $parent).slideUp()

jQuery ->
  new AdminProductDownloadable()
