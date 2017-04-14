<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;

/**
 * @var $product Product The product.
 */
?>
<fieldset class="<?= $product instanceof Product\External ? '' : 'not-active'; ?>">
	<?php
	Forms::text([
		'name' => 'product[external_url]',
		'label' => __('Product URL', 'jigoshop'),
		'classes' => ['product-external'],
		'placeholder' => __('Enter external product URL...', 'jigoshop'),
		'value' => $product instanceof Product\External ? $product->getUrl() : '',
    ]);
	?>
</fieldset>