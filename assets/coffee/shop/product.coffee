jQuery ($) ->
  $('a[data-lightbox], img[data-lightbox]').colorbox
    rel: 'product-gallery'
    scalePhotos: true
    preloading: false
    loop: false
    maxWidth: window.innerWidth - 50
    maxHeight: window.innerHeight - 50
  $('ul.tabs a').on 'click', (e) ->
    e.preventDefault()
    $(this).tab('show')
  $('.comment-form-rating').on 'click', 'a', (event) ->
    event.preventDefault()
    $item = $(event.target).parent()
    $('#rating').val($item.data('rating')).trigger 'change'
    $item.prevAll('a').find('span').removeClass('glyphicon-star-empty').addClass 'glyphicon-star'
    $item.find('span').removeClass('glyphicon-star-empty').addClass 'glyphicon-star'
    $item.nextAll('a').find('span').removeClass('glyphicon-star').addClass 'glyphicon-star-empty'
