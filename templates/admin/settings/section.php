<?php
/**
 * @var $tab \Jigoshop\Admin\Settings\TabInterface Tab to display
 * @var $section array Section to display.
 */
?>
<?php if(isset($section['description'])): ?>
<p class="help"><?= $section['description']; ?></p>
<?php endif; ?>
