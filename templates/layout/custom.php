<?php
/**
 * @var $content string Content to display.
 * @var $options
 */
$contentSize = $options['structure'] != 'only_content' ? $options['proportions']['content'].'%' : '100%';
$contentPosition = $options['structure'] == 'sidebar_left' ? 'right' : 'left';
get_header('shop');
?>
<style><?php echo $options['global_css']; ?></style>
<style><?php echo $options['css']; ?></style>
<div style="width: <?php echo $options['page_width']; ?>; margin-left: auto; margin-right: auto">
    <?php if($options['structure'] == 'sidebar_left') : ?>
        <div style="float: left; width: <?php echo $options['proportions']['sidebar'].'%';?>">
            <?php do_action('jigoshop\template\sidebar'); ?>
            <?php dynamic_sidebar('jigoshop_sidebar_'.$options['sidebar']); ?>
        </div>
    <?php endif; ?>
    <div style="width: <?php echo $contentSize; ?>; float: <?php echo $contentPosition; ?>">
        <div id="jigoshop_content" role="main" class="jigoshop">
            <?php /** @deprecated */ do_action('jigoshop\shop\content\before'); ?>
            <?php do_action('jigoshop\template\shop\content\before'); ?>
            <?php echo $content; ?>
            <?php /** @deprecated */ do_action('jigoshop\shop\content\after'); ?>
            <?php do_action('jigoshop\template\shop\content\after'); ?>
        </div>
    </div>
    <?php if($options['structure'] == 'sidebar_right') : ?>
        <div style="float: right; width: <?php echo $options['proportions']['sidebar'].'%';?>">
            <?php do_action('jigoshop\template\sidebar'); ?>
            <?php dynamic_sidebar('jigoshop_sidebar_'.$options['sidebar']); ?>
        </div>
    <?php endif; ?>
</div>
<div style="clear: both"></div>
<?php get_footer('shop'); ?>

