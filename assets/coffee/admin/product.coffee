class AdminProduct
  params:
    ajax: ''
    i18n:
      saved: ''
      confirm_remove: ''
      attribute_removed: ''
      invalid_attribute: ''
      attribute_without_label: ''
    menu: {}
    attachments: {}
  wpMedia: false

  constructor: (@params) ->
    jQuery('#add-attribute').on 'click', @addAttribute
    jQuery('#new-attribute').on 'change', (event) ->
      $label = jQuery('#new-attribute-label')
      window.console.log jQuery(event.target).val()
      if jQuery(event.target).val() == '-1'
        $label.closest('.form-group').css('display', 'inline-block')
        $label.fadeIn()
      else
        $label.fadeOut()

    jQuery('#product-attributes')
      .on 'click', '.show-variation', (event) ->
        $item = jQuery(event.target)
        jQuery('.list-group-item-text', $item.closest('li')).slideToggle ->
          jQuery('span', $item).toggleClass('glyphicon-collapse-down').toggleClass('glyphicon-collapse-up')

    jQuery('#product-attributes')
      .on 'change', 'input, select', @updateAttribute
      .on 'click', '.remove-attribute', @removeAttribute
    jQuery('#product-type').on 'change', @changeProductType

    jQuery('.jigoshop_product_data a').on 'click', (e) ->
      e.preventDefault()
      jQuery(this).tab('show')
    jQuery('#stock-manage').on 'change', ->
      if jQuery(this).is(':checked')
        jQuery('.stock-status_field').slideUp()
        jQuery('.stock-status').slideDown()
      else
        jQuery('.stock-status_field').slideDown()
        jQuery('.stock-status').slideUp()
    jQuery('.stock-status_field .not-active').show()
    jQuery('#sales-enabled').on 'change', ->
      if jQuery(this).is(':checked')
        jQuery('.schedule').slideDown()
      else
        jQuery('.schedule').slideUp()
    jQuery('#is_taxable').on 'change', ->
      if jQuery(this).is(':checked')
        jQuery('.tax_classes_field').slideDown()
      else
        jQuery('.tax_classes_field').slideUp()
    jQuery('.tax_classes_field .not-active').show()
    jQuery('#sales-from').datepicker
      todayBtn: 'linked'
      autoclose: true
    jQuery('#sales-to').datepicker
      todayBtn: 'linked'
      autoclose: true
    jQuery('.add-product-attachments')
      .on 'click', @updateAttachments
    jQuery(document)
      .ready @initAttachments

  changeProductType: (event) =>
    type = jQuery(event.target).val()
    jQuery('.jigoshop_product_data li').hide()
    for own tab, visibility of @params.menu
      if visibility == true or type in visibility
        jQuery('.jigoshop_product_data li.' + tab).show()
    jQuery('.jigoshop_product_data li:first a').tab('show')

  addAttribute: (event) =>
    event.preventDefault()
    $parent = jQuery('#product-attributes')
    $attribute = jQuery('#new-attribute')
    $label = jQuery('#new-attribute-label')
    value = parseInt($attribute.val())
    label = $label.val()
    if value < 0 and value != -1
      addMessage('warning', @params.i18n.invalid_attribute)
      return
    if value == -1 and label.length == 0
      addMessage('danger', @params.i18n.attribute_without_label, 6000)
      return
    $attribute.select2('val', '')
    $label.val('').slideUp()
    if value > 0
      jQuery("option[value=#{value}]", $attribute).attr('disabled', 'disabled')
    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product.save_attribute'
        product_id: $parent.closest('.jigoshop').data('id')
        attribute_id: value
        attribute_label: label
    .done (data) ->
      if data.success? and data.success
        jQuery(data.html).hide().appendTo($parent).slideDown()
      else
        addMessage('danger', data.error, 6000)
  updateAttribute: (event) =>
    $container = jQuery('#product-attributes')
    $parent = jQuery(event.target).closest('li.list-group-item')

    items = jQuery('.values input[type=checkbox]:checked', $parent).toArray()
    if items.length
      item = items.reduce(
        (value, current) ->
          current.value + '|' + value
        ''
      )
    else
      item = jQuery('.values select', $parent).val()
      if item == undefined
        item = jQuery('.values input', $parent).val()

    getOptionValue = (current) ->
      if current.type == 'checkbox' or current.type == 'radio'
        return current.checked
      return current.value

    options = {}
    optionsData = jQuery('.options input.attribute-options', $parent).toArray()
    for option in optionsData
      results = /(?:^|\s)product\[attributes]\[\d+]\[(.*?)](?:\s|$)/g.exec(option.name)
      options[results[1]] = getOptionValue(option)

    jQuery.ajax
      url: @params.ajax
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.product.save_attribute'
        product_id: $container.closest('.jigoshop').data('id')
        attribute_id: $parent.data('id')
        value: item
        options: options
    .done (data) =>
      if data.success? and data.success
        addMessage('success', @params.i18n.saved, 2000)
      else
        addMessage('danger', data.error, 6000)
  removeAttribute: (event) =>
    event.preventDefault()
    if confirm(@params.i18n.confirm_remove)
      $parent = jQuery(event.target).closest('li')
      jQuery('option[value=' + $parent.data('id') + ']', jQuery('#new-attribute')).removeAttr('disabled')
      jQuery.ajax
        url: @params.ajax
        type: 'post'
        dataType: 'json'
        data:
          action: 'jigoshop.admin.product.remove_attribute'
          product_id: $parent.closest('.jigoshop').data('id')
          attribute_id: $parent.data('id')
      .done (data) =>
        if data.success? and data.success
          $parent.slideUp -> $parent.remove()
          addMessage('success', @params.i18n.attribute_removed, 2000)
        else
          addMessage('danger', data.error, 6000)

  updateAttachments: (event) =>
    event.preventDefault()
    element = jQuery(event.target).data 'type'

    if(wpMedia)
      @wpMedia.open()
      return
    @wpMedia = wp.media {
      states: [
        new wp.media.controller.Library {
          filterable: 'all'
          multiple: true
        }
      ]
    }

    wpMedia = @wpMedia
    @wpMedia.on 'select', =>
      selection = wpMedia.state().get 'selection'
      attachmentIds = jQuery.map jQuery('input[name="product[attachments][' + element + '][]"]'), (attachment) ->
        return parseInt jQuery(attachment).val()

      selection.map (attachment) =>
        attachment = attachment.toJSON()
        if attachment.id?
          if element == 'gallery'
            options = {
              template_name: 'product-gallery'
              insert_before: '.empty-gallery'
              attachment_class: '.gallery-image'
            }
          else if element == 'downloads'
            options = {
              template_name: 'product-downloads'
              insert_before: '.empty-downloads'
              attachment_class: '.downloads-file'
            }
          @addAttachment attachment, attachmentIds, options

    wpMedia.open()

  initAttachments: =>
    console.log(@params.attachments)
    if @params.attachments.gallery?
      template = wp.template 'product-gallery'
      for attachment in @params.attachments.gallery
        jQuery('.empty-gallery').before template(attachment)
        @addHooks '', jQuery('.gallery-image').last()

    if @params.attachments.downloads?
      template = wp.template 'product-downloads'
      for attachment in @params.attachments.downloads
        jQuery('.empty-downloads').before template(attachment)
        @addHooks '', jQuery('.downloads-file').last()

  addHooks: (index, element) ->
    $delete = jQuery(element).find '.delete'
    jQuery(element). hover ->
      $delete.show()
    , ->
      $delete.hide()
    $delete.click ->
      jQuery(element).remove()

  addAttachment: (attachment, attachmentIds, options) =>
    if attachment.id and jQuery.inArray(attachment.id, attachmentIds) == -1
      template = wp.template options.template_name
      html = template {
        id: attachment.id
        url: if attachment.sizes and attachment.sizes.thumbnail then attachment.sizes.thumbnail.url else attachment.url
        title: attachment.title
      }
      jQuery(options.insert_before).before html
      @addHooks '', jQuery(options.attachment_class).last()

jQuery ->
  new AdminProduct(jigoshop_admin_product)