class Cart
  params:
    assets: ''
    i18n:
      loading: 'Loading...'

  constructor: (@params) ->
    jQuery('#cart')
      .on 'change', '.product-quantity input', @updateQuantity
      .on 'click', '.product-remove a', @removeItem
    jQuery('#mobile')
      .on 'click', '.show-product', (event) ->
        $item = jQuery(event.target)
        jQuery('.list-group-item-text', $item.closest('li')).slideToggle ->
          jQuery('span', $item).toggleClass('glyphicon-collapse-down').toggleClass('glyphicon-collapse-up')
    jQuery('#shipping-calculator')
      .on 'click', '#change-destination', @changeDestination
      .on 'click', '.close', @changeDestination
      .on 'click', 'input[type=radio]', @selectShipping
      .on 'change', '#country', @updateCountry
      .on 'change', '#state', @updateState.bind(@, '#state')
      .on 'change', '#noscript_state', @updateState.bind(@, '#noscript_state')
      .on 'change', '#postcode', @updatePostcode
    jQuery('input#jigoshop_coupons')
      .on 'change', @updateDiscounts
      .select2
        tags: []
        tokenSeparators: [',']
        multiple: true
        formatNoMatches: ''

  block: =>
    jQuery('#cart').block
      message: '<img src="' + @params.assets + '/images/loading.gif" alt="' + @params.i18n.loading + '" width="15" height="15" />'
      css:
        padding: '5px'
        width: 'auto'
        height: 'auto'
        border: '1px solid #83AC31'
      overlayCSS:
        backgroundColor: 'rgba(255, 255, 255, .8)'

  unblock: ->
    jQuery('#cart').unblock()

  changeDestination: (e) ->
    e.preventDefault()
    jQuery('#shipping-calculator td > div').slideToggle()
    jQuery('#change-destination').slideToggle()
    return false

  selectShipping: =>
    $method = jQuery('#shipping-calculator input[type=radio]:checked')
    $rate = jQuery('.shipping-method-rate', $method.closest('li'))
    jQuery.ajax(
      url: jigoshop.getAjaxUrl()
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop_cart_select_shipping'
        method: $method.val()
        rate: $rate.val()
    )
    .done (result) =>
      if result.success
        @_updateTotals(result.html.total, result.html.subtotal)
        @_updateTaxes(result.tax, result.html.tax)
      else
        jigoshop.addMessage('danger', result.error, 6000)

  updateCountry: =>
    @block()
    jQuery('.noscript_state_field').remove()
    jQuery.ajax(
      url: jigoshop.getAjaxUrl()
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop_cart_change_country'
        value: jQuery('#country').val()
    )
    .done (result) =>
      if result.success? and result.success
        jQuery('#shipping-calculator th p > span').html(result.html.estimation)
        @_updateTotals(result.html.total, result.html.subtotal)
        @_updateDiscount(result)
        @_updateTaxes(result.tax, result.html.tax)
        @_updateShipping(result.shipping, result.html.shipping)

        if result.has_states
          data = []
          for own state, label of result.states
            data.push
              id: state
              text: label
          jQuery('#state').select2
            data: data
        else
          jQuery('#state').attr('type', 'text').select2('destroy').val('')
      else
        jigoshop.addMessage('danger', result.error, 6000)
      @unblock()

  updateState: (field) =>
    @_updateShippingField('jigoshop_cart_change_state', jQuery(field).val())

  updatePostcode: =>
    @_updateShippingField('jigoshop_cart_change_postcode', jQuery('#postcode').val())

  _updateShippingField: (action, value) =>
    @block()
    jQuery.ajax(
      url: jigoshop.getAjaxUrl()
      type: 'post'
      dataType: 'json'
      data:
        action: action
        value: value
    )
    .done (result) =>
      if result.success? and result.success
        jQuery('#shipping-calculator th p > span').html(result.html.estimation)
        @_updateTotals(result.html.total, result.html.subtotal)
        @_updateDiscount(result)
        @_updateTaxes(result.tax, result.html.tax)
        @_updateShipping(result.shipping, result.html.shipping)
      else
        jigoshop.addMessage('danger', result.error, 6000)
      @unblock()

  removeItem: (e) =>
    # TODO: Ask nicely if client is sure?
    e.preventDefault()
    $item = jQuery(e.target).closest('tr, li')
    jQuery('.product-quantity', $item).val(0)
    @updateQuantity(e)

  updateQuantity: (e) =>
    $item = jQuery(e.target).closest('tr, li')
    $items = jQuery('input[name="cart[' + $item.data('id') + ']"]').closest('tr, li')
    jQuery('span.product-quantity', $item).html(jQuery(e.target).val())
    jQuery('input.product-quantity', $item).html(jQuery(e.target).val())
    @block()
    jQuery.ajax(
      url: jigoshop.getAjaxUrl()
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop_cart_update_item'
        item: $item.data('id')
        quantity: jQuery(e.target).val()
    )
    .done (result) =>
      if result.success == true
        if result.empty_cart? == true
          $empty = jQuery(result.html).hide()
          $cart = jQuery('#cart')
          $cart.after($empty)
          $cart.slideUp()
          $empty.slideDown()
          @unblock()
          return

        if result.remove_item? == true
          $items.remove()
        else
          jQuery('.product-subtotal', $item).html(result.html.item_subtotal)
          jQuery('.product-price', $item).html(result.html.item_price)

        jQuery('td#product-subtotal').html(result.html.product_subtotal)
        @_updateTotals(result.html.total, result.html.subtotal)
        @_updateDiscount(result)
        @_updateTaxes(result.tax, result.html.tax)
        @_updateShipping(result.shipping, result.html.shipping)
      else
        jigoshop.addMessage('danger', result.error, 6000)
      @unblock()

  updateDiscounts: (event) =>
    $item = jQuery(event.target)
    @block()
    jQuery.ajax(
      url: jigoshop.getAjaxUrl()
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop_cart_update_discounts'
        coupons: $item.val()
    )
    .done (result) =>
      if result.success? && result.success
        if result.empty_cart? == true
          $empty = jQuery(result.html).hide()
          $cart = jQuery('#cart')
          $cart.after($empty)
          $cart.slideUp()
          $empty.slideDown()
          @unblock()
          return

        jQuery('td#product-subtotal').html(result.html.product_subtotal)
        @_updateTotals(result.html.total, result.html.subtotal)
        @_updateDiscount(result)
        @_updateTaxes(result.tax, result.html.tax)
        @_updateShipping(result.shipping, result.html.shipping)
      else
        jigoshop.addMessage('danger', result.error, 6000)
      @unblock()

  _updateTotals: (total, subtotal) ->
    jQuery('#cart-total > td').html(total)
    jQuery('#cart-subtotal > td').html(subtotal)

  _updateDiscount: (data) ->
    if data.coupons?
      jQuery('input#jigoshop_coupons').select2('val', data.coupons.split(','))
      $parent = jQuery('tr#cart-discount')
      if data.discount > 0
        jQuery('td', $parent).html(data.html.discount)
        $parent.show()
      else
        $parent.hide()
      if data.html.coupons?
        jigoshop.addMessage('warning', data.html.coupons)

  _updateShipping: (shipping, html) ->

    if typeof shipping == "object" && !Array.isArray(shipping) && shipping != null
      jQuery('#shipping-calculator').slideDown()
    else
      jQuery('#shipping-calculator').slideUp()

    for own shippingClass, value of shipping
      $method = jQuery(".shipping-#{shippingClass}")
      $method.addClass('existing')

      if html[shippingClass] == undefined
        continue

      if $method.length > 0
        if value > -1
          $item = jQuery(html[shippingClass].html).addClass('existing')
          $method.replaceWith($item)
        else
          $method.slideUp -> jQuery(this).remove()
      else if html[shippingClass] != undefined
        $item = jQuery(html[shippingClass].html)
        $item.hide().addClass('existing').appendTo(jQuery('#shipping-methods')).slideDown()
    # Remove non-existent methods
    jQuery('#shipping-methods > li:not(.existing)').slideUp -> jQuery(this).remove()
    jQuery('#shipping-methods > li').removeClass('existing')

  _updateTaxes: (taxes, html) ->
    for own taxClass, tax of html
      $tax = jQuery("#tax-#{taxClass}")
      jQuery("th", $tax).html(tax.label)
      jQuery("td", $tax).html(tax.value)
      if taxes[taxClass] > 0
        $tax.show()
      else
        $tax.hide()

jQuery () ->
  new Cart(jigoshop_cart)
