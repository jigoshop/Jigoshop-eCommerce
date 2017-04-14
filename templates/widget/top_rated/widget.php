<?php
use Jigoshop\Core\Options;
use Jigoshop\Helper\Product;

/**
 * @var $before_widget string
 * @var $before_title string
 * @var $title string
 * @var $after_title string
 * @var $after_widget string
 * @var $products array
 */

echo $before_widget;
if ($title) {
	echo $before_title.$title.$after_title;
}
?>
<ul class="product_list_widget">
	<?php foreach ($products as $product): /** @var $product \Jigoshop\Entity\Product */?>
	<li>
		<a href="<?= $product->getLink(); ?>">
			<?= Product::getFeaturedImage($product, Options::IMAGE_TINY); ?>
			<span class="js_widget_product_title"><?= $product->getName(); ?></span>
		</a>
		<?= Product::getRatingHtml(Product::getRating($product)); ?> ?>
		<span class="js_widget_product_price"><?= Product::getPriceHtml($product); ?></span>
	</li>
	<?php endforeach; ?>
</ul>
<?= $after_widget;
