class AdminProducts

  constructor: () ->
    jQuery('.product-featured').on 'click', @featureProduct
    jQuery('#the-list').on 'click', '.editinline', @quickEditInit
    jQuery('#the-list').on 'click', 'input.stock-manage', @toggleStockFields

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

  quickEditInit: (event) =>
    jQuery('div.toggle').hide()
    inlineData = @getInlineData(jQuery(event.target).closest('tr').attr('id').replace('post-', ''))
    for key, value of inlineData
      $item = jQuery('input.' + key + ', select.' + key, '.jigoshop-inline-edit-row')
      $item.closest('.toggle').show()
      if $item.prop('tagName') == 'INPUT' and $item.attr('type') == 'checkbox'
        $item.prop('checked', value)
      else
        $item.val(value)
      $item.change()

  getInlineData: (id) ->
    result = {}
    jQuery('#jigoshop-inline-' + id + ' div').each (index, element) ->
      $element = jQuery(element)
      result[$element.attr('class')] = $element.text()
    result

  toggleStockFields: (event) ->
    if jQuery(event.target).is(':checked')
      jQuery('input.stock-stock').show()
      jQuery('select.stock-status').hide()
    else
      jQuery('input.stock-stock').hide()
      jQuery('select.stock-status').show()

jQuery ->
  new AdminProducts()