<?php
use Jigoshop\Entity\Order\Status;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $content string Contents of cart page
 * @var $order \Jigoshop\Entity\Order The order.
 * @var $cancelUrl string URL to cancel order.
 * @var $shopUrl string URL to product list page.
 * @var $showWithTax bool Whether to show product price with or without tax.
 * @var $suffix
 * @var $getTaxLabel \Closure Function to retrieve tax label.
 */
?>

<h1><?php _e('Thank you for your order', 'jigoshop'); ?></h1>
<?php Render::output('shop/messages', ['messages' => $messages]); ?>
<?= wpautop(wptexturize($content)); ?>
<dl class="dl-horizontal">
	<dt><?php _e('Order number', 'jigoshop'); ?></dt>
	<dd><?= $order->getNumber(); ?></dd>
	<dt><?php _e('Status', 'jigoshop'); ?></dt>
	<dd><?= Status::getName($order->getStatus()); ?></dd>
</dl>
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Billing address', 'jigoshop'); ?></h3>
		</div>
		<div class="panel-body clearfix">
			<?php Render::output('user/account/address', ['address' => $order->getCustomer()->getBillingAddress()]); ?>
		</div>
	</div>
</div>
<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Shipping address', 'jigoshop'); ?></h3>
		</div>
		<div class="panel-body">
			<?php Render::output('user/account/address', ['address' => $order->getCustomer()->getShippingAddress()]); ?>
		</div>
	</div>
</div>
<h3><?php _e('Order details', 'jigoshop'); ?></h3>
<table class="table table-hover">
	<thead>
	<tr>
		<th class="product-thumbnail"></th>
		<th class="product-name"><?php _e('Product Name', 'jigoshop'); ?></th>
		<th class="product-price"><?php _e('Unit Price', 'jigoshop'); ?></th>
		<th class="product-quantity"><?php _e('Quantity', 'jigoshop'); ?></th>
		<th class="product-subtotal"><?php _e('Price', 'jigoshop'); ?></th>
	</tr>
	<?php /** @deprecated */ do_action('jigoshop\checkout\table_head', $order); ?>
	<?php do_action('jigoshop\template\checkout\table_head', $order); ?>
	</thead>
	<tbody>
	<?php foreach($order->getItems() as $key => $item): /** @var $item \Jigoshop\Entity\Order\Item */ ?>
		<?php Render::output('shop/checkout/item/'.$item->getType(), ['cart' => $order, 'key' => $key, 'item' => $item, 'showWithTax' => $showWithTax, 'suffix' => $suffix]); ?>
	<?php endforeach; ?>
	<?php /** @deprecated */ do_action('jigoshop\checkout\table_body', $order); ?>
	<?php do_action('jigoshop\template\checkout\table_body', $order); ?>
	</tbody>
	<tfoot>
	<tr id="product-subtotal">
		<?php $productSubtotal = $showWithTax ? $order->getProductSubtotal() + $order->getTotalTax() : $order->getProductSubtotal(); ?>
		<th scope="row" colspan="4" class="text-right"><?php _e('Products subtotal', 'jigoshop'); ?></th>
		<td><?= Product::formatPrice($productSubtotal); ?></td>
	</tr>
    <?php /** @deprecated */ do_action('jigoshop\checkout\table_body', $order); ?>
    <?php do_action('jigoshop\template\checkout\table_body', $order); ?>
    </tfoot>
</table>
<div id="cart-collaterals">
	<div id="cart-totals" class="panel panel-primary pull-right">
		<div class="panel-heading"><h2 class="panel-title"><?php _e('Order Totals', 'jigoshop'); ?></h2></div>
		<table class="table">
			<tbody>
			<?php if ($order->getShippingMethod()): ?>
				<tr id="shipping-calculator">
					<th scope="row">
						<?php _e('Shipping', 'jigoshop'); ?>
					</th>
					<td>
						<?= Product::formatPrice($order->getShippingPrice()); ?>
						<p class="method">
							<small><?= $order->getShippingMethod()->getName(); ?></small>
						</p>
					</td>
				</tr>
			<?php endif; ?>
			<tr id="cart-subtotal">
				<th scope="row"><?php _e('Subtotal', 'jigoshop'); ?></th>
				<td><?= Product::formatPrice($order->getSubtotal()); ?></td>
			</tr>
			<?php foreach ($order->getCombinedTax() as $taxClass => $tax): //TODO: Fix showing tax after registering ?>
				<?php if ($tax > 0): ?>
					<tr id="tax-<?= $taxClass; ?>">
						<th scope="row"><?= $getTaxLabel($taxClass); ?></th>
						<td><?= Product::formatPrice($tax); ?></td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
			<tr id="cart-discount"<?php $order->getDiscount() == 0 and print ' class="not-active"'; ?>>
				<th scope="row"><?php _e('Discount', 'jigoshop'); ?></th>
				<td><?= Product::formatPrice($order->getDiscount()); ?></td>
			</tr>
			<tr id="cart-total">
				<th scope="row"><?php _e('Total', 'jigoshop'); ?></th>
				<td><?= Product::formatPrice($order->getTotal()); ?></td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
<?php if ($order->getStatus() == Status::PENDING): ?>
<a href="<?= $cancelUrl; ?>" class="btn btn-danger"><?php _e('Cancel this order', 'jigoshop'); ?></a>
<?php endif; ?>
<a href="<?= $shopUrl; ?>" class="btn btn-primary pull-right"><?php _e('Continue shopping', 'jigoshop'); ?></a>
