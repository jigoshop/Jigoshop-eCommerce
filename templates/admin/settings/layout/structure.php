<?php
/**
 * @var $id sting
 * @var $name string
 * @var $value string
 */
$url = JigoshopInit::getUrl().'/assets/images/';
?>
<style>
    input[type=radio].structure {
        display: none;
    }
    input[type=radio].structure:checked ~ label {
        background: #ddd;
    }
</style>
<div class="tooltip-inline-badge"></div>
<div class="tooltip-inline-input">
    <div style="float: left">
        <input class="structure" id="<?= $id.'_sidebar_left'; ?>" type="radio" name="<?= $name; ?>" value="sidebar_left" <?php checked('sidebar_left', $value); ?>>
        <label for="<?= $id.'_sidebar_left'; ?>">
            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php _e('Sidebar on left', 'jigoshop-ecommerce'); ?>">
                <img src="<?= $url.'sidebar_left.png'; ?>"/>
            </span>
        </label>
    </div>
    <div style="float: left">
        <input class="structure" id="<?= $id.'_only_content'; ?>" type="radio" name="<?= $name; ?>" value="only_content" <?php checked('only_content', $value); ?>>
        <label for="<?= $id.'_only_content'; ?>">
            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php _e('Only content', 'jigoshop-ecommerce'); ?>">
                <img src="<?= $url.'only_content.png'; ?>"/>
            </span>
        </label>
    </div>
    <div style="float: left">
        <input class="structure" id="<?= $id.'_sidebar_right'; ?>" type="radio" name="<?= $name; ?>" value="sidebar_right" <?php checked('sidebar_right', $value); ?>>
        <label for="<?= $id.'_sidebar_right'; ?>">
            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php _e('Sidebar on right', 'jigoshop-ecommerce'); ?>">
                <img src="<?= $url.'sidebar_right.png'; ?>"/>
            </span>
        </label>
    </div>
</div>
