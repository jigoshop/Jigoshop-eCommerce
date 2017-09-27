<h1><?php _e('Login', 'jigoshop-ecommerce'); ?></h1>
<form role="form" action="<?= wp_login_url(); ?>" method="post">
	<div class="form-group">
		<label for="user_login"><?php _e('Username', 'jigoshop-ecommerce'); ?></label>
		<input type="text" name="log" class="form-control" id="user_login" placeholder="<?php _e('Enter username', 'jigoshop-ecommerce'); ?>">
	</div>
	<div class="form-group">
		<label for="user_pass"><?php _e('Password', 'jigoshop-ecommerce'); ?></label>
		<input type="password" name="pwd" class="form-control" id="user_pass" placeholder="<?php _e('Your password', 'jigoshop-ecommerce'); ?>">
	</div>
	<div class="checkbox">
		<label>
			<input type="checkbox" name="rememberme" value="forever"> <?php _e('Remember me', 'jigoshop-ecommerce'); ?>
		</label>
	</div>
	<button type="submit" name="wp-submit" value="<?php _e('Log in', 'jigoshop-ecommerce'); ?>" class="btn btn-default"><?php _e('Log in', 'jigoshop-ecommerce'); ?></button>
	<input type="hidden" value="<?= get_permalink(); ?>" name="redirect_to">
</form>
