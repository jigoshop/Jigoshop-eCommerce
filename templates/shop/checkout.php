<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Tax;

/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $content string Contents of cart page
 * @var $cart \Jigoshop\Entity\Cart Cart object.
 * @var $customer \Jigoshop\Entity\Customer Current customer.
 * @var $shippingMethods array List of available shipping methods.
 * @var $paymentMethods array List of available payment methods.
 * @var $defaultGateway string Default gateway.
 * @var $allowRegistration bool Whether to allow registering.
 * @var $showRegistrationForm bool Whether to show registration form.
 * @var $showLoginForm bool Whether to show login form.
 * @var $alwaysShowShipping bool Whether to always show shipping fields.
 * @var $cartUrl string URL to cart.
 * @var $billingFields array Fields to display as billing fields.
 * @var $shippingFields array Fields to display as shipping fields.
 * @var $differentShipping boolean Whether to use different shipping address.
 * @var $termsUrl string Url to terms and conditions page.
 */
?>
<h1><?php _e('Checkout', 'jigoshop-ecommerce'); ?></h1>
<?php Render::output('shop/messages', ['messages' => $messages]); ?>
<?= wpautop(wptexturize($content)); ?>
<?php if ($showLoginForm): ?>
	<?php Render::output('shop/checkout/login', []); ?>
