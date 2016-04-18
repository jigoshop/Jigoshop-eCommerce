var Checkout,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
  hasProp = {}.hasOwnProperty;

Checkout = (function() {
  Checkout.prototype.params = {
    ajax: '',
    assets: '',
    i18n: {
      loading: 'Loading...'
    }
  };

  function Checkout(params) {
    this.params = params;
    this._updateShippingField = bind(this._updateShippingField, this);
    this.updateDiscounts = bind(this.updateDiscounts, this);
    this.updatePostcode = bind(this.updatePostcode, this);
    this.updateState = bind(this.updateState, this);
    this.updateCountry = bind(this.updateCountry, this);
    this.selectShipping = bind(this.selectShipping, this);
    this.block = bind(this.block, this);
    this._prepareStateField("#jigoshop_order_billing_address_state");
    this._prepareStateField("#jigoshop_order_shipping_address_state");
    jQuery('#jigoshop-login').on('click', function(event) {
      event.preventDefault();
      return jQuery('#jigoshop-login-form').slideToggle();
    });
    jQuery('#create-account').on('change', function() {
      return jQuery('#registration-form').slideToggle();
    });
    jQuery('#different_shipping_address').on('change', function() {
      jQuery('#shipping-address').slideToggle();
      if (jQuery(this).is(':checked')) {
        return jQuery('#jigoshop_order_shipping_address_country').change();
      } else {
        return jQuery('#jigoshop_order_billing_address_country').change();
      }
    });
    jQuery('#payment-methods').on('change', 'li input[type=radio]', function() {
      jQuery('#payment-methods li > div').slideUp();
      return jQuery('div', jQuery(this).closest('li')).slideDown();
    });
    jQuery('#shipping-calculator').on('click', 'input[type=radio]', this.selectShipping);
    jQuery('#jigoshop_order_billing_address_country').on('change', (function(_this) {
      return function(event) {
        return _this.updateCountry('billing_address', event);
      };
    })(this));
    jQuery('#jigoshop_order_shipping_address_country').on('change', (function(_this) {
      return function(event) {
        return _this.updateCountry('shipping_address', event);
      };
    })(this));
    jQuery('#jigoshop_order_billing_address_state').on('change', this.updateState.bind(this, 'billing_address'));
    jQuery('#jigoshop_order_shipping_address_state').on('change', this.updateState.bind(this, 'shipping_address'));
    jQuery('#jigoshop_order_billing_address_postcode').on('change', this.updatePostcode.bind(this, 'billing_address'));
    jQuery('#jigoshop_order_shipping_address_postcode').on('change', this.updatePostcode.bind(this, 'shipping_address'));
    jQuery('#jigoshop_coupons').on('change', this.updateDiscounts).select2({
      tags: [],
      tokenSeparators: [','],
      multiple: true,
      formatNoMatches: ''
    });
  }

  Checkout.prototype.block = function() {
    return jQuery('#checkout > button').block({
      message: '<img src="' + this.params.assets + '/images/loading.gif" alt="' + this.params.i18n.loading + '" />',
      css: {
        padding: '20px',
        width: 'auto',
        height: 'auto',
        border: '1px solid #83AC31'
      },
      overlayCss: {
        opacity: 0.01
      }
    });
  };

  Checkout.prototype.unblock = function() {
    return jQuery('#checkout > button').unblock();
  };

  Checkout.prototype._prepareStateField = function(id) {
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

  Checkout.prototype.selectShipping = function() {
    var $method, $rate;
    $method = jQuery('#shipping-calculator input[type=radio]:checked');
    $rate = jQuery('.shipping-method-rate', $method.closest('li'));
    return jQuery.ajax(this.params.ajax, {
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop_cart_select_shipping',
        method: $method.val(),
        rate: $rate.val()
      }
    }).done((function(_this) {
      return function(result) {
        if (result.success) {
          _this._updateTotals(result.html.total, result.html.subtotal);
          _this._updateDiscount(result);
          return _this._updateTaxes(result.tax, result.html.tax);
        } else {
          return jigoshop.addMessage('danger', result.error, 6000);
        }
      };
    })(this));
  };

  Checkout.prototype.updateCountry = function(field, event) {
    this.block();
    jQuery('.noscript_state_field').remove();
    return jQuery.ajax(this.params.ajax, {
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop_checkout_change_country',
        field: field,
        differentShipping: jQuery('#different_shipping_address').is(':checked'),
        value: jQuery(event.target).val()
      }
    }).done((function(_this) {
      return function(result) {
        var data, label, ref, state, stateClass;
        if ((result.success != null) && result.success) {
          _this._updateTotals(result.html.total, result.html.subtotal);
          _this._updateDiscount(result);
          _this._updateTaxes(result.tax, result.html.tax);
          _this._updateShipping(result.shipping_address, result.html.shipping_address);
          stateClass = '#' + jQuery(event.target).attr('id').replace(/country/, 'state');
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
            jQuery(stateClass).select2({
              data: data
            });
          } else {
            jQuery(stateClass).attr('type', 'text').select2('destroy').val('');
          }
        } else {
          jigoshop.addMessage('danger', result.error, 6000);
        }
        return _this.unblock();
      };
    })(this));
  };

  Checkout.prototype.updateState = function(field) {
    var fieldClass;
    fieldClass = "#jigoshop_order_" + field + "_state";
    return this._updateShippingField('jigoshop_checkout_change_state', field, jQuery(fieldClass).val());
  };

  Checkout.prototype.updatePostcode = function(field) {
    var fieldClass;
    fieldClass = "#jigoshop_order_" + field + "_postcode";
    return this._updateShippingField('jigoshop_checkout_change_postcode', field, jQuery(fieldClass).val());
  };

  Checkout.prototype.updateDiscounts = function(event) {
    var $item;
    $item = jQuery(event.target);
    this.block();
    return jQuery.ajax(this.params.ajax, {
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop_cart_update_discounts',
        coupons: $item.val()
      }
    }).done((function(_this) {
      return function(result) {
        var $cart, $empty;
        if ((result.success != null) && result.success) {
          if ((result.empty_cart != null) === true) {
            $empty = jQuery(result.html).hide();
            $cart = jQuery('#cart');
            $cart.after($empty);
            $cart.slideUp();
            $empty.slideDown();
            _this.unblock();
            return;
          }
          jQuery('td#product-subtotal').html(result.html.product_subtotal);
          _this._updateTotals(result.html.total, result.html.subtotal);
          _this._updateDiscount(result);
          _this._updateTaxes(result.tax, result.html.tax);
          _this._updateShipping(result.shipping_address, result.html.shipping_address);
        } else {
          jigoshop.addMessage('danger', result.error, 6000);
        }
        return _this.unblock();
      };
    })(this));
  };

  Checkout.prototype._updateShippingField = function(action, field, value) {
    this.block();
    return jQuery.ajax(this.params.ajax, {
      type: 'post',
      dataType: 'json',
      data: {
        action: action,
        field: field,
        differentShipping: jQuery('#different_shipping_address').is(':checked'),
        value: value
      }
    }).done((function(_this) {
      return function(result) {
        if ((result.success != null) && result.success) {
          _this._updateTotals(result.html.total, result.html.subtotal);
          _this._updateDiscount(result);
          _this._updateTaxes(result.tax, result.html.tax);
          _this._updateShipping(result.shipping_address, result.html.shipping_address);
        } else {
          jigoshop.addMessage('danger', result.error, 6000);
        }
        return _this.unblock();
      };
    })(this));
  };

  Checkout.prototype._updateTotals = function(total, subtotal) {
    jQuery('#cart-total > td > strong').html(total);
    return jQuery('#cart-subtotal > td').html(subtotal);
  };

  Checkout.prototype._updateDiscount = function(data) {
    var $parent;
    if (data.coupons != null) {
      jQuery('input#jigoshop_coupons').select2('val', data.coupons.split(','));
      $parent = jQuery('tr#cart-discount');
      if (data.discount > 0) {
        jQuery('td', $parent).html(data.html.discount);
        $parent.show();
      } else {
        $parent.hide();
      }
      if (data.html.coupons != null) {
        return jigoshop.addMessage('warning', data.html.coupons);
      }
    }
  };

  Checkout.prototype._updateShipping = function(shipping_address, html) {
    var $item, $method, shipping_addressClass, value;
    for (shipping_addressClass in shipping_address) {
      if (!hasProp.call(shipping_address, shipping_addressClass)) continue;
      value = shipping_address[shipping_addressClass];
      $method = jQuery(".shipping_address-" + shipping_addressClass);
      $method.addClass('existing');
      if ($method.length > 0) {
        if (value > -1) {
          $item = jQuery(html[shipping_addressClass].html).addClass('existing');
          $method.replaceWith($item);
        } else {
          $method.slideUp(function() {
            return jQuery(this).remove();
          });
        }
      } else if (html[shipping_addressClass] != null) {
        $item = jQuery(html[shipping_addressClass].html);
        $item.hide().addClass('existing').appendTo(jQuery('#shipping_address-methods')).slideDown();
      }
    }
    jQuery('#shipping_address-methods > li:not(.existing)').slideUp(function() {
      return jQuery(this).remove();
    });
    return jQuery('#shipping_address-methods > li').removeClass('existing');
  };

  Checkout.prototype._updateTaxes = function(taxes, html) {
    var $tax, results, tax, taxClass;
    results = [];
    for (taxClass in html) {
      if (!hasProp.call(html, taxClass)) continue;
      tax = html[taxClass];
      $tax = jQuery("#tax-" + taxClass);
      jQuery("th", $tax).html(tax.label);
      jQuery("td", $tax).html(tax.value);
      if (taxes[taxClass] > 0) {
        results.push($tax.show());
      } else {
        results.push($tax.hide());
      }
    }
    return results;
  };

  return Checkout;

})();

jQuery(function() {
  return new Checkout(jigoshop_checkout);
});
