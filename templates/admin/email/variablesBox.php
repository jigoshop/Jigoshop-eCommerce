<?php
/**
 * @var $email \Jigoshop\Entity\Email The email.
 * @var $emails array List of registered emails.
 */
?>
<div class="jigoshop">
	<?php \Jigoshop\Helper\Render::output('admin/email/variables', [
		'email' => $email,
		'emails' => $emails,
    ]); ?>
</div>
<div class="clear"></div>
