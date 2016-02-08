var AdminProductTags;

AdminProductTags = (function() {
  AdminProductTags.prototype.params = {
    tag_name: 'product_tag',
    placeholder: ''
  };

  function AdminProductTags(params) {
    var $field, $thumbnail;
    this.params = params;
    $field = jQuery('#' + this.params.tag_name + '_thumbnail_id');
    $thumbnail = jQuery('#' + this.params.tag_name + '_thumbnail > img');
    jQuery('#add-image').jigoshop_media({
      field: $field,
      thumbnail: $thumbnail,
      callback: function() {
        if ($field.val() !== '') {
          return jQuery('#remove-image').show();
        }
      },
      library: {
        type: 'image'
      }
    });
    jQuery('#remove-image').on('click', (function(_this) {
      return function(e) {
        e.preventDefault();
        $field.val('');
        $thumbnail.attr('src', _this.params.placeholder);
        return jQuery(e.target).hide();
      };
    })(this));
  }

  return AdminProductTags;

})();

jQuery(function() {
  return new AdminProductTags(jigoshop_admin_product_tags);
});
