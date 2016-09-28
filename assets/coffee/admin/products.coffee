class AdminProducts

  constructor: () ->
    jQuery('.product-featured').on 'click', @featureProduct

  featureProduct: (event) ->
    event.preventDefault()
    $button = jQuery(event.target).closest('a.product-featured')
    jQuery.ajax
      url: jigoshop.getAjaxUrl()
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.products.feature_product'
        product_id: $button.data('id')
    .done (data) ->
      if data.success? and data.success
        jQuery('span', $button).toggleClass('glyphicon-star').toggleClass('glyphicon-star-empty')
      else
        jigoshop.addMessage('danger', data.error, 6000)

jQuery ->
  new AdminProducts()
