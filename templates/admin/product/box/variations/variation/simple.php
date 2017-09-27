<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Product as ProductHelper;

/**
 * @var $product Product The product.
 * @var $parent Product\Variable Parent of current variation..
 * @var $variation Product\Variable\Variation The variation.
 */
?>
<fieldset class="product-simple<?= $product instanceof Product\Simple ? '' : ' not-active'; ?>">
    <?php
    Forms::text([
        'name' => 'product[variation][' . $variation->getId() . '][product][size_weight]',
        'label' => __('Weight', 'jigoshop-ecommerce').' ('.ProductHelper::weightUnit().')',
        'value' => $product->getSize()->getWeight(),
        'size' => 11,
        'classes' => ['size-weight']
    ]);
    Forms::text([
        'name' => 'product[variation][' . $variation->getId() . '][product][size_length]',
        'label' => __('Length', 'jigoshop-ecommerce').' ('.ProductHelper::dimensionsUnit().')',
        'value' => $product->getSize()->getLength(),
        'size' => 11,
        'classes' => ['size-length']
    ]);
    Forms::text([
        'name' => 'product[variation][' . $variation->getId() . '][product][size_width]',
        'label' => __('Width', 'jigoshop-ecommerce').' ('.ProductHelper::dimensionsUnit().')',
        'value' => $product->getSize()->getWidth(),
        'size' => 11,
        'classes' => ['size-width']
    ]);
    Forms::text([
        'name' => 'product[variation][' . $variation->getId() . '][product][size_height]',
        'label' => __('Height', 'jigoshop-ecommerce').' ('.ProductHelper::dimensionsUnit().')',
        'value' => $product->getSize()->getHeight(),
        'size' => 11,
        'classes' => ['size-height']
    ]);
    ?>
</fieldset>