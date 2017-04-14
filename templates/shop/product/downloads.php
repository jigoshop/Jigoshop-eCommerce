<?php
/**
 * @var $currentTab string Currently selected tab.
 * @var $product \Jigoshop\Entity\Product Currently displayed product.
 * @var $attachments Jigoshop\Entity\Product\Attachment\Datafile[] List of Attachments attached to the product.
 */
?>
<div role="tabpanel" id="tab-downloads" class="tab-pane<?php $currentTab == 'downloads' and print ' active'; ?>">
    <ul class="downloads-list" >
        <?php foreach($attachments as $attachment): ?>
            <li><a href="<?= $attachment->getUrl() ?>"><?= $attachment->getTitle(); ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
