jQuery ($) ->
  $('#generate-report').on 'click', ->
    report = []
    $.each system_data, (index, value) ->
      report.push value

    $('#report-for-support').html report join '\n'
    $('#report-for-support').slideDown 1000
    $('#report-for-support').removeClass 'hidden'
    $(this).slideUp 1000