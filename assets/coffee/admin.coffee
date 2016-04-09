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
