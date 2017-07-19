<?php
use Jigoshop\Admin\Helper\Forms;
?>
<div id="jigoshop-product-categories-edit-form-content">
			<?php
			Forms::hidden([
				'id' => 'id',
				'name' => 'id',
				'value' => isset($category)?$category->getId():0
			]);

			Forms::text([
				'id' => 'name',
				'name' => 'name',
				'label' => __('Name', 'jigoshop'),
				'description' => __('The name is how it appears on your site.', 'jigoshop'),
				'value' => isset($category)?$category->getName():''
			]);

			Forms::textarea([
				'id' => 'description',
				'name' => 'description',
				'label' => __('Description', 'jigoshop'),
				'description' => __('The description is not prominent by default; however, some themes may show it.', 'jigoshop'),
				'value' => isset($category)?$category->getDescription():''
			]);

			Forms::text([
				'id' => 'slug',
				'name' => 'slug',
				'label' => __('Slug', 'jigoshop'),
				'description' => __('The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'jigoshop'),
				'value' => isset($category)?$category->getSlug():''
			]);

			Forms::select([
				'id' => 'parentId',
				'name' => 'parentId',
				'label' => __('Parent category', 'jigoshop'),
				'options' => $parentOptions,
				'value' => isset($category)?$category->getParentId():0
			]);
			?>
</div>