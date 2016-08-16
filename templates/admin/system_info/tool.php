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
<div class="form-group <?php echo $id; ?>_field <?php echo join(' ', $classes); ?>">
	<div class="row">
		<div class="col-sm-<?php echo $size; ?>">
			<div class="col-xs-2 col-sm-1 text-right">
				<?php if (!empty($tip)): ?>
					<span data-toggle="tooltip" class="badge margin-top-bottom-9" data-placement="top" title="<?php echo $tip; ?>">?</span>
				<?php endif; ?>
			</div>
			<div class="col-xs-<?php echo $size - 2 ?> col-sm-<?php echo $size - 1 ?>">
				<a href="?page=<?php echo SystemInfo::NAME; ?>&tab=<?php echo ToolsTab::SLUG; ?>&request=<?php echo $id; ?>" class="btn btn-primary"><?php echo $title ?></a>
				<?php if(!empty($description)): ?>
					<span class="help"><?php echo $description; ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
