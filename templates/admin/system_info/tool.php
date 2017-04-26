<?php
/**
* @var $id string Field ID.
* @var $name string Field name.
* @var $classes array List of classes to add to the field.
* @var $tip string Tip to show to the user.
* @var $description string Field description.
*/

use Jigoshop\Admin\SystemInfo;
use Jigoshop\Admin\SystemInfo\ToolsTab;

?>
<div class="form-group <?= $id; ?>_field <?= join(' ', $classes); ?>">
	<div class="row">
		<div class="col-sm-<?= $size; ?>">
			<div class="col-xs-2 col-sm-1 text-right">
				<?php if (!empty($tip)): ?>
					<span data-toggle="tooltip" class="badge margin-top-bottom-9" data-placement="top" title="<?= $tip; ?>">?</span>
				<?php endif; ?>
			</div>
			<div class="col-xs-<?= $size - 2 ?> col-sm-<?= $size - 1 ?>">
				<a href="?page=<?= SystemInfo::NAME; ?>&tab=<?= ToolsTab::SLUG; ?>&request=<?= $id; ?>" class="btn btn-primary"><?= $title ?></a>
				<?php if(!empty($description)): ?>
					<span class="help"><?= $description; ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
