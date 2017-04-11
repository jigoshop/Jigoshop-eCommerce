<?php
/**
 * @var $featured string Featured image.
 * @var $featuredUrl string URL to featured image.
 * @var $featuredTitle string featured image title.
 * @var $imageClasses array List of classes to attach to image.
 */
?>
<a href="<?= $featuredUrl; ?>" class="active <?= join(' ', $imageClasses); ?>" <?= $featuredUrl ? 'data-gallery="#blueimp-gallery"' : ''; ?> title="<?= $featuredTitle ?>">
    <?= $featured; ?>
</a>
