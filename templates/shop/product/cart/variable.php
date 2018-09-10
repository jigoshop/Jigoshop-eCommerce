<?php
use Jigoshop\Helper\Product;

/**
 * @var $product \Jigoshop\Entity\Product\Variable Product to add.
 */
$defaultAttributesValues = Product::getVariationAttributes($product, $product->getVariation($product->getDefaultVariationId()));
?>
<form action="" method="post" class="form" role="form">
	<input type="hidden" name="action" value="add-to-cart" />
	<?php do_action('jigoshop\template\product\before_cart', $product); ?>
	<?php
	/** @var $attribute \Jigoshop\Entity\Product\Attribute */
	foreach ($product->getAssignedVariableAttributes() as $id => $attribute) {
        $attribute['options'] = ['' => __('Choose an option', 'jigoshop-ecommerce')] + $attribute['options'];
        \Jigoshop\Helper\Forms::select([
			'name' => 'attributes['.$id.']',
			'classes' => ['product-attribute'],
			'label' => $attribute['label'],
			'options' => $attribute['options'],
			'value' => isset($defaultAttributesValues[$id]) ? $defaultAttributesValues[$id] : '',
			'placeholder' => __('Please select...', 'jigoshop-ecommerce'),
    	]);
    }
    ?>
	<div id="add-to-cart-buttons">
		<div class="price"><?php _e('Current price:', 'jigoshop-ecommerce'); ?> <span></span></div>
		<?php \Jigoshop\Helper\Forms::number([
			'id' => 'product-quantity',
			'name' => 'quantity',
			'label' => __('Quantity', 'jigoshop-ecommerce'),
			'value' => 1,
			'min' => 1,
        ]); ?>
		<input type="hidden" name="variation_id" id="variation-id" value="" />
		<button class="btn btn-primary" type="submit"><i class="fas fa-shopping-cart"></i></button>
	</div>
	<div id="add-to-cart-messages">
		<div class="alert alert-warning"><?php _e('Selected variation is not available.', 'jigoshop-ecommerce'); ?></div>
	</div>
</form>
