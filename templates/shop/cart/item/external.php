<?php
use Jigoshop\Helper\Order;
use Jigoshop\Helper\Product;

/**
 * @var $cart \Jigoshop\Entity\Cart Cart object.
 * @var $key string Cart item key.
 * @var $item \Jigoshop\Entity\Order\Item Cart item to display.
 */
?>
<?php
$product = $item->getProduct();
$url = apply_filters('jigoshop\cart\product_url', get_permalink($product->getId()), $key);

$price = $item->getPrice();
$priceWithTax = $item->getPrice() + ($item->getTax() / $item->getQuantity());

$prices = Product::generatePrices($price, $priceWithTax, 1, $cart->getCurrency());
if(count($prices) == 2) {
    $pricesStr = sprintf('%s 
        (%s)', $prices[0], $prices[1]);
}
else {
    $pricesStr = $prices[0];
}

$priceTotal = $item->getQuantity() * $price;
$priceTotalWithTax = $item->getQuantity() * $priceWithTax;

$pricesTotal = Product::generatePrices($priceTotal, $priceTotalWithTax, 1, $cart->getCurrency());
if(count($pricesTotal) == 2) {
    $pricesTotalStr = sprintf('%s 
        (%s)', $pricesTotal[0], $pricesTotal[1]);
}
else {
    $pricesTotalStr = $pricesTotal[0];
}
?>
<tr data-id="<?= $key; ?>" data-product="<?= $product->getId(); ?>">
    <td class="product-remove">
        <a href="<?= Order::getRemoveLink($key); ?>" class="remove" title="<?= __('Remove', 'jigoshop-ecommerce'); ?>">&times;</a>
    </td>
    <td class="product-thumbnail"><a href="<?= $url; ?>"><?= Product::getFeaturedImage($product, 'shop_tiny'); ?></a></td>
    <td class="product-name">
        <a href="<?= $url; ?>"><?= apply_filters('jigoshop\template\shop\cart\product_title', $product->getName(), $product, $item); ?></a>
        <?php do_action('jigoshop\template\shop\cart\after_product_title', $product, $item); ?>
    </td>
    <td class="product-price"><?= apply_filters('jigoshop\template\shop\cart\product_price', $pricesStr, $price, $product, $item); ?></td>
    <td class="product-quantity"><input type="number" name="cart[<?= $key; ?>]" value="<?= $item->getQuantity(); ?>" /></td>
    <td class="product-subtotal"><?= apply_filters('jigoshop\template\shop\cart\product_subtotal', $pricesTotalStr, $item->getQuantity() * $price, $product, $item); ?></td>

</tr>
