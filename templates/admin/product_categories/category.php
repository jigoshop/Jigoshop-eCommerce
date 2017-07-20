<tr data-category-id="<?php echo $category->getId(); ?>" data-parent-category-id="<?php echo $category->getParentId(); ?>" <?php echo ($category->getParentId() > 0?'style="display: none"':''); ?>>
	<td>
		<a href="#" class="jigoshop-product-categories-expand-subcategories">
			<?php echo ($category->getLevel() > 0?str_repeat('- ', $category->getLevel()):'') . $category->getName(); ?>
		</a>
	</td>
	<td><?php echo $category->getSlug(); ?></td>
	<td><?php echo $category->getCount(); ?></td>
	<td>
		<button type="submit" class="jigoshop-product-categories-edit-button btn btn-default text-left">
			<span class="glyphicon glyphicon-plus"></span>
			<?php echo __('Edit', 'jigoshop'); ?>
		</button>
		<button type="submit" class="jigoshop-product-categories-remove-button btn btn-default text-left">
			<span class="glyphicon glyphicon-remove"></span>
		</button>
	</td>
</tr>