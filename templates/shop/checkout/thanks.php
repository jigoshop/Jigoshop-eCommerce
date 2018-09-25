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

<h1><?php _e('Thank you for your order', 'jigoshop-ecommerce'); ?></h1>
<?php Render::output('shop/messages', ['messages' => $messages]); ?>
<?= wpautop(wptexturize($content)); ?>
<dl class="dl-horizontal">
	<dt><?php _e('Order number', 'jigoshop-ecommerce'); ?></dt>
	<dd><?= $order->getNumber(); ?></dd>
	<dt><?php _e('Status', 'jigoshop-ecommerce'); ?></dt>
	<dd><?= Status::getName($order->getStatus()); ?></dd>
</dl>
<div class="row">
    <div class="col-md-6">
        <div class="panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?php _e('Billing address', 'jigoshop-ecommerce'); ?></h3>
            </div>
            <div class="panel-body clearfix">
                <?php Render::output('user/account/address', ['address' => $order->getCustomer()->getBillingAddress()]); ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?php _e('Shipping address', 'jigoshop-ecommerce'); ?></h3>
            </div>
            <div class="panel-body">
                <?php Render::output('user/account/address', ['address' => $order->getCustomer()->getShippingAddress()]); ?>
            </div>
        </div>
    </div>
</div>
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
		<td><?= Product::formatPrice($productSubtotal); ?></td>
	</tr>
    <?php /** @deprecated */ do_action('jigoshop\checkout\table_body', $order); ?>
    <?php do_action('jigoshop\template\checkout\table_body', $order); ?>
    </tfoot>
</table>
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
						<?= Product::formatPrice($order->getShippingPrice()); ?>
						<p class="method">
							<small><?= $order->getShippingMethod()->getName(); ?></small>
						</p>
					</td>
				</tr>
			<?php endif; ?>
			<tr id="cart-subtotal">
				<th scope="row"><?php _e('Subtotal', 'jigoshop-ecommerce'); ?></th>
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
				<th scope="row"><?php _e('Discount', 'jigoshop-ecommerce'); ?></th>
				<td><?= Product::formatPrice($order->getDiscount()); ?></td>
			</tr>
			<?php 
			if($order->getProcessingFee()) {
			?>
			<tr id="cart-payment-processing-fee">
				<th scope="row"><?php echo strip_tags(sprintf(__('Payment processing fee (%s)', 'jigoshop-ecommerce'), $order->getProcessingFeeAsPercent())); ?></th>
				<td><?php echo Product::formatPrice($order->getProcessingFee()); ?></td>
			</tr>
			<?php 
			}
			?>
			<tr id="cart-total">
				<th scope="row"><?php _e('Total', 'jigoshop-ecommerce'); ?></th>
				<td><?= Product::formatPrice($order->getTotal()); ?></td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
<?php if ($order->getStatus() == Status::PENDING): ?>
<a href="<?= $cancelUrl; ?>" class="btn btn-danger"><?php _e('Cancel this order', 'jigoshop-ecommerce'); ?></a>
<?php endif; ?>
<a href="<?= $shopUrl; ?>" class="btn btn-primary pull-right"><?php _e('Continue shopping', 'jigoshop-ecommerce'); ?></a>
