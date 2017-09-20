jQuery(document).ready ($) ->
  $('#next-step').on 'click', (event) ->
    event.preventDefault()
    form = new FormData document.getElementById('form')
    form.append 'action', 'jigoshop.ajax.logged'
    form.append 'service', 'jigoshop.ajax.save_setup_step'
    request = new XMLHttpRequest()
    request.open "POST", jigoshop.getAjaxUrl()
    request.onload = (event) ->
      if request.readyState == 4
        if request.status == 200
          response = JSON.parse(request.responseText)
          if response && response.success != "undefined" && response.success
            window.location = $('#next-step').data('url')
    request.send(form)

  $('select#country').on 'change', (event) ->
    $country = $ event.target
    $states = $ 'input#state'
    country = $country.val()
    if jigoshop_setup.states[country]?
      $states.select2
        data: jigoshop_setup.states[country]
        multiple: false
    else
      $states.select2 'destroy'
  .change()

  $('select#currency').on 'change', (event) ->
    currency = $(event.target).val()
    $position = $('input#currency_position')
    $position.select2
      data: jigoshop_setup.currency[currency]
      multiple: false
  .change()