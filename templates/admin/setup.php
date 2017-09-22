<?php
/**
 *
 */
$printActive = true;
$printDone = true;
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php _e('Jigoshop &raquo; Setup', 'jigoshop-ecommerce'); ?></title>
    <?php wp_print_scripts(); ?>
    <?php do_action( 'admin_print_styles' ); ?>
    <?php do_action( 'admin_head' ); ?>
</head>
<body class="jigo-setup jigo-core-ui jigoshop">
    <h1 id="jigo-logo"><a href="https://www.jigoshop.com/" target="_blank">
            <img src="<?= JigoshopInit::getUrl().'/assets/images/logo-dark.png'; ?>"
                alt="Jigoshop eCommerce"></a>
    </h1>
    <ol class="jigo-setup-steps">
        <?php foreach($steps as $key => $step) : ?>
            <?php if($key == $nextStep) $printActive = false; ?>
            <?php if($key == $currentStep || $currentStep == '') $printDone = false; ?>
            <?= '<li class="' . ($printActive ? 'active' : '') . ($printDone ? ' done' : '') .'">' . $step . '</li>'; ?>
        <?php endforeach; ?>
    </ol>
    <div class="jigo-setup-content">
        <?php if(count($options) == 0 && $currentStep == ''): ?>
            <h1><?= __('Welcome to the world of Jigoshop!', 'jigoshop-ecommerce'); ?></h1>
            <p><?= __('Thank you for choosing Jigoshop (yup, you made the right choice) to give your eCommerce site the power it needs to turn even more profit than it already does!', 'jigoshop-ecommerce'); ?><br/>
            <strong><?= __('This quick setup wizard will help you configure the basic settings of the platform.', 'jigoshop-ecommerce'); ?></strong></p>
            <br/>
            <p><?= __('It\'s completely optional (but recommended) and it shouldn\'t take you more than five minutes.', 'jigoshop-ecommerce'); ?></p>
        <?php endif; ?>
        <?php if(count($options)): ?>
            <form id="form" action="#">
                <?php foreach ($options as $option) : ?>
                    <?php \Jigoshop\Helper\Forms::field($option['type'], $option); ?>
                <?php endforeach; ?>
            </form>
        <?php endif; ?>
        <?php if($currentStep == 'ready') : ?>
            <h1><?= __('Your store is ready now!', 'jigoshop-ecommerce'); ?></h1>
            <p><?= sprintf(__('If you\'re satisfied with this wizard, please consider rating us at %s.<br/>', 'jigoshop-ecommerce'), '<a href="https://wordpress.org/support/plugin/jigoshop-ecommerce/reviews/#new-post" target="_blank">Wordpress.org</a>'); ?>
                <?= sprintf(__('If you have any thoughts or suggestions, feel free to post them at our %s - your feedback is valuable to us.', 'jigoshop-ecommerce'), '<a href="https://wordpress.org/support/plugin/jigoshop-ecommerce/" target="_blank">' . __('support forum', 'jigoshop-ecommerce') .'</a>'); ?></p>
        <?php endif; ?>
        <p class="jigo-setup-actions step">
            <?php if($nextStep && $currentStep != '') : ?>
                <a id="next-step" href="#" data-url="<?= admin_url('admin.php?page=' . \Jigoshop\Admin\Setup::SLUG . '&step='. $nextStep); ?>"
                   class="button-primary button button-large button-next"><?= __('Let\'s go!', 'jigoshop-ecommerce'); ?></a>
                <a href="<?= admin_url('admin.php?page=' . \Jigoshop\Admin\Setup::SLUG . '&step='. $nextStep); ?>" class="button button-large"><?= __('Skip', 'jigoshop-ecommerce'); ?></a>
            <?php elseif($nextStep && $currentStep == '') : ?>
                <a href="<?= admin_url('admin.php?page=' . \Jigoshop\Admin\Setup::SLUG . '&step='. $nextStep); ?>"
                   class="button-primary button button-large button-next"><?= __('Let\'s go!', 'jigoshop-ecommerce'); ?></a>
                <a href="<?= admin_url('admin.php?page=' . \Jigoshop\Admin\Dashboard::NAME); ?>" class="button button-large"><?= __('No thanks', 'jigoshop-ecommerce'); ?></a>
            <?php else : ?>
                <a href="<?= admin_url('admin.php?page=' . \Jigoshop\Admin\Dashboard::NAME); ?>"
                   class="button-primary button button-large button-next"><?= __('Go to admin page', 'jigoshop-ecommerce'); ?></a>
            <?php endif; ?>
        </p>
    </div>
</body>
</html>
