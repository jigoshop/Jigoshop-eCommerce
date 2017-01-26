<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Product as ProductHelper;

/**
 * @var $variation \Jigoshop\Entity\Product\Variable\Variation Variation to display.
 * @var $attributes array List of attributes for variation.
 * @var $allowedSubtypes array List of types allowed as variations.
 */
$product = $variation->getProduct();
?>
<li class="list-group-item variation" data-id="<?php echo $variation->getId(); ?>">
	<h4 class="list-group-item-heading clearfix">
		<button type="button" class="remove-variation btn btn-default pull-right" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
		<button type="button" class="show-variation btn btn-default pull-right" title="<?php _e('Expand', 'jigoshop'); ?>"><span class="glyphicon glyphicon-collapse-down"></span></button>
		<label for="default_variation_<?php echo $variation->getId(); ?>" class="animated pull-right">
			<span data-toggle="tooltip" data-placement="top" title="" data-original-title="This variation will be pre-selected on product page.">
				<span class="small">Is default?</span>
				<input id="default_variation_<?php echo $variation->getId(); ?>" name="product[default_variation_id]" type="radio" value="<?php echo $variation->getId(); ?>" <?php echo Forms::checked($variation->getParent()->getDefaultVariationId(), $variation->getId()); ?>">
				<i class="glyphicon"></i>
			</span>
		</label>
		<?php foreach($attributes as $attribute): /** @var $attribute Attribute */ $value = $variation->getAttribute($attribute->getId());?>
			<?php Forms::select(array(
				'name' => 'product[variation]['.$variation->getId().'][attribute]['.$attribute->getId().']',
				'classes' => array('variation-attribute'),
				'placeholder' => $attribute->getLabel(),
				'value' => $value !== null ? $value->getValue() : '',
				'options' => ProductHelper::getSelectOption($attribute->getOptions(), sprintf(__('Any of %s', 'jigoshop'), $attribute->getLabel())),
				'size' => 12,
			)); ?>
		<?php endforeach; ?>
	</h4>
	<div class="list-group-item-text row clearfix">
		<div class="col-md-2">
			<?php echo Product::getFeaturedImage($product, \Jigoshop\Core\Options::IMAGE_SMALL); ?>
			<button class="btn btn-block btn-default set_variation_image"><?php _e('Set image', 'jigoshop'); ?></button>
			<button class="btn btn-block btn-danger remove_variation_image<?php !Product::hasFeaturedImage($product) and print ' not-active'; ?>"><?php _e('Remove image', 'jigoshop'); ?></button>
		</div>
		<div class="col-md-10">
			<fieldset>
			<?php
			Forms::select(array(
				'name' => 'product[variation]['.$variation->getId().'][product][type]',
				'classes' => array('variation-type'),
				'label' => __('Type', 'jigoshop'),
				'value' => $product->getType(),
				'options' => $allowedSubtypes,
				'size' => 11,
			));
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][regular_price]',
				'label' => __('Price', 'jigoshop'),
				'placeholder' => __('Price not announced', 'jigoshop'),
				'value' => $product->getPrice(),
				'size' => 11,
			));
			Forms::select(array(
				'name' => 'product[variation]['.$variation->getId().'][product][tax_classes]',
				'label' => __('Tax classes', 'jigoshop'),
				'multiple' => true,
				'value' => $variation->getProduct()->getTaxClasses(),
				'options' => $taxClasses,
				'classes' => array($product->isTaxable() ? '' : 'not-active'),
				'size' => 11,
			));
			?>
			</fieldset>
			<fieldset>
			<?php
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][sku]',
				'label' => __('SKU', 'jigoshop'),
				'value' => $product->getSku(),
				'placeholder' => $variation->getParent()->getId().' - '.$variation->getId(),
				'size' => 11,
			));
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][brand]',
				'label' => __('Brand', 'jigoshop'),
				'value' => $product->getBrand(),
				'size' => 11,
			));
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][gtin]',
				'label' => __('GTIN', 'jigoshop'),
				'tip' => 'Global Trade Item Number',
				'value' => $product->getGtin(),
				'size' => 11,
			));
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][mpn]',
				'label' => __('MPN', 'jigoshop'),
				'tip' => 'Manufacturer Part Number',
				'value' => $product->getMpn(),
				'size' => 11,
			));
			?>
			</fieldset>
			<fieldset>
			<?php
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][stock_stock]',
				'label' => __('Stock', 'jigoshop'),
				'value' => $product->getStock()->getStock(),
				'size' => 11,
			));
			Forms::text(array(
				'name' => 'product[variation]['.$variation->getId().'][product][sales_price]',
				'label' => __('Sale price', 'jigoshop'),
				'value' => $product->getSales()->getPrice(),
				'placeholder' => ProductHelper::formatNumericPrice(0),
				'size' => 11,
			));
			Forms::daterange(array(
				'id' => 'product_variation_'.$variation->getId().'_product_sales_date',
				'name' => array(
					'from' => 'product[variation]['.$variation->getId().'][product][sales_from]',
					'to' => 'product[variation]['.$variation->getId().'][product][sales_to]',
				),
				'label' => __('Sale date', 'jigoshop'),
				'value' => array(
					'from' => $product->getSales()->getFrom()->format('m/d/Y'),
					'to' => $product->getSales()->getTo()->format('m/d/Y'),
				),
				'size' => 11,
				'startDate' => $product->getSales()->getFrom()->format('m/d/Y'),
				'endDate' => $product->getSales()->getTo()->format('m/d/Y'),
			));
			?>
			</fieldset>
			<?php do_action('jigoshop\admin\variation', $variation, $product); ?>
		</div>
	</div>
</li>