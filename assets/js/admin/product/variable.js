var AdminProductVariable,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

AdminProductVariable = (function() {
  AdminProductVariable.prototype.params = {
    ajax: '',
    i18n: {
      confirm_remove: '',
      variation_removed: ''
    }
  };

  function AdminProductVariable(params) {
    this.params = params;
    this.removeImage = bind(this.removeImage, this);
    this.connectImage = bind(this.connectImage, this);
    this.removeVariation = bind(this.removeVariation, this);
    this.updateVariation = bind(this.updateVariation, this);
    this.addVariation = bind(this.addVariation, this);
    jQuery('#product-type').on('change', this.removeParameters);
    jQuery('#add-variation').on('click', this.addVariation);
    jQuery('#product-variations').on('click', '.remove-variation', this.removeVariation).on('click', '.show-variation', function(event) {
      var $item;
      $item = jQuery(event.target);
      return jQuery('.list-group-item-text', $item.closest('li')).slideToggle(function() {
        return jQuery('span', $item).toggleClass('glyphicon-collapse-down').toggleClass('glyphicon-collapse-up');
      });
    }).on('change', 'select.variation-attribute', this.updateVariation).on('change', '.list-group-item-text input.form-control, .list-group-item-text select.form-control', this.updateVariation).on('click', '.set_variation_image', this.setImage).on('click', '.remove_variation_image', this.removeImage);
    jQuery('.set_variation_image').each(this.connectImage);
  }

  AdminProductVariable.prototype.removeParameters = function(event) {
    var $item;
    $item = jQuery(event.target);
    if ($item.val() === 'variable') {
      return jQuery('.product_regular_price_field').slideUp();
    }
  };

  AdminProductVariable.prototype.addVariation = function(event) {
    var $parent;
    event.preventDefault();
    $parent = jQuery('#product-variations');
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.product.add_variation',
        product_id: $parent.closest('.jigoshop').data('id')
      }
    }).done(function(data) {
      if ((data.success != null) && data.success) {
        return jQuery(data.html).hide().appendTo($parent).slideDown().trigger('jigoshop.variation.add');
      } else {
        return addMessage('danger', data.error, 6000);
      }
    });
  };

  AdminProductVariable.prototype.updateVariation = function(event) {
    var $container, $parent, attributes, attributesData, getOptionValue, i, j, len, len1, option, product, productData, results;
    $container = jQuery('#product-variations');
    $parent = jQuery(event.target).closest('li.list-group-item');
    getOptionValue = function(current) {
      if (current.type === 'checkbox' || current.type === 'radio') {
        return current.checked;
      }
      return current.value;
    };
    attributes = {};
    attributesData = jQuery('select.variation-attribute', $parent).toArray();
    for (i = 0, len = attributesData.length; i < len; i++) {
      option = attributesData[i];
      results = /(?:^|\s)product\[variation]\[\d+]\[attribute]\[(.*?)](?:\s|$)/g.exec(option.name);
      attributes[results[1]] = getOptionValue(option);
    }
    product = {};
    productData = jQuery('.list-group-item-text input.form-control', $parent).toArray();
    for (j = 0, len1 = productData.length; j < len1; j++) {
      option = productData[j];
      results = /(?:^|\s)product\[variation]\[\d+]\[product]\[(.*?)](\[(.*?)])?(?:\s|$)/g.exec(option.name);
      if (results[3] != null) {
        product[results[1]] = {};
        product[results[1]][results[3]] = getOptionValue(option);
      } else {
        product[results[1]] = getOptionValue(option);
      }
    }
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.product.save_variation',
        product_id: $container.closest('.jigoshop').data('id'),
        variation_id: $parent.data('id'),
        attributes: attributes,
        product: product
      }
    }).done((function(_this) {
      return function(data) {
        if ((data.success != null) && data.success) {
          $parent.trigger('jigoshop.variation.update');
          return addMessage('success', _this.params.i18n.saved, 2000);
        } else {
          return addMessage('danger', data.error, 6000);
        }
      };
    })(this));
  };

  AdminProductVariable.prototype.removeVariation = function(event) {
    var $parent;
    event.preventDefault();
    if (confirm(this.params.i18n.confirm_remove)) {
      $parent = jQuery(event.target).closest('li');
      return jQuery.ajax({
        url: this.params.ajax,
        type: 'post',
        dataType: 'json',
        data: {
          action: 'jigoshop.admin.product.remove_variation',
          product_id: $parent.closest('.jigoshop').data('id'),
          variation_id: $parent.data('id')
        }
      }).done((function(_this) {
        return function(data) {
          if ((data.success != null) && data.success) {
            $parent.trigger('jigoshop.variation.remove');
            $parent.slideUp(function() {
              return $parent.remove();
            });
            return addMessage('success', _this.params.i18n.variation_removed, 2000);
          } else {
            return addMessage('danger', data.error, 6000);
          }
        };
      })(this));
    }
  };

  AdminProductVariable.prototype.connectImage = function(index, element) {
    var $element, $remove, $thumbnail;
    $element = jQuery(element);
    $remove = $element.next('.remove_variation_image');
    $thumbnail = jQuery('img', $element.parent());
    return $element.jigoshop_media({
      field: false,
      bind: false,
      thumbnail: $thumbnail,
      callback: (function(_this) {
        return function(attachment) {
          $remove.show();
          return jQuery.ajax({
            url: _this.params.ajax,
            type: 'post',
            dataType: 'json',
            data: {
              action: 'jigoshop.admin.product.set_variation_image',
              product_id: $element.closest('.jigoshop').data('id'),
              variation_id: $element.closest('.variation').data('id'),
              image_id: attachment.id
            }
          }).done(function(data) {
            if ((data.success == null) || !data.success) {
              return addMessage('danger', data.error, 6000);
            }
          });
        };
      })(this),
      library: {
        type: 'image'
      }
    });
  };

  AdminProductVariable.prototype.setImage = function(event) {
    event.preventDefault();
    return jQuery(event.target).trigger('jigoshop_media');
  };

  AdminProductVariable.prototype.removeImage = function(event) {
    var $element, $thumbnail;
    event.preventDefault();
    $element = jQuery(event.target);
    $thumbnail = jQuery('img', $element.parent());
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.product.set_variation_image',
        product_id: $element.closest('.jigoshop').data('id'),
        variation_id: $element.closest('.variation').data('id'),
        image_id: -1
      }
    }).done(function(data) {
      if ((data.success != null) && data.success) {
        $thumbnail.attr('src', data.url).attr('width', 150).attr('height', 150);
        return $element.hide();
      } else {
        return addMessage('danger', data.error, 6000);
      }
    });
  };

  return AdminProductVariable;

})();

jQuery(function() {
  return new AdminProductVariable(jigoshop_admin_product_variable);
});
