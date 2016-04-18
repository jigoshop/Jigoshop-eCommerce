var AdminOrder,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
  hasProp = {}.hasOwnProperty;

AdminOrder = (function() {
  AdminOrder.prototype.params = {
    ajax: '',
    tax_shipping: false,
    ship_to_billing: false
  };

  function AdminOrder(params) {
    this.params = params;
    this.updatePostcode = bind(this.updatePostcode, this);
    this.updateState = bind(this.updateState, this);
    this.updateCountry = bind(this.updateCountry, this);
    this.removeItemClick = bind(this.removeItemClick, this);
    this.updateItem = bind(this.updateItem, this);
    this.newItemClick = bind(this.newItemClick, this);
    this.newItemSelect = bind(this.newItemSelect, this);
    this.selectShipping = bind(this.selectShipping, this);
    this.newItemSelect();
    this._prepareStateField("#order_billing_address_state");
    this._prepareStateField("#order_shipping_address_state");
    jQuery('#add-item').on('click', this.newItemClick);
    jQuery('.jigoshop-order table').on('click', 'a.remove', this.removeItemClick);
    jQuery('.jigoshop-order table').on('change', '.price input, .quantity input', this.updateItem);
    jQuery('.jigoshop-data').on('change', "#order_billing_address_country", this.updateCountry).on('change', "#order_shipping_address_country", this.updateCountry).on('change', '#order_billing_address_state', this.updateState).on('change', '#order_shipping_address_state', this.updateState).on('change', '#order_billing_address_postcode', this.updatePostcode).on('change', '#order_shipping_address_postcode', this.updatePostcode);
    jQuery('.jigoshop-totals').on('click', 'input[type=radio]', this.selectShipping);
  }

  AdminOrder.prototype.selectShipping = function(e) {
    var $method, $parent, $rate;
    $parent = jQuery(e.target).closest('div.jigoshop');
    $method = jQuery(e.target);
    $rate = jQuery('.shipping-method-rate', $method.closest('li'));
    return jQuery.ajax(this.params.ajax, {
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.order.change_shipping_method',
        order: $parent.data('order'),
        method: $method.val(),
        rate: $rate.val()
      }
    }).done((function(_this) {
      return function(result) {
        if ((result.success != null) && result.success) {
          _this._updateTotals(result.html.total, result.html.subtotal);
          return _this._updateTaxes(result.tax, result.html.tax);
        } else {
          return alert(result.error);
        }
      };
    })(this));
  };

  AdminOrder.prototype.newItemSelect = function() {
    return jQuery('#new-item').select2({
      minimumInputLength: 3,
      ajax: {
        url: this.params.ajax,
        type: 'post',
        dataType: 'json',
        data: function(term) {
          return {
            query: term,
            action: 'jigoshop.admin.product.find'
          };
        },
        results: function(data) {
          if (data.success != null) {
            return {
              results: data.results
            };
          }
          return {
            results: []
          };
        }
      }
    });
  };

  AdminOrder.prototype.newItemClick = function(e) {
    var $existing, $parent, $quantity, value;
    e.preventDefault();
    value = jQuery('#new-item').val();
    if (value === '') {
      return false;
    }
    $parent = jQuery(e.target).closest('table');
    $existing = jQuery("tr[data-product=" + value + "]", $parent);
    if ($existing.length > 0) {
      $quantity = jQuery('.quantity input', $existing);
      $quantity.val(parseInt($quantity.val()) + 1).trigger('change');
      return;
    }
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.order.add_product',
        product: value,
        order: $parent.data('order')
      }
    }).done((function(_this) {
      return function(data) {
        if ((data.success != null) && data.success) {
          jQuery(data.html.row).appendTo($parent);
          jQuery('#product-subtotal', $parent).html(data.html.product_subtotal);
          _this._updateTotals(data.html.total, data.html.subtotal);
          return _this._updateTaxes(data.tax, data.html.tax);
        }
      };
    })(this));
  };

  AdminOrder.prototype.updateItem = function(e) {
    var $parent, $row;
    e.preventDefault();
    $row = jQuery(e.target).closest('tr');
    $parent = $row.closest('table');
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.order.update_product',
        product: $row.data('id'),
        order: $parent.data('order'),
        price: jQuery('.price input', $row).val(),
        quantity: jQuery('.quantity input', $row).val()
      }
    }).done((function(_this) {
      return function(data) {
        if ((data.success != null) && data.success) {
          if (data.item_cost > 0) {
            jQuery('.total p', $row).html(data.html.item_cost);
          } else {
            $row.remove();
          }
          jQuery('#product-subtotal', $parent).html(data.html.product_subtotal);
          _this._updateTotals(data.html.total, data.html.subtotal);
          return _this._updateTaxes(data.tax, data.html.tax);
        }
      };
    })(this));
  };

  AdminOrder.prototype.removeItemClick = function(e) {
    var $parent, $row;
    e.preventDefault();
    $row = jQuery(e.target).closest('tr');
    $parent = $row.closest('table');
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.order.remove_product',
        product: $row.data('id'),
        order: $parent.data('order')
      }
    }).done((function(_this) {
      return function(data) {
        if ((data.success != null) && data.success) {
          $row.remove();
          jQuery('#product-subtotal', $parent).html(data.html.product_subtotal);
          _this._updateTaxes(data.tax, data.html.tax);
          return _this._updateTotals(data.html.total, data.html.subtotal);
        }
      };
    })(this));
  };

  AdminOrder.prototype.updateCountry = function(e) {
    var $parent, $target, id, type;
    $target = jQuery(e.target);
    $parent = $target.closest('.jigoshop');
    id = $target.attr('id');
    type = id.replace(/order_/, '').replace(/_country/, '');
    return jQuery.ajax(this.params.ajax, {
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.order.change_country',
        value: $target.val(),
        order: $parent.data('order'),
        type: type
      }
    }).done((function(_this) {
      return function(result) {
        var $field, data, fieldId, label, ref, state;
        if ((result.success != null) && result.success) {
          _this._updateTotals(result.html.total, result.html.subtotal);
          _this._updateTaxes(result.tax, result.html.tax);
          _this._updateShipping(result.shipping, result.html.shipping);
          fieldId = "#order_" + type + "_state";
          $field = jQuery(fieldId);
          if (result.has_states) {
            data = [];
            ref = result.states;
            for (state in ref) {
              if (!hasProp.call(ref, state)) continue;
              label = ref[state];
              data.push({
                id: state,
                text: label
              });
            }
            return $field.select2({
              data: data
            });
          } else {
            return $field.attr('type', 'text').select2('destroy').val('');
          }
        } else {
          return jigoshop.addMessage('danger', result.error, 6000);
        }
      };
    })(this));
  };

  AdminOrder.prototype.updateState = function(e) {
    var $parent, $target, id, type;
    $target = jQuery(e.target);
    $parent = $target.closest('.jigoshop');
    id = $target.attr('id');
    type = id.replace(/order_/, '').replace(/_state/, '');
    return jQuery.ajax(this.params.ajax, {
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.order.change_state',
        value: $target.val(),
        order: $parent.data('order'),
        type: type
      }
    }).done((function(_this) {
      return function(result) {
        if ((result.success != null) && result.success) {
          _this._updateTotals(result.html.total, result.html.subtotal);
          _this._updateTaxes(result.tax, result.html.tax);
          return _this._updateShipping(result.shipping, result.html.shipping);
        } else {
          return jigoshop.addMessage('danger', result.error, 6000);
        }
      };
    })(this));
  };

  AdminOrder.prototype.updatePostcode = function(e) {
    var $parent, $target, id, type;
    $target = jQuery(e.target);
    $parent = $target.closest('.jigoshop');
    id = $target.attr('id');
    type = id.replace(/order_/, '').replace(/_postcode/, '');
    return jQuery.ajax(this.params.ajax, {
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.order.change_postcode',
        value: $target.val(),
        order: $parent.data('order'),
        type: type
      }
    }).done((function(_this) {
      return function(result) {
        if ((result.success != null) && result.success) {
          _this._updateTotals(result.html.total, result.html.subtotal);
          _this._updateTaxes(result.tax, result.html.tax);
          return _this._updateShipping(result.shipping, result.html.shipping);
        } else {
          return jigoshop.addMessage('danger', result.error, 6000);
        }
      };
    })(this));
  };

  AdminOrder.prototype._updateTaxes = function(taxes, html) {
    var $tax, fieldClass, results, tax, taxClass;
    results = [];
    for (taxClass in html) {
      if (!hasProp.call(html, taxClass)) continue;
      tax = html[taxClass];
      fieldClass = ".order_tax_" + taxClass + "_field";
      $tax = jQuery(fieldClass);
      jQuery("label", $tax).html(tax.label);
      jQuery("p", $tax).html(tax.value).show();
      if (taxes[taxClass] > 0) {
        results.push($tax.show());
      } else {
        results.push($tax.hide());
      }
    }
    return results;
  };

  AdminOrder.prototype._updateTotals = function(total, subtotal) {
    jQuery('#subtotal').html(subtotal);
    return jQuery('#total').html(total);
  };

  AdminOrder.prototype._updateShipping = function(shipping, html) {
    var $item, $method, shippingClass, value;
    for (shippingClass in shipping) {
      if (!hasProp.call(shipping, shippingClass)) continue;
      value = shipping[shippingClass];
      $method = jQuery(".shipping-" + shippingClass);
      $method.addClass('existing');
      if ($method.length > 0) {
        if (value > -1) {
          $item = jQuery(html[shippingClass].html).addClass('existing');
          $method.replaceWith($item);
        } else {
          $method.slideUp(function() {
            return jQuery(this).remove();
          });
        }
      } else if (html[shippingClass] != null) {
        $item = jQuery(html[shippingClass].html);
        $item.hide().addClass('existing').appendTo(jQuery('#shipping-methods')).slideDown();
      }
    }
    jQuery('#shipping-methods > li:not(.existing)').slideUp(function() {
      return jQuery(this).remove();
    });
    return jQuery('#shipping-methods > li').removeClass('existing');
  };

  AdminOrder.prototype._prepareStateField = function(id) {
    var $field, $replacement, data;
    $field = jQuery(id);
    if (!$field.is('select')) {
      return;
    }
    $replacement = jQuery(document.createElement('input')).attr('type', 'text').attr('id', $field.attr('id')).attr('name', $field.attr('name')).attr('class', $field.attr('class')).val($field.val());
    data = [];
    jQuery('option', $field).each(function() {
      return data.push({
        id: jQuery(this).val(),
        text: jQuery(this).html()
      });
    });
    $field.replaceWith($replacement);
    return $replacement.select2({
      data: data
    });
  };

  return AdminOrder;

})();

jQuery(function() {
  return new AdminOrder(jigoshop_admin_order);
});
