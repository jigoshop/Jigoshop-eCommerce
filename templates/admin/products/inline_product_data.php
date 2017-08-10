<?php
use Jigoshop\Entity\Product;

/**
 * @var $product Product
 */
?>
<div class="hidden" id="jigoshop-inline-<?= $product->getId(); ?>">
    <?php if($product instanceof Product\Purchasable) : ?>
        <div class="regular-price"><?= $product->getRegularPrice(); ?></div>
        <?php if($product instanceof Product\Saleable) : ?>
            <div class="sales-enabled"><?= $product->getSales()->isEnabled(); ?></div>
            <div class="sales-price"><?= $product->getSales()->getPrice(); ?></div>
        <?php endif; ?>
    <?php endif; ?>
    <div class="sku"><?= $product->getSku(); ?></div>
    <?php if($product instanceof Product\Purchasable) : ?>
        <div class="stock-manage"><?= $product->getStock()->getManage(); ?></div>
        <div class="stock-stock"><?= $product->getStock()->getStock(); ?></div>
        <div class="stock-status"><?= $product->getStock()->getStatus(); ?></div>
        <div class="stock-allow-backorders"><?= $product->getStock()->getAllowBackorders(); ?></div>
    <?php endif; ?>
    <div class="featured"><?= $product->isFeatured(); ?></div>
    <div class="visibility"><?= $product->getVisibility(); ?></div>
</div>
