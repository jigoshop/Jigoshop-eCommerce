class ProductSearch
  constructor: ->
    jigoshop.ajaxSearch jQuery('#jigoshop_find_products'), {
      action: 'jigoshop.admin.product.find'
    }

jQuery ->
  new ProductSearch()
