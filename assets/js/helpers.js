var addMessage, delay, jigoshop;

jigoshop = {};

jigoshop.delay = delay = function(time, callback) {
  return setTimeout(callback, time);
};

jigoshop.addMessage = addMessage = function(type, message, ms) {
  var $alert;
  $alert = jQuery(document.createElement('div')).attr('class', "alert alert-" + type).html(message).hide();
  $alert.appendTo(jQuery('#messages'));
  $alert.slideDown();
  return jigoshop.delay(ms, function() {
    return $alert.slideUp(function() {
      return $alert.remove();
    });
  });
};

jigoshop.blockUiStyle = function(params) {
  return {
    message: '<img src="' + params.assets + '/images/loading.gif" width="15" height="15" />',
    css: {
      padding: '5px',
      width: 'auto',
      height: 'auto',
      border: '1px solid #83AC31'
    },
    overlayCSS: {
      backgroundColor: 'rgba(255, 255, 255, .8)'
    }
  };
};
