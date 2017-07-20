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
		'description' => __('The “slug” is the URL-friendly version of the name. It is usually all lowercase and containonly letters, numbers, and hyphens.', 'jigoshop'),
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

	<div class="form-group thumbnail_field">
		<div class="row">
			<div class="col-sm-12">
				<label for="thumbnail" class="col-xs-12 col-sm-2 margin-top-bottom-9"><?php echo __('Thumbnail', 'jigoshop'); ?></label>
				<div class="col-xs-12 col-sm-10 clearfix">
					<div class="tooltip-inline-badge"></div>
					<div class="tooltip-inline-input">
						<div id="jigoshop-product-categories-thumbnail">
							<img src="<?php echo $categoryImage['image']; ?>" />
						</div>	
						<div id="jigoshop-product-categories-thumbnail-controls">
							<input type="hidden" name="thumbnailId" id="thumbnailId" value="<?php echo $categoryImage['thumbnail_id']; ?>" />

							<a id="jigoshop-product-categories-thumbnail-add-button" href="#" class="button" data-title="<?php echo __('Choose thumbnail image', 'jigoshop'); ?>" data-button="<?php echo __('Set as thumbnail', 'jigoshop'); ?>"><?php echo __('Change image', 'jigoshop'); ?></a>

							<a id="jigoshop-product-categories-thumbnail-remove-button" href="#" class="button"><?php echo __('Remove image', 'jigoshop'); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>