<?php
/**
 *
 */

?>
<div class="input-daterange input-group" id="datepicker">
	<input type="text" size="9" placeholder="MM/DD/YYYY" name="start_date" value="<?= esc_attr($args['start_date']); ?>" class="input-sm form-control"/>
    <span class="input-group-addon"><?php _e('to', 'jigoshop-ecommerce'); ?></span>
    <input type="text" size="9" placeholder="MM/DD/YYYY" name="end_date" value="<?= esc_attr($args['end_date']); ?>" class="input-sm form-control"/>
</div>

