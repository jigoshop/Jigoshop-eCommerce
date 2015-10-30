<?php
/**
 * @var $wpdb \WPDB Database.
 * @var $tabs array List of tabs to display.
 * @var $current_tab string Current tab slug.
*/
use Jigoshop\Admin\SystemInfo;

?>
<div class="wrap jigoshop">
	<h1><?php _e('Jigoshop &rang; System Information', 'jigoshop'); ?></h1>
	<?php settings_errors(); ?>
	<ul class="nav nav-tabs nav-justified" role="tablist">
		<?php foreach($tabs as $tab): /** @var $tab \Jigoshop\Admin\Settings\TabInterface */ ?>
			<li class="<?php $tab->getSlug() == $current_tab and print 'active'; ?>">
				<a href="?page=<?php echo SystemInfo::NAME; ?>&tab=<?php echo $tab->getSlug(); ?>"><?php echo $tab->getTitle(); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
	<noscript>
		<div class="alert alert-danger" role="alert"><?php _e('<strong>Warning</strong> System Info panel will not work properly without JavaScript.', 'jigoshop'); ?></div>
	</noscript>
	<div class="tab-content">
		<div class="tab-pane active">
			<?php settings_fields(SystemInfo::NAME); ?>
			<?php do_settings_sections(SystemInfo::NAME); ?>
		</div>
	</div>
</div>

