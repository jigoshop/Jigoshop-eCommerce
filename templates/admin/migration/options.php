<button<?php echo($countDone == $countAll ? ' disabled="disabled"' : ''); ?> type="submit" name="tool"
        value="<?php echo Jigoshop\Admin\Migration\Options::ID; ?>" class="btn btn-default btn-block migration-options<?php echo($countDone == $countAll ? ' btn_disabled' : ''); ?>"><?php echo __('Migrate options', 'jigoshop') . sprintf(__(' - %d/%d', 'jigoshop'), $countDone, $countAll) . ($countDone == $countAll ? __(' - done', 'jigoshop') : '') ?>
</button>
SELECT DISTINCT p.ID FROM jigoshop_wp_posts p
LEFT JOIN jigoshop_wp_postmeta pm ON pm.post_id = p.ID
WHERE p.post_type = 'shop_order' AND p.post_status <> 'auto-draft'
ORDER BY p.ID

SELECT DISTINCT p.ID FROM jigoshop_wp_posts p
LEFT JOIN jigoshop_wp_postmeta pm ON pm.post_id = p.ID
WHERE p.post_type = 'shop_order' AND p.post_status <> 'auto-draft'
ORDER BY p.IDPOST XHR http://tomasz.devserver.com/wp-admin/admin-ajax.php [HTTP/1.1 200 OK 139ms]


http://tomasz.devservPOST XHR http://tomasz.devserver.com/wp-admin/admin-ajax.php [HTTP/1.1 200 OK 444ms]er.com/wp-admin/admin-ajax.php

POST XHR http://tomasz.devserver.com/wp-admin/admin-ajax.php [HTTP/1.1 200 OK 444ms]