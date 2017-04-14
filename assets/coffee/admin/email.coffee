class AdminEmail
  wpMedia: false

  constructor: ->
    jQuery('#jigoshop_email_actions').on 'change', @updateVariables
    jQuery('#email-attachments').on 'click', '.delete', (event) ->
      jQuery(event.target).parent().remove()
    jQuery('.add-email-attachments').on 'click', @addAttachments

  updateVariables: (event) ->
    event.preventDefault()
    $parent = jQuery(event.target).closest('div.jigoshop')
    jQuery.ajax
      url: jigoshop.getAjaxUrl()
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.email.update_variable_list'
        email: $parent.data('id')
        actions: jQuery(event.target).val()
    .done (data) ->
      if data.success? and data.success
        jQuery('#available_arguments').replaceWith(data.html)
      else
        jigoshop.addMessage('danger', data.error, 6000)
  addAttachments: (event) =>
    event.preventDefault()
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
    @wpMedia.on 'select', ->
      selection = wpMedia.state().get 'selection'
      attachmentIds = jQuery.map jQuery('input[name="jigoshop_email[attachments][]"]'), (attachment) ->
        return parseInt jQuery(attachment).val()

      selection.map (attachment) ->
        attachment = attachment.toJSON()
        if attachment.id and jQuery.inArray(attachment.id, attachmentIds) == -1
          template = wp.template 'product-downloads'
          jQuery('#email-attachments').append template(
            id: attachment.id
            title: attachment.filename
          )
    wpMedia.open()
jQuery ->
  new AdminEmail()
