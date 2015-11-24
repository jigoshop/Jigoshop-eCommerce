jQuery ($) ->
  $('li[data-toggle=tooltip]')
    .tooltip()

  $('.input-daterange').datepicker
    autoclose: true
    container: '#datepicker'
    orientation: 'top left',

  #jQuery return
  return