<?php endif; ?>
<form action="" role="form" method="post" id="checkout">
	<?php do_action('jigoshop\template\checkout\before', $cart); ?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Billing address', 'jigoshop-ecommerce'); ?></h3>
			<?php do_action('jigoshop\template\checkout\billing_address\head', $cart); ?>
		</div>
		<div class="panel-body">
			<div class="row clearfix" id="billing-address">
				<?php foreach($billingFields as $field): ?>
				<div class="col-md-<?= $field['columnSize']; ?>">
					<?php Forms::field($field['type'], $field); ?>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
    <?php if($billingOnly == false): ?>
        <?php if (!$alwaysShowShipping): ?>
            <div class="col-md-6 col-xs-12 pull-right toggle-panels">
                <?php Forms::checkbox([
                    'description' => __('Different shipping address', 'jigoshop-ecommerce'),
                    'name' => 'jigoshop_order[different_shipping_address]',
                    'id' => 'different_shipping_address',
                    'checked' => $differentShipping,
                ]); ?>

            </div>
        <div class="clear"></div>
        <?php else: ?>
            <?php Forms::hidden([
                'name' => 'jigoshop_order[different_shipping_address]',
                'id' => 'different_shipping_address',
                'value' => 'on',
            ]); ?>
        <?php endif; ?>
        <div id="shipping-address" class="panel panel-default <?php !$differentShipping && !$alwaysShowShipping and print ' not-active'; ?>">
            <div class="panel-heading">
                <h3 class="panel-title"><?php _e('Shipping address', 'jigoshop-ecommerce'); ?></h3>
                <?php do_action('jigoshop\template\checkout\shipping_address\head', $cart); ?>
            </div>
            <div class="panel-body">
                <div class="row clearfix" >
                    <?php foreach($shippingFields as $field): ?>
                        <div class="col-md-<?= $field['columnSize']; ?>">
                            <?php Forms::field($field['type'], $field); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
	<?php if ($allowRegistration): ?>
		<?php Render::output('shop/checkout/registration_form', [
			'showRegistrationForm' => $showRegistrationForm,
        ]); ?>
	<?php endif; ?>
	<div class="panel panel-success">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Your order', 'jigoshop-ecommerce'); ?></h3>
		</div>
		<ul id="checkout-mobile">
			<?php foreach($cart->getItems() as $key => $item): /** @var \Jigoshop\Entity\Order\Item $item */ ?>
				<?php 
				$template = null;
				$template = apply_filters('jigoshop\template\shop\checkout\mobile', $template, $cart, $key, $item);

				if($template === null) {
					Render::output('shop/checkout/mobile/'.$item->getType(), [
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
		</ul>
		<div class="mobile-coupons-product-subtotal">
		<div class="pull-left">
			<?php \Jigoshop\Helper\Forms::text([
				'id' => 'jigoshop_coupons_mobile',
				'name' => 'jigoshop_coupons',
				'placeholder' => __('Enter coupons...', 'jigoshop-ecommerce'),
				'value' => join(',', array_map(function ($coupon){
					/** @var $coupon \Jigoshop\Entity\Coupon */
					return $coupon->getCode();
				}, $cart->getCoupons())),
            ]); ?>
		</div>
		<div class="pull-right">
			<?php 
			$productSubtotalPrices = Product::generatePrices($cart->getProductSubtotal(), $cart->getProductSubtotal() + $cart->getTotalTax(), 1);
			if(count($productSubtotalPrices) == 2) {
				$productSubtotalPricesStr = sprintf('%s (%s)', $productSubtotalPrices[0], $productSubtotalPrices[1]);
			}
			else {
				$productSubtotalPricesStr = $productSubtotalPrices[0];
			}
			?>
			<div class="pull-left mobile-products-subtotal"><?php _e('Products subtotal', 'jigoshop-ecommerce'); ?></div>
			<div class="pull-left product-subtotal" class="pull-right"><?= $productSubtotalPricesStr; ?></div>
		</div>
		</div>
		<table class="table table-hover">
			<thead>
			<tr>
				<th class="product-thumbnail"></th>
				<th class="product-name"><?php _e('Product Name', 'jigoshop-ecommerce'); ?></th>
				<th class="product-price"><?php _e('Unit Price', 'jigoshop-ecommerce'); ?></th>
				<th class="product-quantity"><?php _e('Quantity', 'jigoshop-ecommerce'); ?></th>
				<th class="product-subtotal"><?php _e('Price', 'jigoshop-ecommerce'); ?></th>
			</tr>
			<?php /** @deprecated */ do_action('jigoshop\checkout\table_head', $cart); ?>
			<?php do_action('jigoshop\template\checkout\table_head', $cart); ?>
			</thead>
			<tbody>
			<?php foreach($cart->getItems() as $key => $item): /** @var \Jigoshop\Entity\Order\Item $item */ ?>
				<?php 
				$template = null;
				$template = apply_filters('jigoshop\template\shop\checkout\item', $template, $cart, $key, $item);

				if($template === null) {
					Render::output('shop/checkout/item/'.$item->getType(), [
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
			<?php /** @deprecated  */ do_action('jigoshop\checkout\table_body', $cart); ?>
			<?php do_action('jigoshop\template\checkout\table_body', $cart); ?>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="2">
					<?php \Jigoshop\Helper\Forms::text([
						'id' => 'jigoshop_coupons',
						'name' => 'jigoshop_coupons',
						'placeholder' => __('Enter coupons...', 'jigoshop-ecommerce'),
						'value' => join(',', array_map(function ($coupon){
							/** @var $coupon \Jigoshop\Entity\Coupon */
							return $coupon->getCode();
						}, $cart->getCoupons())),
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
				<th colspan="2" scope="row" class="text-right"><?php _e('Products subtotal', 'jigoshop-ecommerce'); ?></th>
				<td id="product-subtotal"><?= $productSubtotalPricesStr; ?></td>
			</tr>
            <?php do_action('jigoshop\template\checkout\table_foot', $cart); ?>
			</tfoot>
		</table>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Additional order notes', 'jigoshop-ecommerce'); ?></h3>
		</div>
		<div class="panel-body">
			<?php Forms::textarea([
				'label' => '',
				'name' => 'jigoshop_order[customer_note]',
				'rows' => 3,
				'size' => 12,
				'value' => $cart->getCustomerNote(),
            ]); ?>
		</div>
	</div>
	<div class="panel panel-primary" id="totals">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Totals', 'jigoshop-ecommerce'); ?></h3>
		</div>
		<table class="table">
			<tbody>
			<?php if ($cart->isShippingRequired() && count($shippingMethods)): ?>
				<tr id="shipping-calculator">
					<th scope="row"><?php _e('Shipping', 'jigoshop-ecommerce'); ?></th>
					<td>
						<ul class="list-group" id="shipping-methods">
							<?php foreach($shippingMethods as $method): /** @var $method \Jigoshop\Shipping\Method */ ?>
								<?php if ($method instanceof \Jigoshop\Shipping\MultipleMethod): ?>
									<?php Render::output('shop/checkout/shipping/multiple_method', ['method' => $method, 'cart' => $cart]); ?>
								<?php else: ?>
									<?php Render::output('shop/checkout/shipping/method', ['method' => $method, 'cart' => $cart]); ?>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					</td>
				</tr>
			<?php endif; ?>
				<tr id="cart-subtotal">
					<th scope="row"><?php _e('Subtotal', 'jigoshop-ecommerce'); ?></th>
					<td><?= Product::formatPrice($cart->getSubtotal()); ?></td>
				</tr>
			<?php do_action('jigoshop\template\shop\checkout\before_tax'); ?>
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
			<?php do_action('jigoshop\template\shop\checkout\before_total'); ?>
				<tr id="cart-payment-processing-fee" class="not-active">
					<th scope="row"></th>
					<td></td>
				</tr>
				<tr id="cart-total" class="info">
					<th scope="row"><?php _e('Total', 'jigoshop-ecommerce'); ?></th>
					<td><strong><?= Product::formatPrice($cart->getTotal()); ?></strong></td>
				</tr>
			</tbody>
		</table>
	</div>
    <?php do_action('jigoshop\template\checkout\before_payment_methods', $cart); ?>
	<?php if(count($paymentMethods) > 0): ?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><?php _e('Select payment method', 'jigoshop-ecommerce'); ?></h3>
		</div>
		<ul class="list-group" id="payment-methods">
			<?php foreach($paymentMethods as $method): /** @var $method \Jigoshop\Payment\Method */ ?>
				<li class="list-group-item" id="payment-<?= $method->getId(); ?>">
					<label>
						<input type="radio" name="jigoshop_order[payment_method]" value="<?= $method->getId(); ?>"<?php checked($method->getId(), $defaultGateway); ?>/>
						<?= $method->getName(); ?>
					</label>
					<div class="well well-sm" <?= $method->getId() == $defaultGateway ? 'style="display:block"' : ''; ?>>
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
	<?php if(!empty($verificationMessage)) : ?>
		<?php Render::output('shop/checkout/verification_message', [
			'message' => $verificationMessage
        ]); ?>
	<?php endif; ?>
	<?php do_action('jigoshop\template\checkout\after_panels', $cart); ?>
	<a class="btn btn-default" href="<?= $cartUrl; ?>"><?php _e('Back to cart', 'jigoshop-ecommerce'); ?></a>
	<button class="btn btn-success pull-right clearfix" name="action" value="purchase" type="submit"><?php _e('Purchase and pay', 'jigoshop-ecommerce'); ?></button>
	<?php do_action('jigoshop\template\checkout\after', $cart); ?>
</form>