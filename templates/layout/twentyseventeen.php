<?php
/**
 * @var $content string Content to display.
 */
get_header('shop');
?>
<div class="wrap">
    <div id="primary" class="content-area">
        <main id="main" class="site-main jigoshop" role="main">
            <?php /** @deprecated */ do_action('jigoshop\shop\content\before'); ?>
            <?php do_action('jigoshop\template\shop\content\before'); ?>
            <div class="content">
                <?= $content; ?>
            </div>
            <?php /** @deprecated */ do_action('jigoshop\shop\content\after'); ?>
            <?php do_action('jigoshop\template\shop\content\after'); ?>
        </main>
    </div>
    <?php do_action('jigoshop\template\sidebar'); ?>
    <?php get_sidebar('shop'); // TODO: Remove on implementation of jigoshop\sidebar ?>
</div>
<?php get_footer('shop');