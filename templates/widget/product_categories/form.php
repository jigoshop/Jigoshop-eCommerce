<?php
use Jigoshop\Admin\Helper\Forms;

/**
 * @var $title_id string Title field ID.
 * @var $title_name string Title field name.
 * @var $title string The title.
 * @var $dropdown_id string Dropdown field ID.
 * @var $dropdown_name string Dropdown field name.
 * @var $dropdown bool Display as dropdown?
 * @var $count_id string Count field ID.
 * @var $count_name string Count field name.
 * @var $count bool Display count of products?
 * @var $hierarchical_id string Hierarchical field ID.
 * @var $hierarchical_name string Hierarchical field name.
 * @var $hierarchical bool Display hierarchical data?
 * @var $one_level_only_id string One level only field ID.
 * @var $one_level_only_name string One level only field name.
 * @var $one_level_only bool Display One level only data?
 */
?>
<p>
	<label for="<?= $title_id; ?>"><?php _e('Title:', 'jigoshop-ecommerce'); ?></label>
	<input class="widefat" id="<?= $title_id; ?>"  name="<?= $title_name; ?>" type="text" value="<?= $title; ?>" />
</p>
<p>
	<label for="<?= $dropdown_id; ?>">
		<input class="checkbox" id="<?= $dropdown_id; ?>"  name="<?= $dropdown_name; ?>" type="checkbox" value="on" <?= Forms::checked($dropdown, true); ?> />
		<?php _e('Show as dropdown', 'jigoshop-ecommerce'); ?>
	</label>
	<br/>
	<label for="<?= $count_id; ?>">
		<input class="checkbox" id="<?= $count_id; ?>"  name="<?= $count_name; ?>" type="checkbox" value="on" <?= Forms::checked($count, true); ?> />
		<?php _e('Show product counts', 'jigoshop-ecommerce'); ?>
	</label>
	<br/>
	<label for="<?= $hierarchical_id; ?>">
		<input class="checkbox" id="<?= $hierarchical_id; ?>"  name="<?= $hierarchical_name; ?>" type="checkbox" value="on" <?= Forms::checked($hierarchical, true); ?> />
		<?php _e('Show hierarchy', 'jigoshop-ecommerce'); ?>
	</label>
    <br/>
    <label for="<?= $one_level_only_id; ?>">
		<input class="checkbox" id="<?= $one_level_only_id; ?>"  name="<?= $one_level_only_name; ?>" type="checkbox" value="on" <?= Forms::checked($one_level_only, true); ?> />
		<?php _e('One level only', 'jigoshop-ecommerce'); ?>
	</label>
</p>
