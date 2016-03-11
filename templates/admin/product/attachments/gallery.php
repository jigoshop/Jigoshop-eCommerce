<?php
?>

<div class="col-xs-12">
    <style>


    </style>
    <div class="gallery-images">
        <script type="text/template" id="tmpl-product-gallery">
            <div class="gallery-image col-xs-4">
                <span class="delete"></span>
                <img data-id="{{{ data.id }}}" src="{{{ data.url }}}" alt="{{{ data.title }}}"/>
                <input type="hidden" name="product[attachments][gallery][]" value="{{{ data.id }}}">
            </div>
        </script>
        <p class="empty-gallery"><?php _e( 'For this product, gallery has not been created yet.', 'jigoshop' ); ?></p>
    </div>
    <div class="clear"></div>
    <a href="#" class="btn btn-default add-product-attachments" data-type="gallery">Select</a>
</div>
<div class="clear"></div>
