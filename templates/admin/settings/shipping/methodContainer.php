<?php 
use Jigoshop\Helper\Render;
?>

<table class="table table-striped table-valign">
	<thead>
		<tr>
			<th scope="col">Name</th>
			<th scope="col">Configure</th>
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

<div id="shipping-methods-container" style="display: none">
	<?php 
	foreach($methods as $method) {
		Render::output('admin/settings/shipping/methodOptions', [
				'method' => $method
			]);
	}
	?>
</div>