<?php
use Jigoshop\Helper\Render;

/**
 * @var $rules array List of current tax rules
 * @var $classes array List of currently available tax classes
 * @var $countries array List of countries
 */
?>
<div class="form-group">
	<div class="row">
		<div class="col-sm-12">
				<div class="col-xs-2 col-sm-1 text-right"></div>
				<div class="col-xs-10 col-sm-11">
					<table class="table table-striped" id="tax-rules">
						<thead>
						<tr>
							<th scope="col"><?php _e('Label', 'jigoshop'); ?></th>
							<th scope="col">
								<?php _e('Class', 'jigoshop'); ?>
								<span data-toggle="tooltip" class="badge" data-placement="top" title="<?php _e('Tax classes needs to be saved first before updating rules.', 'jigoshop'); ?>">?</span>
							</th>
							<th scope="col"><?php _e('Is compound?', 'jigoshop'); ?></th>
							<th scope="col"><?php _e('Rate', 'jigoshop'); ?></th>
							<th scope="col"><?php _e('Country', 'jigoshop'); ?></th>
							<th scope="col">
								<?php _e('State', 'jigoshop'); ?>
								<span data-toggle="tooltip" class="badge" data-placement="top" title="<?php _e('You can enter more states separating them with comma.', 'jigoshop'); ?>">?</span>
							</th>
							<th scope="col">
								<?php _e('Postcodes', 'jigoshop'); ?>
								<span data-toggle="tooltip" class="badge" data-placement="top" title="<?php _e('Enter list of postcodes, separating with comma.', 'jigoshop'); ?>">?</span>
							</th>
							<th scope="col"></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach($rules as $rule): ?>
							<?php Render::output('admin/settings/tax/rule', array(
								'rule' => $rule, 'classes' => $classes, 'countries' => $countries,
							)); ?>
						<?php endforeach; ?>
						</tbody>
						<tfoot>
						<tr>
							<td colspan="6" class="pull-text-left">
								<button type="button" class="btn btn-default text-left" id="add-tax-rule"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop'); ?></button>
							</td>
						</tr>
						</tfoot>
					</table>
				</div>
		</div>
	</div>
</div>
