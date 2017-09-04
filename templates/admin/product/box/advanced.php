<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;

/**
 * @var $product Product The product.
 * @var $taxClasses array Available tax classes.
 */
?>
<fieldset>
	<?php
	Forms::checkbox([
		'name' => 'product[is_taxable]',
		'id' => 'is_taxable',
		'label' => __('Is taxable?', 'jigoshop-ecommerce'),
		'checked' => $product->isTaxable(),
    ]);
	Forms::select([
		'name' => 'product[tax_classes]',
		'id' => 'tax_classes',
		'label' => __('Tax classes', 'jigoshop-ecommerce'),
		'multiple' => true,
		'value' => $product->getTaxClasses(),
		'options' => $taxClasses,
		'classes' => [$product->isTaxable() ? '' : 'not-active'],
    ]);
	?>
</fieldset>
<?php do_action('jigoshop\product\tabs\advanced', $product); ?>
