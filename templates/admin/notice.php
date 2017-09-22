<?php
/**
 *
 */
?>
<div class="jigoshop notice notice-<?= $type; ?> is-dismissible">
    <p>
    <?= $message; ?>
    <?php if($interval == \Jigoshop\Admin\Notices::UNTIL_DISABLE) : ?>
        <a href="#" style="" class="disable-notice" data-notice="<?= md5($message); ?>"><?= __('Do not show this message again.', 'jigoshop-ecommerce'); ?></a>
    <?php endif; ?>
    <div class="clear"></div>
    </p>
</div>