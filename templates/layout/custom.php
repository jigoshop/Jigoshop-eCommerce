<?php
/**
 * @var $content string Content to display.
 * @var $options
 */
$contentSize = $options['structure'] != 'only_content' ? $options['proportions']['content'].'%' : '100%';
$contentPosition = $options['structure'] == 'sidebar_left' ? 'right' : 'left';
get_header('shop');
?>
<style><?= $options['global_css']; ?></style>
<style>
    .custom-layout-container {
        width: <?= $options['page_width']; ?>;
        margin-left: auto;
        margin-right: auto;
        max-width: 100%;
    }
    .custom-layout-content {
        width: <?= $contentSize; ?>;
        float: <?= $contentPosition; ?>
    }
    <?php if(in_array($options['structure'], ['sidebat_left', 'sidebar_right'])): ?>
        .custom-layout-sidebar {
            width: <?= $options['proportions']['sidebar'].'%'; ?>;
            float: <?= $options['structure'] == 'sidebat_left' ? 'left' : 'right'; ?>;
        }
    <?php endif; ?>
    .custom-layout-clear {
        clear: both;
    }
    @media all and (max-width: 768px) {
        .custom-layout-content {
            float: left;
            width: 100%;
        }
        .custom-layout-sidebar {
            float: right;
            width: 100%;
        }
    }
    <?= $options['css']; ?>
</style>
<div class="custom-layout-container">
    <?php if($options['structure'] == 'sidebar_left') : ?>
        <div class="custom-layout-sidebar">
            <?php do_action('jigoshop\template\sidebar'); ?>
            <?php dynamic_sidebar('jigoshop_sidebar_'.$options['sidebar']); ?>
        </div>
    <?php endif; ?>
    <div class="custom-layout-content">
        <div id="jigoshop_content" role="main" class="jigoshop">
            <?php /** @deprecated */ do_action('jigoshop\shop\content\before'); ?>
            <?php do_action('jigoshop\template\shop\content\before'); ?>
            <?= $content; ?>
            <?php /** @deprecated */ do_action('jigoshop\shop\content\after'); ?>
            <?php do_action('jigoshop\template\shop\content\after'); ?>
        </div>
    </div>
    <?php if($options['structure'] == 'sidebar_right') : ?>
        <div class="custom-layout-sidebar">
            <?php do_action('jigoshop\template\sidebar'); ?>
            <?php dynamic_sidebar('jigoshop_sidebar_'.$options['sidebar']); ?>
        </div>
    <?php endif; ?>
</div>
<div class="custom-layout-clear"></div>
<?php get_footer('shop'); ?>

