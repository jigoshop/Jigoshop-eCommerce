<?php
use Jigoshop\Entity\Customer;
use Jigoshop\Helper\Render;

/**
 * @var $customer Customer
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $myAccountUrl string URL to my account.
 */
?>
<h1><?php _e('My account &raquo; Change password', 'jigoshop-ecommerce'); ?></h1>
<?php Render::output('shop/messages', ['messages' => $messages]); ?>
<form class="" role="form" method="post">
	<?php \Jigoshop\Helper\Forms::text([
		'name' => 'password',
		'type' => 'password',
		'label' => __('Current password', 'jigoshop-ecommerce'),
		'value' => '',
    ]); ?>
	<?php \Jigoshop\Helper\Forms::text([
		'name' => 'new-password',
		'type' => 'password',
		'label' => __('New password', 'jigoshop-ecommerce'),
		'value' => '',
    ]); ?>
	<?php \Jigoshop\Helper\Forms::text([
		'name' => 'new-password-2',
		'type' => 'password',
		'label' => __('Re-type new password', 'jigoshop-ecommerce'),
		'value' => '',
    ]); ?>
	<a href="<?= $myAccountUrl; ?>" class="btn btn-default"><?php _e('Go back to My account', 'jigoshop-ecommerce'); ?></a>
	<button class="btn btn-success pull-right" name="action" value="change_password"><?php _e('Change password', 'jigoshop-ecommerce'); ?></button>
</form>
