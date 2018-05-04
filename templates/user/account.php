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

<h1><?php _e('My account', 'jigoshop-ecommerce'); ?></h1>
<?php Render::output('shop/messages', ['messages' => $messages]); ?>
<?= wpautop(wptexturize($content)); ?>
<div class="row clearfix">
    <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php _e('Billing address', 'jigoshop-ecommerce'); ?></h3>
                    <a href="<?= $editBillingAddressUrl; ?>" class="btn btn-xs btn-primary pull-right"><?php _e('Edit', 'jigoshop-ecommerce'); ?></a>
                </div>
                <div class="panel-body clearfix">
                    <?php Render::output('user/account/address', ['address' => $customer->getBillingAddress()]); ?>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php _e('Shipping address', 'jigoshop-ecommerce'); ?></h3>
                    <a href="<?= $editShippingAddressUrl; ?>" class="btn btn-xs btn-primary pull-right"><?php _e('Edit', 'jigoshop-ecommerce'); ?></a>
                </div>
                <div class="panel-body">
                    <?php Render::output('user/account/address', ['address' => $customer->getShippingAddress()]); ?>
                </div>
            </div>
            <?php if(count($downloadableItems)): ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php _e('Your Downloads', 'jigoshop-ecommerce'); ?></h3>
                    </div>
                    <ul class="list-group">
                        <?php foreach($downloadableItems as $data): ?>
                            <li class="list-group-item downloadable-item">
                                <a href="<?= Order::getItemDownloadLink($data['order'], $data['item']); ?>"><?= $data['item']->getName() ?></a>
                                <?php if($data['item']->getMeta('downloads')->getValue() < 0): ?>
                                    <span>
                                        <?= sprintf(__('Left: %d', 'jigoshop-ecommerce'), $data['item']->getMeta('downloads')->getValue()); ?>
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
                <h3 class="panel-title"><?php _e('Account options', 'jigoshop-ecommerce'); ?></h3>
            </div>
            <ul class="list-group">
                <li class="list-group-item"><a href="<?= $changePasswordUrl; ?>"><?php _e('Change password', 'jigoshop-ecommerce'); ?></a></li>
                <li class="list-group-item"><a href="<?= $myOrdersUrl; ?>"><?php _e('My orders', 'jigoshop-ecommerce'); ?></a></li>
            </ul>
        </div>
        <div class="panel panel-warning" id="unpaid-orders">
            <div class="panel-heading">
                <h3 class="panel-title"><?php _e('Unpaid orders', 'jigoshop-ecommerce'); ?></h3>
            </div>
            <ul class="list-group">
                <?php foreach ($unpaidOrders as $order): ?>
                <li class="list-group-item clearfix">
                    <h4 class="list-group-item-heading"><?= $order->getTitle(); ?></h4>
                    <dl class="dl-horizontal list-group-item-text">
                        <dt><?php _e('Date', 'jigoshop-ecommerce'); ?></dt>
                        <dd><?= $order->getCreatedAt()->format(_x('d.m.Y, H:i', 'account', 'jigoshop-ecommerce')); ?></dd>
                        <dt><?php _e('Status', 'jigoshop-ecommerce'); ?></dt>
                        <dd><?= Status::getName($order->getStatus()); ?></dd>
                        <dt><?php _e('Total', 'jigoshop-ecommerce'); ?></dt>
                        <dd><?= Product::formatPrice($order->getTotal(), '', $order->getCurrency()); ?></dd>
                    </dl>
                    <a href="<?= Order::getPayLink($order); ?>" class="btn btn-success pull-right"><?php _e('Pay', 'jigoshop-ecommerce'); ?></a>
                </li>
                <?php endforeach; ?>
                <li class="list-group-item">
                    <a href="<?= $myOrdersUrl; ?>" class="btn btn-default"><?php _e('See more...', 'jigoshop-ecommerce'); ?></span></a>
                </li>
            </ul>
        </div>
        <?php do_action('jigoshop\user\account\secondary_panels', $customer); ?>
    </div>
    <?php do_action('jigoshop\user\account', $customer); ?>
</div>

