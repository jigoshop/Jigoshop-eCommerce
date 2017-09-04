<?php
/**
 *
 */
?>
<li class="list-group-item">
    <h4 class="list-group-item-heading clearfix">
        <span class="title"><?= $login; ?></span>
        <button type="button" class="remove btn btn-default pull-right" title="<?php _e('Remove', 'jigoshop-ecommerce'); ?>"><span class="glyphicon glyphicon-remove"></span></button>
        <button type="button" class="toggle btn btn-default pull-right" title="<?php _e('Expand', 'jigoshop-ecommerce'); ?>"><span class="glyphicon glyphicon-collapse-down"></span></button>
    </h4>
    <div class="list-group-item-text"<?php $active == false and print ' style="display: none"'?>>
        <fieldset>
            <div class="col-sm-6">
                <?php \Jigoshop\Admin\Helper\Forms::text([
                    'label' => __('Login', 'jigoshop-ecommerce'),
                    'name' => sprintf('%s[%s][login]', $name, $index),
                    'value' => $login,
                    'placeholder' => __('Login', 'jigoshop-ecommerce'),
                    'classes' => ['login'],
                ]); ?>
                <div class="col-xs-12">
                    <a href="#" class="btn btn-default pull-right generate"><?php _e('Generate', 'jigoshop-ecommerce'); ?></a>
                </div>
            </div>
            <div class="col-sm-6">
                <?php \Jigoshop\Admin\Helper\Forms::text([
                    'label' => __('Password', 'jigoshop-ecommerce'),
                    'name' => sprintf('%s[%s][password]', $name, $index),
                    'value' => $password,
                    'placeholder' => __('Key', 'jigoshop-ecommerce'),
                    'classes' => ['password'],
                ]); ?>
                <?php \Jigoshop\Admin\Helper\Forms::select([
                    'label' => __('Permissions', 'jigoshop-ecommerce'),
                    'name' => sprintf('%s[%s][permissions]', $name, $index),
                    'type' => 'select',
                    'value' => $permissions,
                    'description' => __('Leave all to set all permissions.', 'jigoshop-ecommerce'),
                    'multiple' => true,
                    'options' => $availablePermissions,
                ]); ?>
            </div>
        </fieldset>
    </div>
</li>
