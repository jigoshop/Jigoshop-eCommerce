<button<?=($countDone == $countAll ? ' disabled="disabled"' : ''); ?> type="submit" name="tool"
        value="<?= Jigoshop\Admin\Migration\Orders::ID; ?>" class="btn btn-default btn-block migration-orders<?=($countDone == $countAll ? ' btn_disabled' : ''); ?>"><?= __('Migrate orders', 'jigoshop') . sprintf(__(' - %d/%d', 'jigoshop'), $countDone, $countAll) . ($countDone == $countAll ? __(' - done', 'jigoshop') : '') ?>
</button>
