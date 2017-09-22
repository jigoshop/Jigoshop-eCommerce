<button<?= ($countDone == $countAll ? ' disabled="disabled"' : ''); ?> type="submit" name="tool"
        value="<?= Jigoshop\Admin\Migration\Products::ID; ?>" class="btn btn-default btn-block migration-products<?= ($countDone == $countAll ? ' btn_disabled' : ''); ?>"><?= __('Migrate products', 'jigoshop-ecommerce') . sprintf(__(' - %d/%d', 'jigoshop-ecommerce'), $countDone, $countAll) . ($countDone == $countAll ? __(' - done', 'jigoshop-ecommerce') : '') ?>
</button>
