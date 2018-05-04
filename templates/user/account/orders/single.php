<?php
use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Helper\Order;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $customer Customer
 * @var $order \Jigoshop\Entity\Order Order to display.
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $myAccountUrl string URL to my account.
 * @var $listUrl string URL to orders list.
 * @var $showWithTax bool Whether to show product price with or without tax.
 * @var $suffix string
 * @var $getTaxLabel \Closure Function to retrieve tax label.
 */
?>
<h1><?php printf(__('My account &raquo; Orders &raquo; %s', 'jigoshop-ecommerce'), $order->getTitle()); ?></h1>
<?php Render::output('shop/messages', ['messages' => $messages]); ?>
<dl class="dl-horizontal">
	<dt><?php _e('Made on', 'jigoshop-ecommerce'); ?></dt>
	<dd><?= $order->getCreatedAt()->format(_x('d.m.Y, H:i', 'account', 'jigoshop-ecommerce')); ?></dd>
	<dt><?php _e('Status', 'jigoshop-ecommerce'); ?></dt>
	<dd><?= Status::getName($order->getStatus()); ?></dd>
</dl>
<?php do_action('jigoshop\template\account\orders\single\before_customer', $order); ?>
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Billing address', 'jigoshop-ecommerce'); ?></h3>
		</div>
		<div class="panel-body clearfix">
			<?php Render::output('user/account/address', ['address' => $order->getCustomer()->getBillingAddress()]); ?>
		</div>
	</div>
</div>
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Shipping address', 'jigoshop-ecommerce'); ?></h3>
		</div>
		<div class="panel-body">
			<?php Render::output('user/account/address', ['address' => $order->getCustomer()->getShippingAddress()]); ?>
		</div>
	</div>
</div>
<?php do_action('jigoshop\template\account\orders\single\before_details', $order); ?>
<h3><?php _e('Order details', 'jigoshop-ecommerce'); ?></h3>
<table class="table table-hover">
	<thead>
		<tr>
			<th class="product-thumbnail"></th>
			<th class="product-name"><?php _e('Product Name', 'jigoshop-ecommerce'); ?></th>
			<th class="product-price"><?php _e('Unit Price', 'jigoshop-ecommerce'); ?></th>
			<th class="product-quantity"><?php _e('Quantity', 'jigoshop-ecommerce'); ?></th>
			<th class="product-subtotal"><?php _e('Price', 'jigoshop-ecommerce'); ?></th>
		</tr>
		<?php /** @deprecated */ do_action('jigoshop\checkout\table_head', $order); ?>
		<?php do_action('jigoshop\template\checkout\table_head', $order); ?>
	</thead>
	<tbody>
		<?php foreach($order->getItems() as $key => $item): /** @var $item \Jigoshop\Entity\Order\Item */ ?>
			<?php
		        $template = null;
                $template = apply_filters('jigoshop\template\shop\checkout\item', $template, $order, $key, $item);

                if($template === null) {
					Render::output('shop/checkout/item/'.$item->getType(), ['cart' => $order, 'key' => $key, 'item' => $item, 'showWithTax' => $showWithTax, 'suffix' => $suffix]);
				}
				else {
					echo $template;
				}
			?>
		<?php endforeach; ?>
		<?php /** @deprecated */ do_action('jigoshop\checkout\table_body', $order); ?>
		<?php do_action('jigoshop\template\checkout\table_body', $order); ?>
	</tbody>
	<tfoot>
		<tr id="product-subtotal">
			<?php $productSubtotal = $showWithTax ? $order->getProductSubtotal() + $order->getTotalTax() : $order->getProductSubtotal(); ?>
			<th scope="row" colspan="4" class="text-right"><?php _e('Products subtotal', 'jigoshop-ecommerce'); ?></th>
			<td><?= Product::formatPrice($productSubtotal, '', $order->getCurrency()); ?></td>
		</tr>
        <?php /** @deprecated */ do_action('jigoshop\checkout\table_foot', $order); ?>
        <?php do_action('jigoshop\template\checkout\table_foot', $order); ?>
    </tfoot>
</table>
<?php do_action('jigoshop\template\account\orders\single\before_totals', $order); ?>
<div id="cart-collaterals">
	<div id="cart-totals" class="panel panel-primary pull-right">
		<div class="panel-heading"><h2 class="panel-title"><?php _e('Order Totals', 'jigoshop-ecommerce'); ?></h2></div>
		<table class="table">
			<tbody>
			<?php if ($order->getShippingMethod()): ?>
				<tr id="shipping-calculator">
					<th scope="row">
						<?php _e('Shipping', 'jigoshop-ecommerce'); ?>
					</th>
					<td>
						<?= Product::formatPrice($order->getShippingPrice(), '', $order->getCurrency()); ?>
						<p class="method">
							<small><?= $order->getShippingMethod()->getName(); ?></small>
						</p>
					</td>
				</tr>
			<?php endif; ?>
			<tr id="cart-subtotal">
				<th scope="row"><?php _e('Subtotal', 'jigoshop-ecommerce'); ?></th>
				<td><?= Product::formatPrice($order->getSubtotal(), '', $order->getCurrency()); ?></td>
			</tr>
			<?php foreach ($order->getCombinedTax() as $taxClass => $tax): ?>
				<?php if ($tax > 0): ?>
					<tr id="tax-<?= $taxClass; ?>">
						<th scope="row"><?= $getTaxLabel($taxClass); ?></th>
						<td><?= Product::formatPrice($tax, '', $order->getCurrency()); ?></td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php 
			if($order->getDiscount() > 0) {
			?>
			<tr id="cart-discount">
				<th scope="row"><?php _e('Discount', 'jigoshop-ecommerce'); ?></th>
				<td><?= Product::formatPrice($order->getDiscount(), '', $order->getCurrency()); ?></td>
			</tr>
			<?php 
			}
			?>
			<?php 
			if($order->getProcessingFee() > 0) {
			?>
			<tr id="cart-payment-processing-fee">
				<th scope="row"><?php echo strip_tags(sprintf(__('Payment processing fee', 'jigoshop-ecommerce'), $order->getProcessingFeeAsPercent())); ?></th>
				<td><?php echo Product::formatPrice($order->getProcessingFee(), '', $order->getCurrency()); ?></td>
			</tr>
			<?php 
			}
			?>
			<tr id="cart-total">
				<th scope="row"><?php _e('Total', 'jigoshop-ecommerce'); ?></th>
				<td><?= Product::formatPrice($order->getTotal(), '', $order->getCurrency()); ?></td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
<a href="<?= $myAccountUrl; ?>" class="btn btn-default"><?php _e('Go back to My account', 'jigoshop-ecommerce'); ?></a>
<a href="<?= $listUrl; ?>" class="btn btn-default"><?php _e('Go back to orders list', 'jigoshop-ecommerce'); ?></a>
<?php if (in_array($order->getStatus(), [Status::PENDING])): ?>
	<a href="<?= Order::getPayLink($order); ?>" class="btn btn-success pull-right"><?php _e('Pay', 'jigoshop-ecommerce'); ?></a>
<?php endif; ?>
