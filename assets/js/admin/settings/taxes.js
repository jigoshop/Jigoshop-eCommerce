var TaxSettings,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

TaxSettings = (function() {
  TaxSettings.prototype.params = {
    new_class: '',
    new_rule: ''
  };

  function TaxSettings(params) {
    this.params = params;
    this.updateStateField = bind(this.updateStateField, this);
    this.addNewRule = bind(this.addNewRule, this);
    this.addNewClass = bind(this.addNewClass, this);
    jQuery('#add-tax-class').on('click', this.addNewClass);
    jQuery('#tax-classes').on('click', 'button.remove-tax-class', this.removeItem);
    jQuery('#add-tax-rule').on('click', this.addNewRule);
    jQuery('#tax-rules').on('click', 'button.remove-tax-rule', this.removeItem).on('change', 'select.tax-rule-country', this.updateStateField);
    this.updateFields();
  }

  TaxSettings.prototype.removeItem = function() {
    jQuery(this).closest('tr').remove();
    return false;
  };

  TaxSettings.prototype.addNewClass = function() {
    jQuery('#tax-classes').append(this.params.new_class);
    return false;
  };

  TaxSettings.prototype.addNewRule = function() {
    var $item;
    $item = jQuery(this.params.new_rule);
    jQuery('input.tax-rule-postcodes', $item).select2({
      tags: [],
      tokenSeparators: [','],
      multiple: true,
      formatNoMatches: ''
    });
    jQuery('#tax-rules').append($item);
    return false;
  };

  TaxSettings.prototype.updateStateField = function(event) {
    var $country, $parent, $states, country;
    $parent = jQuery(event.target).closest('tr');
    $states = jQuery('input.tax-rule-states', $parent);
    $country = jQuery('select.tax-rule-country', $parent);
    country = $country.val();
    if (this.params.states[country] != null) {
      return this._attachSelectField($states, this.params.states[country]);
    } else {
      return this._attachTextField($states);
    }
  };

  TaxSettings.prototype.updateFields = function() {
    jQuery('select.tax-rule-country').change();
    return jQuery('input.tax-rule-postcodes').select2({
      tags: [],
      tokenSeparators: [','],
      multiple: true,
      formatNoMatches: ''
    });
  };


  /*
  Attaches Select2 to provided field with proper states to select
   */

  TaxSettings.prototype._attachSelectField = function($field, states) {
    return $field.select2({
      data: states,
      multiple: true,
      initSelection: function(element, callback) {
        var data, i, len, ref, state, text, value;
        data = [];
        ref = element.val().split(',');
        for (i = 0, len = ref.length; i < len; i++) {
          value = ref[i];
          text = (function() {
            var j, len1, results;
            results = [];
            for (j = 0, len1 = states.length; j < len1; j++) {
              state = states[j];
              if (state.id === value) {
                results.push(state);
              }
            }
            return results;
          })();
          data.push(text[0]);
        }
        return callback(data);
      }
    });
  };


  /*
  Attaches simple text field to write a state
   */

  TaxSettings.prototype._attachTextField = function($field) {
    return $field.select2('destroy');
  };

  return TaxSettings;

})();

jQuery(function() {
  return new TaxSettings(jigoshop_admin_taxes);
});
