jigoshop = {}
jigoshop.delay = delay = (time, callback) -> setTimeout callback, time
jigoshop.addMessage = addMessage = (type, message, ms) ->
  $alert = jQuery(document.createElement('div')).attr('class', "alert alert-#{type}").html(message).hide()
  $alert.appendTo(jQuery('#messages'))
  $alert.slideDown()
  jigoshop.delay ms, ->
    $alert.slideUp ->
      $alert.remove()