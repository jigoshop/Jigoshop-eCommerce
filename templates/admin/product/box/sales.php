<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;

/**
 * @var $product Product The product.
 */
$enabled = false;
$price = '';
$from = time();
$to = time();

if ($product instanceof Product\Saleable) {
	/** @var Product\Saleable $product */
	$enabled = $product->getSales()->isEnabled();
	$price = $product->getSales()->getPrice();

	if($product->getSales()->getFrom()->getTimestamp() < time() && $product->getSales()->getTo()->getTimestamp() < time()) {
		$product->getSales()->getFrom()->setTimestamp(time());
		$product->getSales()->getTo()->setTimestamp(time());
	}
	$from = $product->getSales()->getFrom()->format('m/d/Y');
	$to = $product->getSales()->getTo()->format('m/d/Y');
}
?>
<fieldset>
	<?php
	Forms::checkbox([
		'name' => 'product[sales_enabled]',
		'id' => 'sales-enabled',
		'label' => __('Put product on sale?', 'jigoshop'),
		'checked' => $enabled,
    ]);
	?>
</fieldset>
<fieldset class="schedule" style="<?php !$enabled and print 'display: none;'; ?>">
	<h3><?php _e('Schedule', 'jigoshop'); ?></h3>
	<?php
	Forms::text([
		'name' => 'product[sales_price]',
		'label' => __('Sale price', 'jigoshop'),
		'value' => $price,
		'placeholder' => __('15% or 19.99', 'jigoshop'),
    ]);
	Forms::daterange([
		'id' => 'product_sales_date',
		'name' => [
			'from' => 'product[sales_from]',
			'to' => 'product[sales_to]',
        ],
		'id' => 'sales-range',
		'label' => __('Sale date', 'jigoshop'),
		'value' => [
			'from' => $from,
			'to' => $to,
        ],
    ]);
	?>
</fieldset>
<?php do_action('jigoshop\product\tabs\sales', $product); ?>
