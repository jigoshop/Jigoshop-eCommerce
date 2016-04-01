<button<?php echo($countDone == $countAll ? ' disabled="disabled"' : ''); ?> type="submit" name="tool"
        value="<?php echo Jigoshop\Admin\Migration\Orders::ID; ?>" class="btn btn-default btn-block migration-orders<?php echo($countDone == $countAll ? ' btn_disabled' : ''); ?>"><?php echo __('Migrate orders', 'jigoshop') . sprintf(__(' - %d/%d', 'jigoshop'), $countDone, $countAll) . ($countDone == $countAll ? __(' - done', 'jigoshop') : '') ?>
</button>
