<?php
/**
 *
 *
 */
?>
<div class="row">
	<?php foreach($args as $arg) : ?>
		<a href="<?php echo $arg['url'] ?>">
			<div class="col-xs-3 total"><?php echo $arg['total'] ?></div>
			<div class="col-xs-9"><?php printf('%s (#%d)',$arg['title'], $arg['id']); ?></div>
		</a>
	<?php endforeach; ?>
</div>
<div class="clear"></div>