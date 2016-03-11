<?php
/**
 * @var $currentTab string Currently selected tab.
 * @var $product \Jigoshop\Entity\Product Currently displayed product.
 * @var $attachments List of Attachments attached to the product.
 */
?>
<div role="tabpanel" id="tab-downloads" class="tab-pane<?php $currentTab == 'downloads' and print ' active'; ?>">
    <ul class="downloads-list" >
        <?php foreach($attachments as $attachment): ?>
            <li><a href="<?php echo $attachment['url']; ?>"><?php echo $attachment['title']; ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
