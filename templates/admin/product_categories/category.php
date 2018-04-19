<tr data-category-id="<?php echo $category->getId(); ?>" data-parent-category-id="<?php echo $category->getParentId(); ?>" <?php echo (!in_array($category->getId(), $visibleCategories) && $category->getParentId() > 0?'style="display: none"':''); ?> <?php echo in_array($category->getId(), $visibleCategories)?'data-expanded="1"':''; ?>>
	<td>
		<a href="#" class="jigoshop-product-categories-expand-subcategories">
			<?php
			if($category->getLevel() > 0) {
				echo str_repeat('-- ', $category->getLevel());
			}

			echo $category->getName();

			$subcategoriesCount = count($category->getChildCategories());
			if($subcategoriesCount == 1) {
				echo __(' (1 subcategory)', 'jigoshop-ecommerce');
			}
			elseif($subcategoriesCount > 1) {
				echo sprintf(__(' (%s subcategories)', 'jigoshop-ecommerce'), $subcategoriesCount);
			}
			?>
		</a>	
	</td>
	<td><?php echo $category->getSlug(); ?></td>
	<td><?php echo $category->getCount(); ?></td>
	<td>
		<a href="<?php echo get_term_link($category->getId(), 'product_category');?>" class="btn btn-default" target="_blank">
			<span class="glyphicon glyphicon-eye-open"></span>
			<?php echo __('View', 'jigoshop-ecommerce'); ?>
		</a>
	
		<button type="submit" class="jigoshop-product-categories-edit-button btn btn-default text-left">
			<span class="glyphicon glyphicon-plus"></span>
			<?php echo __('Edit', 'jigoshop-ecommerce'); ?>
		</button>
		<button type="submit" class="jigoshop-product-categories-remove-button btn btn-default text-left">
			<span class="glyphicon glyphicon-remove"></span>
		</button>
	</td>
</tr>