<?php
/**
 * @var $currentTab string Currently selected tab.
 * @var $product \Jigoshop\Entity\Product Currently displayed product.
 */
?>
<div role="tabpanel" id="tab-attributes" class="tab-pane<?php $currentTab == 'attributes' and print ' active'; ?> clearfix">
	<dl class="dl-horizontal">
        <?php if(method_exists($product, 'getSize')): ?>
            <?php if($product->getSize()->getHeight()): ?>
                <dt><?= __('Height', 'jigoshop-ecommerce'); ?></dt>
                <dd><?= $product->getSize()->getHeight(); ?></dd>
            <?php endif; ?>
            <?php if($product->getSize()->getWidth()): ?>
                <dt><?= __('Width', 'jigoshop-ecommerce'); ?></dt>
                <dd><?= $product->getSize()->getWidth(); ?></dd>
            <?php endif; ?>
            <?php if($product->getSize()->getLength()): ?>
                <dt><?= __('Length', 'jigoshop-ecommerce'); ?></dt>
                <dd><?= $product->getSize()->getLength(); ?></dd>
            <?php endif; ?>
            <?php if($product->getSize()->getWeight()): ?>
                <dt><?= __('Weight', 'jigoshop-ecommerce'); ?></dt>
                <dd><?= $product->getSize()->getWeight(); ?></dd>
            <?php endif; ?>
        <?php endif; ?>
		<?php foreach($product->getVisibleAttributes() as $attribute): /** @var $attribute \Jigoshop\Entity\Product\Attribute */?>
			<dt><?= $attribute->getLabel(); ?></dt>
			<dd><?= ($attribute->printValue()?$attribute->printValue():'&nbsp;'); ?></dd>
		<?php endforeach; ?>
	</dl>
</div>
