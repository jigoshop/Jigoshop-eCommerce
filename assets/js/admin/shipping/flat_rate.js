jQuery(function($) {
  $('div.flat_rate_countries_field').show();
  return $('#flat_rate_available_for').on('change', function() {
    if ($(this).val() === 'specific') {
      return $('#flat_rate_countries').closest('tr').show();
    } else {
      return $('#flat_rate_countries').closest('tr').hide();
    }
  });
});
