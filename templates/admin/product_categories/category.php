<tr data-category-id="<?php echo $category->getId(); ?>">
	<td><?php echo ($category->getLevel() > 0?str_repeat('- ', $category->getLevel()):'') . $category->getName(); ?></td>
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