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
 * @var $showShippingCalculator bool Whether to show shipping calculator.
 * @var $termsUrl string Url to terms and conditions page.
 */
?>
<h1><?php _e('Cart', 'jigoshop-ecommerce'); ?></h1>
<?php Render::output('shop/messages', ['messages' => $messages]); ?>
<?= wpautop(wptexturize($content)); ?>
<?php if ($cart->isEmpty()): ?>
	<?php Render::output('shop/cart/empty', ['shopUrl' => $shopUrl]); ?>
<?php else: ?>
    <?php do_action('jigoshop\template\cart\form\before'); ?>
	<form id="cart" role="form" action="" method="post">
		<?php Render::output('shop/cart/mobile', [
			'cart' => $cart
		]); ?>
		<table class="table table-hover">
			<thead>
				<tr>
					<th class="product-remove"></th>
					<th class="product-thumbnail"></th>
					<th class="product-name"><?php _e('Product Name', 'jigoshop-ecommerce'); ?></th>
					<th class="product-price"><?php _e('Unit Price', 'jigoshop-ecommerce'); ?></th>
					<th class="product-quantity"><?php _e('Quantity', 'jigoshop-ecommerce'); ?></th>
					<th class="product-subtotal"><?php _e('Price', 'jigoshop-ecommerce'); ?></th>
				</tr>
				<?php /** @deprectated */ do_action('jigoshop\cart\table_head', $cart); ?>
				<?php do_action('jigoshop\template\cart\table_head', $cart); ?>
			</thead>
			<tbody>
				<?php foreach($cart->getItems() as $key => $item): /** @var $item \Jigoshop\Entity\Order\Item */ ?>
					<?php 
		        	$template = null;
		        	$template = apply_filters('jigoshop\template\shop\cart\item', $template, $cart, $key, $item);

		        	if($template === null) {					
						Render::output('shop/cart/item/'.$item->getType(), [
							'cart' => $cart, 
							'key' => $key, 
							'item' => $item
						]); 
					}
					else {
						echo $template;
					}
					?>
				<?php endforeach; ?>
				<?php /** @deprectated */ do_action('jigoshop\cart\table_body', $cart); ?>
                <?php do_action('jigoshop\template\cart\table_body', $cart); ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="4">
						<?php \Jigoshop\Helper\Forms::text([
							'id' => 'jigoshop_coupons',
							'name' => 'jigoshop_coupons',
							'placeholder' => __('Enter coupons...', 'jigoshop-ecommerce'),
							'value' => join(',', array_map(function($coupon){ return $coupon->getCode(); }, $cart->getCoupons())),
                        ]); ?>
					</td>

					<?php 
					$productSubtotalPrices = Product::generatePrices($cart->getProductSubtotal(), $cart->getProductSubtotal() + $cart->getTotalTax(), 1);

					if(count($productSubtotalPrices) == 2) {
						$productSubtotalPricesStr = sprintf('%s (%s)', $productSubtotalPrices[0], $productSubtotalPrices[1]);
					}
					else {
						$productSubtotalPricesStr = $productSubtotalPrices[0];
					}
					?>
					
					<th scope="row" class="text-right"><?php _e('Products subtotal', 'jigoshop-ecommerce'); ?></th>
					<td id="product-subtotal"><?php echo $productSubtotalPricesStr; ?></td>
				</tr>
				<noscript>
				<tr>
					<td colspan="6">
							<button type="submit" class="btn btn-success pull-right" name="action" value="update-cart"><?php _e('Update Shopping Cart', 'jigoshop-ecommerce'); ?></button>
					</td>
				</tr>
				</noscript>
                <?php do_action('jigoshop\template\cart\table_foot', $cart); ?>
			</tfoot>
		</table>
		<div id="cart-collaterals">
			<?php do_action('cart-collaterals', $cart); ?>
			<?php do_action('jigoshop\template\cart\collaterals', $cart); ?>
			<div id="cart-totals" class="panel-primary pull-right">
				<div class="panel-heading"><h2 class="panel-title"><?php _e('Cart Totals', 'jigoshop-ecommerce'); ?></h2></div>
				<table class="table">
					<tbody>
					<?php if ($showShippingCalculator): ?>
						<tr id="shipping-calculator"<?php !$cart->isShippingRequired() and print ' style="display: none;"'?>>
							<th scope="row">
								<?php _e('Shipping', 'jigoshop-ecommerce'); ?>
								<p class="small text-muted"><?= sprintf(__('Estimated for:<br/><span>%s</span>', 'jigoshop-ecommerce'), $customer->getShippingAddress()->getLocation()); ?></p>
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
											<?php Render::output('shop/cart/shipping/multiple_method', ['method' => $method, 'cart' => $cart]); ?>
										<?php else: ?>
											<?php Render::output('shop/cart/shipping/method', ['method' => $method, 'cart' => $cart]); ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</ul>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h3 class="panel-title">
											<?php _e('Select your destination', 'jigoshop-ecommerce'); ?>
											<button class="btn btn-default pull-right close" title="<?php _e('Close', 'jigoshop-ecommerce'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
										</h3>
									</div>
									<div class="panel-body">
										<?php \Jigoshop\Helper\Forms::select([
											'name' => 'country',
											'value' => $customer->getShippingAddress()->getCountry(),
											'options' => Country::getAllowed(),
                                        ]); ?>
										<?php \Jigoshop\Helper\Forms::hidden([
											'id' => 'state',
											'name' => 'state',
											'value' => $customer->getShippingAddress()->getState(),
                                        ]); ?>
										<?php if ($customer->getShippingAddress()->getCountry() && Country::hasStates($customer->getShippingAddress()->getCountry())): ?>
											<?php \Jigoshop\Helper\Forms::select([
												'id' => 'noscript_state',
												'name' => 'state',
												'value' => $customer->getShippingAddress()->getState(),
												'options' => Country::getStates($customer->getShippingAddress()->getCountry()),
                                            ]); ?>
										<?php else: ?>
											<?php \Jigoshop\Helper\Forms::text([
												'id' => 'noscript_state',
												'name' => 'state',
												'value' => $customer->getShippingAddress()->getState(),
                                            ]); ?>
										<?php endif; ?>
										<?php \Jigoshop\Helper\Forms::text([
											'name' => 'postcode',
											'value' => $customer->getShippingAddress()->getPostcode(),
											'placeholder' => __('Postcode', 'jigoshop-ecommerce'),
                                        ]); ?>
									</div>
								</div>
								<button name="action" value="update-shipping" class="btn btn-default pull-right" id="change-destination"><?php _e('Change destination', 'jigoshop-ecommerce'); ?></button>
							</td>
						</tr>
					<?php endif; ?>
					<tr id="cart-subtotal">
						<th scope="row"><?php _e('Subtotal', 'jigoshop-ecommerce'); ?></th>
						<td><?= Product::formatPrice($cart->getSubtotal()); ?></td>
					</tr>
					<?php foreach ($cart->getCombinedTax() as $taxClass => $tax): ?>
						<tr id="tax-<?= $taxClass; ?>"<?php $tax == 0 and print ' style="display: none;"'; ?>>
							<th scope="row"><?= Tax::getLabel($taxClass, $cart); ?></th>
							<td><?= Product::formatPrice($tax); ?></td>
						</tr>
					<?php endforeach; ?>
					<tr id="cart-discount"<?php $cart->getDiscount() == 0 and print ' class="not-active"'; ?>>
						<th scope="row"><?php _e('Discount', 'jigoshop-ecommerce'); ?></th>
						<td><?= Product::formatPrice($cart->getDiscount()); ?></td>
					</tr>
					<tr id="cart-total">
						<th scope="row"><?php _e('Total', 'jigoshop-ecommerce'); ?></th>
						<td><?= Product::formatPrice($cart->getTotal()); ?></td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
		<a href="<?= $shopUrl; ?>" class="btn btn-default pull-left"><?php _e('&larr; Return to shopping', 'jigoshop-ecommerce'); ?></a>
		<button class="btn btn-primary pull-right" name="action" value="checkout"><?php _e('Proceed to checkout &rarr;', 'jigoshop-ecommerce'); ?></button>
		<div class="clear"></div>
	</form>
    <?php do_action('jigoshop\template\cart\form\after'); ?>
<?php endif; ?>
