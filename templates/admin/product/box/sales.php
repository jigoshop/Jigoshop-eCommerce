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

    if($product->getSales()->getFrom()->getTimestamp() == '') {
        $from = '';
    } else {
        if($product->getSales()->getFrom()->getTimestamp() < time()) {
            $product->getSales()->getFrom()->setTimestamp(time());
        }
        $from = $product->getSales()->getFrom()->format('m/d/Y');
    }
    if($product->getSales()->getTo()->getTimestamp() == '') {
        $to = '';
    } else {
        if($product->getSales()->getTo()->getTimestamp() < time()) {
            $product->getSales()->getTo()->setTimestamp(time());
        }
        $to = $product->getSales()->getTo()->format('m/d/Y');
    }
}
?>
<fieldset>
	<?php
	Forms::checkbox([
		'name' => 'product[sales_enabled]',
		'id' => 'sales-enabled',
		'label' => __('Put product on sale?', 'jigoshop-ecommerce'),
		'description' => __('To enable sale please set up actual sale dates', 'jigoshop-ecommerce'),
        'checked' => $enabled,
    ]);
	?>
</fieldset>
<fieldset class="schedule" style="<?php !$enabled and print 'display: none;'; ?>">
	<h3><?php _e('Schedule', 'jigoshop-ecommerce'); ?></h3>
	<?php
	Forms::number([
		'name' => 'product[sales_price]',
		'label' => __('Sale price', 'jigoshop-ecommerce'),
		'value' => $price,
		'placeholder' => __('15% or 19.99', 'jigoshop-ecommerce'),
    ], "float");
	Forms::daterange([
		'id' => 'product_sales_date',
		'name' => [
			'from' => 'product[sales_from]',
			'to' => 'product[sales_to]',
        ],
		'id' => 'sales-range',
		'label' => __('Sale date', 'jigoshop-ecommerce'),
		'value' => [
			'from' => $from,
			'to' => $to,
        ],
        'description' => __('The above sale period will be set for all the product variations. If you need to set different sale time frame for individual variation, please use the sale setting on the variation edit pane.', 'jigoshop-ecommerce'),
    ]);
	?>
</fieldset>
<?php do_action('jigoshop\product\tabs\sales', $product); ?>
