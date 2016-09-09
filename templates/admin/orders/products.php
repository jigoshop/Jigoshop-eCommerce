<?php
/**
 * @var array $products list of products
 */
?>

<?php if (count($products) > 0): ?>
	<div>
		<?php foreach ($products as $product): ?>
			<a href="<?php echo get_permalink($product->product_id); ?>"><?php echo $product->title; ?></a>,
		<?php endforeach; ?>
	</div>
<?php endif; ?>
