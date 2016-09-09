<?php
/**
 *
 */
?>
<div id="api-keys" class="col-xs-12 col-sm-12 clearfix">
    <div class="tooltip-inline-badge"></div>
    <div class="tooltip-inline-input">
        <span class="help-block"><?php echo $description; ?></span>
        <ul class="list-group">
            <?php if ($values && count($values)) : ?>
                <?php foreach ($values as $i => $keyData): ?>
                    <?php \Jigoshop\Helper\Render::output('admin/settings/api_keys/key', array(
                        'index' => $i,
                        'name' => $name,
                        'userId' => $keyData['user_id'],
                        'key' => $keyData['key'],
                        'permissions' => $keyData['permissions'],
                        'availablePermissions' => $availablePermissions,
                        'active' => false
                    )); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <?php \Jigoshop\Helper\Render::output('admin/settings/api_keys/key', array(
                    'index' => 0,
                    'name' => $name,
                    'userId' => '',
                    'key' => '',
                    'permissions' => array(),
                    'availablePermissions' => $availablePermissions,
                    'active' => true
                )); ?>
            <?php endif; ?>
        </ul>
        <a href="#" class="add-key btn btn-default pull-right"><?php _e('Add key', 'jigoshop'); ?></a>
    </div>
</div>
<script type="text/template" id="tmpl-api-key">
    <?php echo preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', \Jigoshop\Helper\Render::get('admin/settings/api_keys/key', array(
        'index' => '{{{ data.id }}}',
        'name' => $name,
        'userId' => '',
        'key' => '',
        'permissions' => array(),
        'availablePermissions' => $availablePermissions,
        'active' => true,
    ))); ?>
</script>