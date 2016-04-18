jQuery(function($) {
  $('a[data-lightbox], img[data-lightbox]').colorbox({
    rel: 'product-gallery',
    scalePhotos: true,
    preloading: false,
    loop: false,
    maxWidth: window.innerWidth - 50,
    maxHeight: window.innerHeight - 50
  });
  return $('ul.tabs a').on('click', function(e) {
    e.preventDefault();
    return $(this).tab('show');
  });
});
