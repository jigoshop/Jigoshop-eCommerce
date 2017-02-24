<?php
/**
 * @var $content string Content to display.
 * @var $options
 */
get_header('shop');
$contentSize = $options['structure'] != 'only_content' ? $options['proportions']['content'].'%' : '100%';
$contentPosition = $options['structure'] == 'sidebar_left' ? 'right' : 'left';
?>
<style><?php echo $options['css']; ?></style>
<div style="width: <?php echo $options['page_width']; ?>; margin-left: auto; margin-right: auto">
    <?php if($options['structure'] == 'sidebar_left') : ?>
        <div style="float: left; width: <?php echo $options['proportions']['sidebar'].'%';?>">
            <?php do_action('jigoshop\sidebar'); ?>
            <?php dynamic_sidebar('jigoshop_sidebar_'.$options['sidebar']); ?>
        </div>
    <?php endif; ?>
    <div style="width: <?php echo $contentSize; ?>; float: <?php echo $contentPosition; ?>">
        <div id="jigoshop_content" role="main" class="jigoshop">
            <?php do_action('jigoshop\shop\content\before'); ?>
            <?php echo $content; ?>
            <?php do_action('jigoshop\shop\content\after'); ?>
        </div>
    </div>
    <?php if($options['structure'] == 'sidebar_right') : ?>
        <div style="float: right; width: <?php echo $options['proportions']['sidebar'].'%';?>">
            <?php do_action('jigoshop\sidebar'); ?>
            <?php dynamic_sidebar('jigoshop_sidebar_'.$options['sidebar']); ?>
        </div>
    <?php endif; ?>
</div>
<div class="clear"></div>
<?php get_footer('shop'); ?>

