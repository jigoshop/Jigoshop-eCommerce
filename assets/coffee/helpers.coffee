jigoshop = {}
class JigoshopHelpers
  params:
    assets: ''
    ajaxUrl: ''

  constructor: (params) ->
    @params = params

  delay: (time, callback) ->
    setTimeout callback, time

  getAssetsUrl: ->
    @params.assets

  getAjaxUrl: ->
    @params.ajaxUrl

  addMessage: (type, message, ms) ->
    $alert = jQuery(document.createElement('div')).attr('class', "alert alert-#{type}").html(message).hide()
    $alert.appendTo(jQuery('#messages'))
    $alert.slideDown()
    jigoshop.delay ms, ->
      $alert.slideUp ->
        $alert.remove()

  block: ($element, options) ->
    sett = jQuery.extend {
      redirect: ''
      message: ''
      css:
        padding: '5px'
        width: 'auto'
        height: 'auto'
        border: '1px solid #83AC31'
      overlayCSS:
        backgroundColor: 'rgba(255, 255, 255, .8)'
    }, options
    $element.block
      message: '<img src="' + @params.assets + '/images/loading.gif" width="15" height="15" alt="' + sett.redirect + '"/>' + sett.message
      css: sett.css
      overlayCSS: sett.overlayCSS
  unblock: ($element) ->
    $element.unblock()
jQuery ->
  jigoshop = new JigoshopHelpers(jigoshop_helpers)