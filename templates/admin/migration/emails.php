<button<?=($countDone == $countAll ? ' disabled="disabled"' : ''); ?> type="submit" name="tool"
        value="<?= Jigoshop\Admin\Migration\Emails::ID; ?>" class="btn btn-default btn-block migration-emails<?=($countDone == $countAll ? ' btn_disabled' : ''); ?>"><?= __('Migrate emails', 'jigoshop-ecommerce') . sprintf(__(' - %d/%d', 'jigoshop-ecommerce'), $countDone, $countAll) . ($countDone == $countAll ? __(' - done', 'jigoshop-ecommerce') : '') ?>
</button>
