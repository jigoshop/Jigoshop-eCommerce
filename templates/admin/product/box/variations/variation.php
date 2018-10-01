<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Entity\Product\Attributes\StockStatus;
use Jigoshop\Helper\Product as ProductHelper;

/**
 * @var $variation \Jigoshop\Entity\Product\Variable\Variation Variation to display.
 * @var $attributes array List of attributes for variation.
 * @var $allowedSubtypes array List of types allowed as variations.
 */
$product = $variation->getProduct();
$stock = $product instanceof Product\Purchasable ? $product->getStock() : new StockStatus();
?>
<li class="list-group-item variation" data-id="<?= $variation->getId(); ?>">
	<h4 class="list-group-item-heading clearfix">
		<button type="button" class="remove-variation btn btn-default pull-right" title="<?php _e('Remove', 'jigoshop-ecommerce'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
		<button type="button" class="show-variation btn btn-default pull-right" title="<?php _e('Expand', 'jigoshop-ecommerce'); ?>"><span class="glyphicon glyphicon-collapse-down"></span></button>
		<label for="default_variation_<?= $variation->getId(); ?>" class="animated pull-right">
			<span data-toggle="tooltip" data-placement="top" title="" data-original-title="This variation will be pre-selected on product page.">
				<span class="small">Is default?</span>
				<input id="default_variation_<?= $variation->getId(); ?>"
					   class="default_variation"
					   name="product[default_variation_id]"
					   type="checkbox"
					   value="<?= $variation->getId(); ?>"
				<?= Forms::checked($variation->getParent()->getDefaultVariationId(), $variation->getId()); ?>>
				<i class="glyphicon"></i>
			</span>
		</label>
		<?php foreach($attributes as $attribute): /** @var $attribute Attribute */ $value = $variation->getAttribute($attribute->getId());?>
			<?php Forms::select([
				'name' => 'product[variation]['.$variation->getId().'][attribute]['.$attribute->getId().']',
				'classes' => ['variation-attribute'],
				'placeholder' => $attribute->getLabel(),
				'value' => $value !== null ? $value->getValue() : '',
				'options' => ProductHelper::getSelectOption(array_filter($attribute->getOptions(), function($option) use ($attribute) {
				    return in_array($option->getId(), is_array($attribute->getValue()) ? $attribute->getValue() : [$attribute->getValue()]);
                }), sprintf(__('Any of %s', 'jigoshop-ecommerce'), $attribute->getLabel())),
				'size' => 12,
            ]); ?>
		<?php endforeach; ?>
	</h4>
	<div class="list-group-item-text row clearfix">
		<div class="col-md-2">
			<?= ProductHelper::getFeaturedImage($product, \Jigoshop\Core\Options::IMAGE_SMALL); ?>
			<button class="btn btn-block btn-default set_variation_image"><?php _e('Set image', 'jigoshop-ecommerce'); ?></button>
			<button class="btn btn-block btn-danger remove_variation_image<?php !ProductHelper::hasFeaturedImage($product) and print ' not-active'; ?>"><?php _e('Remove image', 'jigoshop-ecommerce'); ?></button>
		</div>
		<div class="col-md-10">
			<fieldset>
			<?php
			Forms::select([
				'name' => 'product[variation]['.$variation->getId().'][product][type]',
				'classes' => ['variation-type'],
				'label' => __('Type', 'jigoshop-ecommerce'),
				'value' => $product->getType(),
				'options' => $allowedSubtypes,
				'size' => 11,
            ]);
			Forms::number([
				'name' => 'product[variation]['.$variation->getId().'][product][regular_price]',
				'label' => __('Price', 'jigoshop-ecommerce'),
				'placeholder' => __('Price not announced', 'jigoshop-ecommerce'),
				'value' => $product->getRegularPrice(),
				'size' => 11,
                'classes' => ['regular-price']
            ], "currency");
			Forms::select([
				'name' => 'product[variation]['.$variation->getId().'][product][tax_classes]',
				'label' => __('Tax classes', 'jigoshop-ecommerce'),
				'multiple' => true,
				'value' => $variation->getProduct()->getTaxClasses(),
				'options' => $taxClasses,
				'classes' => [$product->isTaxable() ? '' : 'not-active'],
				'size' => 11,
            ]);
			?>
			</fieldset>
			<fieldset>
			<?php
			Forms::text([
				'name' => 'product[variation]['.$variation->getId().'][product][sku]',
				'label' => __('SKU', 'jigoshop-ecommerce'),
				'value' => $product->getSku(),
				'placeholder' => $variation->getParent()->getId().' - '.$variation->getId(),
				'size' => 11,
            ]);
			Forms::text([
				'name' => 'product[variation]['.$variation->getId().'][product][brand]',
				'label' => __('Brand', 'jigoshop-ecommerce'),
				'value' => $product->getBrand(),
				'size' => 11,
            ]);
			Forms::text([
				'name' => 'product[variation]['.$variation->getId().'][product][gtin]',
				'label' => __('GTIN', 'jigoshop-ecommerce'),
				'tip' => 'Global Trade Item Number',
				'value' => $product->getGtin(),
				'size' => 11,
            ]);
			Forms::text([
				'name' => 'product[variation]['.$variation->getId().'][product][mpn]',
				'label' => __('MPN', 'jigoshop-ecommerce'),
				'tip' => 'Manufacturer Part Number',
				'value' => $product->getMpn(),
				'size' => 11,
            ]);
			?>
			</fieldset>
            <fieldset class="stock" class="<?php $product instanceof Product\External and print 'display: none;'; ?>">
                <?php
                Forms::checkbox([
                    'name' => 'product[variation]['.$variation->getId().'][product][stock_manage]',
                    'classes' => ['stock-manage'],
                    'label' => __('Manage stock?', 'jigoshop-ecommerce'),
                    'checked' => $stock->getManage(),
                    'size' => 11,
                ]);
                Forms::select([
                    'name' => 'product[variation]['.$variation->getId().'][product][stock_status]',
                    'label' => __('Status', 'jigoshop-ecommerce'),
                    'value' => $stock->getStatus(),
                    'options' => [
                        StockStatus::IN_STOCK => __('In stock', 'jigoshop-ecommerce'),
                        StockStatus::OUT_STOCK => __('Out of stock', 'jigoshop-ecommerce'),
                    ],
                    'classes' => [$stock->getManage() ? 'not-active' : '', 'manual-stock-status'],
                    'size' => 11,
                ]);
                ?>
                <div class="stock-status" style="<?php !$stock->getManage() and print 'display: none;'; ?>">
                    <?php
                    Forms::number([
                        'name' => 'product[variation]['.$variation->getId().'][product][stock_stock]',
                        'label' => __('Items in stock', 'jigoshop-ecommerce'),
                        'value' => $stock->getStock(),
                        'min' => 0,
                        'size' => 11,
                        'classes' => ['stock-stock']
                    ]);
                    Forms::select([
                        'name' => 'product[variation]['.$variation->getId().'][product][stock_allow_backorders]',
                        'label' => __('Allow backorders?', 'jigoshop-ecommerce'),
                        'value' => $stock->getAllowBackorders(),
                        'options' => [
                            StockStatus::BACKORDERS_FORBID => __('Do not allow', 'jigoshop-ecommerce'),
                            StockStatus::BACKORDERS_NOTIFY => __('Allow, but notify customer', 'jigoshop-ecommerce'),
                            StockStatus::BACKORDERS_ALLOW => __('Allow', 'jigoshop-ecommerce')
                        ],
                        'size' => 11,
                    ]);
                    ?>
                </div>
            </fieldset>
			<fieldset id="variation-<?= $variation->getId(); ?>-sales">
			<?php
			Forms::number([
				'name' => 'product[variation]['.$variation->getId().'][product][sales_price]',
				'label' => __('Sale price', 'jigoshop-ecommerce'),
				'value' => $product->getSales()->getPrice(),
				'placeholder' => ProductHelper::formatNumericPrice(0),
				'size' => 11,
                'classes' => ['sale-price'],
                'description' =>
                    '<a href="#" class="schedule'.($product->getSales()->getTo()->getTimestamp() ? ' not-active' : '').'">'.__('Schedule', 'jigoshop-ecommerce').'</a>'.
                    '<a href="#" class="cancel-schedule'.($product->getSales()->getTo()->getTimestamp() == 0 ? ' not-active' : '').'">'.__('Cancel schedule', 'jigoshop-ecommerce').'</a>',
            ], "float");
			Forms::daterange([
				'id' => 'product_variation_'.$variation->getId().'_product_sales_date',
				'name' => [
					'from' => 'product[variation]['.$variation->getId().'][product][sales_from]',
					'to' => 'product[variation]['.$variation->getId().'][product][sales_to]',
                ],
				'label' => __('Sale date', 'jigoshop-ecommerce'),
				'value' => [
					'from' => $product->getSales()->getFrom()->getTimestamp() ? $product->getSales()->getFrom()->format('m/d/Y') : '',
					'to' => $product->getSales()->getTo()->getTimestamp() ? $product->getSales()->getTo()->format('m/d/Y') : '',
                ],
				'size' => 11,
                'classes' => ['datepicker', $product->getSales()->getTo()->getTimestamp() ? '' : 'not-active']
            ]);
			?>
			</fieldset>
			<?php do_action('jigoshop\admin\variation', $variation, $product); ?>
		</div>
	</div>
</li>