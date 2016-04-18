jQuery(function($) {
  $('span[data-toggle=tooltip]').tooltip();
  $('.not-active').closest('tr').hide();
  jigoshop.delay(3000, function() {
    return $('.settings-error.updated').slideUp(function() {
      return $(this).remove();
    });
  });
  jigoshop.delay(3000, function() {
    return $('.alert-success').not('.no-remove').slideUp(function() {
      return $(this).remove();
    });
  });
  jigoshop.delay(4000, function() {
    return $('.alert-warning').not('.no-remove').slideUp(function() {
      return $(this).remove();
    });
  });
  jigoshop.delay(8000, function() {
    return $('.alert-error').not('.no-remove').slideUp(function() {
      return $(this).remove();
    });
  });
  return jigoshop.delay(8000, function() {
    return $('.alert-danger').not('.no-remove').slideUp(function() {
      return $(this).remove();
    });
  });
});
