<?php
/**
 *
 */
?>
<div class="jigoshop">
    <div class="margin-9">
        <ul class="order_actions">
            <li><input type="submit" class="button button-primary" name="save" value="<?php _e('Save Order', 'jigoshop'); ?>" /> <?php _e('- Save/update the order.', 'jigoshop'); ?></li>
            <li><input type="submit" class="button" name="reduce_stock" value="<?php _e('Reduce stock', 'jigoshop'); ?>" /> <?php _e('- Reduces stock for each item in the order; useful after manually creating an order or manually marking an order as complete/processing after payment.', 'jigoshop'); ?></li>
            <li><input type="submit" class="button" name="restore_stock" value="<?php _e('Restore stock', 'jigoshop'); ?>" /> <?php _e('- Restores stock for each item in the order; useful after refunding or canceling the entire order.', 'jigoshop'); ?></li>
        <!--    <li><input type="submit" class="button" name="invoice" value="--><?php //_e('Email invoice', 'jigoshop'); ?><!--" /> --><?php //_e('- Emails the customer order details and a payment link.', 'jigoshop'); ?><!--</li>-->
            <?php if($delete_text) : ?>
                <li><a class="submitdelete deletion" href="<?php echo esc_url(get_delete_post_link($order->getId())); ?>"><?php echo $delete_text; ?></a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>