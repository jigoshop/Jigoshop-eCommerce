<?php
/**
 *
 */
?>
<div id="api-secret" class="col-xs-12 col-sm-12 clearfix">
    <div class="tooltip-inline-badge"></div>
    <div class="tooltip-inline-input">
        <div class="form-group padding-bottom-5">
            <input type="text" class="form-control pull-left" name="<?php echo $name; ?>" value="<?php echo $value; ?>">
        </div>
        <a href="#" class="generate btn btn-default pull-left"><?php _e('Generate', 'jigoshop'); ?></a>
        <span class="help-block"><?php echo $description; ?></span>
    </div>
</div>
