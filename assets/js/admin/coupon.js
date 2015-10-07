var AdminCoupon,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

AdminCoupon = (function() {
  AdminCoupon.prototype.params = {
    ajax: ''
  };

  function AdminCoupon(params) {
    this.params = params;
    this._attachSelectField = bind(this._attachSelectField, this);
    this._attachSelectField(jQuery('#jigoshop_coupon_products'), 'jigoshop.admin.product.find');
    this._attachSelectField(jQuery('#jigoshop_coupon_excluded_products'), 'jigoshop.admin.product.find');
    this._attachSelectField(jQuery('#jigoshop_coupon_categories'), 'jigoshop.admin.coupon.find_category');
    this._attachSelectField(jQuery('#jigoshop_coupon_excluded_categories'), 'jigoshop.admin.coupon.find_category');
  }


  /*
  Attaches Select2 to provided field with proper states to select
   */

  AdminCoupon.prototype._attachSelectField = function($field, action) {
    return $field.select2({
      multiple: true,
      minimumInputLength: 3,
      ajax: {
        url: this.params.ajax,
        type: 'post',
        dataType: 'json',
        cache: true,
        data: function(term) {
          return {
            action: action,
            query: term
          };
        },
        results: function(data) {
          if ((data.success != null) && data.success) {
            return {
              results: data.results
            };
          } else {
            return addMessage('danger', data.error, 6000);
          }
        }
      },
      initSelection: (function(_this) {
        return function(element, callback) {
          return jQuery.ajax({
            url: _this.params.ajax,
            type: 'post',
            dataType: 'json',
            data: {
              action: action,
              value: jQuery(element).val()
            }
          }).done(function(data) {
            if ((data.success != null) && data.success) {
              return callback(data.results);
            } else {
              return addMessage('danger', data.error, 6000);
            }
          });
        };
      })(this)
    });
  };

  return AdminCoupon;

})();

jQuery(function() {
  return new AdminCoupon(jigoshop_admin_coupon);
});
