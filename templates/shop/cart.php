<?php
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Tax;

/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $productService \Jigoshop\Service\ProductServiceInterface Product service.
 * @var $content string Contents of cart page
 * @var $cart \Jigoshop\Entity\Cart Cart object.
 * @var $customer \Jigoshop\Entity\Customer Current customer.
 * @var $shippingMethods array List of available shipping methods.
 * @var $shopUrl string Url to shop (product list).
 * @var $showWithTax bool Whether to show product price with or without tax.
 * @var $suffix string
 * @var $showShippingCalculator bool Whether to show shipping calculator.
 * @var $termsUrl string Url to terms and conditions page.
 */
?>
<h1><?php _e('Cart', 'jigoshop'); ?></h1>
<?php Render::output('shop/messages', array('messages' => $messages)); ?>
<?php echo wpautop(wptexturize($content)); ?>
<?php if ($cart->isEmpty()): ?>
	<?php Render::output('shop/cart/empty', array('shopUrl' => $shopUrl)); ?>
<?php else: ?>
    <?php do_action('jigoshop\template\cart\form\before'); ?>
	<form id="cart" role="form" action="" method="post">
		<?php Render::output('shop/cart/mobile', [
			'cart' => $cart,
			'showWithTax' => $showWithTax,
            'suffix' => $suffix,
		]); ?>
		<table class="table table-hover">
			<thead>
				<tr>
					<th class="product-remove"></th>
					<th class="product-thumbnail"></th>
					<th class="product-name"><?php _e('Product Name', 'jigoshop'); ?></th>
					<th class="product-price"><?php _e('Unit Price', 'jigoshop'); ?></th>
					<th class="product-quantity"><?php _e('Quantity', 'jigoshop'); ?></th>
					<th class="product-subtotal"><?php _e('Price', 'jigoshop'); ?></th>
				</tr>
				<?php /** @deprectated */ do_action('jigoshop\cart\table_head', $cart); ?>
				<?php do_action('jigoshop\template\cart\table_head', $cart); ?>
			</thead>
			<tbody>
				<?php foreach($cart->getItems() as $key => $item): /** @var $item \Jigoshop\Entity\Order\Item */ ?>
					<?php Render::output('shop/cart/item/'.$item->getType(), array('cart' => $cart, 'key' => $key, 'item' => $item, 'showWithTax' => $showWithTax, 'suffix' => $suffix)); ?>
				<?php endforeach; ?>
				<?php /** @deprectated */ do_action('jigoshop\cart\table_body', $cart); ?>
                <?php do_action('jigoshop\template\cart\table_body', $cart); ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4">
						<?php \Jigoshop\Helper\Forms::text(array(
							'id' => 'jigoshop_coupons',
							'name' => 'jigoshop_coupons',
							'placeholder' => __('Enter coupons...', 'jigoshop'),
							'value' => join(',', array_map(function($coupon){ return $coupon->getCode(); }, $cart->getCoupons())),
						)); ?>
					</td>
					<?php $productSubtotal = $showWithTax ? $cart->getProductSubtotal() + $cart->getTotalTax() : $cart->getProductSubtotal(); ?>
					<th scope="row" class="text-right"><?php _e('Products subtotal', 'jigoshop'); ?></th>
					<td id="product-subtotal"><?php printf('%s %s', Product::formatPrice($productSubtotal), $suffix); ?></td>
				</tr>
				<noscript>
				<tr>
					<td colspan="6">
							<button type="submit" class="btn btn-success pull-right" name="action" value="update-cart"><?php _e('Update Shopping Cart', 'jigoshop'); ?></button>
					</td>
				</tr>
				</noscript>
                <?php do_action('jigoshop\template\cart\table_foot', $cart); ?>
			</tfoot>
		</table>
		<div id="cart-collaterals">
			<?php do_action('cart-collaterals', $cart); ?>
			<?php do_action('jigoshop\template\cart\collaterals', $cart); ?>
			<div id="cart-totals" class="panel panel-primary pull-right">
				<div class="panel-heading"><h2 class="panel-title"><?php _e('Cart Totals', 'jigoshop'); ?></h2></div>
				<table class="table">
					<tbody>
					<?php if ($showShippingCalculator && $cart->isShippingRequired()): ?>
						<tr id="shipping-calculator">
							<th scope="row">
								<?php _e('Shipping', 'jigoshop'); ?>
								<p class="small text-muted"><?php echo sprintf(__('Estimated for:<br/><span>%s</span>', 'jigoshop'), $customer->getShippingAddress()->getLocation()); ?></p>
							</th>
							<td>
								<noscript>
									<style type="text/css">
										.jigoshop #cart tr#shipping-calculator td > div {
											display: block;
										}
										.jigoshop #cart tr#shipping-calculator td button.close {
											display: none;
										}
									</style>
								</noscript>
								<ul class="list-group" id="shipping-methods">
									<?php foreach($shippingMethods as $method): /** @var $method \Jigoshop\Shipping\Method */ ?>
										<?php if ($method instanceof \Jigoshop\Shipping\MultipleMethod): ?>
											<?php Render::output('shop/cart/shipping/multiple_method', array('method' => $method, 'cart' => $cart)); ?>
										<?php else: ?>
											<?php Render::output('shop/cart/shipping/method', array('method' => $method, 'cart' => $cart)); ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</ul>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h3 class="panel-title">
											<?php _e('Select your destination', 'jigoshop'); ?>
											<button class="btn btn-default pull-right close" title="<?php _e('Close', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
										</h3>
									</div>
									<div class="panel-body">
										<?php \Jigoshop\Helper\Forms::select(array(
											'name' => 'country',
											'value' => $customer->getShippingAddress()->getCountry(),
											'options' => Country::getAllowed(),
										)); ?>
										<?php \Jigoshop\Helper\Forms::hidden(array(
											'id' => 'state',
											'name' => 'state',
											'value' => $customer->getShippingAddress()->getState(),
										)); ?>
										<?php if ($customer->getShippingAddress()->getCountry() && Country::hasStates($customer->getShippingAddress()->getCountry())): ?>
											<?php \Jigoshop\Helper\Forms::select(array(
												'id' => 'noscript_state',
												'name' => 'state',
												'value' => $customer->getShippingAddress()->getState(),
												'options' => Country::getStates($customer->getShippingAddress()->getCountry()),
											)); ?>
										<?php else: ?>
											<?php \Jigoshop\Helper\Forms::text(array(
												'id' => 'noscript_state',
												'name' => 'state',
												'value' => $customer->getShippingAddress()->getState(),
											)); ?>
										<?php endif; ?>
										<?php \Jigoshop\Helper\Forms::text(array(
											'name' => 'postcode',
											'value' => $customer->getShippingAddress()->getPostcode(),
											'placeholder' => __('Postcode', 'jigoshop'),
										)); ?>
									</div>
								</div>
								<button name="action" value="update-shipping" class="btn btn-default pull-right" id="change-destination"><?php _e('Change destination', 'jigoshop'); ?></button>
							</td>
						</tr>
					<?php endif; ?>
					<tr id="cart-subtotal">
						<th scope="row"><?php _e('Subtotal', 'jigoshop'); ?></th>
						<td><?php echo Product::formatPrice($cart->getSubtotal()); ?></td>
					</tr>
					<?php foreach ($cart->getCombinedTax() as $taxClass => $tax): ?>
						<tr id="tax-<?php echo $taxClass; ?>"<?php $tax == 0 and print ' style="display: none;"'; ?>>
							<th scope="row"><?php echo Tax::getLabel($taxClass, $cart); ?></th>
							<td><?php echo Product::formatPrice($tax); ?></td>
						</tr>
					<?php endforeach; ?>
					<tr id="cart-discount"<?php $cart->getDiscount() == 0 and print ' class="not-active"'; ?>>
						<th scope="row"><?php _e('Discount', 'jigoshop'); ?></th>
						<td><?php echo Product::formatPrice($cart->getDiscount()); ?></td>
					</tr>
					<tr id="cart-total">
						<th scope="row"><?php _e('Total', 'jigoshop'); ?></th>
						<td><?php echo Product::formatPrice($cart->getTotal()); ?></td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
		<a href="<?php echo $shopUrl; ?>" class="btn btn-default pull-left"><?php _e('&larr; Return to shopping', 'jigoshop'); ?></a>
		<button class="btn btn-primary pull-right" name="action" value="checkout"><?php _e('Proceed to checkout &rarr;', 'jigoshopp'); ?></button>
		<div class="clear"></div>
	</form>
    <?php do_action('jigoshop\template\cart\form\after'); ?>
<?php endif; ?>
