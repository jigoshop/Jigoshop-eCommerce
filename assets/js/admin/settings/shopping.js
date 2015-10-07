jQuery(function($) {
  $('#restrict_selling_locations').on('change', function() {
    return $('#selling_locations').closest('tr').toggle();
  });
  return $('#selling_locations').show().closest('div.form-group').show();
});
