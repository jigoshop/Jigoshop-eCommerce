<?php
use Jigoshop\Helper\Order;
use Jigoshop\Helper\Product;

/**
 * @var $cart \Jigoshop\Entity\Cart Cart object.
 * @var $key string Cart item key.
 * @var $item \Jigoshop\Entity\Order\Item Cart item to display.
 * @var $showWithTax bool Whether to show product price with or without tax.
 * @var $suffix string
 */

/** @var \Jigoshop\Entity\Product\Variable $product */
$product = $item->getProduct();
$variation = $product->getVariation($item->getMeta('variation_id')->getValue());
$url = apply_filters('jigoshop\cart\product_url', get_permalink($product->getId()), $key);
$price = $showWithTax ? $item->getPrice() + $item->getTax() / $item->getQuantity() : $item->getPrice();
?>
<tr data-id="<?= $key; ?>" data-product="<?= $product->getId(); ?>">
	<td class="product-remove">
		<a href="<?= Order::getRemoveLink($key); ?>" class="remove" title="<?= __('Remove', 'jigoshop'); ?>">&times;</a>
	</td>
	<td class="product-thumbnail"><a href="<?= $url; ?>"><?= Product::getFeaturedImage($product, 'shop_tiny'); ?></a></td>
	<td class="product-name">
		<a href="<?= $url; ?>"><?= apply_filters('jigoshop\template\shop\cart\product_title', $product->getName(), $product, $item); ?></a>
		<?= Product::getVariation($variation, $item); ?>
		<?php do_action('jigoshop\template\shop\cart\after_product_title', $product, $item); ?>
	</td>
	<td class="product-price"><?= apply_filters('jigoshop\template\shop\cart\product_price', Product::formatPrice($price), $price, $product, $item); ?></td>
	<td class="product-quantity"><input type="number" name="cart[<?= $key; ?>]" value="<?= $item->getQuantity(); ?>" /></td>
    <td class="product-subtotal"><?= apply_filters('jigoshop\template\shop\cart\product_subtotal', Product::formatPrice($item->getQuantity() * $price, $suffix), $item->getQuantity() * $price, $product, $item); ?></td>
</tr>
