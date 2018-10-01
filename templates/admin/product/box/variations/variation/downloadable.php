<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;

/**
 * @var $product Product The product.
 * @var $parent Product\Variable Parent of current variation..
 * @var $variation Product\Variable\Variation The variation.
 */
?>
<fieldset class="product-downloadable<?= $product instanceof Product\Downloadable ? '' : ' not-active'; ?>">
    <?php
    Forms::text([
        'name' => 'product[variation][' . $variation->getId() . '][product][url]',
        'label' => __('File path', 'jigoshop-ecommerce'),
        'classes' => ['product-downloadable', $product instanceof Product\Downloadable ? '' : 'not-active'],
        'placeholder' => __('Enter file URL...', 'jigoshop-ecommerce'),
        'size' => 11,
        'value' => $product instanceof Product\Downloadable ? $product->getUrl() : '',
    ]);
    ?>
    <?php
    Forms::number([
        'name' => 'product[variation][' . $variation->getId() . '][product][limit]',
        'type' => 'number',
        'label' => __('Downloads limit', 'jigoshop-ecommerce'),
        'description' => __('Leave empty for unlimited downloads.', 'jigoshop-ecommerce'),
        'classes' => ['download-limit', 'product-downloadable', $product instanceof Product\Downloadable ? '' : 'not-active'],
        'placeholder' => 0,
        'size' => 11,
        'value' => $product instanceof Product\Downloadable ? $product->getLimit() : '',
    ]);
    ?>
</fieldset>
