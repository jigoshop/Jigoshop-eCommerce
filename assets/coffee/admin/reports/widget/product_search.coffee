class ProductSearch
  params:
    ajax: ''

  constructor: (@params) ->
    jigoshop.ajaxSearch jQuery('#jigoshop_find_products'), {
      action: 'jigoshop.admin.product.find'
      ajax: @params.ajax
    }

jQuery ->
  new ProductSearch(jigoshop_admin_reports_widget_product_search)
