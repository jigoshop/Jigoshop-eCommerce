jigoshop = {}
jigoshop.delay = delay = (time, callback) -> setTimeout callback, time
jigoshop.addMessage = addMessage = (type, message, ms) ->
  $alert = jQuery(document.createElement('div')).attr('class', "alert alert-#{type}").html(message).hide()
  $alert.appendTo(jQuery('#messages'))
  $alert.slideDown()
  jigoshop.delay ms, ->
    $alert.slideUp ->
      $alert.remove()
jigoshop.blockUiStyle = (params) ->
  return {
    message: '<img src="' + params.assets + '/images/loading.gif" width="15" height="15" />'
    css:
      padding: '5px'
      width: 'auto'
      height: 'auto'
      border: '1px solid #83AC31'
    overlayCSS:
      backgroundColor: 'rgba(255, 255, 255, .8)'
  }