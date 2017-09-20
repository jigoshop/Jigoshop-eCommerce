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
    <title><?php _e('Jigoshop &raquo; Setup', 'jigoshop'); ?></title>
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
            <h1>Welcome to the world of Jigoshop!</h1>
            <p>Thank you for choosing Jigoshop to power your online store! This quick setup wizard will help you configure the
                basic settings. <strong>It’s completely optional and shouldn’t take longer than five minutes.</strong></p>
            <p>No time right now? If you don’t want to go through the wizard, you can skip and return to the WordPress
                dashboard. Come back anytime if you change your mind!</p>
        <?php endif; ?>
        <?php if(count($options)): ?>
            <form id="form" action="#">
                <?php foreach ($options as $option) : ?>
                    <?php \Jigoshop\Helper\Forms::field($option['type'], $option); ?>
                <?php endforeach; ?>
            </form>
        <?php endif; ?>
        <?php if($currentStep == 'ready') : ?>
            <h1>Your store is ready!</h1>

            Plsee rate us etc itd. Coś chwytliwego trzeba wymyślić.
        <?php endif; ?>
        <p class="jigo-setup-actions step">
            <?php if($nextStep && $currentStep != '') : ?>
                <a id="next-step" href="#" data-url="<?= admin_url('admin.php?page=' . \Jigoshop\Admin\Setup::SLUG . '&step='. $nextStep); ?>"
                   class="button-primary button button-large button-next"><?= __('Let\'s go!', 'jigoshop'); ?></a>
                <a href="<?= admin_url('admin.php?page=' . \Jigoshop\Admin\Setup::SLUG . '&step='. $nextStep); ?>" class="button button-large"><?= __('Skip', 'jigoshop'); ?></a>
            <?php elseif($nextStep && $currentStep == '') : ?>
                <a href="<?= admin_url('admin.php?page=' . \Jigoshop\Admin\Setup::SLUG . '&step='. $nextStep); ?>"
                   class="button-primary button button-large button-next"><?= __('Let\'s go!', 'jigoshop'); ?></a>
                <a href="<?= admin_url('admin.php?page=' . \Jigoshop\Admin\Dashboard::NAME); ?>" class="button button-large"><?= __('No thanks', 'jigoshop'); ?></a>
            <?php else : ?>
                <a href="<?= admin_url('admin.php?page=' . \Jigoshop\Admin\Dashboard::NAME); ?>"
                   class="button-primary button button-large button-next"><?= __('Go to admin page', 'jigoshop'); ?></a>
            <?php endif; ?>
        </p>
    </div>
</body>
</html>
