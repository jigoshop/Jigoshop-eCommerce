<?php
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Helper\Api;
use Jigoshop\Helper\Order;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $customer Customer
 * @var $orders array List of user's orders
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $myAccountUrl string URL to my account.
 */
?>
<h1><?php _e('My account &raquo; Orders', 'jigoshop-ecommerce'); ?></h1>
<?php Render::output('shop/messages', ['messages' => $messages]); ?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><?php _e('Orders list', 'jigoshop-ecommerce'); ?></h3>
	</div>
	<ul class="list-group">
		<?php foreach ($orders as $order): /** @var $order \Jigoshop\Entity\Order */?>
			<?php $unpaid = in_array($order->getStatus(), [Status::PENDING]); ?>
			<li class="list-group-item clearfix <?php $unpaid and print 'list-group-item-warning'; ?>">
				<h4 class="list-group-item-heading">
					<?= $order->getTitle(); ?>
					<?php if ($unpaid): ?>
						<a href="<?= Order::getPayLink($order); ?>" class="btn btn-success pull-right"><?php _e('Pay', 'jigoshop-ecommerce'); ?></a>
					<?php endif; ?>
					<a href="<?= Api::getEndpointUrl('orders', $order->getId(), get_permalink()); ?>" class="btn btn-primary pull-right"><?php _e('View', 'jigoshop-ecommerce'); ?></span></a>
				</h4>
				<dl class="dl-horizontal list-group-item-text">
					<dt><?php _e('Date', 'jigoshop-ecommerce'); ?></dt>
					<dd><?= $order->getCreatedAt()->format(_x('d.m.Y, H:i', 'account', 'jigoshop-ecommerce')); ?></dd>
					<dt><?php _e('Status', 'jigoshop-ecommerce'); ?></dt>
					<dd><?= Status::getName($order->getStatus()); ?></dd>
					<dt><?php _e('Total', 'jigoshop-ecommerce'); ?></dt>
					<dd><?= Product::formatPrice($order->getTotal(), '', $order->getCurrency()); ?></dd>
				</dl>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<a href="<?= $myAccountUrl; ?>" class="btn btn-default"><?php _e('Go back to My account', 'jigoshop-ecommerce'); ?></a>
