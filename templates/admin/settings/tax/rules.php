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
				<div class="tooltip-inline-badge text-right"></div>
				<div class="tooltip-inline-input">
					<table class="table table-striped" id="tax-rules">
						<thead>
						<tr>
							<th scope="col"><?php _e('Label', 'jigoshop-ecommerce'); ?></th>
							<th scope="col">
								<?php _e('Class', 'jigoshop-ecommerce'); ?>
								<span data-toggle="tooltip" class="badge" data-placement="top" title="<?php _e('Tax classes needs to be saved first before updating rules.', 'jigoshop-ecommerce'); ?>">?</span>
							</th>
							<th scope="col">
								<?php _e('Is compound?', 'jigoshop-ecommerce'); ?>
								<span data-toggle="tooltip" class="badge" data-placement="top" title="<?php _e('A compound tax, is calculated on top of a primary tax.', 'jigoshop-ecommerce'); ?>">?</span>
							</th>
							<th scope="col"><?php _e('Rate', 'jigoshop-ecommerce'); ?></th>
							<th scope="col"><?php _e('Country', 'jigoshop-ecommerce'); ?></th>
							<th scope="col">
								<?php _e('State', 'jigoshop-ecommerce'); ?>
								<span data-toggle="tooltip" class="badge" data-placement="top" title="<?php _e('You can enter more states separating them with comma.', 'jigoshop-ecommerce'); ?>">?</span>
							</th>
							<th scope="col">
								<?php _e('Postcodes', 'jigoshop-ecommerce'); ?>
								<span data-toggle="tooltip" class="badge" data-placement="top" title="<?php _e('Enter list of postcodes, separating with comma.', 'jigoshop-ecommerce'); ?>">?</span>
							</th>
							<th scope="col"></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach($rules as $rule): ?>
							<?php Render::output('admin/settings/tax/rule', [
								'rule' => $rule, 'classes' => $classes, 'countries' => $countries,
                            ]); ?>
						<?php endforeach; ?>
						</tbody>
						<tfoot>
						<tr>
							<td colspan="6" class="pull-text-left">
								<button type="button" class="btn btn-default text-left" id="add-tax-rule"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop-ecommerce'); ?></button>
							</td>
						</tr>
						</tfoot>
					</table>
				</div>
		</div>
	</div>
</div>
