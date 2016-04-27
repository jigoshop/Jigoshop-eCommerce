<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $order \Jigoshop\Entity\Order
 * @var $item \Jigoshop\Entity\Order\Item
 */

$id = $item->getKey();
/** @var \Jigoshop\Entity\Product\Variable $product */
$product = $item->getProduct();
$variation = $product->getVariation($item->getMeta('variation_id')->getValue());
$product = $variation ? $variation->getProduct() : $product;
?>
<tr data-id="<?php echo $id; ?>" data-product="<?php echo $product->getId(); ?>">
	<td class="id"><?php Forms::constant(array('name' => 'order[items]['.$id.'][id]', 'value' => $product->getId())); ?></td>
	<td class="sku"><?php Forms::constant(array('name' => 'order[items]['.$id.'][sku]', 'value' => $product->getSku())); ?></td>
	<td class="name"><?php Forms::constant(array('name' => 'order[items]['.$id.'][name]', 'value' => apply_filters('jigoshop\template\admin\order\item_title', $item->getName(), $item->getProduct(), $item))); ?></td>
	<td class="price"><?php Forms::text(array('name' => 'order[items]['.$id.'][price]', 'value' => Product::formatNumericPrice($item->getPrice()))); ?></td>
	<td class="quantity"><?php Forms::text(array('name' => 'quantity['.$id.']', 'value' => $item->getQuantity())); ?></td>
	<td class="total"><?php Forms::constant(array('name' => 'order[items]['.$id.'][total]', 'value' => Product::formatPrice($item->getCost()))); ?></td>
	<td class="actions">
		<a href="" class="close remove"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Remove', 'jigoshop'); ?></span></a>
	</td>
</tr>
