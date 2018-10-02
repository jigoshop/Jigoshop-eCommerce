<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attributes\StockStatus;

/**
 * @var $product Product The product.
 */
$stock = $product instanceof Product\Purchasable ? $product->getStock() : new StockStatus();
?>
<fieldset>
	<?php
	Forms::checkbox([
		'name' => 'product[stock_manage]',
		'id' => 'stock-manage',
		'label' => __('Manage stock?', 'jigoshop-ecommerce'),
		'checked' => $stock->getManage(),
    ]);
	Forms::select([
		'name' => 'product[stock_status]',
		'id' => 'stock-status',
		'label' => __('Status', 'jigoshop-ecommerce'),
		'value' => $stock->getStatus(),
		'options' => [
			StockStatus::IN_STOCK => __('In stock', 'jigoshop-ecommerce'),
			StockStatus::OUT_STOCK => __('Out of stock', 'jigoshop-ecommerce'),
        ],
		'classes' => [$stock->getManage() ? 'not-active' : ''],
    ]);
	?>
</fieldset>
<fieldset class="stock-status" style="<?php !$stock->getManage() and print 'display: none;'; ?>">
	<?php
	Forms::number([
		'name' => 'product[stock_stock]',
		'label' => __('Items in stock', 'jigoshop-ecommerce'),
		'value' => $stock->getStock(),
		'min' => 0,
    ], "int");
	?>
	<?php
	Forms::select([
		'name' => 'product[stock_allow_backorders]',
		'label' => __('Allow backorders?', 'jigoshop-ecommerce'),
		'value' => $stock->getAllowBackorders(),
		'options' => [
			StockStatus::BACKORDERS_FORBID => __('Do not allow', 'jigoshop-ecommerce'),
			StockStatus::BACKORDERS_NOTIFY => __('Allow, but notify customer', 'jigoshop-ecommerce'),
			StockStatus::BACKORDERS_ALLOW => __('Allow', 'jigoshop-ecommerce')
        ],
    ]);
	?>
</fieldset>
<?php do_action('jigoshop\product\tabs\stock', $product); ?>
