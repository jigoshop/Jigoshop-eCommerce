<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Product;

/**
 * @var $order \Jigoshop\Entity\Order The order.
 * @var $shippingMethods array List of available shipping methods.
 */
?>
<div class="jigoshop jigoshop-order">
	<div class="form-horizontal">
		<table class="table table-striped" data-order="<?= $order->getId(); ?>">
			<thead>
			<tr>
				<th scope="col"><?php Forms::constant(['name' => 'order[items][id]', 'value' => __('ID', 'jigoshop')]); ?></th>
				<th scope="col"><?php Forms::constant(['name' => 'order[items][sku]', 'value' => __('SKU', 'jigoshop')]); ?></th>
				<th scope="col"><?php Forms::constant(['size' => 12,'name' => 'order[items][name]', 'value' => __('Name', 'jigoshop')]); ?></th>
				<th scope="col"><?php Forms::constant(['size' => 12,'name' => 'order[items][unit_price]', 'value' => sprintf(__('Unit price (%s)', 'jigoshop'), Currency::symbol())]); ?></th>
				<th scope="col"><?php Forms::constant(['size' => 12,'name' => 'order[items][qty]', 'value' => __('Quantity', 'jigoshop')]); ?></th>
				<th scope="col"><?php Forms::constant(['name' => 'order[items][id][price]', 'value' => __('Price', 'jigoshop')]); ?></th>
				<th scope="col"></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach($order->getItems() as $item): /** @var $item \Jigoshop\Entity\Order\Item */?>
				<?php \Jigoshop\Helper\Render::output('admin/order/item/'.$item->getType(), ['order' => $order, 'item' => $item]); ?>
			<?php endforeach; ?>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="3"><?php Forms::text(['name' => 'new_item', 'id' => 'new-item', 'placeholder' => __('Search for products...', 'jigoshop')]); ?></td>
				<td><button class="btn btn-primary" id="add-item"><?php _e('Add item', 'jigoshop'); ?></button></td>
				<td class="text-right"><strong><?php _e('Product subtotal:', 'jigoshop'); ?></strong></td>
				<td id="product-subtotal"><?php Forms::constant(['name' => 'order[subtotal]', 'value' => Product::formatPrice($order->getProductSubtotal())]); ?></td>
				<td></td>
			</tr>
			</tfoot>
		</table>
	</div>
</div>
