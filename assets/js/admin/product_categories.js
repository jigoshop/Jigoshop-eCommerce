var AdminProductCategories;

AdminProductCategories = (function() {
  AdminProductCategories.prototype.params = {
    category_name: 'product_category',
    placeholder: ''
  };

  function AdminProductCategories(params) {
    var $field, $thumbnail;
    this.params = params;
    $field = jQuery('#' + this.params.category_name + '_thumbnail_id');
    $thumbnail = jQuery('#' + this.params.category_name + '_thumbnail > img');
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

  return AdminProductCategories;

})();

jQuery(function() {
  return new AdminProductCategories(jigoshop_admin_product_categories);
});
