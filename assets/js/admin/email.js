var AdminEmail,
  bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

AdminEmail = (function() {
  AdminEmail.prototype.params = {
    ajax: ''
  };

  function AdminEmail(params) {
    this.params = params;
    this.updateVariables = bind(this.updateVariables, this);
    jQuery('#jigoshop_email_actions').on('change', this.updateVariables);
  }

  AdminEmail.prototype.updateVariables = function(event) {
    var $parent;
    event.preventDefault();
    $parent = jQuery(event.target).closest('div.jigoshop');
    return jQuery.ajax({
      url: this.params.ajax,
      type: 'post',
      dataType: 'json',
      data: {
        action: 'jigoshop.admin.email.update_variable_list',
        email: $parent.data('id'),
        actions: jQuery(event.target).val()
      }
    }).done(function(data) {
      if ((data.success != null) && data.success) {
        return jQuery('#available_arguments').replaceWith(data.html);
      } else {
        return addMessage('danger', data.error, 6000);
      }
    });
  };

  return AdminEmail;

})();

jQuery(function() {
  return new AdminEmail(jigoshop_admin_email);
});
