<button<?php echo($countDone == $countAll ? ' disabled="disabled"' : ''); ?> type="submit" name="tool"
        value="<?php echo Jigoshop\Admin\Migration\Products::ID; ?>" class="btn btn-default btn-block migration-coupons<?php echo($countDone == $countAll ? ' btn_disabled' : ''); ?>"><?php echo __('Migrate coupons', 'jigoshop') . sprintf(__(' - %d/%d', 'jigoshop'), $countDone, $countAll) . ($countDone == $countAll ? __(' - done', 'jigoshop') : '') ?>
</button>
