<?php
use Jigoshop\Helper\Product;

/**
 * @var $product \Jigoshop\Entity\Product Product object.
 * @var $featured string Featured image.
 * @var $featuredUrl string URL to featured image.
 * @var $featuredTitle string featured image title.
 * @var $thumbnails \Jigoshop\Entity\Product\Attachment\Image[] List of product thumbnails.
 * @var $imageClasses array List of classes to attach to image.
 */
?>
<div class="images js-img-product col-sm-4">
	<?php if (Product::isOnSale($product)): ?>
		<div class="on-sale"><?= apply_filters('jigoshop\template\product\sale_text', __('Sale!', 'jigoshop'), $product) ?></div>
	<?php endif; ?>
	<?php do_action('jigoshop\template\product\before_featured_image', $product); ?>
    <div class="product-gallery">
        <?php \Jigoshop\Helper\Render::output('shop/product/images/featured', [
            'featured' => $featured,
            'featuredUrl' => $featuredUrl,
            'featuredTitle' => $featuredTitle,
            'imageClasses' => $imageClasses,
        ]); ?>
    </div>
	<?php do_action('jigoshop\template\product\before_thumbnails', $product); ?>
	<div class="product-gallery thumbnails">
		<?php foreach($thumbnails as $thumbnail): ?>
			<a href="<?= $thumbnail->getUrl(); ?>" data-lightbox="product-gallery" data-gallery="#blueimp-gallery" title="<?= $thumbnail->getTitle(); ?>" title="<?= $thumbnail->getTitle(); ?>" class="zoom active">
				<?= apply_filters('jigoshop\template\product\thumbnail', $thumbnail->getImage(), $thumbnail, $product); ?>
			</a>
		<?php endforeach; ?>
	</div>
	<?php do_action('jigoshop\template\product\after_thumbnails', $product); ?>
</div>
