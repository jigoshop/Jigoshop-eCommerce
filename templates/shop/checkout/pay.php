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

<h1><?php printf(__('Checkout &raquo; Pay &raquo; %s', 'jigoshop-ecommerce'), $order->getTitle()); ?></h1>
<?php Render::output('shop/messages', ['messages' => $messages]); ?>
<form action="" role="form" method="post" id="checkout">
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
            $template = apply_filters('jigoshop\template\shop\checkout\pay\item', $template, $order, $key, $item, $showWithTax, $suffix);

            if($template === null) {
                Render::output('shop/checkout/item/'.$item->getType(), [
                    'cart' => $order,
                    'key' => $key,
                    'item' => $item,
                    'showWithTax' => $showWithTax,
                    'suffix' => $suffix
                ]);
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
        <?php /** @deprecated */ do_action('jigoshop\checkout\table_foot', $order); ?>
        <?php do_action('jigoshop\template\checkout\table_foot', $order); ?>

        </tfoot>
	</table>
	<div id="cart-collaterals">
		<div id="cart-totals" class="panel panel-primary">
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
				<tr id="cart-total">
					<th scope="row"><?php _e('Total', 'jigoshop-ecommerce'); ?></th>
					<td><?= Product::formatPrice($order->getTotal()); ?></td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?php if(count($paymentMethods) > 0): ?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Select payment method', 'jigoshop-ecommerce'); ?></h3>
		</div>
		<ul class="list-group" id="payment-methods">
			<?php foreach($paymentMethods as $method): /** @var $method \Jigoshop\Payment\Method */ ?>
				<li class="list-group-item" id="payment-<?= $method->getId(); ?>">
					<label>
						<input type="radio" name="payment_method" value="<?= $method->getId(); ?>" />
						<?= $method->getName(); ?>
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
		<?php Forms::checkbox([
			'name' => 'terms',
			'description' => sprintf(__('I accept the <a href="%s">Terms &amp; Conditions</a>'), $termsUrl),
			'checked' => false,
        ]); ?>
	</div>
		<div class="clear"></div>
	<?php endif; ?>
	<a class="btn btn-default" href="<?= $myAccountUrl; ?>"><?php _e('Go back to My account', 'jigoshop-ecommerce'); ?></a>
	<a class="btn btn-default" href="<?= $myOrdersUrl; ?>"><?php _e('Go back to My orders', 'jigoshop-ecommerce'); ?></a>
	<button class="btn btn-success pull-right clearfix" name="action" value="purchase" type="submit"><?php _e('Pay', 'jigoshop-ecommerce'); ?></button>
</form>