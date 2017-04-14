<?php
/**
 *
 *
 */
?>
<div class="row">
	<?php foreach($args as $arg) : ?>
		<a href="<?= $arg['url'] ?>">
			<div class="col-xs-3 total"><?= $arg['total'] ?></div>
			<div class="col-xs-9"><?= $arg['title'] ?></div>
		</a>
	<?php endforeach; ?>
</div>
<div class="clear"></div>