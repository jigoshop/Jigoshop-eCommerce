<?php
/**
 * @var $currentTab string Currently selected tab.
 * @var $product \Jigoshop\Entity\Product Currently displayed product.
 */
?>
<div role="tabpanel" id="tab-attributes" class="tab-pane<?php $currentTab == 'attributes' and print ' active'; ?> clearfix">
	<dl class="dl-horizontal js-tabs-row">
		<?php foreach($product->getVisibleAttributes() as $attribute): /** @var $attribute \Jigoshop\Entity\Product\Attribute */?>
			<dt class="js-main-row"><?= $attribute->getLabel(); ?></dt>
			<dd class="js-second-row"><?= ($attribute->printValue()?$attribute->printValue():'&nbsp;'); ?></dd>
		<?php endforeach; ?>
	</dl>
</div>
