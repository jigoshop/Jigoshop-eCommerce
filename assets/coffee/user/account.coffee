jQuery ->
  jQuery('#address_country').on 'change', (e) ->
    jQuery.post(jigoshop.getAjaxUrl(), {
      action: 'jigoshop.ajax.logged',
      service: 'jigoshop.ajax.get_states',
      country: jQuery('#address_country').val()
      }, (data) ->
        if(data.success)
          $parent = jQuery('#address_state').parents('div').first()

          if(data.states.length == 0)
            jQuery($parent).html('<input type="text" id="address_state" name="address[state]" class="form-control" />')
          else
            jQuery($parent).html('<select id="address_state" name="address[state]" class="form-control"></select>')
            jQuery('#address_state').select2()

            for own state, label of data.states
              jQuery('#address_state').append(new Option(label, state, false, false))

            jQuery('#address_state').trigger('change')
      , 'json')
