jQuery ($) ->
  jigoshop.delay 8000,  -> $('.alert-danger').not('.no-remove').slideUp ->
    $(this).remove()
  jigoshop.delay 4000,  -> $('.alert-success').not('.no-remove').slideUp ->
    $(this).remove()
