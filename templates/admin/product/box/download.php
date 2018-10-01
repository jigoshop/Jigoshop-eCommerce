<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;

/**
 * @var $product Product The product.
 */
?>
<fieldset>
	<?php
	Forms::text([
		'name' => 'product[url]',
		'label' => __('File path', 'jigoshop-ecommerce'),
		'classes' => ['product-downloadable'],
		'placeholder' => __('Enter file URL...', 'jigoshop-ecommerce'),
		'value' => $product instanceof Product\Downloadable ? $product->getUrl() : '',
    ]);
	?>
	<?php
	Forms::number([
		'name' => 'product[limit]',
		'type' => 'number',
		'label' => __('Downloads limit', 'jigoshop-ecommerce'),
		'description' => __('Leave empty for unlimited downloads.', 'jigoshop-ecommerce'),
		'classes' => ['product-downloadable'],
		'placeholder' => 0,
		'value' => $product instanceof Product\Downloadable ? $product->getLimit() : '',
    ]);
	?>
</fieldset>
<?php do_action('jigoshop\product\tabs\downloads', $product); ?>
