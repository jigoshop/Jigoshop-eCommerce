var ProductSearch,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

ProductSearch = (function() {
  ProductSearch.prototype.params = {
    ajax: ''
  };

  function ProductSearch(params) {
    this.params = params;
    this._attachSelectField = bind(this._attachSelectField, this);
    this._attachSelectField(jQuery('#jigoshop_find_products'), 'jigoshop.admin.product.find');
  }


  /*
  Attaches Select2 to provided field with proper states to select
   */

  ProductSearch.prototype._attachSelectField = function($field, action) {
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

  return ProductSearch;

})();

jQuery(function() {
  return new ProductSearch(jigoshop_admin_reports_widget_product_search);
});
