<?php 
use Jigoshop\Helper\Render;
?>

<table class="table table-striped table-valign">
	<thead>
		<tr>
			<th scope="col"><?php echo __('Name', 'jigoshop-ecommerce'); ?></th>
			<th scope="col"><?php echo __('Configure', 'jigoshop-ecommerce'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php 
		foreach($methods as $method) {
			Render::output('admin/settings/shipping/method', [
					'method' => $method
				]);
		}
	?>
	</tbody>
</table>

<div id="shipping-payment-methods-container" style="display: none">
	<?php 
	foreach($methods as $method) {
		Render::output('admin/settings/shipping/methodOptions', [
				'method' => $method
			]);
	}
	?>
</div>