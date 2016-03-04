<?php
?>
<div class="col-xs-12">
    <div class="downloadable-files">
        <script type="text/template" id="tmpl-product-downloadable">
            <div class="downloadable-file col-xs-12">
                <div data-id="{{{ data.id }}}">{{{ data.name }}}</div>
                <span class="delete"></span>
                <input type="hidden" name="product[downloadable][]" value="{{{ data.id }}}">
            </div>
        </script>
        <p class="empty-downloadable"><?php _e( 'Nie ma pobieralnych załączników, dodaj plizzzzz', 'jigoshop' ); ?></p>
    </div>
    <div class="clear"></div>
    <a href="#" class="btn btn-default add-product-attachments" data-type="downloadable">Select</a>
</div>
<div class="clear"></div>