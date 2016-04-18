var ShoppingSettings;

ShoppingSettings = (function() {
  function ShoppingSettings() {
    jQuery('#restrict_selling_locations').on('switchChange.bootstrapSwitch', this.toggleSellingLocations);
    jQuery('#selling_locations').show().closest('div.form-group').show();
    jQuery('#enable_verification_message').on('switchChange.bootstrapSwitch', this.toggleVerificationMessage);
    jQuery('#verification_message').show().closest('div.form-group').show();
  }

  ShoppingSettings.prototype.toggleSellingLocations = function() {
    return jQuery('#selling_locations').closest('tr').toggle();
  };

  ShoppingSettings.prototype.toggleVerificationMessage = function() {
    return jQuery('#verification_message').closest('tr').toggle();
  };

  return ShoppingSettings;

})();

jQuery(function() {
  return new ShoppingSettings();
});
