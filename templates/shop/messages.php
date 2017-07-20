<?php
/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 */
?>
<div id="<?php echo (isset($containerId) && $containerId?$containerId:'messages'); ?>">
	<?php foreach ($messages->getErrors() as $error): ?>
		<div class="alert alert-danger" role="alert"><?= $error; ?></div>
	<?php endforeach; ?>
	<?php foreach ($messages->getWarnings() as $warning): ?>
		<div class="alert alert-warning" role="alert"><?= $warning; ?></div>
	<?php endforeach; ?>
	<?php foreach ($messages->getNotices() as $notice): ?>
		<div class="alert alert-success" role="alert"><?= $notice; ?></div>
	<?php endforeach; ?>
	<?php do_action('jigoshop\template\shop\messages'); ?>
</div>
