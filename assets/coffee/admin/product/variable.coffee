class AdminProductVariable
  params:
    i18n:
      confirm_remove: ''
      variation_removed: ''

  constructor: (@params) ->
    jQuery('#product-type').on 'change', @removeParameters
    jQuery('#add-variation').on 'click', @addVariation
    jQuery('#product-variations')
      .on 'click', '.remove-variation', @removeVariation
      .on 'click', '.show-variation', (event) ->
        $item = jQuery(event.target)
        jQuery('.list-group-item-text', $item.closest('li')).slideToggle ->
          jQuery('span', $item).toggleClass('glyphicon-collapse-down').toggleClass('glyphicon-collapse-up')
      .on 'change', 'select.variation-attribute', @updateVariation
      .on 'change', '.list-group-item-text input.form-control' , @updateVariation
      .on 'change', '.list-group-item-text input[type="checkbox"]', @updateVariation
      .on 'change', '.list-group-item-text select.form-control', @updateVariation
      .on 'click', '.set_variation_image', @setImage
      .on 'click', '.remove_variation_image', @removeImage
      .on 'change', 'input[type="checkbox"].default_variation', (event) ->
        jQuery('input[type="checkbox"].default_variation').not(jQuery(event.target)).prop 'checked', false
      .on 'change', 'input[type="checkbox"].stock-manage', (event) ->
        $parent = jQuery(event.target).closest 'li'
        if jQuery(event.target).is(':checked')
          jQuery('div.manual-stock-status', $parent).slideUp()
          jQuery('.stock-status', $parent).slideDown()
        else
          jQuery('div.manual-stock-status', $parent).slideDown()
          jQuery('.stock-status', $parent).slideUp()
    jQuery('.set_variation_image').each @connectImage
    jQuery('#sales-range-from').on 'changeDate', (selected) ->
      jQuery('#product-variations .input-daterange').each () ->
        id = jQuery(this).attr('id')
        jQuery('#' + id + '-from').datepicker 'setStartDate', new Date(selected.date.valueOf())
        jQuery('#' + id + '-to').datepicker 'setStartDate', new Date(selected.date.valueOf())
    jQuery('#sales-range-to').on 'changeDate', (selected) ->
      jQuery('#product-variations .input-daterange').each () ->
        id = jQuery(this).attr('id')
        jQuery('#' + id + '-from').datepicker 'setEndDate', new Date(selected.date.valueOf())
        jQuery('#' + id + '-to').datepicker 'setEndDate', new Date(selected.date.valueOf())

  removeParameters: (event) ->
    $item = jQuery(event.target)
    if $item.val() == 'variable'
      jQuery('.product_regular_price_field').slideUp()
  addVariation: (event) ->
    event.preventDefault()
    $parent = jQuery('#product-variations')
    jQuery.ajax
      url: jigoshop.getAjaxUrl()
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product.add_variation'
        product_id: $parent.closest('.jigoshop').data('id')
    .done (data) ->
      if data.success? and data.success
        jQuery(data.html).hide().appendTo($parent).slideDown().trigger('jigoshop.variation.add')
      else
        jigoshop.addMessage('danger', data.error, 6000)
  updateVariation: (event) =>
    $container = jQuery('#product-variations')
    $parent = jQuery(event.target).closest('li.list-group-item')

    getOptionValue = (current) ->
      if current.type == 'checkbox' or current.type == 'radio'
        return current.checked
      if current.type == 'select-multiple'
        return jQuery(current).val()
      return current.value

    attributes = {}
    attributesData = jQuery('select.variation-attribute', $parent).toArray()
    for option in attributesData
      results = /(?:^|\s)product\[variation]\[\d+]\[attribute]\[(.*?)](?:\s|$)/g.exec(option.name)
      attributes[results[1]] = getOptionValue(option)

    product = {}
    productData = jQuery('.list-group-item-text input.form-control,
                          .list-group-item-text input[type="checkbox"],
                          .list-group-item-text select.form-control', $parent).toArray()

    for option in productData
      results = /(?:^|\s)product\[variation]\[\d+]\[product]\[(.*?)](\[(.*?)])?(?:\s|$)/g.exec(option.name)
      if results[3]?
        product[results[1]] = {}
        product[results[1]][results[3]] = getOptionValue(option)
      else
        product[results[1]] = getOptionValue(option)

    jQuery.ajax
      url: jigoshop.getAjaxUrl()
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product.save_variation'
        product_id: $container.closest('.jigoshop').data('id')
        variation_id: $parent.data('id')
        attributes: attributes
        product: product
    .done (data) =>
      if data.success? and data.success
        $parent.trigger('jigoshop.variation.update')
        jigoshop.addMessage('success', @params.i18n.saved, 2000)
      else
        jigoshop.addMessage('danger', data.error, 6000)
  removeVariation: (event) =>
    event.preventDefault()
    if confirm(@params.i18n.confirm_remove)
      $parent = jQuery(event.target).closest('li')
      jQuery.ajax
        url: jigoshop.getAjaxUrl()
        type: 'post'
        dataType: 'json'
        data:
          action: 'jigoshop.admin.product.remove_variation'
          product_id: $parent.closest('.jigoshop').data('id')
          variation_id: $parent.data('id')
      .done (data) =>
        if data.success? and data.success
          $parent.trigger('jigoshop.variation.remove')
          $parent.slideUp -> $parent.remove()
          jigoshop.addMessage('success', @params.i18n.variation_removed, 2000)
        else
          jigoshop.addMessage('danger', data.error, 6000)
  connectImage: (index, element) ->
    $element = jQuery(element)
    $remove = $element.next('.remove_variation_image')
    $thumbnail = jQuery('img', $element.parent())
    $element.jigoshop_media(
      field: false
      bind: false
      thumbnail: $thumbnail
      callback: (attachment) ->
        $remove.show()
        jQuery.ajax
          url: jigoshop.getAjaxUrl()
          type: 'post'
          dataType: 'json'
          data:
            action: 'jigoshop.admin.product.set_variation_image'
            product_id: $element.closest('.jigoshop').data('id')
            variation_id: $element.closest('.variation').data('id')
            image_id: attachment.id
        .done (data) ->
          if !data.success? or !data.success
            jigoshop.addMessage('danger', data.error, 6000)
      library:
        type: 'image'
    )
  setImage: (event) ->
    event.preventDefault()
    jQuery(event.target).trigger('jigoshop_media')
  removeImage: (event) ->
    event.preventDefault()
    $element = jQuery(event.target)
    $thumbnail = jQuery('img', $element.parent())
    jQuery.ajax
      url: jigoshop.getAjaxUrl()
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product.set_variation_image'
        product_id: $element.closest('.jigoshop').data('id')
        variation_id: $element.closest('.variation').data('id')
        image_id: -1
    .done (data) ->
      if data.success? and data.success
        $thumbnail
          .attr('src', data.url)
          .attr('width', 150)
          .attr('height', 150)
        $element.hide()
      else
        jigoshop.addMessage('danger', data.error, 6000)

jQuery ->
  new AdminProductVariable(jigoshop_admin_product_variable)
