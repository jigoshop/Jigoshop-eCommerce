<?php
/**
 * @var $content string Content to display.
 */
get_header('shop');
?>
<div id="primary" class="content-area">
	<div id="content" class="site-content jigoshop" role="main">
        <?php /** @deprecated */ do_action('jigoshop\shop\content\before'); ?>
        <?php do_action('jigoshop\template\shop\content\before'); ?>
		<?= $content; ?>
		<<?php /** @deprecated */ do_action('jigoshop\shop\content\after'); ?>
        <?php do_action('jigoshop\template\shop\content\after'); ?>
	</div>
</div>
<?php do_action('jigoshop\template\sidebar'); ?>
<?php get_sidebar('shop'); // TODO: Remove on implementation of jigoshop\sidebar ?>
<?php get_footer('shop'); ?>
