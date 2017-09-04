<?php
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Render;

/**
 * @var $product Product The product.
 * @var $allowedSubtypes array List of types allowed as variations.
 * @var $taxClasses
 * @var $bulkActions
 */
?>
<div class="clearfix padding-bottom-5">
    <div style="width: 70%; float: left">
        <?php \Jigoshop\Admin\Helper\Forms::select([
            'placeholder' => __('Select action...', 'jigoshop-ecommerce'),
            'name' => 'variation_bulk_actions',
            'id' => 'variation-bulk-actions',
            'options' => $bulkActions,
            'value' => false,
        ]); ?>
    </div>
    <button type="button" class="btn btn-default pull-right" id="do-bulk-action"><?= __('Go', 'jigoshop-ecommerce'); ?></button>
</div>
<ul id="product-variations" class="list-group">
	<?php if ($product instanceof Product\Variable): ?>
		<?php foreach($product->getVariations() as $variation): /** @var $variation \Jigoshop\Entity\Product */?>
			<?php Render::output('admin/product/box/variations/variation', [
				'variation' => $variation,
				'attributes' => $product->getVariableAttributes(),
				'allowedSubtypes' => $allowedSubtypes,
				'taxClasses' => $taxClasses,
            ]); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</ul>
<!-- TODO: Default selections -->
<noscript>
	<style type="text/css">
		.jigoshop #product-variations .list-group-item-text {
			display: block;
		}
		.jigoshop #product-variations .show-variation {
			display: none;
		}
	</style>
</noscript>
<?php do_action('jigoshop\product\tabs\variations', $product); ?>
