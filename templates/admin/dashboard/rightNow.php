<?php
use Jigoshop\Admin\Product\Attributes;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Status;

/**
 * @var $productCount int Number of products.
 * @var $categoryCount int Number of product categories.
 * @var $tagCount int Number of tags for products.
 * @var $attributesCount int Number of product attributes.
 * @var $newCount int Number of new orders.
 * @var $pendingCount int Number of pending orders.
 * @var $onHoldCount int Number of orders on hold.
 * @var $processingCount int Number of processing orders.
 * @var $completedCount int Number of completed orders.
 * @var $cancelledCount int Number of cancelled orders.
 * @var $refundedCount int Number of refunded orders.
 */
?>
<div id="jigoshop_right_now" class="jigoshop_right_now">
	<div class="table table_content">
		<p class="sub"><?= __('<span>Shop</span> Content', 'jigoshop'); ?></p>
		<table>
			<tbody>
			<tr class="first">
				<td class="first b"><a href="edit.php?post_type=<?= Types::PRODUCT; ?>"><?= $productCount; ?></a></td>
				<td class="t"><a href="edit.php?post_type=<?= Types::PRODUCT; ?>"><?php _e('Products', 'jigoshop'); ?></a></td>
			</tr>
			<tr>
				<td class="first b"><a href="edit-tags.php?taxonomy=<?= Types::PRODUCT_CATEGORY; ?>&amp;post_type=<?= Types::PRODUCT; ?>"><?= $categoryCount; ?></a></td>
				<td class="t"><a href="edit-tags.php?taxonomy=<?= Types::PRODUCT_CATEGORY; ?>&amp;post_type=<?= Types::PRODUCT; ?>"><?php _e('Product Categories', 'jigoshop'); ?></a></td>
			</tr>
			<tr>
				<td class="first b"><a href="edit-tags.php?taxonomy=<?= Types::PRODUCT_TAG; ?>&amp;post_type=<?= Types::PRODUCT; ?>"><?= $tagCount; ?></a></td>
				<td class="t"><a href="edit-tags.php?taxonomy=<?= Types::PRODUCT_TAG; ?>&amp;post_type=<?= Types::PRODUCT; ?>"><?php _e('Product Tag', 'jigoshop'); ?></a></td>
			</tr>
			<tr>
				<td class="first b"><a href="edit.php?post_type=<?= Types::PRODUCT; ?>&page=<?= Attributes::NAME; ?>"><?= $attributesCount; ?></a></td>
				<td class="t"><a href="edit.php?post_type=<?= Types::PRODUCT; ?>&page=<?= Attributes::NAME; ?>"><?php _e('Product attributes', 'jigoshop'); ?></a></td>
			</tr>
			</tbody>
		</table>
	</div>
	<div class="table table_discussion">
		<p class="sub"></p>
		<table>
			<tbody>

			<tr class="first pending-orders">
				<td class="b"><a href="edit.php?post_type=<?= Types::ORDER; ?>&amp;post_status=<?= Status::PENDING; ?>"><span class="total-count"><?= $pendingCount; ?></span></a></td>
				<td class="last t"><a class="pending" href="edit.php?post_type=<?= Types::ORDER; ?>&amp;post_status=<?= Status::PENDING; ?>"><?php _e('Pending', 'jigoshop'); ?></a></td>
			</tr>
			<tr class="on-hold-orders">
				<td class="b"><a href="edit.php?post_type=<?= Types::ORDER; ?>&amp;post_status=<?= Status::ON_HOLD; ?>"><span class="total-count"><?= $onHoldCount; ?></span></a></td>
				<td class="last t"><a class="onhold" href="edit.php?post_type=<?= Types::ORDER; ?>&amp;post_status=<?= Status::ON_HOLD; ?>"><?php _e('On-Hold', 'jigoshop'); ?></a></td>
			</tr>
			<tr class="processing-orders">
				<td class="b"><a href="edit.php?post_type=<?= Types::ORDER; ?>&amp;post_status=<?= Status::PROCESSING; ?>"><span class="total-count"><?= $processingCount; ?></span></a></td>
				<td class="last t"><a class="processing" href="edit.php?post_type=<?= Types::ORDER; ?>&amp;post_status=<?= Status::PROCESSING; ?>"><?php _e('Processing', 'jigoshop'); ?></a></td>
			</tr>
			<tr class="completed-orders">
				<td class="b"><a href="edit.php?post_type=<?= Types::ORDER; ?>&amp;post_status=<?= Status::COMPLETED; ?>"><span class="total-count"><?= $completedCount; ?></span></a></td>
				<td class="last t"><a class="complete" href="edit.php?post_type=<?= Types::ORDER; ?>&amp;post_status=<?= Status::COMPLETED; ?>"><?php _e('Completed', 'jigoshop'); ?></a></td>
			</tr>
			</tbody>
		</table>
	</div>
	<br class="clear" />
</div>
