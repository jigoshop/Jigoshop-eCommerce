<?php
/**
 * @var $tab \Jigoshop\Admin\ThemeOptions\ThemeTabInterface Tab to display
 * @var $section array Section to display.
 */
?>
<?php if(isset($section['description'])): ?>
<p class="help"><?= $section['description']; ?></p>
<?php endif; ?>
