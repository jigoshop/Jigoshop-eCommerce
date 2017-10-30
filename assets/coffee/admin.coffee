jQuery ($) ->
  $('span[data-toggle=tooltip]').tooltip()
  $('.not-active').closest('tr').hide()
  jigoshop.delay 3000, -> $('.settings-error.updated').slideUp ->
    $(this).remove()
  jigoshop.delay 3000, -> $('.alert-success').not('.no-remove').slideUp ->
    $(this).remove()
  jigoshop.delay 4000, -> $('.alert-warning').not('.no-remove').slideUp ->
    $(this).remove()
  jigoshop.delay 8000, -> $('.alert-error').not('.no-remove').slideUp ->
    $(this).remove()
  jigoshop.delay 8000, -> $('.alert-danger').not('.no-remove').slideUp ->
    $(this).remove()

  $('.notice .disable-notice').on 'click', (event) ->
    $.ajax(
      url: jigoshop.getAjaxUrl()
      type: 'post'
      dataType: 'json'
      cache: true
      data:
        action: 'jigoshop.ajax.logged'
        service: 'jigoshop.ajax.disable_notice'
        notice: $(event.target).data('notice')
    ).done((data) ->
      if data.success? and data.success
        $(event.target).closest('.notice').fadeOut(1000)
    )