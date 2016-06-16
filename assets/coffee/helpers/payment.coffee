JigoshopHelpers.prototype.payment = ($element, options) ->
  settings = jQuery.extend {
    redirect: 'Redirecting...'
    message: 'Thank you for your order. We are now redirecting you to make payment.'
    overlayCss:
      opacity: 0.01
  }, options

  @block jQuery(document.body), settings
  $element.submit()
