jQuery ($) ->
  jigoshop.delay 8000,  -> $('.alert-danger').slideUp ->
    $(this).remove()
  jigoshop.delay 4000,  -> $('.alert-success').slideUp ->
    $(this).remove()
