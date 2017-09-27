<?php
use Jigoshop\Helper\Forms;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

/**
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $content string Content to display.
 * @var $order \Jigoshop\Entity\Order The order.
 */
?>

<h1><?php printf(__('Checkout &raquo; Payment &raquo; %s', 'jigoshop-ecommerce'), $order->getTitle()); ?></h1>
<?php Render::output('shop/messages', ['messages' => $messages]); ?>
<div class="payment">
	<?= $content; ?>
</div>
