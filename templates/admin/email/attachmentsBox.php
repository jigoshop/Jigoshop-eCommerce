<?php
/**
 * @var attachments array
 */
?>
<div class="jigoshop">
    <div class="col-xs-12">
        <div id="email-attachments">
            <script type="text/template" id="tmpl-product-downloads">
                <div class="email-attachment col-xs-12">
                    <div data-id="{{{ data.id }}}">{{{ data.title }}}</div>
                    <span class="delete"></span>
                    <input type="hidden" name="jigoshop_email[attachments][]" value="{{{ data.id }}}">
                </div>
            </script>
            <?php foreach ($attachments as $id => $attachment) : ?>
                <div class="email-attachment col-xs-12">
                    <div data-id="<?= $id; ?>"><?= basename($attachment); ?></div>
                    <span class="delete"></span>
                    <input type="hidden" name="jigoshop_email[attachments][]" value="<?= $id; ?>">
                </div>
            <?php endforeach; ?>
            <p class="empty-attachments"><?php _e('This email template does not have attachments.', 'jigoshop-ecommerce'); ?></p>
        </div>
        <div class="clear"></div>
        <a href="#" class="btn btn-default add-email-attachments">Select</a>
    </div>
    <div class="clear"></div>
</div>