<?php
/**
 *
 *
 */
?>
<div class="row">
	<?php foreach($args as $arg) : ?>
		<a href="<?= $arg['url'] ?>">
			<div class="col-xs-3 count"><?= $arg['count'] ?></div>
			<div class="col-xs-9"><?= $arg['title']; ?></div>
		</a>
	<?php endforeach; ?>
</div>
<div class="clear"></div>