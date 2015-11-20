jQuery(document).ready ($) ->
  doSummary = (msg, status) ->
    if status == 'success'
      $('.glyphicon').removeClass('glyphicon-time').addClass 'glyphicon-ok'
    else if status == 'danger'
      $('.glyphicon').removeClass('glyphicon-time').addClass 'glyphicon-remove'
    $('.migration-id').html '&nbsp;&nbsp; - &nbsp;&nbsp;' + msg
    $('.back-to-home').removeClass 'invisible'
    $('#migration_alert').removeClass('alert-info').addClass 'alert-' + status
    $('#migration_progress_bar').addClass 'progress-bar-' + status

  migrateProduct = ->
    params = jigoshop_admin_migration_products
    $.ajax(
      url: params['ajax']
      type: 'post'
      dataType: 'json'
      data: action: 'jigoshop.admin.migration').done((data) ->
      if data.success == true
        $('.migration').css 'display', 'none'
        $('.migration_progress').css 'display', 'block'
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
        setTimeout (->
          migrateProduct()
        ), 500
      else if data.success == false
        if maxError <= 0
          doSummary params['i18n']['migration_error'], 'danger'
          return false
        setTimeout (->
          migrateProduct()
        ), 2000
        maxError--
    ).fail ->
    doSummary params['i18n']['migration_error'], 'danger'
    alert params['i18n']['alert_msg']
    false

  migrateReset = ->
    params = jigoshop_admin_migration_products
    $.ajax(
      url: params['ajax']
      type: 'post'
      dataType: 'json'
      data:
        action: 'jigoshop.admin.migration'
        wwee: 11).done (data) ->
    if data.success == true
      location.reload()
    else if data.success == false
      alert 'error reset'

  dotCount = 0
  maxError = 3
  $('.migration-products').click ->
    migrateProduct()
  $('.migration-reset').click ->
    migrateReset()
