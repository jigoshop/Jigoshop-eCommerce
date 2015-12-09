jQuery(document).ready(function($) {
  var doSummary, dotCount, maxError, migrateItems, showUI;
  dotCount = 0;
  maxError = 3;
  doSummary = function(msg, status) {
    if (status === 'success') {
      $('.glyphicon').removeClass('glyphicon-time').addClass('glyphicon-ok');
    } else if (status === 'danger') {
      $('.glyphicon').removeClass('glyphicon-time').addClass('glyphicon-remove');
    }
    $('.migration-id').html('&nbsp;&nbsp; - &nbsp;&nbsp;' + msg);
    $('.back-to-home').removeClass('invisible');
    $('#migration_alert').removeClass('alert-info').addClass('alert-' + status);
    return $('#migration_progress_bar').addClass('progress-bar-' + status);
  };
  migrateItems = function(ajaxModule) {
    var params;
    params = jigoshop_admin_migration;
    return $.ajax({
      url: params['ajax'],
      type: 'post',
      dataType: 'json',
      data: {
        action: ajaxModule
      }
    }).done(function(data) {
      if (data.success === true) {
        $('.migration_processed').html(data.processed);
        $('.migration_remain').html(data.remain);
        $('.migration_total').html(data.total);
        $('.migration-id').html('.'.repeat(dotCount));
        dotCount++;
        if (dotCount > 3) {
          dotCount = 0;
        }
        $('.progress_bar').css('width', data.percent + '%').html(data.percent + '%');
        if (data.remain <= 0) {
          doSummary(params['i18n']['migration_complete'], 'success');
          return false;
        }
        return migrateItems(ajaxModule);
      } else if (data.success === false) {
        if (maxError <= 0) {
          doSummary(params['i18n']['migration_error'], 'danger');
          alert(params['i18n']['alert_msg']);
          return false;
        }
        console.log(data);
        setTimeout((function() {
          return migrateItems(ajaxModule);
        }), 2000);
        return maxError--;
      }
    }).fail(function(data) {
      console.log(data.responseText);
      doSummary(params['i18n']['migration_error'], 'danger');
      alert(params['i18n']['alert_msg']);
      return false;
    });
  };
  showUI = function(ajaxModule) {
    var params;
    params = jigoshop_admin_migration;
    $('.migration').css('display', 'none');
    $('.migration_progress').css('display', 'block');
    $('.migration_processed').html('0');
    $('.migration_remain').html(params['i18n']['processing']);
    $('.migration_total').html(params['i18n']['processing']);
    return $('#title').html(params['i18n'][ajaxModule]);
  };
  $('.migration-products').click(function() {
    showUI('jigoshop.admin.migration.products');
    return migrateItems('jigoshop.admin.migration.products');
  });
  $('.migration-coupons').click(function() {
    showUI('jigoshop.admin.migration.coupons');
    return migrateItems('jigoshop.admin.migration.coupons');
  });
  $('.migration-emails').click(function() {
    showUI('jigoshop.admin.migration.emails');
    return migrateItems('jigoshop.admin.migration.emails');
  });
  $('.migration-options').click(function() {
    showUI('jigoshop.admin.migration.options');
    return migrateItems('jigoshop.admin.migration.options');
  });
  return $('.migration-orders').click(function() {
    showUI('jigoshop.admin.migration.orders');
    return migrateItems('jigoshop.admin.migration.orders');
  });
});
