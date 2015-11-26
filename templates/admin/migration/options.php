<button<?php echo($countDone == $countAll ? ' disabled="disabled"' : ''); ?> type="submit" name="tool"
        value="<?php echo Jigoshop\Admin\Migration\Options::ID; ?>" class="btn btn-default btn-block migration-options<?php echo($countDone == $countAll ? ' btn_disabled' : ''); ?>"><?php echo __('Migrate options', 'jigoshop') . sprintf(__(' - %d/%d', 'jigoshop'), $countDone, $countAll) . ($countDone == $countAll ? __(' - done', 'jigoshop') : '') ?>
</button>

<button type="submit" name="tool" value="<?php echo Jigoshop\Admin\Migration\Options::ID; ?>" class="btn btn-default btn-block migration-reset-options">reset options</button>
