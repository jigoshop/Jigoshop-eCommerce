<?php 
use Jigoshop\Helper\Render;
?>

<table class="table table-striped table-valign">
	<thead>
		<tr>
			<th scope="col"><?php echo __('Name', 'jigoshop-ecommerce'); ?></th>
			<th scope="col"><?php echo __('Enabled', 'jigoshop-ecommerce'); ?></th>
			<th scope="col"><?php echo __('Test Mode', 'jigoshop-ecommerce'); ?></th>
			<th scope="col"><?php echo __('Configure', 'jigoshop-ecommerce'); ?></th>
			<th scope="col"><?php echo __('Status', 'jigoshop-ecommerce'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php 
		foreach($methods as $method) {
			if($method['basicSummary']) {
				Render::output('admin/settings/payment/methodBasic', [
						'method' => $method
					]);
			}
			else {
				Render::output('admin/settings/payment/method', [
						'method' => $method
					]);				
			}
		}
	?>
	</tbody>
</table>

<div id="shipping-payment-methods-container" style="display: none">
	<?php 
	foreach($methods as $method) {
		Render::output('admin/settings/payment/methodOptions', [
				'id' => $method['id'],
				'name' => $method['name'],
				'options' => $method['options']
			]);
	}
	?>
</div>