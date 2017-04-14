<?php
use Jigoshop\Admin\Helper\Forms;

/**
 * @var $coupon \Jigoshop\Entity\Coupon Currently displayed coupon.
 * @var $types array List of coupon types.
 * @var $paymentMethods array List of available payment methods.
 */
?>
<div class="jigoshop" data-id="<?= $coupon->getId(); ?>">
	<fieldset>
		<?php Forms::constant([
			'name' => 'jigoshop_coupon[code]',
			'label' => __('Code', 'jigoshop'),
			'description' => $coupon->getCode() ? '' : __('Will not appear until coupon is saved.  This is the front end code for use on the Cart.','jigoshop'),
			'value' => $coupon->getCode(),
        ]); ?>
	</fieldset>
	<fieldset>
		<?php Forms::select([
			'id' => 'jigoshop_coupon_type',
			'name' => 'jigoshop_coupon[type]',
			'label' => __('Type', 'jigoshop'),
			'options' => $types,
			'value' => $coupon->getType(),
        ]); ?>
		<?php Forms::text([
			'name' => 'jigoshop_coupon[amount]',
			'label' => __('Amount', 'jigoshop'),
			'type' => 'number',
			'description' => __('Enter an amount e.g. 9.99.','jigoshop'),
			'tip' => __('Amount this coupon is worth. If it is a percentage, just include the number without the percentage sign.', 'jigoshop'),
			'placeholder' => \Jigoshop\Helper\Product::formatNumericPrice(0),
			'value' => $coupon->getAmount(),
        ]); ?>
	</fieldset>
	<fieldset>
		<?php Forms::daterange([
			'id' => 'coupon_date',
			'name' => [
				'from' => 'jigoshop_coupon[from]',
				'to' => 'jigoshop_coupon[to]',
            ],
			'label' => __('Coupon date', 'jigoshop'),
			'tip' => __('Choose between which dates this coupon is enabled.  Leave empty for any date.','jigoshop'),
			'placeholder' => __('Any date', 'jigoshop'),
			'value' => [
				'from' => $coupon->getFrom() ? $coupon->getFrom()->format('m/d/Y') : '',
				'to' =>  $coupon->getTo() ? $coupon->getTo()->format('m/d/Y') : '',
            ],
        ]);
		?>
	</fieldset>
	<fieldset>
		<?php Forms::text([
			'name' => 'jigoshop_coupon[usage_limit]',
			'label' => __('Usage limit', 'jigoshop'),
			'type' => 'number',
			'description' => sprintf(__('Times used: %s','jigoshop'), $coupon->getUsage()),
			'tip' => __('Control how many times this coupon may be used.', 'jigoshop'),
			'placeholder' => 0,
			'value' => $coupon->getUsageLimit(),
        ]); ?>
		<?php Forms::checkbox([
			'name' => 'jigoshop_coupon[individual_use]',
			'label' => __('Individual use', 'jigoshop'),
			'description' => __('Prevent other coupons from being used while this one is applied to the Cart.','jigoshop'),
			'checked' => $coupon->isIndividualUse(),
        ]); ?>
		<?php Forms::checkbox([
			'name' => 'jigoshop_coupon[free_shipping]',
			'label' => __('Free shipping', 'jigoshop'),
			'description' => __('Show the Free Shipping method on the Checkout with this enabled.','jigoshop'),
			'checked' => $coupon->isFreeShipping(),
        ]); ?>
	</fieldset>
	<fieldset>
		<?php Forms::text([
			'name' => 'jigoshop_coupon[order_total_minimum]',
			'label' => __('Order total minimum', 'jigoshop'),
			'type' => 'number',
			'description' => __('Set the required minimum subtotal for this coupon to be valid on an order.','jigoshop'),
			'placeholder' => __('No minimum', 'jigoshop'),
			'value' => $coupon->getOrderTotalMinimum(),
        ]); ?>
		<?php Forms::text([
			'name' => 'jigoshop_coupon[order_total_maximum]',
			'label' => __('Order total maximum', 'jigoshop'),
			'type' => 'number',
			'description' => __('Set the required maximum subtotal for this coupon to be valid on an order.','jigoshop'),
			'placeholder' => __('No maximum', 'jigoshop'),
			'value' => $coupon->getOrderTotalMaximum(),
        ]); ?>
	</fieldset>
	<fieldset>
		<?php Forms::text([
			'name' => 'jigoshop_coupon[products]',
			'label' => __('Include products', 'jigoshop'),
			'description' => __('Control which products this coupon can apply to. If this is left blank it will have effect on all of the products.','jigoshop'),
			'value' => join(',', $coupon->getProducts()),
        ]); ?>
		<?php Forms::text([
			'name' => 'jigoshop_coupon[excluded_products]',
			'label' => __('Excluded products', 'jigoshop'),
			'description' => __('Control which products this coupon cannot be applied to.','jigoshop'),
			'value' => join(',', $coupon->getExcludedProducts()),
        ]); ?>
	</fieldset>
	<fieldset>
		<?php Forms::text([
			'name' => 'jigoshop_coupon[categories]',
			'label' => __('Include categories', 'jigoshop'),
			'description' => __('Control which categories this coupon can apply to. If this is left blank it will have effect on all of the products.','jigoshop'),
			'value' => join(',', $coupon->getCategories()),
        ]); ?>
		<?php Forms::text([
			'name' => 'jigoshop_coupon[excluded_categories]',
			'label' => __('Excluded categories', 'jigoshop'),
			'description' => __('Control which categories this coupon cannot be applied to.','jigoshop'),
			'value' => join(',', $coupon->getExcludedCategories()),
        ]); ?>
	</fieldset>
	<fieldset>
		<?php Forms::select([
			'name' => 'jigoshop_coupon[payment_methods]',
			'label' => __('Payment methods', 'jigoshop'),
			'description' => __('Control which payment methods are allowed for this coupon to be effective.','jigoshop'),
			'multiple' => true,
			'value' => $coupon->getPaymentMethods(),
			'options' => $paymentMethods,
        ]); ?>
	</fieldset>
</div>
