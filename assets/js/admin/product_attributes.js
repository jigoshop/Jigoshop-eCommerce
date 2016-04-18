var AdminProductAttributes,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

AdminProductAttributes = (function() {
  AdminProductAttributes.prototype.params = {
    ajax: '',
    i18n: {
      saved: '',
      removed: '',
      option_removed: '',
      confirm_remove: ''
    }
  };

  function AdminProductAttributes(params) {
    this.params = params;
    this.removeAttributeOption = bind(this.removeAttributeOption, this);
    this.updateAttributeOption = bind(this.updateAttributeOption, this);
    this.addAttributeOption = bind(this.addAttributeOption, this);
    this.removeAttribute = bind(this.removeAttribute, this);
    this.updateAttribute = bind(this.updateAttribute, this);
    this.addAttribute = bind(this.addAttribute, this);
    jQuery('#add-attribute').on('click', this.addAttribute);
    jQuery('table#product-attributes > tbody').on('click', '.remove-attribute', this.removeAttribute).on('change', '.attribute input, .attribute select', this.updateAttribute).on('click', '.configure-attribute, .options button', this.configureAttribute).on('click', '.remove-attribute-option', this.removeAttributeOption).on('click', '.add-option', this.addAttributeOption).on('change', '.options tbody input', this.updateAttributeOption);
    this.$newLabel = jQuery('#attribute-label');
    this.$newSlug = jQuery('#attribute-slug');
    this.$newType = jQuery('#attribute-type');
  }

  AdminProductAttributes.prototype.addAttribute = function(event) {
    var $container;
    $container = jQuery('tbody', jQuery(event.target).closest('table'));
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.product_attributes.save',
        label: this.$newLabel.val(),
        slug: this.$newSlug.val(),
        type: this.$newType.val()
      }
    }).done((function(_this) {
      return function(data) {
        if ((data.success != null) && data.success) {
          _this.$newLabel.val('');
          _this.$newSlug.val('');
          _this.$newType.val('0').trigger('change');
          return jQuery(data.html).appendTo($container);
        } else {
          return jigoshop.addMessage('danger', data.error, 6000);
        }
      };
    })(this));
  };

  AdminProductAttributes.prototype.updateAttribute = function(event) {
    var $parent;
    $parent = jQuery(event.target).closest('tr');
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.product_attributes.save',
        id: $parent.data('id'),
        label: jQuery('input.attribute-label', $parent).val(),
        slug: jQuery('input.attribute-slug', $parent).val(),
        type: jQuery('select.attribute-type', $parent).val()
      }
    }).done((function(_this) {
      return function(data) {
        if ((data.success != null) && data.success) {
          $parent.replaceWith(data.html);
          return jigoshop.addMessage('success', _this.params.i18n.saved, 2000);
        } else {
          return jigoshop.addMessage('danger', data.error, 6000);
        }
      };
    })(this));
  };

  AdminProductAttributes.prototype.removeAttribute = function(event) {
    var $parent;
    if (confirm(this.params.i18n.confirm_remove)) {
      $parent = jQuery(event.target).closest('tr');
      return jQuery.ajax({
        url: this.params.ajax,
        type: 'post',
        dataType: 'json',
        data: {
          action: 'jigoshop.admin.product_attributes.remove',
          id: $parent.data('id')
        }
      }).done((function(_this) {
        return function(data) {
          if ((data.success != null) && data.success) {
            $parent.remove();
            return jigoshop.addMessage('success', _this.params.i18n.removed, 2000);
          } else {
            return jigoshop.addMessage('danger', data.error, 6000);
          }
        };
      })(this));
    }
  };

  AdminProductAttributes.prototype.configureAttribute = function(event) {
    var $options, $parent;
    $parent = jQuery(event.target).closest('tr');
    return $options = jQuery('tr.options[data-id=' + $parent.data('id') + ']').toggle();
  };

  AdminProductAttributes.prototype.addAttributeOption = function(event) {
    var $container, $label, $parent, $value;
    $parent = jQuery(event.target).closest('tr.options');
    $container = jQuery('tbody', $parent);
    $label = jQuery('input.new-option-label', $parent);
    $value = jQuery('input.new-option-value', $parent);
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.product_attributes.save_option',
        attribute_id: $parent.data('id'),
        label: $label.val(),
        value: $value.val()
      }
    }).done(function(data) {
      if ((data.success != null) && data.success) {
        $label.val('');
        $value.val('');
        return jQuery(data.html).appendTo($container);
      } else {
        return jigoshop.addMessage('danger', data.error, 6000);
      }
    });
  };

  AdminProductAttributes.prototype.updateAttributeOption = function(event) {
    var $parent;
    $parent = jQuery(event.target).closest('tr');
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.product_attributes.save_option',
        id: $parent.data('id'),
        attribute_id: $parent.closest('tr.options').data('id'),
        label: jQuery('input.option-label', $parent).val(),
        value: jQuery('input.option-value', $parent).val()
      }
    }).done((function(_this) {
      return function(data) {
        if ((data.success != null) && data.success) {
          $parent.replaceWith(data.html);
          return jigoshop.addMessage('success', _this.params.i18n.saved, 2000);
        } else {
          return jigoshop.addMessage('danger', data.error, 6000);
        }
      };
    })(this));
  };

  AdminProductAttributes.prototype.removeAttributeOption = function(event) {
    var $parent;
    if (confirm(this.params.i18n.confirm_remove)) {
      $parent = jQuery(event.target).closest('tr');
      return jQuery.ajax({
        url: this.params.ajax,
        type: 'post',
        dataType: 'json',
        data: {
          action: 'jigoshop.admin.product_attributes.remove_option',
          id: $parent.data('id'),
          attribute_id: $parent.closest('tr.options').data('id')
        }
      }).done((function(_this) {
        return function(data) {
          if ((data.success != null) && data.success) {
            $parent.remove();
            return jigoshop.addMessage('success', _this.params.i18n.option_removed, 2000);
          } else {
            return jigoshop.addMessage('danger', data.error, 6000);
          }
        };
      })(this));
    }
  };

  return AdminProductAttributes;

})();

jQuery(function() {
  return new AdminProductAttributes(jigoshop_admin_product_attributes);
});
