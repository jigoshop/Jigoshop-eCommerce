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
 * @var $style string
 */

echo $before_widget;
if ($title) {
	echo $before_title.$title.$after_title;
}
?>
<?php if($style == 'full') : ?>
    <div class="jigoshop">
        <ul class="products list-inline">
            <?php foreach($products as $product): ?>
                <?php \Jigoshop\Helper\Render::output('shop/list/product', [
                    'product' => $product,
                ]); ?>
            <?php endforeach; ?>
        </ul>
    </div>
<?php else: ?>
    <ul class="product_list_widget">
        <?php foreach ($products as $product): /** @var $product \Jigoshop\Entity\Product */?>
        <li>
            <a href="<?= $product->getLink(); ?>">
                <?= Product::getFeaturedImage($product, Options::IMAGE_TINY); ?>
                <span class="js_widget_product_title"><?= $product->getName(); ?></span>
            </a>
            <span class="js_widget_product_price"><?= Product::getPriceHtml($product); ?></span>
        </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?= $after_widget;
