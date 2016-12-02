<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;

/**
 * @var $product Product The product.
 */
?>
<fieldset class="<?php echo $product instanceof Product\External ? '' : 'not-active'; ?>">
	<?php
	Forms::text(array(
		'name' => 'product[external_url]',
		'label' => __('Product URL', 'jigoshop'),
		'classes' => array('product-external'),
		'placeholder' => __('Enter external product URL...', 'jigoshop'),
		'value' => $product instanceof Product\External ? $product->getUrl() : '',
	));
	?>
</fieldset>