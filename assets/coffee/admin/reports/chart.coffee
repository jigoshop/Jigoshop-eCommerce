jQuery ($) ->
  main_chart = null
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
    main_chart = $.plot $('.main-chart'), series, options
    $ '.main-chart'
    .resize()
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

  prepareTooltip = (pos, item) ->
    tooltip_content = item.series.data[item.dataIndex][1]
    if item.series.append_tooltip
      tooltip_content = tooltip_content + item.series.append_tooltip
    if item.series.pie.show
      return [pos.pageX, pos.pageY, tooltip_content]
    return [item.pageX, item.pageY, tooltip_content]

  prev_data_index = null
  prev_series_index = null
  $('.main-chart')
  .bind 'plothover', (event, pos, item) ->
    if item
      if prev_data_index != item.dataIndex or prev_series_index != item.seriesIndex
        prev_data_index = item.dataIndex
        prev_series_index = item.seriesIndex
        $('.chart-tooltip').remove()
        if item.series.points.show or item.series.enable_tooltip
          tooltip_data = prepareTooltip pos, item
          showTooltip tooltip_data[0], tooltip_data[1], tooltip_data[2]
    else
      $('.chart-tooltip').remove()
      prev_data_index = null
    return

  a = document.createElement('a')
  if typeof a.download == 'undefined'
    $('.export-csv').hide()

  $('.export-csv').click ->
    exclude_series = $(this).data('exclude_series') or ''
    exclude_series = exclude_series.toString()
    exclude_series = exclude_series.split ','
    xaxes_label = $(this).data 'xaxes'
    groupby = $(this).data 'groupby'
    export_format = $(this).data 'export'
    csv_data = 'data:application/csv;charset=utf-8,'

    if export_format == 'table'
      $(this).closest 'div'
      .find 'thead tr,tbody tr'
      .each ->
        $(this).find 'th,td'
        .each ->
          value = $(this).text()
          value = value.replace '[?]', ''
          csv_data += '"' + value + '"' + ','
      csv_data = csv_data.substring  0, csv_data.length - 1
      csv_data += "\n"

      $(this).closest 'div'
      .find 'tfoot tr'
      .each ->
        $(this).find 'th,td'
        .each ->
          value = $(this).text()
          value = value.replace '[?]', ''
          csv_data += '"' + value + '"' + ','
          if $(this).attr 'colspan' > 0
            i = 1
            while i < $(this).attr('colspan')
              i++
              csv_data += '"",'
        csv_data = csv_data.substring( 0, csv_data.length - 1 )
        csv_data += "\n"
    else
      if main_chart == null
        return false

      the_series = main_chart.getData()
      series = []
      csv_data += xaxes_label + ','

      $.each the_series, (index, value) ->
        if !exclude_series or $.inArray(index.toString(), exclude_series) == -1
          series.push value

      # CSV Headers
      s = 0
      while s < series.length
        csv_data += series[s].label + ','
        ++s

      csv_data = csv_data.substring 0, csv_data.length - 1
      csv_data += "\n"

      # Get x axis values
      xaxis = {}
      s = 0
      while s < series.length
        series_data = series[s].data
        d = 0
        while d < series_data.length
          xaxis[parseInt series_data[d][0]]  = new Array()
          # Zero values to start
          i = 0
          while i < series.length
            xaxis[parseInt series_data[d][0]].push 0
            ++i
          ++d
        ++s

      # Add chart data
      s = 0
      while s < series.length
        series_data = series[s].data
        d = 0
        while d < series_data.length
          xaxis[parseInt series_data[d][0]][s] = series_data[d][1]
          ++d
        ++s

      # Loop data and output to csv string
      $.each xaxis, (index, value) ->
        date = new Date parseInt index
        if groupby == 'day'
          csv_data += date.getUTCFullYear() + "-" + parseInt(date.getUTCMonth() + 1) + "-" + date.getUTCDate() + ','
        else
          csv_data += date.getUTCFullYear() + "-" + parseInt(date.getUTCMonth() + 1) + ','
        d = 0
        while d < value.length
          val = value[d]
          if Math.round(val) != val
            val = parseFloat val
            val = val.toFixed 2
          ++d
          csv_data += val + ','
        csv_data = csv_data.substring 0, csv_data.length - 1
        csv_data += "\n"
    # Set data as href and return
    $(this).attr 'href', encodeURI csv_data
  #jQuery return
  return