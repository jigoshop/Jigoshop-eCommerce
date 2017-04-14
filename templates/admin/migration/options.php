<button<?=($countDone == $countAll ? ' disabled="disabled"' : ''); ?> type="submit" name="tool"
        value="<?= Jigoshop\Admin\Migration\Options::ID; ?>" class="btn btn-default btn-block migration-options<?=($countDone == $countAll ? ' btn_disabled' : ''); ?>"><?= __('Migrate options', 'jigoshop') . sprintf(__(' - %d/%d', 'jigoshop'), $countDone, $countAll) . ($countDone == $countAll ? __(' - done', 'jigoshop') : '') ?>
</button>
