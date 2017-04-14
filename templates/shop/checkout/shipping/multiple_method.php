<?php

/**
 * @var $method \Jigoshop\Shipping\MultipleMethod Method to display.
 * @var $cart \Jigoshop\Entity\Cart Current cart.
 */
?>
<?php foreach ($method->getRates($cart) as $rate): /** @var $rate \Jigoshop\Shipping\Rate */ ?>
	<?php \Jigoshop\Helper\Render::output('shop/checkout/shipping/rate', ['method' => $method, 'rate' => $rate, 'cart' => $cart]); ?>
<?php endforeach; ?>
