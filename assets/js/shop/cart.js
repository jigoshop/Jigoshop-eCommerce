var Cart,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
  hasProp = {}.hasOwnProperty;

Cart = (function() {
  Cart.prototype.params = {
    ajax: '',
    assets: '',
    i18n: {
      loading: 'Loading...'
    }
  };

  function Cart(params) {
    this.params = params;
    this.updateDiscounts = bind(this.updateDiscounts, this);
    this.updateQuantity = bind(this.updateQuantity, this);
    this.removeItem = bind(this.removeItem, this);
    this._updateShippingField = bind(this._updateShippingField, this);
    this.updatePostcode = bind(this.updatePostcode, this);
    this.updateState = bind(this.updateState, this);
    this.updateCountry = bind(this.updateCountry, this);
    this.selectShipping = bind(this.selectShipping, this);
    this.block = bind(this.block, this);
    jQuery('#cart').on('change', '.product-quantity input', this.updateQuantity).on('click', '.product-remove a', this.removeItem);
    jQuery('#shipping-calculator').on('click', '#change-destination', this.changeDestination).on('click', '.close', this.changeDestination).on('click', 'input[type=radio]', this.selectShipping).on('change', '#country', this.updateCountry).on('change', '#state', this.updateState.bind(this, '#state')).on('change', '#noscript_state', this.updateState.bind(this, '#noscript_state')).on('change', '#postcode', this.updatePostcode);
    jQuery('input#jigoshop_coupons').on('change', this.updateDiscounts).select2({
      tags: [],
      tokenSeparators: [','],
      multiple: true,
      formatNoMatches: ''
    });
  }

  Cart.prototype.block = function() {
    return jQuery('#cart > button').block({
      message: '<img src="' + this.params.assets + '/images/loading.gif" alt="' + this.params.i18n.loading + '" width="15" height="15" />',
      css: {
        padding: '5px',
        width: 'auto',
        height: 'auto',
        border: '1px solid #83AC31'
      },
      overlayCss: {
        opacity: 0.01
      }
    });
  };

  Cart.prototype.unblock = function() {
    return jQuery('#cart > button').unblock();
  };

  Cart.prototype.changeDestination = function(e) {
    e.preventDefault();
    jQuery('#shipping-calculator td > div').slideToggle();
    jQuery('#change-destination').slideToggle();
    return false;
  };

  Cart.prototype.selectShipping = function() {
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
          return _this._updateTaxes(result.tax, result.html.tax);
        } else {
          return addMessage('danger', result.error, 6000);
        }
      };
    })(this));
  };

  Cart.prototype.updateCountry = function() {
    this.block();
    jQuery('.noscript_state_field').remove();
    return jQuery.ajax(this.params.ajax, {
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop_cart_change_country',
        value: jQuery('#country').val()
      }
    }).done((function(_this) {
      return function(result) {
        var data, label, ref, state;
        if ((result.success != null) && result.success) {
          jQuery('#shipping-calculator th p > span').html(result.html.estimation);
          _this._updateTotals(result.html.total, result.html.subtotal);
          _this._updateDiscount(result);
          _this._updateTaxes(result.tax, result.html.tax);
          _this._updateShipping(result.shipping, result.html.shipping);
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
            jQuery('#state').select2({
              data: data
            });
          } else {
            jQuery('#state').attr('type', 'text').select2('destroy').val('');
          }
        } else {
          addMessage('danger', result.error, 6000);
        }
        return _this.unblock();
      };
    })(this));
  };

  Cart.prototype.updateState = function(field) {
    return this._updateShippingField('jigoshop_cart_change_state', jQuery(field).val());
  };

  Cart.prototype.updatePostcode = function() {
    return this._updateShippingField('jigoshop_cart_change_postcode', jQuery('#postcode').val());
  };

  Cart.prototype._updateShippingField = function(action, value) {
    this.block();
    return jQuery.ajax(this.params.ajax, {
      type: 'post',
      dataType: 'json',
      data: {
        action: action,
        value: value
      }
    }).done((function(_this) {
      return function(result) {
        if ((result.success != null) && result.success) {
          jQuery('#shipping-calculator th p > span').html(result.html.estimation);
          _this._updateTotals(result.html.total, result.html.subtotal);
          _this._updateDiscount(result);
          _this._updateTaxes(result.tax, result.html.tax);
          _this._updateShipping(result.shipping, result.html.shipping);
        } else {
          addMessage('danger', result.error, 6000);
        }
        return _this.unblock();
      };
    })(this));
  };

  Cart.prototype.removeItem = function(e) {
    var $item;
    e.preventDefault();
    $item = jQuery(e.target).closest('tr');
    jQuery('.product-quantity', $item).val(0);
    return this.updateQuantity(e);
  };

  Cart.prototype.updateQuantity = function(e) {
    var $item;
    $item = jQuery(e.target).closest('tr');
    this.block();
    return jQuery.ajax(this.params.ajax, {
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop_cart_update_item',
        item: $item.data('id'),
        quantity: jQuery(e.target).val()
      }
    }).done((function(_this) {
      return function(result) {
        var $cart, $empty;
        if (result.success === true) {
          if ((result.empty_cart != null) === true) {
            $empty = jQuery(result.html).hide();
            $cart = jQuery('#cart');
            $cart.after($empty);
            $cart.slideUp();
            $empty.slideDown();
            _this.unblock();
            return;
          }
          if ((result.remove_item != null) === true) {
            $item.remove();
          } else {
            jQuery('.product-subtotal', $item).html(result.html.item_subtotal);
          }
          jQuery('td#product-subtotal').html(result.html.product_subtotal);
          _this._updateTotals(result.html.total, result.html.subtotal);
          _this._updateDiscount(result);
          _this._updateTaxes(result.tax, result.html.tax);
          _this._updateShipping(result.shipping, result.html.shipping);
        } else {
          addMessage('danger', result.error, 6000);
        }
        return _this.unblock();
      };
    })(this));
  };

  Cart.prototype.updateDiscounts = function(event) {
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
          _this._updateShipping(result.shipping, result.html.shipping);
        } else {
          addMessage('danger', result.error, 6000);
        }
        return _this.unblock();
      };
    })(this));
  };

  Cart.prototype._updateTotals = function(total, subtotal) {
    jQuery('#cart-total > td').html(total);
    return jQuery('#cart-subtotal > td').html(subtotal);
  };

  Cart.prototype._updateDiscount = function(data) {
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
        return addMessage('warning', data.html.coupons);
      }
    }
  };

  Cart.prototype._updateShipping = function(shipping, html) {
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

  Cart.prototype._updateTaxes = function(taxes, html) {
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

  return Cart;

})();

jQuery(function() {
  return new Cart(jigoshop);
});
