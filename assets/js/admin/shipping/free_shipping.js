jQuery(function($) {
  $('div.free_shipping_countries_field').show();
  return $('#free_shipping_available_for').on('change', function() {
    if ($(this).val() === 'specific') {
      return $('#free_shipping_countries').closest('tr').show();
    } else {
      return $('#free_shipping_countries').closest('tr').hide();
    }
  });
});
