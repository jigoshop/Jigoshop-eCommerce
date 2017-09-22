<?php
use Jigoshop\Helper\Forms;

/**
 * @var $structures array
 * @var $permalink string
 * @var $shopPageId int
 * @var $base string
 * @var $productBase string
 * @var $homeUrl string
 * @var $front string
 */
?>
<table class="form-table">
	<tbody>
	<tr>
		<th><label><input name="product_permalink" type="radio" value="<?= $structures[0]; ?>" class="jigoshop-structure" <?= Forms::checked($structures[0], $permalink); ?> /> <?php _e('Default', 'jigoshop-ecommerce'); ?></label></th>
		<td><code><?= $homeUrl; ?>/?product=sample-product</code></td>
	</tr>
	<tr>
		<th><label><input name="product_permalink" type="radio" value="<?= $structures[1]; ?>" class="jigoshop-structure" <?= Forms::checked($structures[1], $permalink); ?> /> <?php _e('Product', 'jigoshop-ecommerce'); ?></label></th>
		<td><code><?= $homeUrl; ?>/<?= $productBase; ?>/sample-product/</code></td>
	</tr>
	<?php if ($shopPageId) : ?>
		<tr>
			<th><label><input name="product_permalink" type="radio" value="<?= $structures[2]; ?>" class="jigoshop-structure" <?= Forms::checked($structures[2], $permalink); ?> /> <?php _e('Shop base', 'jigoshop-ecommerce'); ?></label></th>
			<td><code><?= $homeUrl; ?>/<?= $base; ?>/sample-product/</code></td>
		</tr>
		<tr>
			<th><label><input name="product_permalink" type="radio" value="<?= $structures[3]; ?>" class="jigoshop-structure" <?= Forms::checked($structures[3], $permalink); ?> /> <?php _e('Shop base with category', 'jigoshop-ecommerce'); ?></label></th>
			<td><code><?= $homeUrl; ?>/<?= $base; ?>/product-category/sample-product/</code></td>
		</tr>
	<?php endif; ?>
	<tr>
		<th><label><input name="product_permalink" id="jigoshop_custom_selection" type="radio" value="custom" <?= Forms::checked(in_array($permalink, $structures), false); ?> /> <?php _e('Custom Base', 'jigoshop-ecommerce'); ?></label></th>
		<td>
			<input name="product_permalink_structure" id="jigoshop_permalink_structure" type="text" value="<?= $permalink; ?>" class="regular-text code">
			<span class="description"><?php _e('Enter a custom base to use. A base <strong>must</strong> be set or WordPress will use default instead.', 'jigoshop-ecommerce'); ?></span>
		</td>
	</tr>
    <tr>
        <th><label><input name="product_permalink_with_front" type="checkbox" value="on" <?= Forms::checked($with_front, true); ?> /> <?php _e('With Front', 'jigoshop-ecommerce'); ?></label></th>
        <td>
            <span class=""><?= sprintf(__('Prepend product permalink with <code>%s</code>.', 'jigoshop-ecommerce'), preg_replace('$/%(.*)%/$', '', get_option('permalink_structure'))); ?></span>
        </td>
    </tr>
	</tbody>
</table>
<script type="text/javascript">
	jQuery(function(){
		jQuery('input.jigoshop-structure').change(function(){
			jQuery('#jigoshop_permalink_structure').val(jQuery(this).val());
		});
		jQuery('#jigoshop_permalink_structure').focus(function(){
			jQuery('#jigoshop_custom_selection').click();
		});
	});
</script>
