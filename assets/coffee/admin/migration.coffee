jQuery(document).ready ($) ->
  dotCount = 0
  maxError = 3

  doSummary = (msg, status) ->
    if status == 'success'
      $('.glyphicon').removeClass('glyphicon-time').addClass 'glyphicon-ok'
    else if status == 'danger'
      $('.glyphicon').removeClass('glyphicon-time').addClass 'glyphicon-remove'
    $('.migration-id').html '&nbsp;&nbsp; - &nbsp;&nbsp;' + msg
    $('.back-to-home').removeClass 'invisible'
    $('#migration_alert').removeClass('alert-info').addClass 'alert-' + status
    $('#migration_progress_bar').addClass 'progress-bar-' + status

  migrateItems = (ajaxModule) ->
    params = jigoshop_admin_migration
    $.ajax(
      url: params['ajax']
      type: 'post'
      dataType: 'json'
      data: action: ajaxModule).done((data) ->
        if data.success == true
          $('.migration_processed').html data.processed
          $('.migration_remain').html data.remain
          $('.migration_total').html data.total
          $('.migration-id').html '.'.repeat(dotCount)
          dotCount++
          if dotCount > 3
            dotCount = 0
          $('.progress_bar').css('width', data.percent + '%').html data.percent + '%'
          if data.remain <= 0
            doSummary params['i18n']['migration_complete'], 'success'
            return false
          migrateItems ajaxModule
        else if data.success == false
          if maxError <= 0
            doSummary params['i18n']['migration_error'], 'danger'
            alert params['i18n']['alert_msg']
            return false
          console.log data
          setTimeout (->
            migrateItems ajaxModule
          ), 2000
          maxError--
    ).fail (data) ->
      console.log data.responseText
      doSummary params['i18n']['migration_error'], 'danger'
      alert params['i18n']['alert_msg']
      false

  showUI = (ajaxModule) ->
    params = jigoshop_admin_migration
    $('.migration').css 'display', 'none'
    $('.migration_progress').css 'display', 'block'
    $('.migration_processed').html '0'
    $('.migration_remain').html params['i18n']['processing']
    $('.migration_total').html params['i18n']['processing']
    $('#title').html params['i18n'][ajaxModule]

  $('.migration-products').click ->
    showUI 'jigoshop.admin.migration.products'
    migrateItems 'jigoshop.admin.migration.products'
  $('.migration-coupons').click ->
    showUI 'jigoshop.admin.migration.coupons'
    migrateItems 'jigoshop.admin.migration.coupons'
  $('.migration-emails').click ->
    showUI 'jigoshop.admin.migration.emails'
    migrateItems 'jigoshop.admin.migration.emails'
  $('.migration-options').click ->
    showUI 'jigoshop.admin.migration.options'
    migrateItems 'jigoshop.admin.migration.options'
  $('.migration-orders').click ->
    showUI 'jigoshop.admin.migration.orders'
    migrateItems 'jigoshop.admin.migration.orders'
