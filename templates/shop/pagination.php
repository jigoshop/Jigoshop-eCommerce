<?php
/**
 * @var $product_count int Number of all available products
 */
?>
<?php if ($product_count > 1): ?>
	<div class="navigation">
		<?php if (function_exists('wp_pagenavi')) : ?>
			<?php wp_pagenavi(); ?>
		<?php else: ?>
            <?php the_posts_pagination( [
                'prev_text' =>  __('<span class="meta-nav">&larr;</span> Previous', 'jigoshop-ecommerce'),
                'next_text' => __('Next <span class="meta-nav">&rarr;</span>', 'jigoshop-ecommerce'),
                'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'jigoshop-ecommerce' ) . ' </span>',
            ]); ?>
		<?php endif; ?>
	</div>
<?php endif; ?>
