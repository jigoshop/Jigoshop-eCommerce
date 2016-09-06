class AdvancedSettings
  keysCount: 0
  template: ''

  constructor: ->
    @keysCount = jQuery('#api-keys li').length
    jQuery('#api-keys').on 'click', '.generate', @generateKey
    jQuery('#api-keys').on 'click', '.toggle', @toggleKey
    jQuery('#api-keys').on 'click', '.remove', @removeKey
    jQuery('#api-keys').on 'keyup', 'input', @updateListHeader
    jQuery('#api-keys').on 'click', '.add-key', @addKey


  toggleKey: (event) ->
    $item = jQuery(event.target)
    jQuery('.list-group-item-text', $item.closest('li')).slideToggle ->
      jQuery('span', $item).toggleClass('glyphicon-collapse-down').toggleClass('glyphicon-collapse-up')

  removeKey: (event) ->
    jQuery(event.target).closest('.list-group-item').remove()

  generateKey: (event) =>
    event.preventDefault()
    key = @generateHexString(40)
    $item = jQuery event.target
    $item.parent().parent().find('input').val(key)
    $item.closest('li').find('.title').html(key)

  generateHexString: (length) ->
    ret = ''
    while ret.length < length
      ret += Math.random().toString(16).substring(2)

    ret.substring(0,length)

  updateListHeader: (event) ->
    jQuery(event.target).closest('li').find('.title').html(jQuery(event.target).val())

  addKey: (event) =>
    event.preventDefault()
    template = @getTemplate()
    jQuery('#api-keys .list-group').append(template(
      id: @keysCount
    ))
    @keysCount++
    jQuery('#api-keys select').last().select2()
    jQuery('#api-keys .generate').last().trigger('click')

  getTemplate: () ->
    if @template == ''
      @template = wp.template('api-key')
    @template
jQuery ->
  new AdvancedSettings()