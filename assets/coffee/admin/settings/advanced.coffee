class AdvancedSettings
  keysCount: 0
  template: ''

  constructor: ->
    @keysCount = jQuery('#api-keys li').length
    jQuery('#api-keys').on 'click', '.generate', @generate
    jQuery('#api-keys').on 'click', '.toggle', @toggleGroupItem
    jQuery('#api-keys').on 'click', '.remove', @removeGroupItem
    jQuery('#api-keys').on 'keyup', '.user-id', @updateListHeader
    jQuery('#api-keys').on 'click', '.add-key', @addGroupItem


  toggleGroupItem: (event) ->
    $item = jQuery(event.target)
    jQuery('.list-group-item-text', $item.closest('li')).slideToggle ->
      jQuery('span', $item).toggleClass('glyphicon-collapse-down').toggleClass('glyphicon-collapse-up')

  removeGroupItem: (event) ->
    jQuery(event.target).closest('.list-group-item').remove()

  addGroupItem: (event) =>
    event.preventDefault()
    template = @getTemplate()
    jQuery('#api-keys .list-group').append(template(
      id: @keysCount
    ))
    @keysCount++
    jQuery('#api-keys select').last().select2()
    jQuery('#api-keys .generate').last().trigger('click')

  generate: (event) =>
    event.preventDefault()
    id = @generateUniqueId()
    key = @generateHexString(52)
    $item = jQuery event.target
    $item.closest('fieldset').find('input.user-id').val(id).trigger 'change'
    $item.closest('fieldset').find('input.key').val(key).trigger 'change'
    $item.closest('li').find('.title').html(id)

  generateUniqueId: () ->
    id = Math.round(Math.random() * 10000000000)
    if jQuery('#api-keys .user-id').filter((i, element) ->
      (parseInt(jQuery(element).val()) is id)
    ).length > 0
      id = @generateUniqueId()
    id

  generateHexString: (length) ->
    ret = ''
    while ret.length < length
      ret += Math.random().toString(16).substring(2)

    ret.substring(0,length)

  updateListHeader: (event) ->
    jQuery(event.target).closest('li').find('.title').html(jQuery(event.target).val())

  getTemplate: () ->
    if @template == ''
      @template = wp.template('api-key')
    @template
jQuery ->
  new AdvancedSettings()