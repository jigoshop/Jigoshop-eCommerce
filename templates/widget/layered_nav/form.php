<?php
/**
 * @var $title_id string Title field ID.
 * @var $title_name string Title field name.
 * @var $title string The title.
 * @var $attribute_id string Attribute field ID.
 * @var $attribute_name string Attribute field name.
 * @var $attribute int Selected attribute.
 * @var $attributes array Available attributes.
 */
use Jigoshop\Helper\Forms;

?>
<p>
	<label for="<?= $title_id; ?>"><?php _e('Title:', 'jigoshop-ecommerce'); ?></label>
	<input class="widefat" id="<?= $title_id; ?>"  name="<?= $title_name; ?>" type="text" value="<?= $title; ?>" />
</p>
<p>
	<label for="<?= $attribute_id; ?>"><?php _e('Attributes:', 'jigoshop-ecommerce'); ?></label>
	<select id="<?= $attribute_id; ?>"  name="<?= $attribute_name; ?>">
		<?php foreach ($attributes as $attr): /** @var $attr \Jigoshop\Entity\Product\Attribute */?>
			<option value="<?= $attr->getId(); ?>" <?php Forms::selected($attr->getId(), $attribute); ?>><?= $attr->getLabel(); ?></option>
		<?php endforeach; ?>
	</select>
</p>
