var ProductVariable,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
  hasProp = {}.hasOwnProperty;

ProductVariable = (function() {
  ProductVariable.prototype.VARIATION_EXISTS = 1;

  ProductVariable.prototype.VARIATION_NOT_EXISTS = 2;

  ProductVariable.prototype.VARIATION_NOT_FULL = 3;

  ProductVariable.prototype.params = {
    ajax: '',
    variations: {}
  };

  ProductVariable.prototype.attributes = {};

  function ProductVariable(params) {
    this.params = params;
    this.updateAttributes = bind(this.updateAttributes, this);
    jQuery('select.product-attribute').on('change', this.updateAttributes);
  }

  ProductVariable.prototype.updateAttributes = function(event) {
    var $buttons, $messages, attributeId, attributeValue, definition, id, proper, ref, ref1, results, size;
    $buttons = jQuery('#add-to-cart-buttons');
    $messages = jQuery('#add-to-cart-messages');
    results = /(?:^|\s)attributes\[(\d+)](?:\s|$)/g.exec(event.target.name);
    this.attributes[results[1]] = event.target.value;
    proper = this.VARIATION_NOT_FULL;
    size = Object.keys(this.attributes).length;
    ref = this.params.variations;
    for (id in ref) {
      if (!hasProp.call(ref, id)) continue;
      definition = ref[id];
      proper = this.VARIATION_EXISTS;
      if (Object.keys(definition.attributes).length !== size) {
        proper = this.VARIATION_NOT_FULL;
        continue;
      }
      ref1 = this.attributes;
      for (attributeId in ref1) {
        if (!hasProp.call(ref1, attributeId)) continue;
        attributeValue = ref1[attributeId];
        if (definition.attributes[attributeId] !== '' && definition.attributes[attributeId] !== attributeValue) {
          proper = this.VARIATION_NOT_EXISTS;
          break;
        }
      }
      if (proper === this.VARIATION_EXISTS) {
        if (!definition.price) {
          proper = this.VARIATION_NOT_EXISTS;
          continue;
        }
        jQuery('p.price > span', $buttons).html(definition.html.price);
        jQuery('#variation-id').val(id);
        $buttons.slideDown();
        $messages.slideUp();
        break;
      }
    }
    if (proper !== this.VARIATION_EXISTS) {
      jQuery('#variation-id').val('');
      $buttons.slideUp();
    }
    if (proper === this.VARIATION_NOT_EXISTS) {
      return $messages.slideDown();
    }
  };

  return ProductVariable;

})();

jQuery(function() {
  return new ProductVariable(jigoshop_product_variable);
});
