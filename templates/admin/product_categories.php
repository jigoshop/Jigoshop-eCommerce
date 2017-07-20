<?php
use Jigoshop\Helper\Render;
?>
<div class="wrap jigoshop">
	<h1 class="wp-heading-inline"><?php echo __('Jigoshop &raquo; Product &raquo; Categories', 'jigoshop'); ?></h1>
	<a href="" class="page-title-action" id="jigoshop-product-categories-add-button">Add New</a>

	<?php 
	Render::output('shop/messages', [
		'containerId' => 'internalMessages',
		'messages' => $messages
	]); 
	?>

	<div class="tab-content">
		<form role="form" method="POST">
			<table class="table table-striped table-valign">
				<thead>
					<tr>
						<th>
							<?php echo __('Name', 'jigoshop'); ?>
						</th>
						<th>
							<?php echo __('Description', 'jigoshop'); ?>
						</th>
						<th>
							<?php echo __('Slug', 'jigoshop'); ?>
						</th>
						<th>
							<?php echo __('Count', 'jigoshop'); ?>
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

	<form action="" method="" class="jigoshop-product-categories-edit-form">
		<div class="tab-content">

			<div id="messages"></div>	

			<?php 
			Render::output('admin/product_categories/form', [
				'parentOptions' => $parentOptions,
				'categoryImage' => $categoryImage
			]);
			?>

			<div class="col-sm-12">
				<button type="submit" class="btn btn-primary pull-right"><?php echo __('Save changes', 'jigoshop'); ?></button>
			</div>

			<div class="clear"></div>
		</div>
	</form>			
</div>