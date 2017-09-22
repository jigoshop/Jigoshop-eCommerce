<?php
?>
<fieldset>
	<?php

	// DOWNLOADABLE OPTIONS
	// File URL
	// TODO: Refactor this into a helper
	$file_path = get_post_meta($post->ID, 'file_path', true);
	$field = ['id' => 'file_path', 'label' => __('File Path', 'jigoshop-ecommerce')];
	echo '<p class="form-field"><label for="'.esc_attr($field['id']).'">'.$field['label'].':</label>
				<input type="text" class="file_path" name="'.esc_attr($field['id']).'" id="'.esc_attr($field['id']).'" value="'.esc_attr($file_path).'" placeholder="'.site_url().'" />
				<input type="button"  class="upload_file_button button" data-postid="'.esc_attr($post->ID).'" value="'.__('Upload a file', 'jigoshop-ecommerce').'" />
			</p>';

	// Download Limit
	$args = [
		'id' => 'download_limit',
		'label' => __('Download Limit', 'jigoshop-ecommerce'),
		'type' => 'number',
		'desc' => __('Leave blank for unlimited re-downloads', 'jigoshop-ecommerce'),
    ];
	echo Jigoshop_Forms::input($args);

	do_action('additional_downloadable_product_type_options');
	?>
</fieldset>