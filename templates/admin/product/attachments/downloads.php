<?php

?>
<div class="col-xs-12">
    <div class="downloads-files">
        <script type="text/template" id="tmpl-product-downloads">
            <div class="downloads-file col-xs-12">
                <div data-id="{{{ data.id }}}">{{{ data.title }}}</div>
                <span class="delete"></span>
                <input type="hidden" name="product[attachments][datafile][]" value="{{{ data.id }}}">
            </div>
        </script>
        <p class="empty-downloads"><?php _e('This product does not have files to download.', 'jigoshop' ); ?></p>
    </div>
    <div class="clear"></div>
    <a href="#" class="btn btn-default add-product-attachments" data-type="datafile">Select</a>
</div>
<div class="clear"></div>