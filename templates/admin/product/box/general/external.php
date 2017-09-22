<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;

/**
 * @var $product Product The product.
 */
?>
<fieldset class="product-external <?= $product instanceof Product\External ? '' : 'not-active'; ?>">
	<?php
	Forms::text([
		'name' => 'product[external_url]',
		'label' => __('Product URL', 'jigoshop-ecommerce'),
		'classes' => ['product-external'],
		'placeholder' => __('Enter external product URL...', 'jigoshop-ecommerce'),
		'value' => $product instanceof Product\External ? $product->getUrl() : '',
    ]);
	?>
</fieldset>