var addMessage, delay;

delay = function(time, callback) {
  return setTimeout(callback, time);
};

addMessage = function(type, message, ms) {
  var $alert;
  $alert = jQuery(document.createElement('div')).attr('class', "alert alert-" + type).html(message).hide();
  $alert.appendTo(jQuery('#messages'));
  $alert.slideDown();
  return delay(ms, function() {
    return $alert.slideUp(function() {
      return $alert.remove();
    });
  });
};
