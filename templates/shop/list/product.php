<?php
use Jigoshop\Core\Options;
use Jigoshop\Helper\Product;

/**
 * @var $product \Jigoshop\Entity\Product Product to display.
 * @var bool $show_add_to_cart_form
 */
$show_add_to_cart_form = !isset($show_add_to_cart_form) || $show_add_to_cart_form;
?>
<li class="product">
    <?php do_action('jigoshop\shop\list\product\before', $product); ?>
    <div class="js-product">

        <a class="image" href="<?= $product->getLink(); ?>">
            <?php do_action('jigoshop\shop\list\product\before_thumbnail', $product); ?>
            <?php if (Product::isOnSale($product)): ?>
                <span class="on-sale"><?= apply_filters('jigoshop\shop\list\product\sale_text', __('Sale!', 'jigoshop-ecommerce'), $product) ?></span>
            <?php endif; ?>
            <div class="js-product-img">
                <span class="helper"></span>
                <?= Product::getFeaturedImage($product, Options::IMAGE_LARGE); ?>
            </div>
        </a>
    </div>
    <div class="js-product-info">
        <div class="price-option">
            <a href="<?= $product->getLink(); ?>">
                <?php do_action('jigoshop\shop\list\product\before_title', $product); ?>
                <strong><?= $product->getName(); ?></strong>
                <?php do_action('jigoshop\shop\list\product\after_title', $product); ?>
            </a>
            <?php do_action('jigoshop\shop\list\product\before_button', $product); ?>
            <span class="price"><?= Product::getPriceHtml($product); ?></span>
        </div>
        <div class="cart-option">
            <?php if ($show_add_to_cart_form) : ?>
                <?php Product::printAddToCartForm($product, 'list'); ?>
            <?php endif; ?>
            <a class="js-details-link btn" href="<?= $product->getLink(); ?>"><i
                        class="fas fa-bars"></i> <?php _("Details", "jigoshop-ecommerce"); ?></a>
            <?php do_action('jigoshop\shop\list\product\after', $product); ?>
        </div>
    </div>
</li>
