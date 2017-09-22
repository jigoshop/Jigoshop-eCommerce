<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Core\Options;

/**
 * @var $rule array Rule to display
 * @var $classes array List of currently available tax classes
 * @var $countries array List of countries
 */
?>
<tr>
	<td>
	<?php Forms::text([
		'id' => 'tax_rule_label_'.$rule['id'],
		'name' => Options::NAME.'[rules][label]['.$rule['id'].']',
		'value' => $rule['label'],
		'placeholder' => __('Rule label', 'jigoshop-ecommerce'),
    ]); ?>
	</td>
	<td>
	<?php Forms::select([
		'id' => 'tax_rule_class_'.$rule['id'],
		'name' => Options::NAME.'[rules][class]['.$rule['id'].']',
		'value' => $rule['class'],
		'options' => $classes,
		'placeholder' => __('Tax class', 'jigoshop-ecommerce'),
    ]); ?>
	</td>
	<td>
	<?php Forms::checkbox([
		'id' => 'tax_rule_compound_'.$rule['id'],
		'name' => Options::NAME.'[rules][compound]['.$rule['id'].']',
		'checked' => $rule['is_compound'],
    ]); ?>
	</td>
	<td>
	<?php Forms::text([
		'id' => 'tax_rule_rate_'.$rule['id'],
		'name' => Options::NAME.'[rules][rate]['.$rule['id'].']',
		'value' => $rule['rate'],
		'placeholder' => __('Tax rate', 'jigoshop-ecommerce'),
    ]); ?>
	</td>
	<td>
	<?php Forms::select([
		'id' => 'tax_rule_country_'.$rule['id'],
		'name' => Options::NAME.'[rules][country]['.$rule['id'].']',
		'classes' => ['tax-rule-country'],
		'value' => $rule['country'],
		'options' => $countries,
    ]); ?>
	</td>
	<td>
	<?php Forms::text([
		'id' => 'tax_rule_states_'.$rule['id'],
		'name' => Options::NAME.'[rules][states]['.$rule['id'].']',
		'classes' => ['tax-rule-states'],
		'placeholder' => _x('Write the state', 'admin_taxing', 'jigoshop-ecommerce'),
		'value' => is_array($rule['states']) ? join(',', $rule['states']) : $rule['states'],
    ]); ?>
	</td>
	<td>
		<?php Forms::text([
			'id' => 'tax_rule_postcodes_'.$rule['id'],
			'name' => Options::NAME.'[rules][postcodes]['.$rule['id'].']',
			'classes' => ['tax-rule-postcodes'],
			'value' => is_array($rule['postcodes']) ? join(',', $rule['postcodes']) : $rule['postcodes'],
			'placeholder' => __('Postcodes', 'jigoshop-ecommerce'),
        ]); ?>
	</td>
	<td class="vert-align">
		<input type="hidden" name="<?= Options::NAME.'[rules][id]['.$rule['id'].']'; ?>" value="<?= $rule['id']; ?>" />
		<button type="button" class="remove-tax-rule btn btn-default" title="<?php _e('Remove', 'jigoshop-ecommerce'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
	</td>
</tr>
