<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Render;
?>
<div class="wrap jigoshop">
	<h1 class="wp-heading-inline"><?php echo __('Jigoshop &raquo; Product &raquo; Categories', 'jigoshop-ecommerce'); ?></h1>
	<a href="" class="page-title-action" id="jigoshop-product-categories-add-button"><?php echo __('Add new', 'jigoshop-ecommerce'); ?></a>

	<?php 
	Render::output('shop/messages', [
		'containerId' => 'internalMessages',
		'messages' => $messages
	]); 
	?>

	<div class="tab-content">
		<form role="form" method="POST">
			<table class="table table-condensed table-valign" id="jigoshop-product-categories">
				<thead>
					<tr>
						<th>
							<?php echo __('Name', 'jigoshop-ecommerce'); ?>
						</th>
						<th>
							<?php echo __('Slug', 'jigoshop-ecommerce'); ?>
						</th>
						<th>
							<?php echo __('Count', 'jigoshop-ecommerce'); ?>
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php echo $categories; ?>				
				</tbody>
			</table>
		</form>
	</div>

	<form action="" method="" class="jigoshop-product-categories-edit-form jigoshop">
		<div class="tab-content">

			<div id="messages"></div>	

			<?php 
			Render::output('admin/product_categories/form', [
				'parentOptions' => $parentOptions,
				'categoryImage' => $categoryImage,
				'attributes' => $attributes,
				'category' => $category
			]);
			?>

			<div class="margin-top-bottom-9 col-sm-12">
				<button type="submit" class="btn btn-primary pull-right margin-left-right-5"><?php echo __('Save changes', 'jigoshop-ecommerce'); ?></button>

				<a href="" id="jigoshop-product-categories-edit-form-link" target="_blank" class="btn btn-default pull-right margin-left-right-5">
					<span class="glyphicon glyphicon-eye-open"></span>
					<?php echo __('View', 'jigoshop-ecommerce'); ?>
				</a>

				<a href="" id="jigoshop-product-categories-edit-form-close" class="btn btn-default pull-right margin-left-right-5">
					<span class="glyphicon glyphicon-eject"></span>
					<?php echo __('Close', 'jigoshop-ecommerce'); ?>
				</a>
			</div>

			<div class="clear"></div>
		</div>
	</form>			

	<div id="jigoshop-product-categories-attributes-add-new-container" class="jigoshop tab-content add-new-attribute-container">
		<form action="" method="" id="jigoshop-product-categories-attributes-add-new-form">
			<div class="row clearfix"><h2><?php echo __('Add new attribute', 'jigoshop-ecommerce'); ?></h2></div>

			<?php 
			Forms::text([
				'id' => 'jigoshop-product-categories-attributes-add-new-label',
				'name' => 'jigoshop-product-categories-attributes-add-new-label',
				'placeholder' => __('New attribute label', 'jigoshop-ecommerce'),
				'label' => __('Label', 'jigoshop-ecommerce')
			]);

			Forms::text([
				'id' => 'jigoshop-product-categories-attributes-add-new-slug',
				'name' => 'jigoshop-product-categories-attributes-add-new-slug',
				'placeholder' => __('New attribute slug', 'jigoshop-ecommerce'),
				'label' => __('Slug', 'jigoshop-ecommerce')
			]);

			Forms::select([
				'id' => 'jigoshop-product-categories-attributes-add-new-type',
				'name' => 'jigoshop-product-categories-attributes-add-new-type',
				'options' => $attributesTypes,
				'label' => __('Type', 'jigoshop-ecommerce')
			]);
			?>

			<div class="col-sm-12">
				<button type="submit" class="btn btn-default" id="jigoshop-product-categories-attributes-add-new-configure-button">
					<span class="glyphicon glyphicon-wrench"></span>
					<?php echo __('Configure', 'jigoshop-ecommerce'); ?>
				</button>
			</div>

			<div class="col-sm-12">
				<table id="jigoshop-product-categories-attributes-add-new-configure-container" class="add-new-attribute-configure-container table table-condensed">
					<thead>
						<tr>
							<th></th>
							<th><?php echo __('Label', 'jigoshop-ecommerce'); ?></th>
							<th><?php echo __('Value', 'jigoshop-ecommerce'); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<tr id="attribute-option-prototype">
							<td>
								<span class="glyphicon glyphicon-sort" style="display: none"></span>
							</td>
							<td>
								<?php
								Forms::text([
									'id' => 'option-label',
									'name' => 'option-label',
									'placeholder' => __('New option label', 'jigoshop-ecommerce')
								]);
								?>
							</td>
							<td>
								<?php 
								Forms::text([
									'id' => 'option-value',
									'name' => 'option-value',
									'placeholder' => __('New option value', 'jigoshop-ecommerce')
								]);
								?>
							</td>
							<td>
								<button type="submit" class="btn btn-default attribute-option-add-button">
									<span class="glyphicon glyphicon-plus"></span>
									<?php echo __('Add', 'jigoshop-ecommerce'); ?>
								</button>

								<button type="submit" class="btn btn-default attribute-option-remove-button">
									<span class="glyphicon glyphicon-remove"></span>
								</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="col-sm-12">
				<button type="submit" class="btn btn-primary pull-right" id="jigoshop-product-categories-attributes-add-new-button"><?php echo __('Add attribute', 'jigoshop-ecommerce'); ?></button>

				<button type="submit" class="btn btn-default pull-right" id="jigoshop-product-categories-attributes-add-new-close-button">
					<span class="glyphicon glyphicon-remove-circle"></span>
					<?php echo __('Close', 'jigoshop-ecommerce'); ?>
				</button>
			</div>

			<div class="clearfix"></div>
		</form>
	</div>
</div>