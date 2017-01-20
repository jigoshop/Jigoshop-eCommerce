<?php
/**
 *
 */
?>
<li class="list-group-item">
    <h4 class="list-group-item-heading clearfix">
        <span class="title"><?php echo $userId; ?></span>
        <button type="button" class="remove btn btn-default pull-right" title="<?php _e('Remove', 'jigoshop'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
        <button type="button" class="toggle btn btn-default pull-right" title="<?php _e('Expand', 'jigoshop'); ?>"><span class="glyphicon glyphicon-collapse-down"></span></button>
    </h4>
    <div class="list-group-item-text"<?php $active == false and print ' style="display: none"'?>>
        <fieldset>
            <div class="col-sm-6">
                <?php \Jigoshop\Admin\Helper\Forms::text(array(
                    'label' => __('User Id', 'jigoshop'),
                    'name' => sprintf('%s[%s][user_login]', $name, $index),
                    'value' => $userLogin,
                    'placeholder' => __('User Login', 'jigoshop'),
                    'classes' => array('user-login'),
                )); ?>
                <div class="col-xs-12">
                    <a href="#" class="btn btn-default pull-right generate">Generate</a>
                </div>
            </div>
            <div class="col-sm-6">
                <?php \Jigoshop\Admin\Helper\Forms::text(array(
                    'label' => __('Key', 'jigoshop'),
                    'name' => sprintf('%s[%s][user_password]', $name, $index),
                    'type' => 'text',
                    'value' => $userPassword,
                    'placeholder' => __('Key', 'jigoshop'),
                    'classes' => array('user-password'),
                )); ?>
                <?php \Jigoshop\Admin\Helper\Forms::select(array(
                    'label' => __('Permissions', 'jigoshop'),
                    'name' => sprintf('%s[%s][permissions]', $name, $index),
                    'type' => 'select',
                    'value' => $permissions,
                    'description' => __('Leave all to set all permissions.', 'jigoshop'),
                    'multiple' => true,
                    'options' => $availablePermissions,
                )); ?>
            </div>
        </fieldset>
    </div>
</li>
