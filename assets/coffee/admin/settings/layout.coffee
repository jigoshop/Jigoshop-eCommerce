class LayoutSettings
  constructor: ->
    jQuery('.enable_section').on('switchChange.bootstrapSwitch', (event) ->
      $table = jQuery(event.target).closest('h2').next('table')
      if jQuery(event.target).is(':checked')
        $table.show()
      else
        $table.hide()
    ).trigger('switchChange.bootstrapSwitch')

    jQuery('select.proportions').on('change', (event) ->
      if jQuery(event.target).val() == 'custom'
        jQuery(event.target).closest('tr').next().show()
      else
        jQuery(event.target).closest('tr').next().hide()
    ).trigger('change')

    jQuery('input.structure').on('change', (event) ->
      console.log(jQuery(event.target).val())
      if jQuery(event.target).val() == 'only_content'
        jQuery(event.target).closest('tr').next().hide().next().hide().next().hide()
      else
        jQuery(event.target).closest('tr').next().show().next().show()
        jQuery('.proportions').change()
    )
    jQuery('input.structure:checked').change()

jQuery () ->
  new LayoutSettings()