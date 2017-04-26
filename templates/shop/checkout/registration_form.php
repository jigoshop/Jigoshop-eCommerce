<?php
use Jigoshop\Helper\Forms;

/**
 * @var $showRegistrationForm bool Whether to show registration form.
 */
?>
<?php if (!$showRegistrationForm): ?>
<div class="col-md-6 col-xs-12 pull-right toggle-panels">
	<?php Forms::checkbox([
		'description' => __('Would you like to create an account?', 'jigoshop'),
		'name' => 'jigoshop_account[create]',
		'id' => 'create-account',
    ]); ?>
	</div>
	<div class="clear"></div>
<?php endif; ?>
<div id="registration-form" class="panel panel-default<?php !$showRegistrationForm and print ' not-active'; ?>">
	<div class="panel-heading">
		<h3 class="panel-title"><?php _e('Registration', 'jigoshop'); ?></h3>
	</div>
	<div class="panel-body">
		<div class="row clearfix" >
			<?php Forms::text([
				'label' => __('Username', 'jigoshop'),
				'name' => 'jigoshop_account[login]',
				'placeholder' => __('Enter username', 'jigoshop'),
            ]); ?>
			<?php Forms::text([
				'label' => __('Password', 'jigoshop'),
				'type' => 'password',
				'name' => 'jigoshop_account[password]',
				'placeholder' => __('Your password', 'jigoshop'),
            ]); ?>
			<?php Forms::text([
				'label' => __('Re-type password', 'jigoshop'),
				'type' => 'password',
				'name' => 'jigoshop_account[password2]',
				'placeholder' => __('Re-type your password', 'jigoshop'),
            ]); ?>
			<?php if ($showRegistrationForm): ?>
				<?php Forms::checkbox([
					'description' => __('I agree to account creation', 'jigoshop'),
					'name' => 'jigoshop_account[create]',
					'size' => 9
                ]); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
