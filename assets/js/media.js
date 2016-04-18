jQuery(function() {
  return jQuery.fn.jigoshop_media = function(options) {
    var frame, settings;
    frame = false;
    settings = jQuery.extend({
      field: jQuery('#media-library-file'),
      thumbnail: false,
      callback: false,
      library: {},
      bind: true
    }, options);
    jQuery(this).on('jigoshop_media', function(e) {
      var $el;
      e.preventDefault();
      $el = jQuery(e.target);
      if (frame) {
        frame.open();
        return;
      }
      frame = wp.media({
        title: $el.data('title'),
        library: settings.library,
        button: {
          text: $el.data('button')
        }
      });
      frame.on('select', function() {
        var attachment;
        attachment = frame.state().get('selection').first();
        if (settings.field) {
          settings.field.val(attachment.id);
        }
        if (settings.thumbnail) {
          settings.thumbnail.attr('src', attachment.changed.url).attr('width', attachment.changed.width).attr('height', attachment.changed.height);
        }
        if (typeof settings.callback === 'function') {
          return settings.callback(attachment);
        }
      });
      return frame.open();
    });
    if (settings.bind) {
      return jQuery(this).on('click', function(event) {
        event.preventDefault();
        return jQuery(event.target).trigger('jigoshop_media');
      });
    }
  };
});
