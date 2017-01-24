<?php
use Jigoshop\Helper\Product;

/**
 * @var $product \Jigoshop\Entity\Product\Variable Product to add.
 */
?>
<form action="" method="post" class="form" role="form">
	<input type="hidden" name="action" value="add-to-cart" />
	<?php do_action('jigoshop\template\product\before_cart', $product); ?>
	<?php foreach ($product->getAssignedVariableAttributes() as $id => $attribute): /** @var $attribute \Jigoshop\Entity\Product\Attribute */ ?>
		<?php \Jigoshop\Helper\Forms::select(array(
			'name' => 'attributes['.$id.']',
			'classes' => array('product-attribute'),
			'label' => $attribute['label'],
			'options' => $attribute['options'],
			// TODO: Default selections
			'placeholder' => __('Please select...', 'jigoshop'),
		)); ?>
	<?php endforeach; ?>
	<div id="add-to-cart-buttons">
		<p class="price"><?php _e('Current price:', 'jigoshop'); ?> <span></span></p>
		<?php \Jigoshop\Helper\Forms::number(array(
			'id' => 'product-quantity',
			'name' => 'quantity',
			'label' => __('Quantity', 'jigoshop'),
			'value' => 1,
			'min' => 1,
		)); ?>
		<input type="hidden" name="variation_id" id="variation-id" value="" />
		<button class="btn btn-primary" type="submit"><?php _e('Add to cart', 'jigoshop'); ?></button>
	</div>
	<div id="add-to-cart-messages">
		<div class="alert alert-warning"><?php _e('Selected variation is not available.', 'jigoshop'); ?></div>
	</div>
</form>
