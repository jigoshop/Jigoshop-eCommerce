<?php
/**
 * @var $content string Contelnt to display.
 */
get_header('shop');
?>
<div id="content" class="col-full">
	<div id="main" class="col-left">
		<div class="post jigoshop">
            <?php /** @deprecated */ do_action('jigoshop\shop\content\before'); ?>
            <?php do_action('jigoshop\template\shop\content\before'); ?>
			<?= $content; ?>
            <?php /** @deprecated */ do_action('jigoshop\shop\content\after'); ?>
            <?php do_action('jigoshop\template\shop\content\after'); ?>
		</div>
	</div>
</div>
<?php do_action('jigoshop\template\sidebar'); ?>
<?php get_sidebar('shop'); // TODO: Remove on implementation of jigoshop\sidebar ?>
<?php get_footer('shop'); ?>
