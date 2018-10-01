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
			'label' => __('Code', 'jigoshop-ecommerce'),
			'description' => $coupon->getCode() ? '' : __('Will not appear until coupon is saved.  This is the front end code for use on the Cart.','jigoshop-ecommerce'),
			'value' => $coupon->getCode(),
        ]); ?>
	</fieldset>
	<fieldset>
		<?php Forms::select([
			'id' => 'jigoshop_coupon_type',
			'name' => 'jigoshop_coupon[type]',
			'label' => __('Type', 'jigoshop-ecommerce'),
			'options' => $types,
			'value' => $coupon->getType(),
        ]); ?>
		<?php Forms::number([
			'name' => 'jigoshop_coupon[amount]',
			'label' => __('Amount', 'jigoshop-ecommerce'),
			'type' => 'number',
			'description' => __('Enter an amount e.g. 9.99.','jigoshop-ecommerce'),
			'tip' => __('Amount this coupon is worth. If it is a percentage, just include the number without the percentage sign.', 'jigoshop-ecommerce'),
			'placeholder' => \Jigoshop\Helper\Product::formatNumericPrice(0),
			'value' => $coupon->getAmount(),
        ], "float"); ?>
	</fieldset>
	<fieldset>
		<?php Forms::daterange([
			'id' => 'coupon_date',
			'name' => [
				'from' => 'jigoshop_coupon[from]',
				'to' => 'jigoshop_coupon[to]',
            ],
			'label' => __('Coupon date', 'jigoshop-ecommerce'),
			'tip' => __('Choose between which dates this coupon is enabled.  Leave empty for any date.','jigoshop-ecommerce'),
			'placeholder' => __('Any date', 'jigoshop-ecommerce'),
			'value' => [
				'from' => $coupon->getFrom() ? $coupon->getFrom()->format('m/d/Y') : '',
				'to' =>  $coupon->getTo() ? $coupon->getTo()->format('m/d/Y') : '',
            ],
        ]);
		?>
	</fieldset>
	<fieldset>
		<?php Forms::number([
			'name' => 'jigoshop_coupon[usage_limit]',
			'label' => __('Usage limit', 'jigoshop-ecommerce'),
			'type' => 'number',
			'description' => sprintf(__('Times used: %s','jigoshop-ecommerce'), $coupon->getUsage()),
			'tip' => __('Control how many times this coupon may be used.', 'jigoshop-ecommerce'),
			'placeholder' => 0,
			'value' => $coupon->getUsageLimit(),
        ]); ?>
		<?php Forms::checkbox([
			'name' => 'jigoshop_coupon[individual_use]',
			'label' => __('Individual use', 'jigoshop-ecommerce'),
			'description' => __('Prevent other coupons from being used while this one is applied to the Cart.','jigoshop-ecommerce'),
			'checked' => $coupon->isIndividualUse(),
        ]); ?>
		<?php Forms::checkbox([
			'name' => 'jigoshop_coupon[free_shipping]',
			'label' => __('Free shipping', 'jigoshop-ecommerce'),
			'description' => __('Show the Free Shipping method on the Checkout with this enabled.','jigoshop-ecommerce'),
			'checked' => $coupon->isFreeShipping(),
        ]); ?>
	</fieldset>
	<fieldset>
		<?php Forms::number([
			'name' => 'jigoshop_coupon[order_total_minimum]',
			'label' => __('Order total minimum', 'jigoshop-ecommerce'),
			'type' => 'number',
			'description' => __('Set the required minimum subtotal for this coupon to be valid on an order.','jigoshop-ecommerce'),
			'placeholder' => __('No minimum', 'jigoshop-ecommerce'),
			'value' => $coupon->getOrderTotalMinimum(),
        ], "currency"); ?>
		<?php Forms::number([
			'name' => 'jigoshop_coupon[order_total_maximum]',
			'label' => __('Order total maximum', 'jigoshop-ecommerce'),
			'type' => 'number',
			'description' => __('Set the required maximum subtotal for this coupon to be valid on an order.','jigoshop-ecommerce'),
			'placeholder' => __('No maximum', 'jigoshop-ecommerce'),
			'value' => $coupon->getOrderTotalMaximum(),
        ], "currency"); ?>
	</fieldset>
	<div id="jigoshop_coupon_type_product">
		<fieldset>
			<?php Forms::text([
				'name' => 'jigoshop_coupon[products]',
				'label' => __('Include products', 'jigoshop-ecommerce'),
				'description' => __('Control which products this coupon can apply to. If this is left blank it will have effect on all of the products.','jigoshop-ecommerce'),
				'value' => join(',', $coupon->getProducts()),
	        ]); ?>
			<?php Forms::text([
				'name' => 'jigoshop_coupon[excluded_products]',
				'label' => __('Excluded products', 'jigoshop-ecommerce'),
				'description' => __('Control which products this coupon cannot be applied to.','jigoshop-ecommerce'),
				'value' => join(',', $coupon->getExcludedProducts()),
	        ]); ?>
		</fieldset>
		<fieldset>
			<?php Forms::text([
				'name' => 'jigoshop_coupon[categories]',
				'label' => __('Include categories', 'jigoshop-ecommerce'),
				'description' => __('Control which categories this coupon can apply to. If this is left blank it will have effect on all of the products.','jigoshop-ecommerce'),
				'value' => join(',', $coupon->getCategories()),
	        ]); ?>
			<?php Forms::text([
				'name' => 'jigoshop_coupon[excluded_categories]',
				'label' => __('Excluded categories', 'jigoshop-ecommerce'),
				'description' => __('Control which categories this coupon cannot be applied to.','jigoshop-ecommerce'),
				'value' => join(',', $coupon->getExcludedCategories()),
	        ]); ?>
		</fieldset>
	</div>
	<fieldset>
		<?php Forms::select([
			'name' => 'jigoshop_coupon[payment_methods]',
			'label' => __('Payment methods', 'jigoshop-ecommerce'),
			'description' => __('Control which payment methods are allowed for this coupon to be effective.','jigoshop-ecommerce'),
			'multiple' => true,
			'value' => $coupon->getPaymentMethods(),
			'options' => $paymentMethods,
        ]); ?>
	</fieldset>
</div>
