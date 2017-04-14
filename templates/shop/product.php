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
	<?php Render::output('shop/messages', array('messages' => $messages)); ?>
	<?php do_action('jigoshop\template\product\before_summary', $product); ?>
	<div class="summary">
		<h1><?= $product->getName(); ?></h1>
		<p class="price"><?= Product::getPriceHtml($product); ?></p>
		<p class="stock"><?= Product::getStock($product); ?></p>
		<?php Product::printAddToCartForm($product, 'product'); ?>
		<dl class="dl-horizontal">
			<?php if($product->getSku()): ?>
			<dt><?= __('SKU', 'jigoshop'); ?></dt><dd><?= $product->getSku(); ?></dd>
			<?php endif; ?>
			<?php if(count($product->getCategories()) > 0): ?>
			<dt><?= __('Categories', 'jigoshop'); ?></dt>
			<dd class="categories">
				<?php foreach($product->getCategories() as $category): ?>
					<a href="<?= $category['link']; ?>"><?= $category['name']; ?></a>
				<?php endforeach; ?>
			</dd>
			<?php endif; ?>
			<?php if(count($product->getTags()) > 0): ?>
			<dt><?= __('Tagged as', 'jigoshop'); ?></dt>
			<dd class="tags">
				<?php foreach($product->getTags() as $tag): ?>
					<a href="<?= $tag['link']; ?>"><?= $tag['name']; ?></a>
				<?php endforeach; ?>
			</dd>
			<?php endif; ?>
			<?php do_action('jigoshop\template\product\data', $product); ?>
		</dl>
		<?php do_action('jigoshop\template\product\summary', $product); ?>
	</div>
	<?php do_action('jigoshop\template\product\after_summary', $product); ?>
</article>
<?php do_action('jigoshop\template\product\after', $product); ?>
