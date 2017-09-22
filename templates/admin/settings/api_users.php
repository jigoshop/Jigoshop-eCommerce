<?php
/**
 *
 */
?>
<div id="api-users" class="col-xs-12 col-sm-12 clearfix">
    <div class="tooltip-inline-badge"></div>
    <div class="tooltip-inline-input">
        <span class="help-block"><?= $description; ?></span>
        <ul class="list-group">
            <?php if ($values && count($values)) : ?>
                <?php foreach ($values as $i => $keyData): ?>
                    <?php \Jigoshop\Helper\Render::output('admin/settings/api_users/user', [
                        'index' => $i,
                        'name' => $name,
                        'login' => $keyData['login'],
                        'password' => $keyData['password'],
                        'permissions' => isset($keyData['permissions'])?$keyData['permissions']:[],
                        'availablePermissions' => $availablePermissions,
                        'active' => false
                    ]); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <?php \Jigoshop\Helper\Render::output('admin/settings/api_users/user', [
                    'index' => 0,
                    'name' => $name,
                    'login' => '',
                    'password' => '',
                    'permissions' => [],
                    'availablePermissions' => $availablePermissions,
                    'active' => true
                ]); ?>
            <?php endif; ?>
        </ul>
        <a href="#" class="add-user btn btn-default pull-right"><?php _e('Add User', 'jigoshop-ecommerce'); ?></a>
    </div>
</div>
<script type="text/template" id="tmpl-api-user">
    <?= preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', \Jigoshop\Helper\Render::get('admin/settings/api_users/user', [
        'index' => '{{{ data.id }}}',
        'name' => $name,
        'login' => '',
        'password' => '',
        'permissions' => [],
        'availablePermissions' => $availablePermissions,
        'active' => true,
    ])); ?>
</script>