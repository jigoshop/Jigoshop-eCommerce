jQuery(function($) {
  return $('#generate-report').on('click', function() {
    var report;
    report = [];
    $.each(system_data, function(index, value) {
      return report.push(value);
    });
    $('#report-for-support').html(report.join('\n'));
    $('#report-for-support').slideDown(1000);
    $('#report-for-support').removeClass('hidden');
    return $(this).slideUp(1000);
  });
});
