<?php
use Jigoshop\Core\Options;
use Jigoshop\Helper\Product;

/**
 * @var $before_widget string
 * @var $before_title string
 * @var $title string
 * @var $after_title string
 * @var $after_widget string
 * @var $comments array
 */

echo $before_widget;
if ($title) {
	echo $before_title.$title.$after_title;
}
?>
<ul class="product_list_widget">
	<?php foreach ($comments as $comment):
		/** @var $product \Jigoshop\Entity\Product */
		$product = $comment->product;
		?>
		<li>
		<a href="<?= get_comment_link($comment->comment_ID); ?>">
			<?= Product::getFeaturedImage($product, Options::IMAGE_TINY); ?>
			<span class="js_widget_product_title"><?= $product->getName(); ?></span>
		</a>
		<?= Product::getRatingHtml($comment->rating, 'recent_reviews'); ?>
		<?php printf(_x('by %1$s', 'author', 'jigoshop-ecommerce'), get_comment_author($comment->comment_ID)); ?>
	</li>
	<?php endforeach; ?>
</ul>
<?= $after_widget;
