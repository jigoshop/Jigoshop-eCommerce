AdminProductCategories = undefined
AdminProductCategories = do ->

  AdminProductCategories = (params) ->
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
    jQuery('#parentId').on 'switchChange.bootstrapSwitch', self.attributesGetAttributes
    jQuery('#attributesInheritEnabled').on 'switchChange.bootstrapSwitch', (event, state) ->
      self.attributesInheritEnabledChange 1
      self.attributesGetAttributes()
      return
    jQuery('#attributesInheritMode').on 'switchChange.bootstrapSwitch', self.attributesGetAttributes
    self.attributesInheritEnabledChange()
    self.attributesGetAttributes()
    return

  AdminProductCategories::params =
    category_name: 'product_category'
    placeholder: ''

  AdminProductCategories::resetForm = ->
    jQuery('.jigoshop-product-categories-edit-form').find('input,select,textarea').each (index, element) ->
      jQuery(element).val ''
      return
    return

  AdminProductCategories::showForm = ->
    @bindThumbnailControls()
    if jQuery('.jigoshop-product-categories-edit-form').css('display') != 'block'
      jQuery('.jigoshop-product-categories-edit-form').slideToggle()
    jQuery('html,body').animate scrollTop: jQuery('.jigoshop-product-categories-edit-form').offset().top
    return

  AdminProductCategories::bindThumbnailControls = ->
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
    return

  AdminProductCategories::attributesInheritEnabledChange = (animate) ->
    state = jQuery('#attributesInheritEnabled').is(':checked')
    if state
      if animate
        jQuery('#jigoshop-product-categories-attributes-inherit-mode').slideToggle()
      else
        jQuery('#jigoshop-product-categories-attributes-inherit-mode').show()
    else
      jQuery('#jigoshop-product-categories-attributes-inherit-mode').hide()
    return

  AdminProductCategories::attributesGetAttributes = ->
    jQuery.post ajaxurl, {
      action: 'jigoshop_product_categories_getAttributes'
      id: jQuery('#id').val()
      parentId: jQuery('#parentId').val()
      inheritMode: jQuery('#attributesInheritMode').val()
    }, ((data) ->
      data.status = 1
    ), 'json'
    return

  AdminProductCategories
jQuery ->
  new AdminProductCategories