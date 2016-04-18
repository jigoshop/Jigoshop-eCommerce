jQuery(function($) {
  $('input[type=checkbox].switch-medium').bootstrapSwitch({
    size: 'small'
  });
  $('input[type=checkbox].switch-mini').bootstrapSwitch({
    size: 'mini'
  });
  return $('input[type=checkbox].switch-normal').bootstrapSwitch({
    size: 'normal'
  });
});
