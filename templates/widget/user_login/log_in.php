<?php
use Jigoshop\Core\Options;
use Jigoshop\Helper\Product;

/**
 * @var $before_widget string
 * @var $before_title string
 * @var $title string
 * @var $after_title string
 * @var $after_widget string
 * @var $links array
 * @var $loginUrl string
 * @var $passwordUrl string
 */

echo $before_widget;
if ($title) {
	echo $before_title.$title.$after_title;
}
do_action('jigoshop_widget_login_before_form');
?>
<form action="<?= $loginUrl; ?>" method="post" class="jigoshop_login_widget">
	<p>
		<input type="text" name="log" class="input-text username" placeholder="<?php _e('Username', 'jigoshop-ecommerce'); ?>" />
	</p>
	<p>
		<input type="password" name="pwd" class="input-text password" placeholder="<?php _e('Password', 'jigoshop-ecommerce'); ?>" />
	</p>
	<p>
		<input type="submit" name="submit" value="<?php _e('Login', 'jigoshop-ecommerce'); ?>" class="input-submit" />
	</p>
	<p>
		<a class="forgot" href="<?= $passwordUrl; ?>"><?php _e('Remind password', 'jigoshop-ecommerce'); ?></a>
	</p>
	<?php if (\Jigoshop\Helper\Options::getOptions('shopping')['allow_registration']): ?>
		<p class="register">
			<?php wp_register(__('New user?', 'jigoshop-ecommerce') . ' ', '', true); ?>
		</p>
	<?php endif; ?>
</form>
<?php if(!empty($links)): ?>
	<nav role="navigation">
		<ul class="pagenav">
			<?php foreach ($links as $title => $href): ?>
				<li><a title="<?php printf(__('Go to %s', 'jigoshop-ecommerce'), $title); ?>" href="<?= $href; ?>"><?= $title; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>
<?php endif; ?>
<?php
do_action('jigoshop_widget_login_after_form');
echo $after_widget;
