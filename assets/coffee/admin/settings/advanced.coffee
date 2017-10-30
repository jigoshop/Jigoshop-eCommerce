class AdvancedSettings
  usersCount: 0
  template: ''

  constructor: ->
    @usersCount = jQuery('#api-users li').length
    jQuery('#api-users').on 'click', '.generate', @generateUserDetails
    jQuery('#api-users').on 'click', '.toggle', @toggleGroupItem
    jQuery('#api-users').on 'click', '.remove', @removeGroupItem
    jQuery('#api-users').on 'keyup', '.login', @updateListHeader
    jQuery('#api-users').on 'click', '.add-user', @addGroupItem
    jQuery('#api-secret').on 'click', '.generate', @generateSecret


  toggleGroupItem: (event) ->
    $item = jQuery(event.target)
    jQuery('.list-group-item-text', $item.closest('li')).slideToggle ->
      jQuery('span', $item).toggleClass('glyphicon-collapse-down').toggleClass('glyphicon-collapse-up')

  removeGroupItem: (event) ->
    jQuery(event.target).closest('.list-group-item').remove()

  addGroupItem: (event) =>
    event.preventDefault()
    template = @getTemplate()
    jQuery('#api-users .list-group').append(template(
      id: @usersCount
    ))
    @usersCount++
    jQuery('#api-users select').last().select2()
    jQuery('#api-users .generate').last().trigger('click')

  generateUserDetails: (event) =>
    event.preventDefault()
    login = @generateString(16)
    password = @generateString(52)
    $item = jQuery event.target
    $item.closest('fieldset').find('input.login').val(login).trigger 'change'
    $item.closest('fieldset').find('input.password').val(password).trigger 'change'
    $item.closest('li').find('.title').html(login)

  generateSecret: (event) =>
    event.preventDefault()
    $item = jQuery event.target
    $item.closest('div').find('input').val(@generateString(52)).trigger 'change'

  generateString: (length) ->
    ret = ''
    while ret.length < length
      ret += Math.random().toString(16).substring(2)

    ret.substring(0,length)

  updateListHeader: (event) ->
    jQuery(event.target).closest('li').find('.title').html(jQuery(event.target).val())

  getTemplate: () ->
    if @template == ''
      @template = wp.template('api-user')
    @template
jQuery ->
  new AdvancedSettings()