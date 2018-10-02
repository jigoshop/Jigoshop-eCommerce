<?php
?>

<div class="col-xs-12">
    <div class="gallery-images">
        <script type="text/template" id="tmpl-product-gallery">
            <div class="gallery-image col-xs-4">
                <span class="delete"></span>
                <img data-id="{{{ data.id }}}" src="{{{ data.url }}}" alt="{{{ data.title }}}"/>
                <input type="hidden" name="product[attachments][image][]" value="{{{ data.id }}}">
            </div>
        </script>
        <p class="empty-gallery"><?php _e('For this product, gallery has not been created yet.', 'jigoshop-ecommerce'); ?></p>
    </div>
    <div class="clear"></div>
    <a href="#" class="btn btn-default add-product-attachments"
       data-type="image"><?php __('Select', 'jigoshop-ecommerce'); ?></a>
</div>
<div class="clear"></div>
