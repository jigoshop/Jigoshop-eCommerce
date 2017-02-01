<?php
use Jigoshop\Helper\Product;

/**
 * @var $product \Jigoshop\Entity\Product Product object.
 * @var $featured string Featured image.
 * @var $featuredUrl string URL to featured image.
 * @var $thumbnails \Jigoshop\Entity\Product\Attachment\Image[] List of product thumbnails.
 * @var $imageClasses array List of classes to attach to image.
 */
?>
<div class="images">
	<?php if (Product::isOnSale($product)): ?>
		<span class="on-sale"><?php echo apply_filters('jigoshop\template\product\sale_text', __('Sale!', 'jigoshop'), $product) ?></span>
	<?php endif; ?>
	<?php do_action('jigoshop\template\product\before_featured_image', $product); ?>
	<a href="<?php echo $featuredUrl; ?>" class="<?php echo join(' ', $imageClasses); ?>" data-lightbox="product-gallery"><?php echo $featured; ?></a>
	<?php do_action('jigoshop\template\product\before_thumbnails', $product); ?>
	<div class="thumbnails">
		<?php foreach($thumbnails as $thumbnail): ?>
			<a href="<?php echo $thumbnail->getUrl(); ?>" data-lightbox="product-gallery" data-title="<?php echo $thumbnail->getTitle(); ?>" title="<?php echo $thumbnail->getTitle(); ?>" class="zoom">
				<?php echo apply_filters('jigoshop\template\product\thumbnail', $thumbnail->getImage(), $thumbnail, $product); ?>
			</a>
		<?php endforeach; ?>
	</div>
	<?php do_action('jigoshop\template\product\after_thumbnails', $product); ?>
</div>
