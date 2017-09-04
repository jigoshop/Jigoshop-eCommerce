<?php
use Jigoshop\Admin\Settings;
use Jigoshop\Helper\Render;

/**
 * @var $tabs array List of tabs to display.
 * @var $current_tab string Current tab slug.
 * @var $messages \Jigoshop\Core\Messages Messages container.
 */
?>
<div class="wrap jigoshop">
	<h1><?php _e('Jigoshop &raquo; Settings', 'jigoshop-ecommerce'); ?></h1>
	<?php settings_errors(); ?>
	<?php Render::output('shop/messages', ['messages' => $messages]);
	$menuContent = '';
	$activeTitle = '';
	foreach ($tabs as $tab): /** @var $tab \Jigoshop\Admin\Settings\TabInterface */
		$active = '';
		if($tab->getSlug() == $current_tab)
		{
			$active = 'active';
			$activeTitle = $tab->getTitle();
		}
		$menuContent .= '<li class="' . $active . '">' .
			'<a href="?page=' . Settings::NAME . '&amp;tab=' . $tab->getSlug() . '">' . $tab->getTitle() . '</a>' .
		'</li>';
	endforeach; ?>
	<nav class="navbar navbar-default hidden-md hidden-lg hidden-sm">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle margin-9" data-toggle="collapse" data-target="#settingsBar">
					<span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#"><?= $activeTitle; ?></a>
				<div class="clear"></div>
			</div>
			<div class="collapse navbar-collapse" id="settingsBar">
				<ul class="nav navbar-nav">
					<?= $menuContent; ?>
				</ul>
			</div>
		</div>
	</nav>
	<nav class="hidden-xs">
		<ul class="nav nav-tabs nav-justified">
			<?= $menuContent; ?>
		</ul>
	</nav>
	<noscript>
		<div class="alert alert-danger" role="alert"><?php _e('<strong>Warning</strong> Options panel will not work properly without JavaScript.', 'jigoshop-ecommerce'); ?></div>
	</noscript>
	<div class="tab-content">
		<div class="tab-pane active">
			<form action="options.php" method="post" enctype="multipart/form-data" role="form" class="clearfix">
				<input type="hidden" name="tab" value="<?= $current_tab; ?>" />
				<?php settings_fields(Settings::NAME); ?>
				<?php do_settings_sections(Settings::NAME); ?>
				<button type="submit" class="btn btn-primary pull-right button-save-options"><?= __('Save changes', 'jigoshop-ecommerce'); ?></button>
			</form>
		</div>
	</div>
</div>
