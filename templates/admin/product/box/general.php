<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Product as ProductHelper;

/**
 * @var $product Product The product.
 */
?>
<fieldset>
	<?php
	Forms::number([
		'name' => 'product[regular_price]',
		'label' => __('Price', 'jigoshop-ecommerce').' ('.Currency::symbol().')',
		'placeholder' => __('Price not announced', 'jigoshop-ecommerce'),
		'classes' => ['product-simple', $product instanceof Product\Purchasable ? '' : 'not-active'],
		'value' => $product instanceof Product\Purchasable ? $product->getRegularPrice() : 0,
    ], "currency");
	Forms::text([
		'name' => 'product[sku]',
		'label' => __('SKU', 'jigoshop-ecommerce'),
		'value' => $product->getSku(),
		'placeholder' => $product->getId(),
    ]);
	Forms::text([
		'name' => 'product[brand]',
		'label' => __('Brand', 'jigoshop-ecommerce'),
		'value' => $product->getBrand(),
    ]);
	Forms::text([
		'name' => 'product[gtin]',
		'label' => __('GTIN', 'jigoshop-ecommerce'),
		'tip' => 'Global Trade Item Number',
		'value' => $product->getGtin(),
    ]);
	Forms::text([
		'name' => 'product[mpn]',
		'label' => __('MPN', 'jigoshop-ecommerce'),
		'tip' => 'Manufacturer Part Number',
		'value' => $product->getMpn(),
    ]);
	?>
</fieldset>
<fieldset>
	<?php
	Forms::number([
		'name' => 'product[size_weight]',
		'label' => __('Weight', 'jigoshop-ecommerce').' ('.ProductHelper::weightUnit().')',
		'value' => $product->getSize()->getWeight(),
    ], "float");
	Forms::number([
		'name' => 'product[size_length]',
		'label' => __('Length', 'jigoshop-ecommerce').' ('.ProductHelper::dimensionsUnit().')',
		'value' => $product->getSize()->getLength(),
    ], "float");
	Forms::number([
		'name' => 'product[size_width]',
		'label' => __('Width', 'jigoshop-ecommerce').' ('.ProductHelper::dimensionsUnit().')',
		'value' => $product->getSize()->getWidth(),
    ], "float");
	Forms::number([
		'name' => 'product[size_height]',
		'label' => __('Height', 'jigoshop-ecommerce').' ('.ProductHelper::dimensionsUnit().')',
		'value' => $product->getSize()->getHeight(),
    ], "float");
	?>
</fieldset>
<fieldset>
	<?php
	Forms::select([
		'name' => 'product[visibility]',
		'label' => __('Visibility', 'jigoshop-ecommerce'),
		'options' => [
			Product::VISIBILITY_PUBLIC => __('Catalog & Search', 'jigoshop-ecommerce'),
			Product::VISIBILITY_CATALOG => __('Catalog Only', 'jigoshop-ecommerce'),
			Product::VISIBILITY_SEARCH => __('Search Only', 'jigoshop-ecommerce'),
			Product::VISIBILITY_NONE => __('Hidden', 'jigoshop-ecommerce')
        ],
		'value' => $product->getVisibility(),
    ]);
	Forms::checkbox([
		'name' => 'product[featured]',
		'label' => __('Featured?', 'jigoshop-ecommerce'),
		'checked' => $product->isFeatured(),
		'description' => __('Enable this option to feature this product', 'jigoshop-ecommerce'),
    ]);
	?>
</fieldset>
<fieldset>
    <?php
    Forms::text([
        'name' => 'product[cross_sells]',
        'label' => __('Cross Sells', 'jigoshop-ecommerce'),
        'value' => join(',', $product->getCrossSells())
    ]);
    Forms::text([
        'name' => 'product[up_sells]',
        'label' => __('Up Sells', 'jigoshop-ecommerce'),
        'value' => join(',', $product->getUpSells())
    ]);
    ?>
</fieldset>
<?php do_action('jigoshop\product\tabs\general', $product); ?>
