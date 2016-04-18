var AdminProduct,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
  hasProp = {}.hasOwnProperty,
  indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

AdminProduct = (function() {
  AdminProduct.prototype.params = {
    ajax: '',
    i18n: {
      saved: '',
      confirm_remove: '',
      attribute_removed: '',
      invalid_attribute: '',
      attribute_without_label: ''
    },
    menu: {},
    attachments: {}
  };

  AdminProduct.prototype.wpMedia = false;

  function AdminProduct(params) {
    this.params = params;
    this.addAttachment = bind(this.addAttachment, this);
    this.initAttachments = bind(this.initAttachments, this);
    this.updateAttachments = bind(this.updateAttachments, this);
    this.removeAttribute = bind(this.removeAttribute, this);
    this.updateAttribute = bind(this.updateAttribute, this);
    this.addAttribute = bind(this.addAttribute, this);
    this.changeProductType = bind(this.changeProductType, this);
    jQuery('#add-attribute').on('click', this.addAttribute);
    jQuery('#new-attribute').on('change', function(event) {
      var $label;
      $label = jQuery('#new-attribute-label');
      if (jQuery(event.target).val() === '-1') {
        $label.closest('.form-group').css('display', 'inline-block');
        return $label.fadeIn();
      } else {
        return $label.fadeOut();
      }
    });
    jQuery('#product-attributes').on('click', '.show-variation', function(event) {
      var $item;
      $item = jQuery(event.target);
      return jQuery('.list-group-item-text', $item.closest('li')).slideToggle(function() {
        return jQuery('span', $item).toggleClass('glyphicon-collapse-down').toggleClass('glyphicon-collapse-up');
      });
    });
    jQuery('#product-attributes').on('change', 'input, select', this.updateAttribute).on('click', '.remove-attribute', this.removeAttribute);
    jQuery('#product-type').on('change', this.changeProductType);
    jQuery('.jigoshop_product_data a').on('click', function(e) {
      e.preventDefault();
      return jQuery(this).tab('show');
    });
    jQuery('#stock-manage').on('change', function() {
      if (jQuery(this).is(':checked')) {
        jQuery('.stock-status_field').slideUp();
        return jQuery('.stock-status').slideDown();
      } else {
        jQuery('.stock-status_field').slideDown();
        return jQuery('.stock-status').slideUp();
      }
    });
    jQuery('.stock-status_field .not-active').show();
    jQuery('#sales-enabled').on('change', function() {
      if (jQuery(this).is(':checked')) {
        return jQuery('.schedule').slideDown();
      } else {
        return jQuery('.schedule').slideUp();
      }
    });
    jQuery('#is_taxable').on('change', function() {
      if (jQuery(this).is(':checked')) {
        return jQuery('.tax_classes_field').slideDown();
      } else {
        return jQuery('.tax_classes_field').slideUp();
      }
    });
    jQuery('.tax_classes_field .not-active').show();
    jQuery('#sales-from').datepicker({
      todayBtn: 'linked',
      autoclose: true
    });
    jQuery('#sales-to').datepicker({
      todayBtn: 'linked',
      autoclose: true
    });
    jQuery('.add-product-attachments').on('click', this.updateAttachments);
    jQuery(document).ready(this.initAttachments);
  }

  AdminProduct.prototype.changeProductType = function(event) {
    var ref, tab, type, visibility;
    type = jQuery(event.target).val();
    jQuery('.jigoshop_product_data li').hide();
    ref = this.params.menu;
    for (tab in ref) {
      if (!hasProp.call(ref, tab)) continue;
      visibility = ref[tab];
      if (visibility === true || indexOf.call(visibility, type) >= 0) {
        jQuery('.jigoshop_product_data li.' + tab).show();
      }
    }
    return jQuery('.jigoshop_product_data li:first a').tab('show');
  };

  AdminProduct.prototype.addAttribute = function(event) {
    var $attribute, $label, $parent, label, value;
    event.preventDefault();
    $parent = jQuery('#product-attributes');
    $attribute = jQuery('#new-attribute');
    $label = jQuery('#new-attribute-label');
    value = parseInt($attribute.val());
    label = $label.val();
    if (value < 0 && value !== -1) {
      jigoshop.addMessage('warning', this.params.i18n.invalid_attribute);
      return;
    }
    if (value === -1 && label.length === 0) {
      jigoshop.addMessage('danger', this.params.i18n.attribute_without_label, 6000);
      return;
    }
    $attribute.select2('val', '');
    $label.val('').slideUp();
    if (value > 0) {
      jQuery("option[value=" + value + "]", $attribute).attr('disabled', 'disabled');
    }
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.product.save_attribute',
        product_id: $parent.closest('.jigoshop').data('id'),
        attribute_id: value,
        attribute_label: label
      }
    }).done(function(data) {
      if ((data.success != null) && data.success) {
        return jQuery(data.html).hide().appendTo($parent).slideDown();
      } else {
        return jigoshop.addMessage('danger', data.error, 6000);
      }
    });
  };

  AdminProduct.prototype.updateAttribute = function(event) {
    var $container, $parent, getOptionValue, i, item, items, len, option, options, optionsData, results;
    $container = jQuery('#product-attributes');
    $parent = jQuery(event.target).closest('li.list-group-item');
    items = jQuery('.values input[type=checkbox]:checked', $parent).toArray();
    if (items.length) {
      item = items.reduce(function(value, current) {
        return current.value + '|' + value;
      }, '');
    } else {
      item = jQuery('.values select', $parent).val();
      if (item === void 0) {
        item = jQuery('.values input', $parent).val();
      }
    }
    getOptionValue = function(current) {
      if (current.type === 'checkbox' || current.type === 'radio') {
        return current.checked;
      }
      return current.value;
    };
    options = {};
    optionsData = jQuery('.options input.attribute-options', $parent).toArray();
    for (i = 0, len = optionsData.length; i < len; i++) {
      option = optionsData[i];
      results = /(?:^|\s)product\[attributes]\[\d+]\[(.*?)](?:\s|$)/g.exec(option.name);
      options[results[1]] = getOptionValue(option);
    }
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.product.save_attribute',
        product_id: $container.closest('.jigoshop').data('id'),
        attribute_id: $parent.data('id'),
        value: item,
        options: options
      }
    }).done((function(_this) {
      return function(data) {
        if ((data.success != null) && data.success) {
          return jigoshop.addMessage('success', _this.params.i18n.saved, 2000);
        } else {
          return jigoshop.addMessage('danger', data.error, 6000);
        }
      };
    })(this));
  };

  AdminProduct.prototype.removeAttribute = function(event) {
    var $parent;
    event.preventDefault();
    if (confirm(this.params.i18n.confirm_remove)) {
      $parent = jQuery(event.target).closest('li');
      jQuery('option[value=' + $parent.data('id') + ']', jQuery('#new-attribute')).removeAttr('disabled');
      return jQuery.ajax({
        url: this.params.ajax,
        type: 'post',
        dataType: 'json',
        data: {
          action: 'jigoshop.admin.product.remove_attribute',
          product_id: $parent.closest('.jigoshop').data('id'),
          attribute_id: $parent.data('id')
        }
      }).done((function(_this) {
        return function(data) {
          if ((data.success != null) && data.success) {
            $parent.slideUp(function() {
              return $parent.remove();
            });
            return jigoshop.addMessage('success', _this.params.i18n.attribute_removed, 2000);
          } else {
            return jigoshop.addMessage('danger', data.error, 6000);
          }
        };
      })(this));
    }
  };

  AdminProduct.prototype.updateAttachments = function(event) {
    var element, wpMedia;
    event.preventDefault();
    element = jQuery(event.target).data('type');
    if (wpMedia) {
      this.wpMedia.open();
      return;
    }
    this.wpMedia = wp.media({
      states: [
        new wp.media.controller.Library({
          filterable: 'all',
          multiple: true
        })
      ]
    });
    wpMedia = this.wpMedia;
    this.wpMedia.on('select', (function(_this) {
      return function() {
        var attachmentIds, selection;
        selection = wpMedia.state().get('selection');
        attachmentIds = jQuery.map(jQuery('input[name="product[attachments][' + element + '][]"]'), function(attachment) {
          return parseInt(jQuery(attachment).val());
        });
        return selection.map(function(attachment) {
          var options;
          attachment = attachment.toJSON();
          if (attachment.id != null) {
            if (element === 'gallery') {
              options = {
                template_name: 'product-gallery',
                insert_before: '.empty-gallery',
                attachment_class: '.gallery-image'
              };
            } else if (element === 'downloads') {
              options = {
                template_name: 'product-downloads',
                insert_before: '.empty-downloads',
                attachment_class: '.downloads-file'
              };
            }
            return _this.addAttachment(attachment, attachmentIds, options);
          }
        });
      };
    })(this));
    return wpMedia.open();
  };

  AdminProduct.prototype.initAttachments = function() {
    var attachment, i, j, len, len1, ref, ref1, results1, template;
    if (this.params.attachments.gallery != null) {
      template = wp.template('product-gallery');
      ref = this.params.attachments.gallery;
      for (i = 0, len = ref.length; i < len; i++) {
        attachment = ref[i];
        jQuery('.empty-gallery').before(template(attachment));
        this.addHooks('', jQuery('.gallery-image').last());
      }
    }
    if (this.params.attachments.downloads != null) {
      template = wp.template('product-downloads');
      ref1 = this.params.attachments.downloads;
      results1 = [];
      for (j = 0, len1 = ref1.length; j < len1; j++) {
        attachment = ref1[j];
        jQuery('.empty-downloads').before(template(attachment));
        results1.push(this.addHooks('', jQuery('.downloads-file').last()));
      }
      return results1;
    }
  };

  AdminProduct.prototype.addHooks = function(index, element) {
    var $delete;
    $delete = jQuery(element).find('.delete');
    jQuery(element).hover(function() {
      return $delete.show();
    }, function() {
      return $delete.hide();
    });
    return $delete.click(function() {
      return jQuery(element).remove();
    });
  };

  AdminProduct.prototype.addAttachment = function(attachment, attachmentIds, options) {
    var html, template;
    if (attachment.id && jQuery.inArray(attachment.id, attachmentIds) === -1) {
      template = wp.template(options.template_name);
      html = template({
        id: attachment.id,
        url: attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url,
        title: attachment.title
      });
      jQuery(options.insert_before).before(html);
      return this.addHooks('', jQuery(options.attachment_class).last());
    }
  };

  return AdminProduct;

})();

jQuery(function() {
  return new AdminProduct(jigoshop_admin_product);
});
