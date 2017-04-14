<?php
use Jigoshop\Helper\Render;
/**
 * @var $menu
 */
?>
<div class="jigoshop">
    <div class="form-horizontal">
        <ul class="jigoshop_product_attachments nav nav-tabs" role="tablist">
            <?php foreach($menu as $id => $name) : ?>
            <li class="<?= $id; ?><?= $id == 'gallery' ? ' active' : ''; ?>">
                <a href="#<?= $id; ?>" data-toggle="tab"><?= $name; ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="tab-content">
            <?php foreach($menu as $id => $name) : ?>
            <div class="tab-pane<?= $id == 'gallery' ? ' active' : ''; ?>" id="<?= $id; ?>">
                <?php Render::output('admin/product/attachments/'.$id, []); ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>