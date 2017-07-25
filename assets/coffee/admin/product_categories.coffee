AdminProductCategories = undefined
AdminProductCategories = do ->

  AdminProductCategories = (params) ->
    self = undefined
    self = this
    jQuery('.jigoshop-product-categories-expand-subcategories').click (e) ->
      categoryId = undefined
      e.preventDefault()
      categoryId = jQuery(e.delegateTarget).parents('tr').data('category-id')
      jQuery('#jigoshop-product-categories').find('tr').each (index, element) ->
        if jQuery(element).data('parent-category-id') == categoryId
          if jQuery(element).css('display') == 'none'
            jQuery(element).show()
          else
            jQuery(element).hide()
        return
      return
    jQuery('#jigoshop-product-categories-add-button').click (e) ->
      e.preventDefault()
      self.resetForm()
      self.showForm()
      return
    jQuery('.jigoshop-product-categories-edit-button').click (e) ->
      categoryId = undefined
      e.preventDefault()
      categoryId = jQuery(e.delegateTarget).parents('tr').data('category-id')
      if !categoryId
        return

      self.select2 = jQuery.fn.select2
      self.jigoshop_media = jQuery.fn.jigoshop_media
      self.bootstrapSwitch = jQuery.fn.bootstrapSwitch
      self.magnificPopup = jQuery.magnificPopup

      jQuery.post ajaxurl, {
        action: 'jigoshop_product_categories_getEditForm'
        categoryId: categoryId
      }, ((data) ->
        if data.status == 1
          jQuery('#jigoshop-product-categories-edit-form-content').replaceWith data.form
          self.showForm()
        return
      ), 'json'
      return
    jQuery('.jigoshop-product-categories-remove-button').click (e) ->
      categoryId = undefined
      e.preventDefault()
      if confirm(jigoshop_admin_product_categories_data['lang']['categoryRemovalConfirmation'])
        categoryId = jQuery(e.delegateTarget).parents('tr').data('category-id')
        jQuery.post ajaxurl, {
          action: 'jigoshop_product_categories_removeCategory'
          categoryId: categoryId
        }, ((data) ->
          if data.status == 1
            location.href = document.URL
          return
        ), 'json'
      return
    jQuery('.jigoshop-product-categories-edit-form').submit (e) ->
      fields = undefined
      e.preventDefault()
      jQuery('.jigoshop-product-categories-edit-form').find('button').attr 'disabled', 'disabled'
      fields = {}
      jQuery('.jigoshop-product-categories-edit-form').find('input,select,textarea').each (index, element) ->
        if !jQuery(element).attr('name')
          return
        if jQuery(element).attr('type') == 'checkbox'
          fields[jQuery(element).attr('name')] = jQuery(element).is(':checked')
          return
        fields[jQuery(element).attr('name')] = jQuery(element).val()
        return
      fields['action'] = 'jigoshop_product_categories_updateCategory'
      jQuery.post ajaxurl, fields, ((data) ->
        if data.status == 1
          location.href = document.URL
        else
          jigoshop.addMessage 'danger', data.error, 3000
          jQuery('.jigoshop-product-categories-edit-form').find('button').removeAttr 'disabled'
        return
      ), 'json'
      return
    return

  AdminProductCategories::params =
    category_name: 'product_category'
    placeholder: ''

  AdminProductCategories::resetForm = ->
    jQuery('.jigoshop-product-categories-edit-form').find('input,select,textarea').each (index, element) ->
      if jQuery(element).closest('.description_field').length == 0
        jQuery(element).val ''

    jQuery('#description').val('')
    return

  AdminProductCategories::showForm = ->
    @bindGeneralControls()
    @attributesInheritEnabledChange()
    @attributesGetAttributes()
    if jQuery('.jigoshop-product-categories-edit-form').css('display') != 'block'
      jQuery('.jigoshop-product-categories-edit-form').slideToggle()
    jQuery('html,body').animate scrollTop: jQuery('.jigoshop-product-categories-edit-form').offset().top
    jQuery('.jigoshop-product-categories-edit-form').find('button').removeAttr 'disabled'
    return

  AdminProductCategories::bindGeneralControls = ->
    self = this
    jQuery('.jigoshop-product-categories-edit-form').find('input[type="checkbox"]').each (index, element) ->
      if typeof jQuery.fn.bootstrapSwitch == "undefined"
        jQuery.fn.bootstrapSwitch = self.bootstrapSwitch
      jQuery(element).bootstrapSwitch
        size: 'small'
        onText: 'Yes'
        offText: 'No'
      return
    jQuery('#parentId').on 'change', self.attributesGetAttributes
    if typeof jQuery.fn.jigoshop_media == "undefined"
      jQuery.fn.jigoshop_media = self.jigoshop_media
    jQuery('#jigoshop-product-categories-thumbnail-add-button').jigoshop_media
      field: jQuery('#thumbnailId')
      thumbnail: jQuery('#jigoshop-product-categories-thumbnail').find('img')
      callback: ->
        if jQuery('#thumbnailId').val() != ''
          return jQuery('#jigoshop-product-categories-thumbnail-remove-button').css('display', 'inline-block')
        return
      library: type: 'image'
    jQuery('#jigoshop-product-categories-thumbnail-remove-button').click (e) ->
      e.preventDefault()
      jQuery('#thumbnailId').val ''
      jQuery('#jigoshop-product-categories-thumbnail img').attr 'src', jigoshop_admin_product_categories_data['thumbnailPlaceholder']
      jQuery('#jigoshop-product-categories-thumbnail-remove-button').hide()
      return
    if jQuery('#thumbnailId').val() != ''
      jQuery('#jigoshop-product-categories-thumbnail-remove-button').css 'display', 'inline-block'
    jQuery('#attributesInheritEnabled').on 'switchChange.bootstrapSwitch', (event, state) ->
      self.attributesInheritEnabledChange 1
      self.attributesGetAttributes()
      return
    jQuery('#attributesInheritMode').on 'change', (e) ->
      self.attributesGetAttributes()
      return
    if typeof jQuery.fn.select2 == "undefined"
      jQuery.fn.select2 = self.select2
    jQuery('#parentId, #attributesInheritMode, #attributesNewSelector').select2()
    jQuery('#jigoshop-product-categories-attributes-add-button').click (e) ->
      e.preventDefault()
      addedAttributes = jQuery('#attributesNewSelector').val()
      if addedAttributes == null or addedAttributes.length == 0
        return
      self.attributesGetAttributes addedAttributes

      if typeof jQuery.fn.select2 == 'undefined'
        jQuery.fn.select2 = self.select2

      jQuery('#attributesNewSelector').select2 'val', ''
      return

    jQuery('#jigoshop-product-categories-attributes-add-new-button').click(self.attributesAddNewForm.bind(this))

    if typeof jQuery.fn.select2 == 'undefined'
      jQuery.fn.select2 = self.select2

    if typeof jQuery.magnificPopup == 'undefined'
      jQuery.magnificPopup = self.magnificPopup
    jQuery('#parentId, #attributesInheritMode, #attributesNewSelector').select2()
    return

  AdminProductCategories::attributesInheritEnabledChange = (animate) ->
    state = undefined
    state = jQuery('#attributesInheritEnabled').is(':checked')
    if state
      if animate
        jQuery('#jigoshop-product-categories-attributes-inherit-mode').slideToggle()
      else
        jQuery('#jigoshop-product-categories-attributes-inherit-mode').show()
    else
      jQuery('#jigoshop-product-categories-attributes-inherit-mode').hide()
    return

  AdminProductCategories::attributesGetAttributes = (addedAttributes, removedAttributeId) ->
    self = this
    existingAttributes = {}
    jQuery('#jigoshop-product-categories-attributes').find('tbody').find('tr').each (index, element) ->
      if jQuery(element).data('attribute-inherited')
        return
      existingAttribute = enabled: jQuery(element).find('input[type="checkbox"]').is(':checked')
      existingAttributes[jQuery(element).data('attribute-id')] = existingAttribute
      return
    jQuery.post ajaxurl, {
      action: 'jigoshop_product_categories_getAttributes'
      id: jQuery('#id').val()
      parentId: jQuery('#parentId').val()
      inheritEnabled: jQuery('#attributesInheritEnabled').is(':checked')
      inheritMode: jQuery('#attributesInheritMode').val()
      existingAttributes: existingAttributes
      addedAttributes: addedAttributes
      removedAttributeId: removedAttributeId
    }, ((data) ->
      if data.status == 1
        jQuery('#jigoshop-product-categories-attributes').find('tbody').html data.attributes
        jQuery('#jigoshop-product-categories-attributes').find('input[type="checkbox"]').each (index, element) ->
          jQuery(element).bootstrapSwitch
            size: 'small'
            onText: 'Yes'
            offText: 'No'
          return
        jQuery('.attributeRemoveButton').click (e) ->
          e.preventDefault()
          removedAttributeId = jQuery(e.delegateTarget).parents('tr').data('attribute-id')
          jQuery(e.delegateTarget).parents('tr').remove()
          self.attributesGetAttributes [], removedAttributeId
          return
        jQuery('#attributesNewSelector').html ''
        jQuery.each data.attributesPossibleToAdd, (key, value) ->
          jQuery('#attributesNewSelector').append new Option(value, key)
          return
      return
    ), 'json'
    return

  AdminProductCategories::attributesAddNewForm = (e) ->
    self = this
    e.preventDefault()
    jQuery.magnificPopup.open
      mainClass: 'jigoshop'
      closeOnContentClick: false
      closeOnBgClick: false
      closeBtnInside: true
      showCloseBtn: true
      enableEscapeKey: true
      modal: true
      items: src: jQuery('#jigoshop-product-categories-attributes-add-new-container')
      type: 'inline'
      callbacks:
        open: ->
          jQuery('#jigoshop-product-categories-attributes-add-new-type').on('change', self.attributesAddNewTypeChanged).trigger 'change'
          jQuery('#jigoshop-product-categories-attributes-add-new-configure-button').click self.attributesAddNewConfigure
          jQuery('#jigoshop-product-categories-attributes-add-new-configure-container').find('.attribute-option-add-button')
            .click self.attributesAddOption.bind(self)
          jQuery('#jigoshop-product-categories-attributes-add-new-configure-container').find('.attribute-option-remove-button').hide()
          jQuery('#jigoshop-product-categories-attributes-add-new-form').submit self.attributesAddNewSave.bind(self)
          jQuery('#jigoshop-product-categories-attributes-add-new-container').css 'display', 'block'
          return
    return

  AdminProductCategories::attributesAddNewTypeChanged = ->
    attributeType = undefined
    display = undefined
    attributeType = jQuery('#jigoshop-product-categories-attributes-add-new-type').val()
    if attributeType == 2
      display = 'none'
    else
      display = 'block'
    jQuery('#jigoshop-product-categories-attributes-add-new-configure-button').css 'display', display
    if jQuery('#jigoshop-product-categories-attributes-add-new-configure-container').css('display') == 'block' and display == 'none'
      jQuery('#jigoshop-product-categories-attributes-add-new-configure-container').css 'display', 'block'
    return

  AdminProductCategories::attributesAddNewConfigure = (e) ->
    e.preventDefault()
    jQuery('#jigoshop-product-categories-attributes-add-new-configure-container').show()
    return

  AdminProductCategories::attributesAddOption = (e) ->
    self = undefined
    prototype = undefined
    self = this
    e.preventDefault()
    prototype = jQuery('#jigoshop-product-categories-attributes-add-new-configure-container').find('#attribute-option-prototype')
    if prototype.find('#option-label').val() == '' or prototype.find('#option-value').val() == ''
      return
    option = prototype.clone()
    option.removeAttr 'id'
    option.find('.attribute-option-add-button').remove()
    option.find('.attribute-option-remove-button').show()
    option.find('.attribute-option-remove-button').click self.attributesRemoveOption.bind(self)
    jQuery('#jigoshop-product-categories-attributes-add-new-configure-container').prepend option
    prototype.find('#option-label').val ''
    prototype.find('#option-value').val ''
    return

  AdminProductCategories::attributesRemoveOption = (e) ->
    self = this
    e.preventDefault()
    jQuery(e.delegateTarget).parents('tr').remove()
    return

  AdminProductCategories::attributesAddNewSave = (e) ->
    self = undefined
    form = undefined
    label = undefined
    slug = undefined
    type = undefined
    options = undefined
    self = this
    e.preventDefault()
    form = jQuery('#jigoshop-product-categories-attributes-add-new-form')
    label = jQuery('#jigoshop-product-categories-attributes-add-new-label').val()
    slug = jQuery('#jigoshop-product-categories-attributes-add-new-slug').val()
    type = jQuery('#jigoshop-product-categories-attributes-add-new-type').val()
    options = []
    jQuery('#jigoshop-product-categories-attributes-add-new-configure-container').find('tr').each (index, element) ->
      option = undefined
      if jQuery(element).attr('id') == 'attribute-option-prototype'
        return
      option =
        label: jQuery(element).find('#option-label').val()
        value: jQuery(element).find('#option-value').val()
      options.push option
      return
    if !label or options.length == 0
      return
    jQuery('#jigoshop-product-categories-attributes-add-new-button').attr 'disabled', 'disabled'
    jQuery.post ajaxurl, {
      action: 'jigoshop_product_categories_saveAttribute'
      categoryId: jQuery('#id').val()
      label: label
      slug: slug
      type: type
      options: options
    }, ((data) ->
      if data.status == 1
        self.attributesGetAttributes [ data.attributeId ]
        jQuery.magnificPopup.close()
      jQuery('#jigoshop-product-categories-attributes-add-new-button').removeAttr 'disabled'
      return
    ), 'json'
    return

  AdminProductCategories
jQuery ->
  new AdminProductCategories