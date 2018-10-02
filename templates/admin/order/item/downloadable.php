<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Product;

/**
 * @var $order \Jigoshop\Entity\Order
 * @var $item \Jigoshop\Entity\Order\Item
 */

$id = $item->getKey();
?>
<tr data-id="<?= $id; ?>" data-product="<?= $item->getProduct()->getId(); ?>">
	<td class="id"><?php Forms::constant(['name' => 'order[items]['.$id.'][id]', 'value' => $item->getProduct()->getId()]); ?></td>
	<td class="sku"><?php Forms::constant(['name' => 'order[items]['.$id.'][sku]', 'value' => $item->getProduct()->getSku()]); ?></td>
	<td class="name"><?php Forms::constant(['name' => 'order[items]['.$id.'][name]', 'value' => apply_filters('jigoshop\template\admin\order\item_title', $item->getName(), $item->getProduct(), $item)]); ?></td>
	<td class="price"><?php Forms::number(['name' => 'order[items]['.$id.'][price]', 'value' => Product::formatNumericPrice($item->getPrice())], "currency"); ?></td>
	<td class="quantity"><?php Forms::number(['name' => 'quantity['.$id.']', 'value' => $item->getQuantity()]); ?></td>
	<td class="total"><?php Forms::constant(['name' => 'order[items]['.$id.'][total]', 'value' => Product::formatPrice($item->getCost(), '', $order->getCurrency())]); ?></td>
	<td class="actions">
		<a href="" class="close remove"><span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Remove', 'jigoshop-ecommerce'); ?></span></a>
	</td>
</tr>
