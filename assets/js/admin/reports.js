jQuery(function($) {
  var all_widgets;
  $('li[data-toggle=tooltip]').tooltip();
  $('.input-daterange').datepicker({
    autoclose: true,
    todayHighlight: true,
    container: '#datepicker',
    orientation: 'top left',
    todayBtn: 'linked'
  });
  all_widgets = $('.chart-widget').click(function() {
    $(this).find('.content').slideDown(500);
    return all_widgets.not(this).find('.content').slideUp(500);
  });

  /*jQuery return */
});
