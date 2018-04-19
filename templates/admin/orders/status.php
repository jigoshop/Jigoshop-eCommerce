<?php
/**
 * @var string $currentStatusText Ready button to show
 * @var bool $pendingTo Is it possible to change the status to pending
 * @var bool $processingTo Is it possible to change the status to processing
 * @var int $orderId Order id
 * @var array $statuses List of availble status to change to
 * @var bool $hideCancel Hide cancelled button when status is completed or cancelled
 */
?>
<div class="status">
    <?= $currentStatusText; ?>
    <?php if (!empty($pendingTo)): ?>
        <span class="btn-status glyphicon glyphicon-arrow-right" aria-hidden="true"
              data-order_id="<?= $orderId; ?>"
              title="<?php _e('Change to &quot;Processing&quot;', 'jigoshop-ecommerce'); ?>"
              data-status_to="<?= $statuses['processing']; ?>"></span>
    <?php endif; ?>
    <?php if (!empty($processingTo)): ?>
        <span class="btn-status glyphicon glyphicon-ok" aria-hidden="true"
              data-order_id="<?= $orderId; ?>"
              title="<?php _e('Change to &quot;Completed&quot;', 'jigoshop-ecommerce'); ?>"
              data-status_to="<?= $statuses['completed']; ?>"></span>
    <?php endif; ?>
    <?php if (!$hideCancel): ?>
        <span class="btn-status glyphicon glyphicon-remove" aria-hidden="true"
              data-order_id="<?= $orderId; ?>"
              title="<?php _e('Change to &quot;Cancelled&quot;', 'jigoshop-ecommerce'); ?>"
              data-status_to="<?= $statuses['cancelled']; ?>"></span>
    <?php endif; ?>
</div>
