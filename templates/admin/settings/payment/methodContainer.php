<?php 
use Jigoshop\Helper\Render;
?>

<table class="table table-striped table-valign">
	<thead>
		<tr>
			<th scope="col">Name</th>
			<th scope="col">Enabled</th>
			<th scope="col">Test Mode</th>
			<th scope="col">Configure</th>
			<th scope="col">Status</th>
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

<div id="payment-methods-container" style="display: none">
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