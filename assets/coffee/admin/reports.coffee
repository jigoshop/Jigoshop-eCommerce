jQuery ($) ->
  $('li[data-toggle=tooltip]')
    .tooltip()

  $('.input-daterange').datepicker
    autoclose: true
    todayHighlight: true
    container: '#datepicker'
    orientation: 'top left'
    todayBtn: 'linked'

  all_widgets = $('.chart-widget').click ->
    $(this).find('.content').slideDown 500
    all_widgets.not(this).find('.content').slideUp 500
  ###jQuery return###
  return