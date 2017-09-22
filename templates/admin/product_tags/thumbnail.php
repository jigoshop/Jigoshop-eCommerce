<?php
use Jigoshop\Core\Types;

/**
 * @var $image array Image data array.
 */
?>
<tr class="form-field">
	<th scope="row" valign="top"><label><?php _e('Thumbnail', 'jigoshop-ecommerce'); ?></label></th>
	<td>
		<div id="<?= Types::PRODUCT_TAG; ?>_thumbnail" style="float:left;margin-right:10px;"><img
				src="<?= $image['image']; ?>" width="60px" height="60px" /></div>
		<div style="line-height:60px;">
			<input type="hidden" id="<?= Types::PRODUCT_TAG; ?>_thumbnail_id"
			       name="<?= Types::PRODUCT_TAG; ?>_thumbnail_id"
			       value="<?= $image['thumbnail_id']; ?>" />
			<a id="add-image" href="#" class="button"
			   data-title="<?php esc_attr_e('Choose thumbnail image', 'jigoshop-ecommerce'); ?>"
			   data-button="<?php esc_attr_e('Set as thumbnail', 'jigoshop-ecommerce'); ?>"><?php _e('Change image', 'jigoshop-ecommerce'); ?></a>
			<a id="remove-image" href="#" class="button"
			   style="display: none;"><?php _e('Remove image', 'jigoshop-ecommerce'); ?></a>
		</div>
		<div class="clear"></div>
	</td>
</tr>
