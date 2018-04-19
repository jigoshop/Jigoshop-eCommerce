<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Render;
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
		'label' => __('Name', 'jigoshop-ecommerce'),
		'description' => __('The name is how it appears on your site.', 'jigoshop-ecommerce'),
		'value' => isset($category)?$category->getName():''
	]);
	?>
    <div class="form-group description_field clearfix">
        <div class="row">
            <div class="col-sm-12">
            <label for="description" class="col-xs-12 col-sm-2 margin-top-bottom-9">
                <?= __('Description', 'jigoshop-ecommerce') ?>
            </label>
            <div class="col-xs-12 col-sm-10 clearfix">
                <div class="tooltip-inline-badge"></div>
                <div class="tooltip-inline-input">
                    <?php 
                    wp_editor(isset($category)?$category->getDescription():'', 'description', array(
                            'editor_height'    => 300,
                            'media_buttons'    => true,
                            'textarea_name'    => 'description',
                        ));
                    if(defined('DOING_AJAX') && DOING_AJAX){
                        \_WP_Editors::enqueue_scripts();
                        print_footer_scripts();
                        \_WP_Editors::editor_js();
                    }; 
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php
	Forms::text([
		'id' => 'slug',
		'name' => 'slug',
		'label' => __('Slug', 'jigoshop-ecommerce'),
		'description' => __('The “slug” is the URL-friendly version of the name. It is usually all lowercase and containonly letters, numbers, and hyphens.', 'jigoshop-ecommerce'),
		'value' => isset($category)?$category->getSlug():''
	]);

	ob_start();

	Forms::select([
		'id' => 'parentId',
		'name' => 'parentId',
		'label' => __('Parent category', 'jigoshop-ecommerce'),
		'options' => $parentOptions,
		'value' => isset($category)?$category->getParentId():0
	]);
	
	if(defined('DOING_AJAX') && DOING_AJAX){
        echo preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", ob_get_clean());
    } else {
	    echo ob_get_clean();
    }
	?>

	<div class="form-group thumbnail_field">
		<div class="row">
			<div class="col-sm-12">
				<label for="thumbnail" class="col-xs-12 col-sm-2 margin-top-bottom-9"><?php echo __('Thumbnail', 'jigoshop-ecommerce'); ?></label>
				<div class="col-xs-12 col-sm-10 clearfix">
					<div class="tooltip-inline-badge"></div>
					<div class="tooltip-inline-input">
						<div id="jigoshop-product-categories-thumbnail">
							<img src="<?php echo $categoryImage['image']; ?>" />
						</div>	
						<div id="jigoshop-product-categories-thumbnail-controls">
							<input type="hidden" name="thumbnailId" id="thumbnailId" value="<?php echo $categoryImage['thumbnail_id']; ?>" />

							<a id="jigoshop-product-categories-thumbnail-add-button" href="#" class="button" data-title="<?php echo __('Choose thumbnail image', 'jigoshop-ecommerce'); ?>" data-button="<?php echo __('Set as thumbnail', 'jigoshop-ecommerce'); ?>"><?php echo __('Change image', 'jigoshop-ecommerce'); ?></a>

							<a id="jigoshop-product-categories-thumbnail-remove-button" href="#" class="button"><?php echo __('Remove image', 'jigoshop-ecommerce'); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<h3>Attributes</h3>

	<?php 
	Forms::checkbox([
		'id' => 'attributesInheritEnabled',
		'name' => 'attributesInheritEnabled',
		'label' => __('Enable inherited attributes', 'jigoshop-ecommerce'),
		'classes' => ['switch-medium'],
		'checked' => isset($category)?$category->getAttributesInheritEnabled():false
	]);
	?>

	<div id="jigoshop-product-categories-attributes-inherit-mode">
		<?php
        ob_start();
		Forms::select([
			'id' => 'attributesInheritMode',
			'name' => 'attributesInheritMode',
			'label' => __('Inherit attributes from', 'jigoshop-ecommerce'),
			'options' => [
				'all' => __('All parent categories', 'jigoshop-ecommerce'),
				'direct' => __('Direct parent category', 'jigoshop-ecommerce')
			],
			'value' => isset($category)?$category->getAttributesInheritMode():'all'
		]);
		
        if(defined('DOING_AJAX') && DOING_AJAX){
            echo preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", ob_get_clean());
        } else {
            echo ob_get_clean();
        }
		?>
	</div>

	<table class="table table-striped table-valign" id="jigoshop-product-categories-attributes">
		<thead>
			<tr>
				<th><?php echo __('Label', 'jigoshop-ecommerce'); ?></th>
				<th><?php echo __('Slug', 'jigoshop-ecommerce'); ?></th>
				<th><?php echo __('Type', 'jigoshop-ecommerce'); ?></th>
				<th><?php echo __('Inherited from', 'jigoshop-ecommerce'); ?></th>
				<th><?php echo __('Enabled', 'jigoshop-ecommerce'); ?></th>
				<th><?php echo __('Remove', 'jigoshop-ecommerce'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php echo (isset($attributes)?$attributes:''); ?>
		</tbody>
	</table>

	<div class="jigoshop-product-categories-attributes-new-controls">
		<div class="col-sm-6">
			<?php
            ob_start();
			Forms::select([
				'id' => 'attributesNewSelector',
				'name' => 'attributesNewSelector',
				'label' => __('Existing attr.', 'jigoshop-ecommerce'),
				'options' => [],
				'multiple' => true
			]);
			
            if(defined('DOING_AJAX') && DOING_AJAX){
                echo preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", ob_get_clean());
            } else {
                echo ob_get_clean();
            }
			?>
		</div>
		<div class="col-sm-6">
			<div class="row">
				<button type="submit" class="btn btn-default" id="jigoshop-product-categories-attributes-add-button">
					<span class="glyphicon glyphicon-plus"></span>
					<?php echo __('Add', 'jigoshop-ecommerce'); ?>
				</button>
			</div>
			<div class="row margin-top-bottom-9">
				<button type="submit" class="btn btn-default" id="jigoshop-product-categories-attributes-add-new-button">
					<span class="glyphicon glyphicon-plus"></span>
					<?php echo __('Add new attribute', 'jigoshop-ecommerce'); ?>
				</button>			
			</div>
		</div>

        <div class="clearfix"></div>

        <?php do_action('jigoshop\admin\product\category\form', isset($category) ? $category : null); ?>
	</div>
</div>