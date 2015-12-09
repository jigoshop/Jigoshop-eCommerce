jQuery(function($) {
  var a, drawGraph, main_chart, prepareTooltip, prev_data_index, prev_series_index, showTooltip;
  main_chart = null;
  drawGraph = function(highlight) {
    var highlight_series, options, series;
    series = $.extend(true, [], chart_data['series']);
    options = $.extend(true, [], chart_data['options']);
    if (highlight !== 'undefined' && series[highlight]) {
      highlight_series = series[highlight];
      highlight_series.color = '#98c242';
      if (highlight_series.bars) {
        highlight_series.bars.fillColor = '#98c242';
      }
      if (highlight_series.lines) {
        highlight_series.lines.lineWidth = 5;
      }
    }
    main_chart = $.plot($('.main-chart'), series, options);
    $('.main-chart').resize();
  };
  drawGraph();
  $('.highlight_series').hover(function() {
    drawGraph($(this).data('series'));
  }, function() {
    drawGraph();
  });
  showTooltip = function(x, y, contents) {
    $('<div class="chart-tooltip">' + contents + '</div>').css({
      top: y - 16,
      left: x + 20
    }).appendTo('body').fadeIn(200);
  };
  prepareTooltip = function(pos, item) {
    var tooltip_content;
    tooltip_content = item.series.data[item.dataIndex][1];
    if (item.series.append_tooltip) {
      tooltip_content = tooltip_content + item.series.append_tooltip;
    }
    if (item.series.pie.show) {
      return [pos.pageX, pos.pageY, tooltip_content];
    }
    return [item.pageX, item.pageY, tooltip_content];
  };
  prev_data_index = null;
  prev_series_index = null;
  $('.main-chart').bind('plothover', function(event, pos, item) {
    var tooltip_data;
    if (item) {
      if (prev_data_index !== item.dataIndex || prev_series_index !== item.seriesIndex) {
        prev_data_index = item.dataIndex;
        prev_series_index = item.seriesIndex;
        $('.chart-tooltip').remove();
        if (item.series.points.show || item.series.enable_tooltip) {
          tooltip_data = prepareTooltip(pos, item);
          showTooltip(tooltip_data[0], tooltip_data[1], tooltip_data[2]);
        }
      }
    } else {
      $('.chart-tooltip').remove();
      prev_data_index = null;
    }
  });
  a = document.createElement('a');
  if (typeof a.download === 'undefined') {
    $('.export-csv').hide();
  }
  $('.export-csv').click(function() {
    var csv_data, d, exclude_series, export_format, groupby, i, s, series, series_data, the_series, xaxes_label, xaxis;
    exclude_series = $(this).data('exclude_series') || '';
    exclude_series = exclude_series.toString();
    exclude_series = exclude_series.split(',');
    xaxes_label = $(this).data('xaxes');
    groupby = $(this).data('groupby');
    export_format = $(this).data('export');
    csv_data = 'data:application/csv;charset=utf-8,';
    if (export_format === 'table') {
      $(this).closest('div').find('thead tr,tbody tr').each(function() {
        return $(this).find('th,td').each(function() {
          var value;
          value = $(this).text();
          value = value.replace('[?]', '');
          return csv_data += '"' + value + '"' + ',';
        });
      });
      csv_data = csv_data.substring(0, csv_data.length - 1);
      csv_data += "\n";
      $(this).closest('div').find('tfoot tr').each(function() {
        $(this).find('th,td').each(function() {
          var i, results, value;
          value = $(this).text();
          value = value.replace('[?]', '');
          csv_data += '"' + value + '"' + ',';
          if ($(this).attr('colspan' > 0)) {
            i = 1;
            results = [];
            while (i < $(this).attr('colspan')) {
              i++;
              results.push(csv_data += '"",');
            }
            return results;
          }
        });
        csv_data = csv_data.substring(0, csv_data.length - 1);
        return csv_data += "\n";
      });
    } else {
      if (main_chart === null) {
        return false;
      }
      the_series = main_chart.getData();
      series = [];
      csv_data += xaxes_label + ',';
      $.each(the_series, function(index, value) {
        if (!exclude_series || $.inArray(index.toString(), exclude_series === -1)) {
          return series.push(value);
        }
      });
      s = 0;
      while (s < series.length) {
        csv_data += series[s].label + ',';
        ++s;
      }
      csv_data = csv_data.substring(0, csv_data.length - 1);
      csv_data += "\n";
      xaxis = {};
      s = 0;
      while (s < series.length) {
        series_data = series[s].data;
        d = 0;
        while (d < series_data.length) {
          xaxis[parseInt(series_data[d][0])] = new Array();
          i = 0;
          while (i < series.length) {
            xaxis[parseInt(series_data[d][0])].push(0);
            ++i;
          }
          ++d;
        }
        ++s;
      }
      s = 0;
      while (s < series.length) {
        series_data = series[s].data;
        d = 0;
        while (d < series_data.length) {
          xaxis[parseInt(series_data[d][0])][s] = series_data[d][1];
          ++d;
        }
        ++s;
      }
      $.each(xaxis, function(index, value) {
        var date, val;
        date = new Date(parseInt(index));
        if (groupby === 'day') {
          csv_data += date.getUTCFullYear() + "-" + parseInt(date.getUTCMonth() + 1) + "-" + date.getUTCDate() + ',';
        } else {
          csv_data += date.getUTCFullYear() + "-" + parseInt(date.getUTCMonth() + 1) + ',';
        }
        d = 0;
        while (d < value.length) {
          val = value[d];
          if (Math.round(val) !== val) {
            val = parseFloat(val);
            val = val.toFixed(2);
          }
          ++d;
          csv_data += val + ',';
        }
        csv_data = csv_data.substring(0, csv_data.length - 1);
        return csv_data += "\n";
      });
    }
    return $(this).attr('href', encodeURI(csv_data));
  });
});
