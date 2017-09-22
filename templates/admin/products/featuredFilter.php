<?php
/**
 * @var $current
 */
?>
<label for="featured"><?= __('Only featured?', 'jigoshop-ecommerce'); ?>
    <input type="checkbox" name="featured" id="featured" value="1" <?= \Jigoshop\Admin\Helper\Forms::checked('1', $current); ?>>
</label>
