<button<?php echo($countDone == $countAll ? ' disabled="disabled"' : ''); ?> type="submit" name="tool"
        value="<?php echo Jigoshop\Admin\Migration\Emails::ID; ?>" class="btn btn-default btn-block migration-emails<?php echo($countDone == $countAll ? ' btn_disabled' : ''); ?>"><?php echo __('Migrate emails', 'jigoshop') . sprintf(__(' - %d/%d', 'jigoshop'), $countDone, $countAll) . ($countDone == $countAll ? __(' - done', 'jigoshop') : '') ?>
</button>
