jQuery(function($) {
  $('span[data-toggle=tooltip]').tooltip();
  $('.not-active').closest('tr').hide();
  delay(3000, function() {
    return $('.settings-error.updated').slideUp(function() {
      return $(this).remove();
    });
  });
  delay(3000, function() {
    return $('.alert-success').slideUp(function() {
      return $(this).remove();
    });
  });
  delay(4000, function() {
    return $('.alert-warning').slideUp(function() {
      return $(this).remove();
    });
  });
  return delay(8000, function() {
    return $('.alert-error').slideUp(function() {
      return $(this).remove();
    });
  });
});
