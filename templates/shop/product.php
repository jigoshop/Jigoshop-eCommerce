<?php
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $product \Jigoshop\Entity\Product The product.
 * @var $messages \Jigoshop\Core\Messages Messages container.
 */
?>
<?php do_action('jigoshop\template\product\before', $product); ?>
<article id="post-<?= $product->getId(); ?>" class="product">
	<?php Render::output('shop/messages', ['messages' => $messages]); ?>
	<div class="product-parts">
	<?php do_action('jigoshop\template\product\before_summary', $product); ?>
	
	<div class="summary col-sm-8">
		<h1><?= $product->getName(); ?></h1>
		<div class="price"><?= Product::getPriceHtml($product); ?></div>
		<p class="stock"><?= Product::getStock($product); ?></p>
		<?php Product::printAddToCartForm($product, 'product'); ?>
		<dl class="dl-horizontal js-tabs-row">
			<?php if($product->getSku()): ?>
			<dt class="js-main-row-2"><?= __('SKU', 'jigoshop'); ?></dt><dd class="js-second-row-2" ><?= $product->getSku(); ?></dd>
			<?php endif; ?>
			<?php if(count($product->getCategories()) > 0): ?>
			<dt class="js-main-row-2"><?= __('Categories', 'jigoshop'); ?></dt>
			<dd class="categories js-second-row-2">
				<?php foreach($product->getCategories() as $category): ?>
					<a href="<?= $category['link']; ?>"><?= $category['name']; ?></a>
				<?php endforeach; ?>
			</dd>
			<?php endif; ?>
			<?php if(count($product->getTags()) > 0): ?>
			<dt class="js-main-row-2"><?= __('Tagged as', 'jigoshop'); ?></dt>
			<dd class="tags js-second-row-2">
				<?php foreach($product->getTags() as $tag): ?>
					<a href="<?= $tag['link']; ?>"><?= $tag['name']; ?></a>
				<?php endforeach; ?>
			</dd>
			<?php endif; ?>
			<?php do_action('jigoshop\template\product\data', $product); ?>
		</dl>
		</div>
		<?php do_action('jigoshop\template\product\summary', $product); ?>
	</div>
	<?php do_action('jigoshop\template\product\after_summary', $product); ?>
</article>
<?php do_action('jigoshop\template\product\after', $product); ?>
