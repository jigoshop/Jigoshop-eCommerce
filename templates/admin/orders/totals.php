<?php
use Jigoshop\Helper\Product;

/**
 * @var $order \Jigoshop\Entity\Order
 * @var $getTaxLabel \Closure
 */
?>
<div class="jigoshop">
	<div>
		<?php if ($order->getProductSubtotal() != $order->getTotal()): ?>
			<div class="font-bold pull-left" scope="row"><?php printf(__('Product subtotal%s', 'jigoshop-ecommerce'), ':&nbsp;'); ?></div>
			<div class="pull-left"><?= Product::formatPrice($order->getProductSubtotal(), '', $order->getCurrency()); ?></div>
			<div class="clear"></div>
		<?php endif; ?>
		<?php if ($order->getShippingPrice() > 0): ?>
			<div class="font-bold pull-left" scope="row"><?php printf(__('Shipping%s', 'jigoshop-ecommerce'), ':&nbsp;'); ?></div>
			<div class="pull-left"><?= Product::formatPrice($order->getShippingPrice(), '', $order->getCurrency()); ?></div>
			<div class="clear"></div>
		<?php endif; ?>
		<?php do_action('jigoshop\admin\orders\totals\after_shipping'); ?>
		<?php foreach ($order->getCombinedTax() as $taxClass => $tax): ?>
			<?php if ($tax > 0): ?>
				<div class="font-bold pull-left" scope="row"><?= $getTaxLabel($taxClass) . ':&nbsp;'; ?></div>
				<div class="pull-left"><?= Product::formatPrice($tax, '', $order->getCurrency()); ?></div>
				<div class="clear"></div>
			<?php endif; ?>
		<?php endforeach; ?>
		<div class="font-bold pull-left" scope="row"><?php printf(__('Total%s', 'jigoshop-ecommerce'), ':&nbsp;'); ?></div>
		<div class="pull-left"><?= Product::formatPrice($order->getTotal(), '', $order->getCurrency()); ?></div>
	</div>
</div>
