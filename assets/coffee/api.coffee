class JigoshopApi
  host: ''

  constructor: ->
    @host = window.location.protocol + '//' + window.location.hostname

    if window.location.pathname.search 'index.php'
      @host += '/index.php'


  request: (method, path, params, callback) ->
    jQuery.ajax({
      url: @host + path
      type: method
      dataType: 'json'
      data: params
    }).done callback

  get: (path, params, callback) ->
    this.request('GET', path, params, callback)

  post: (path, params, callback) ->
    this.request('POST', path, params, callback)

  put: (path, params, callback) ->
    this.request('PUT', path, params, callback)

  delete: (path, params, callback) ->
    this.request('DELETE', path, params, callback)

jQuery ->
  jigoshop.api = new JigoshopApi()
