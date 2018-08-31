<?php

use Jigoshop\Entity\Product;
use Jigoshop\Helper\Currency;

/**
 *
 */
?>
<fieldset class="inline-edit-col"></fieldset>
<fieldset class="inline-edit-col-right jigoshop-inline-edit-row">
    <div class="inline-edit-col">
        <div class="toggle">
            <label>
                <span class="title"><?= __('Price', 'jigoshop-ecommerce').' ('.Currency::symbol().')'; ?></span>
                <span class="input-text-wrap"><input type="text" name="product[regular_price]" class="regular-price" value=""></span>
            </label>
            <div class="inline-edit-group wp-clearfix">
                <label class="alignleft">
                    <span class="title"><?= __('Sale', 'jigoshop-ecommerce').' ('.Currency::symbol().')'; ?></span>
                    <span class="input-text-wrap"><input type="text" name="product[sales_price]" class="sales-price" value=""></span>
                </label>
                <label class="alignleft">
                    <input type="hidden" name="product[sales_enabled]" value="off">
                    <input type="checkbox" name="product[sales_enabled]" class="sales-enabled" value="on">
                    <span class="checkbox-title"><?= __('Enable sale?'); ?></span>
                </label>
            </div>
        </div>
        <label>
            <span class="title"><?= __('SKU', 'jigoshop-ecommerce'); ?></span>
            <span class="input-text-wrap"><input type="text" name="product[sku]" class="sku" value=""></span>
        </label>
        <?php if($product instanceof Product\Purchasable): ?>
        <div class="toggle">
            <div class="inline-edit-group wp-clearfix">
                <label class="alignleft">
                    <span class="title"><?= __('In stock?', 'jigoshop-ecommerce'); ?></span>
                    <span class="input-text-wrap">
                        <input type="text" name="product[stock_stock]" style="<?= $product->getStock()->getManage() ? '' : 'display: none;'?>" class="stock-stock" value="">
                        <select name="product[stock_status]" style="<?= $product->getStock()->getManage() ? 'display: none;' : ''?>" class="stock-status">
                            <option value="<?= Product\Attributes\StockStatus::IN_STOCK ?>"><?= __('In stock', 'jigoshop-ecommerce'); ?></option>
                            <option value="<?= Product\Attributes\StockStatus::OUT_STOCK ?>"><?= __('Out of stock', 'jigoshop-ecommerce'); ?></option>
                        </select>
                    </span>
                </label>
                <label class="alignleft">
                    <input type="hidden" name="product[stock_manage]" value="off">
                    <input type="checkbox" name="product[stock_manage]" class="stock-manage" value="on">
                    <span class="checkbox-title"><?= __('Manage stock?'); ?></span>
                </label>
            </div>
            <label>
                <span class="title"><?= __('Backorders?', 'jigoshop-ecommerce'); ?></span>
                <select name="product[stock_allow_backorders]" class="stock-allow-backorders" class="stock-allow-backorders">
                    <option value="<?= Product\Attributes\StockStatus::BACKORDERS_FORBID ?>"><?= __('Do not allow', 'jigoshop-ecommerce'); ?></option>
                    <option value="<?= Product\Attributes\StockStatus::BACKORDERS_NOTIFY ?>"><?= __('Allow, but notify customer', 'jigoshop-ecommerce'); ?></option>
                    <option value="<?= Product\Attributes\StockStatus::BACKORDERS_ALLOW ?>"><?= __('Allow', 'jigoshop-ecommerce'); ?></option>
                </select>
            </label>
        </div>
        <?php endif; ?>
        <div class="inline-edit-group wp-clearfix">
            <label class="alignleft">
                <span class="title"><?= __('Visibility?', 'jigoshop-ecommerce'); ?></span>
                <span class="input-text-wrap">
                    <select name="product[visibility]" class="visibility">
                        <option value="<?= Product::VISIBILITY_PUBLIC ?>"><?= __('Catalog & Search', 'jigoshop-ecommerce'); ?></option>
                        <option value="<?= Product::VISIBILITY_CATALOG ?>"><?= __('Catalog Only', 'jigoshop-ecommerce'); ?></option>
                        <option value="<?= Product::VISIBILITY_SEARCH ?>"><?= __('Search Only', 'jigoshop-ecommerce'); ?></option>
                        <option value="<?= Product::VISIBILITY_NONE ?>"><?= __('Hidden', 'jigoshop-ecommerce'); ?></option>
                    </select>
                </span>
            </label>
            <label class="alignleft">
                <input type="hidden" name="product[featured]" value="0">
                <input type="checkbox" name="product[featured]" class="featured" value="1">
                <span class="checkbox-title"><?= __('Featured?'); ?></span>
            </label>
        </div>
    </div>
</fieldset>
