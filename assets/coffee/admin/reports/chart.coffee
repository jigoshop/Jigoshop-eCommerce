jQuery ($) ->
  drawGraph = (highlight) ->
    series = $.extend true, [], chart_data['series']
    options = $.extend true, [], chart_data['options']
    if highlight != 'undefined' and series[highlight]
      highlight_series = series[highlight]
      highlight_series.color = '#98c242'
      if highlight_series.bars
        highlight_series.bars.fillColor = '#98c242'
      if highlight_series.lines
        highlight_series.lines.lineWidth = 5
    $.plot $('.main-chart'), series, options
    $ '.main-chart' .resize()
    return

  drawGraph()
  $('.highlight_series').hover ->
    drawGraph $(this).data('series')
    return
  , ->
    drawGraph()
    return

  showTooltip = (x, y, contents) ->
    $('<div class="chart-tooltip">' + contents + '</div>').css(
      top: y - 16
      left: x + 20).appendTo('body').fadeIn 200
    return

  prev_data_index = null
  prev_series_index = null
  prepareTooltip = (pos, item) ->
    tooltip_content = item.series.data[item.dataIndex][1]
    if item.series.append_tooltip
      tooltip_content = tooltip_content + item.series.append_tooltip
    if item.series.pie.show
      return [pos.pageX, pos.pageY, tooltip_content]
    return [item.pageX, item.pageY, tooltip_content]

  $('.chart-placeholder').bind 'plothover', (event, pos, item) ->
    if item
      if prev_data_index != item.dataIndex or prev_series_index != item.seriesIndex
        prev_data_index = item.dataIndex
        prev_series_index = item.seriesIndex
        $('.chart-tooltip').remove()
        if item.series.points.show or item.series.enable_tooltip
          tooltip_data = prepareTooltip pos item
          showTooltip tooltip_data[0], tooltip_data[1], tooltip_data[2]
    else
      $('.chart-tooltip').remove()
      prev_data_index = null
    return

  dates = $('.range_datepicker').datepicker
    changeMonth: true
    changeYear: true
    defaultDate: ''
    dateFormat: 'yy-mm-dd'
    numberOfMonths: 1
    maxDate: '+0D'
    showButtonPanel: true
    showOn: 'focus'
    buttonImageOnly: true
    onSelect: (selectedDate) ->
      option = if $(this).is('.from') then 'minDate' else 'maxDate'
      instance = $(this).data('datepicker')
      date = $.datepicker.parseDate(instance.settings.dateFormat or $.datepicker._defaults.dateFormat, selectedDate, instance.settings)
      dates.not(this).datepicker 'option', option, date
      return

  a = document.createElement('a')
  if typeof a.download == 'undefined'
    $('.export_csv').hide()

  $('.export_csv').click ->
    exclude_series = $(this).data('exclude_series') or ''
    exclude_series = exclude_series.toString()
    exclude_series = exclude_series.split ','
    xaxes_label = $(this).data 'xaxes'
    groupby = $(this).data 'groupby'
    export_format = $(this).data 'export'
    csv_data = 'data:application/csv;charset=utf-8,'

  #jQuery return
  return