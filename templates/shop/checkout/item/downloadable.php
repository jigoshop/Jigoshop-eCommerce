<?php
use Jigoshop\Helper\Product;

/**
 * @var $cart \Jigoshop\Entity\Cart Cart object.
 * @var $key string Cart item key.
 * @var $item \Jigoshop\Entity\Order\Item Cart item to display.
 * @var $showWithTax bool Whether to show product price with or without tax.
 * @var $suffix string
 */
?>
<?php
$product = $item->getProduct();
$url = apply_filters('jigoshop\cart\product_url', get_permalink($product->getId()), $key);
$price = $showWithTax ? $item->getPrice() + $item->getTax() / $item->getQuantity() : $item->getPrice();
?>
<tr data-id="<?= $key; ?>" data-product="<?= $product->getId(); ?>">
	<td class="product-thumbnail"><a href="<?= $url; ?>"><?= Product::getFeaturedImage($product, 'shop_tiny'); ?></a></td>
	<td class="product-name">
		<a href="<?= $url; ?>"><?= apply_filters('jigoshop\template\shop\checkout\product_title', $product->getName(), $product, $item); ?></a>
		<?php do_action('jigoshop\template\shop\checkout\after_product_title', $product, $item); ?>
	</td>
	<td class="product-price"><?= Product::formatPrice($price); ?></td>
	<td class="product-quantity"><?= $item->getQuantity(); ?></td>
	<td class="product-subtotal"><?= Product::formatPrice($item->getQuantity() * $price, $suffix); ?></td>
</tr>
