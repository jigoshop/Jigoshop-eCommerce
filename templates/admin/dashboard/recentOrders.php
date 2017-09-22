<?php
use Jigoshop\Entity\Order;
use Jigoshop\Helper;

/**
 * @var $orders array List of orders.
 */
?>
<ul class="recent-orders">
	<?php foreach ($orders as $order): /** @var $order Order */ ?>
		<?php $totalItems = array_reduce($order->getItems(), function($value, $item){
			/** @var $item Order\Item */
			return $value + $item->getQuantity();
		}, 0); ?>
		<li>
			<a href="<?= get_edit_post_link($order->getId()); ?>">#<?= $order->getNumber(); ?></a>
			<span class="order-customer"><?= $order->getCustomer()->getName() ? $order->getCustomer()->getName() : '&nbsp'; ?></span>
			<?= Helper\Order::getStatus($order); ?>
			<span class="order-time"><?= get_the_time(_x('M d, Y', 'dashboard', 'jigoshop-ecommerce'), $order->getId()); ?></span>
			<small>
				<?= count($order->getItems()); ?> <?= _n('Item', 'Items', count($order->getItems()), 'jigoshop'); ?>,
				<span	class="total-quantity"><?= __('Total Quantity', 'jigoshop-ecommerce'); ?> <?= $totalItems; ?></span>
				<span	class="order-cost"><?= Helper\Product::formatPrice($order->getTotal()); ?></span>
			</small>
		</li>
	<?php endforeach; ?>
</ul>
