<?php
/**
 * @var \Jigoshop\Entity\Product\Variable $product
 * @var array $images
 */
?>
<div class="product-gallery variable-product-gallery thumbnails">
    <?php foreach($images as $image): ?>
        <a id="variation-featured-image-<?= $image['id'] ?>" href="<?= $image['url']; ?>" data-lightbox="product-gallery" data-gallery="#blueimp-gallery" title="<?= $image['title']; ?>" title="<?= $image['title']; ?>" class="zoom">
            <?= apply_filters('jigoshop\template\product\thumbnail', $image['image'], $image['image'], $product); ?>
        </a>
    <?php endforeach; ?>
</div>

