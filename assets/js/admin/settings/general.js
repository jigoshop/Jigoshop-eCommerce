var GeneralSettings,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

GeneralSettings = (function() {
  GeneralSettings.prototype.params = {
    states: {}
  };

  function GeneralSettings(params) {
    this.params = params;
    this.updateStateField = bind(this.updateStateField, this);
    jQuery('#show_message').on('change', function() {
      return jQuery('#custom_message').closest('tr').toggle();
    });
    jQuery('#custom_message').show().closest('div.form-group').show();
    jQuery('select#country').on('change', this.updateStateField);
    this.updateFields();
  }

  GeneralSettings.prototype.updateStateField = function(event) {
    var $country, $states, country;
    $country = jQuery(event.target);
    $states = jQuery('input#state');
    country = $country.val();
    if (this.params.states[country] != null) {
      return this._attachSelectField($states, this.params.states[country]);
    } else {
      return this._attachTextField($states);
    }
  };

  GeneralSettings.prototype.updateFields = function() {
    return jQuery('select#country').change();
  };


  /*
  Attaches Select2 to provided field with proper states to select
   */

  GeneralSettings.prototype._attachSelectField = function($field, states) {
    return $field.select2({
      data: states,
      multiple: false
    });
  };


  /*
  Attaches simple text field to write a state
   */

  GeneralSettings.prototype._attachTextField = function($field) {
    return $field.select2('destroy');
  };

  return GeneralSettings;

})();

jQuery(function() {
  return new GeneralSettings(jigoshop_admin_general);
});
