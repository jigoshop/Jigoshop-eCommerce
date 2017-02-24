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
        <input class="structure" id="<?php echo $id.'_sidebar_left'; ?>" type="radio" name="<?php echo $name; ?>" value="sidebar_left" <?php checked('sidebar_left', $value); ?>>
        <label for="<?php echo $id.'_sidebar_left'; ?>">
            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php _e('Sidebar on left', 'jigoshop'); ?>">
                <img src="<?php echo $url.'sidebar_left.png'; ?>"/>
            </span>
        </label>
    </div>
    <div style="float: left">
        <input class="structure" id="<?php echo $id.'_only_content'; ?>" type="radio" name="<?php echo $name; ?>" value="only_content" <?php checked('only_content', $value); ?>>
        <label for="<?php echo $id.'_only_content'; ?>">
            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php _e('Only content', 'jigoshop'); ?>">
                <img src="<?php echo $url.'only_content.png'; ?>"/>
            </span>
        </label>
    </div>
    <div style="float: left">
        <input class="structure" id="<?php echo $id.'_sidebar_right'; ?>" type="radio" name="<?php echo $name; ?>" value="sidebar_right" <?php checked('sidebar_right', $value); ?>>
        <label for="<?php echo $id.'_sidebar_right'; ?>">
            <span data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php _e('Sidebar on right', 'jigoshop'); ?>">
                <img src="<?php echo $url.'sidebar_right.png'; ?>"/>
            </span>
        </label>
    </div>
</div>
