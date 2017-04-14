<?php
use Jigoshop\Entity\Order\Status;
use Jigoshop\Helper\Order;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $content string Contents of cart page
 * @var $customer \Jigoshop\Entity\Customer The customer.
 * @var $editBillingAddressUrl string URL to billing address edition page.
 * @var $editShippingAddressUrl string URL to shipping address edition page.
 * @var $changePasswordUrl string URL to password changing page.
 * @var $myOrdersUrl string URL to My orders page.
 * @var $unpaidOrders \Jigoshop\Entity\Order[]
 * @var $downloadableItems \Jigoshop\Entity\Order\Item[]
 */
?>

<h1><?php _e('My account', 'jigoshop'); ?></h1>
<?php Render::output('shop/messages', array('messages' => $messages)); ?>
<?= wpautop(wptexturize($content)); ?>
<div class="col-md-8">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><?php _e('Billing address', 'jigoshop'); ?></h3>
				<a href="<?= $editBillingAddressUrl; ?>" class="btn btn-xs btn-primary pull-right"><?php _e('Edit', 'jigoshop'); ?></a>
			</div>
			<div class="panel-body clearfix">
				<?php Render::output('user/account/address', array('address' => $customer->getBillingAddress())); ?>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><?php _e('Shipping address', 'jigoshop'); ?></h3>
				<a href="<?= $editShippingAddressUrl; ?>" class="btn btn-xs btn-primary pull-right"><?php _e('Edit', 'jigoshop'); ?></a>
			</div>
			<div class="panel-body">
				<?php Render::output('user/account/address', array('address' => $customer->getShippingAddress())); ?>
			</div>
		</div>
        <?php if(count($downloadableItems)): ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php _e('Your Downloads', 'jigoshop'); ?></h3>
                </div>
                <ul class="list-group">
                    <?php foreach($downloadableItems as $data): ?>
                        <li class="list-group-item downloadable-item">
                            <a href="<?= Order::getItemDownloadLink($data['order'], $data['item']); ?>"><?= $data['item']->getName() ?></a>
                            <?php if($data['item']->getMeta('downloads')->getValue() < 0): ?>
                                <span>
                                    <?= sprintf(__('Left: %d', 'jigoshop'), $data['item']->getMeta('downloads')->getValue()); ?>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
	<?php do_action('jigoshop\user\account\primary_panels', $customer); ?>
</div>
<div class="col-md-4">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Account options', 'jigoshop'); ?></h3>
		</div>
		<ul class="list-group">
			<li class="list-group-item"><a href="<?= $changePasswordUrl; ?>"><?php _e('Change password', 'jigoshop'); ?></a></li>
			<li class="list-group-item"><a href="<?= $myOrdersUrl; ?>"><?php _e('My orders', 'jigoshop'); ?></a></li>
		</ul>
	</div>
	<div class="panel panel-warning" id="unpaid-orders">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Unpaid orders', 'jigoshop'); ?></h3>
		</div>
		<ul class="list-group">
			<?php foreach ($unpaidOrders as $order): ?>
			<li class="list-group-item clearfix">
				<h4 class="list-group-item-heading"><?= $order->getTitle(); ?></h4>
				<dl class="dl-horizontal list-group-item-text">
					<dt><?php _e('Date', 'jigoshop'); ?></dt>
					<dd><?= $order->getCreatedAt()->format(_x('d.m.Y, H:i', 'account', 'jigoshop')); ?></dd>
					<dt><?php _e('Status', 'jigoshop'); ?></dt>
					<dd><?= Status::getName($order->getStatus()); ?></dd>
					<dt><?php _e('Total', 'jigoshop'); ?></dt>
					<dd><?= Product::formatPrice($order->getTotal()); ?></dd>
				</dl>
				<a href="<?= Order::getPayLink($order); ?>" class="btn btn-success pull-right"><?php _e('Pay', 'jigoshop'); ?></a>
			</li>
			<?php endforeach; ?>
			<li class="list-group-item">
				<a href="<?= $myOrdersUrl; ?>" class="btn btn-default"><?php _e('See more...', 'jigoshop'); ?></span></a>
			</li>
		</ul>
	</div>
	<?php do_action('jigoshop\user\account\secondary_panels', $customer); ?>
</div>
<?php do_action('jigoshop\user\account', $customer);
