<?php
use Jigoshop\Helper\Render;

/**
 * @var $content string Page contents.
 * @var $products array List of products to display.
 * @var $product_count int Number of all available products.
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $title string Title for the page.
 */
?>

<?php if(is_search()): ?>
	<h1 class="page-title"><?php _e('Search Results:', 'jigoshop-ecommerce'); ?> &ldquo;<?php the_search_query(); ?>&rdquo; <?php if (get_query_var('paged')) echo ' &mdash; Page '.get_query_var('paged'); ?></h1>
<?php else: ?>
	<?= apply_filters('jigoshop\shop\content\title', '<h1 class="page-title">'.$title.'</h1>', $title); ?>
<?php endif; ?>

<?php Render::output('shop/messages', ['messages' => $messages]); ?>

<?php if ($content): ?>
	<?= apply_filters('the_content', $content); ?>
<?php endif; ?>

<?php
Render::output('shop/list', [
	'products' => $products,
	'product_count' => $product_count,
]);
?>
