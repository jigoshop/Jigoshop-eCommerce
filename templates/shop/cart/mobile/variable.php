<?php
use Jigoshop\Helper\Order;
use Jigoshop\Helper\Product;

/**
 * @var $cart \Jigoshop\Entity\Cart Cart object.
 * @var $key string Cart item key.
 * @var $item \Jigoshop\Entity\Order\Item Cart item to display.
 * @var $showWithTax bool Whether to show product price with or without tax.
 */

/** @var \Jigoshop\Entity\Product\Variable $product */
$product = $item->getProduct();
$variation = $product->getVariation($item->getMeta('variation_id')->getValue());
$url = apply_filters('jigoshop\cart\product_url', get_permalink($product->getId()), $key);
$price = $showWithTax ? $item->getPrice() + $item->getTax() / $item->getQuantity() : $item->getPrice();
?>
<li class="list-group-item" data-id="<?php echo $key; ?>" data-product="<?php echo $product->getId(); ?>">
	<div class="list-group-item-heading clearfix">
		<div class="buttons pull-right">
			<div class="product-remove pull-right">
				<a class="remove" href="<?php echo \Jigoshop\Helper\Order::getRemoveLink($key); ?>">
					<button type="button" class="remove-product btn btn-default pull-right" title="<?php _e('Remove', 'jigoshop'); ?>">
						<span class="glyphicon glyphicon-remove"></span>
					</button>
				</a>
			</div>

			<button type="button" class="show-product btn btn-default pull-right" title="<?php _e('Expand', 'jigoshop'); ?>">
				<span class="glyphicon glyphicon-collapse-down"></span>
			</button>
		</div>
		<div class="form-group">
			<div class="pull-left">
				<?php echo apply_filters('jigoshop\template\shop\cart\product_title',
					$product->getName(), $product, $item); ?>
				<?php echo Product::getVariation($variation, $item); ?>
				<?php do_action('jigoshop\template\shop\cart\after_product_title', $product, $item); ?>
			</div>
			<div class="pull-right">
				<span class="product-quantity"><?php echo $item->getQuantity(); ?></span>
				&times;
				<span class="product-price">
                            <?php echo apply_filters('jigoshop\template\shop\cart\product_price', \Jigoshop\Helper\Product::formatPrice($price), $price, $product, $item); ?>
                        </span>
			</div>
		</div>
	</div>
	<div class="list-group-item-text" style="display: none">
		<div class="">
			<?php echo \Jigoshop\Helper\Product::getFeaturedImage($product, 'shop_tiny'); ?>
		</div>
		<div class="">
			<fieldset>
				<div class="form-group">
					<label class="margin-top-bottom-9">
						<?php _e('Unit Price', 'jigoshop'); ?>
					</label>
					<div class="clearfix product-price">
						<?php echo apply_filters('jigoshop\template\shop\cart\product_price', \Jigoshop\Helper\Product::formatPrice($price), $price, $product, $item); ?>
					</div>
				</div>
				<div class="form-group product_quantity_field padding-bottom-5">
					<div class="row">
						<label for="product_quantity" class="margin-top-bottom-9">
							<?php _e('Quantity', 'jigoshop'); ?>
						</label>
						<div class="clearfix">
							<div class="tooltip-inline-badge">
							</div>
							<div class="tooltip-inline-input product-quantity">
								<input id="product-quantity" type="number" name="cart[<?php echo $key; ?>]"
									   class="form-control" value="<?php echo $item->getQuantity(); ?>">
							</div>
						</div>
					</div>
				</div>
				<div class="form-group product_regular_price_field ">
					<div class="row">
						<label for="product_regular_price" class="margin-top-bottom-9">
							<?php _e('Price', 'jigoshop'); ?>
						</label>
						<div class="clearfix product-subtotal">
							<?php echo apply_filters('jigoshop\template\shop\cart\product_subtotal', \Jigoshop\Helper\Product::formatPrice($item->getQuantity() * $price), $item->getQuantity() * $price, $product, $item); ?>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
</li>
