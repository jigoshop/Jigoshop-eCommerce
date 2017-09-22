<button<?=($countDone == $countAll ? ' disabled="disabled"' : ''); ?> type="submit" name="tool"
        value="<?= Jigoshop\Admin\Migration\Orders::ID; ?>" class="btn btn-default btn-block migration-orders<?=($countDone == $countAll ? ' btn_disabled' : ''); ?>"><?= __('Migrate orders', 'jigoshop-ecommerce') . sprintf(__(' - %d/%d', 'jigoshop-ecommerce'), $countDone, $countAll) . ($countDone == $countAll ? __(' - done', 'jigoshop-ecommerce') : '') ?>
</button>
