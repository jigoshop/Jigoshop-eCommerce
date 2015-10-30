<?php
/**
 * @var $tab \Jigoshop\Admin\SystemInfo\TabInterface Tab to display
 * @var $section array Section to display.
 */
?>
<?php if(isset($section['description'])): ?>
	<p class="help"><?php echo $section['description']; ?></p>
<?php endif; ?>
