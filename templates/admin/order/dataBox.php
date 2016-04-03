<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Order\Status;

/**
 * @var $order \Jigoshop\Entity\Order The order to display.
 * @var $billingOnly boolean Whether to display only billing fields.
 * @var $billingFields array List of billing fields to display.
 * @var $shippingFields array List of shipping fields to display.
 * @var $customers array List of available customers.
 * @var $shippingTax boolean Whether taxes are based on shipping country.
 */
?>
<style type="text/css">
	#post-body-content, #minor-publishing { display:none }
</style>
<div class="panels jigoshop jigoshop-data" data-order="<?php echo $order->getId(); ?>">
	<div id="messages"></div>
	<input name="post_title" type="hidden" value="<?php echo $order->getTitle(); ?>" />

	<ul class="nav nav-tabs nav-justified" role="tablist">
		<li class="active"><a href="#order" role="tab" data-toggle="tab"><?php _e('Order', 'jigoshop'); ?></a></li>
		<li><a href="#billing-address" role="tab" data-toggle="tab"><?php _e('Billing address', 'jigoshop'); ?></a></li>
		<?php if(!$billingOnly): ?>
		<li><a href="#shipping-address" role="tab" data-toggle="tab"><?php _e('Shipping address', 'jigoshop'); ?></a></li>
		<?php endif; ?>
		<!-- TODO: Maybe a filter to show/hide insignificant data? -->
	</ul>
	<noscript>
		<div class="alert alert-danger" role="alert"><?php _e('<strong>Warning</strong> Order panel will not work properly without JavaScript.', 'jigoshop'); ?></div>
	</noscript>
	<div class="tab-content form-horizontal">
		<div class="tab-pane active" id="order">
			<?php Forms::select(array(
				'name' => 'jigoshop_order[status]',
				'label' => __('Order status', 'jigoshop'),
				'value' => $order->getStatus(),
				'options' => Status::getStatuses(),
			)); ?>
			<?php Forms::select(array(
				'name' => 'jigoshop_order[customer]',
				'label' => __('Customer', 'jigoshop'),
				'value' => $order->getCustomer() ? $order->getCustomer()->getId() : '',
				'options' => $customers,
			)); ?>
			<?php Forms::textarea(array(
				'name' => 'post_excerpt',
				'label' => __("Customer's note", 'jigoshop'),
				'value' => $order->getCustomerNote(),
			)); ?>
		</div>
		<div class="tab-pane" id="billing-address">
			<?php foreach ($billingFields as $field): ?>
				<?php Forms::field($field['type'], $field); ?>
			<?php endforeach; ?>
		</div>
		<?php if (!$billingOnly): ?>
			<div class="tab-pane" id="shipping-address">
				<?php foreach ($shippingFields as $field): ?>
					<?php Forms::field($field['type'], $field); ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
