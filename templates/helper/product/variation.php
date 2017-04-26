<?php
/**
 * @var $variation \Jigoshop\Entity\Product\Variable\Variation
 * @var $item \Jigoshop\Entity\Order\Item
 */
?>
<dl class="dl-horizontal variation-data">
	<?php foreach ($variation->getAttributes() as $attribute): /** @var $attribute \Jigoshop\Entity\Product\Variable\Attribute */?>
		<dt><?= $attribute->getAttribute()->getLabel(); ?></dt>
		<dd><?= $attribute->printValue($item); ?></dd>
	<?php endforeach; ?>
</dl>
