<?php
/**
 * @var $before_widget string
 * @var $before_title string
 * @var $title string
 * @var $after_title string
 * @var $after_widget string
 * @var $productsPerOption array Product counts per option.
 * @var $attribute \Jigoshop\Entity\Product\Attribute Attribute to display.
 * @var $selected array List of selected options.
 * @var $baseUrl string Url without current widget data.
 */
echo $before_widget;
?>
<div class="widget_layered_nav">
<?php
if ($title) {
	echo $before_title.$title.$after_title;
}
?>
	<a class="layered_nav_clear" href="<?= $baseUrl; ?>"><?php _e('Clear', 'jigoshop-ecommerce'); ?></a>
	<ul>
		<?php foreach ($attribute->getOptions() as $option): /** @var $option \Jigoshop\Entity\Product\Attribute\Option */?>
			<li<?php in_array($option->getId(), $selected) and print ' class="chosen"'; ?>>
				<?php if ($productsPerOption[$option->getId()] > 0): ?>
					<?php
					$options = $selected;
					$key = array_search($option->getId(), $selected);
					if ($key === false) {
						$options[] = $option->getId();
					} else {
						unset($options[$key]);
					}
					$options = array_unique($options);
					?>
					<a href="<?= add_query_arg(['filter_'.$attribute->getSlug() => join('|', $options)], $baseUrl); ?>"><?= $option->getLabel(); ?></a>
				<?php else: ?>
					<span><?= $option->getLabel(); ?></span>
				<?php endif; ?>
				<small class="count"><?= $productsPerOption[$option->getId()] ; ?></small>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<?= $after_widget;
