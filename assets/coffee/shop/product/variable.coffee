class ProductVariable
  VARIATION_EXISTS: 1
  VARIATION_NOT_EXISTS: 2
  VARIATION_NOT_FULL: 3

  params:
    variations: {}
  attributes: {}
  defaultFeaturedImage: ''

  constructor: (@params) ->
    @defaultFeaturedImage = jQuery('.featured-image')

    jQuery('select.product-attribute').on 'change', @updateAttributes
    jQuery('select.product-attribute').trigger 'change'

  updateAttributes: (event) =>
    $buttons = jQuery('#add-to-cart-buttons')
    $messages = jQuery('#add-to-cart-messages')
    results = /(?:^|\s)attributes\[(\d+)](?:\s|$)/g.exec(event.target.name)
    @attributes[results[1]] = event.target.value

    if event.target.value == ''
      jQuery('.variable-product-gallery a').not('#variation-featured-image-parent').addClass('active')
      @refreshVariationGallery('parent')

    proper = @VARIATION_NOT_FULL
    size = Object.keys(@attributes).length
    for own id, definition of @params.variations
      proper = @VARIATION_EXISTS
      if Object.keys(definition.attributes).length != size
        proper = @VARIATION_NOT_FULL
        continue
      for own attributeId, attributeValue of @attributes
        if definition.attributes[attributeId] != '' and definition.attributes[attributeId] != attributeValue
          proper = @VARIATION_NOT_EXISTS
          break
      if proper == @VARIATION_EXISTS
        if definition.price == ''
          proper = @VARIATION_NOT_EXISTS
          continue
        jQuery('div.price > span', $buttons).html(definition.html.price)
        if definition.html.image != ''
          jQuery('.featured-image').replaceWith(definition.html.image)
          @refreshVariationGallery(id)
        else
          jQuery('.featured-image').replaceWith(@defaultFeaturedImage)
          @refreshVariationGallery('parent')
        jQuery('#variation-id').val(id)
        $buttons.slideDown()
        $messages.slideUp()
        break
    if proper != @VARIATION_EXISTS
      jQuery('#variation-id').val('')
      jQuery('.featured-image').replaceWith(@defaultFeaturedImage)
      this.refreshVariationGallery('parent')
      $buttons.slideUp()
    if proper == @VARIATION_NOT_EXISTS && event.target.value
      $messages.slideDown()

  refreshVariationGallery: (id) ->
    jQuery('.variable-product-gallery a.active').removeClass('active')
    jQuery('.variable-product-gallery a').not(jQuery('#variation-featured-image-' + id)).addClass('active')

jQuery () ->
  new ProductVariable(jigoshop_product_variable)
