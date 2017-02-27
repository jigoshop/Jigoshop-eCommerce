<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $order \Jigoshop\Entity\Order The order.
 * @var $showWithTax bool Whether to show product price with or without tax.
 * @var $suffix
 * @var $termsUrl string URL to Terms and Conditions page (if applicable).
 * @var $myAccountUrl string URL to My account page.
 * @var $myOrdersUrl string URL to My orders page.
 * @var $getTaxLabel \Closure Function to retrieve tax label.
 * @var $paymentMethods array List of available payment methods.
 */
?>

<h1><?php printf(__('Checkout &raquo; Pay &raquo; %s', 'jigoshop'), $order->getTitle()); ?></h1>
<?php Render::output('shop/messages', array('messages' => $messages)); ?>
<form action="" role="form" method="post" id="checkout">
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
			<?php Render::output('shop/checkout/item/'.$item->getType(), array('cart' => $order, 'key' => $key, 'item' => $item, 'showWithTax' => $showWithTax, 'suffix' => $suffix)); ?>
		<?php endforeach; ?>
		<?php /** @deprecated */ do_action('jigoshop\checkout\table_body', $order); ?>
		<?php do_action('jigoshop\template\checkout\table_body', $order); ?>
		</tbody>
		<tfoot>
		<tr id="product-subtotal">
			<?php $productSubtotal = $showWithTax ? $order->getProductSubtotal() + $order->getTotalTax() : $order->getProductSubtotal(); ?>
			<th scope="row" colspan="4" class="text-right"><?php _e('Products subtotal', 'jigoshop'); ?></th>
			<td><?php echo Product::formatPrice($productSubtotal); ?></td>
		</tr>
        <?php /** @deprecated */ do_action('jigoshop\checkout\table_foot', $order); ?>
        <?php do_action('jigoshop\template\checkout\table_foot', $order); ?>

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
							<?php echo Product::formatPrice($order->getShippingPrice()); ?>
							<p class="method">
								<small><?php echo $order->getShippingMethod()->getName(); ?></small>
							</p>
						</td>
					</tr>
				<?php endif; ?>
				<tr id="cart-subtotal">
					<th scope="row"><?php _e('Subtotal', 'jigoshop'); ?></th>
					<td><?php echo Product::formatPrice($order->getSubtotal()); ?></td>
				</tr>
				<?php foreach ($order->getCombinedTax() as $taxClass => $tax): //TODO: Fix showing tax after registering ?>
					<?php if ($tax > 0): ?>
						<tr id="tax-<?php echo $taxClass; ?>">
							<th scope="row"><?php echo $getTaxLabel($taxClass); ?></th>
							<td><?php echo Product::formatPrice($tax); ?></td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
				<tr id="cart-discount"<?php $order->getDiscount() == 0 and print ' class="not-active"'; ?>>
					<th scope="row"><?php _e('Discount', 'jigoshop'); ?></th>
					<td><?php echo Product::formatPrice($order->getDiscount()); ?></td>
				</tr>
				<tr id="cart-total">
					<th scope="row"><?php _e('Total', 'jigoshop'); ?></th>
					<td><?php echo Product::formatPrice($order->getTotal()); ?></td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?php if(count($paymentMethods) > 0): ?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Select payment method', 'jigoshop'); ?></h3>
		</div>
		<ul class="list-group" id="payment-methods">
			<?php foreach($paymentMethods as $method): /** @var $method \Jigoshop\Payment\Method */ ?>
				<li class="list-group-item" id="payment-<?php echo $method->getId(); ?>">
					<label>
						<input type="radio" name="payment_method" value="<?php echo $method->getId(); ?>" />
						<?php echo $method->getName(); ?>
					</label>
					<div class="well well-sm">
						<?php $method->render(); ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
		<noscript>
			<style type="text/css">
				.jigoshop form #payment-methods li > div {
					display: block;
				}
			</style>
		</noscript>
	</div>
	<?php endif; ?>
	<?php if (!empty($termsUrl)): ?>
	<div class="col-md-6 col-xs-12 pull-right toggle-panels">
		<?php Forms::checkbox(array(
			'name' => 'terms',
			'description' => sprintf(__('I accept the <a href="%s">Terms &amp; Conditions</a>'), $termsUrl),
			'checked' => false,
		)); ?>
	</div>
		<div class="clear"></div>
	<?php endif; ?>
	<a class="btn btn-default" href="<?php echo $myAccountUrl; ?>"><?php _e('Go back to My account', 'jigoshop'); ?></a>
	<a class="btn btn-default" href="<?php echo $myOrdersUrl; ?>"><?php _e('Go back to My orders', 'jigoshop'); ?></a>
	<button class="btn btn-success pull-right clearfix" name="action" value="purchase" type="submit"><?php _e('Pay', 'jigoshop'); ?></button>
</form>
