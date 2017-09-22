<?php
use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Helper\Render;

/**
 * @var $product \Jigoshop\Entity\Product The product.
 * @var $availableAttributes array List of available attributes.
 * @var $attributes array List of attributes attached to current product.
 */
?>
<div class="form-inline">
    <?php Forms::select([
        'placeholder' => __('Select attribute...', 'jigoshop-ecommerce'),
        'name' => 'new_attribute',
        'id' => 'new-attribute',
        'options' => $availableAttributes,
        'value' => false,
    ]); ?>
    <button type="button" class="btn btn-default pull-right" id="add-attribute"><span
            class="glyphicon glyphicon-plus"></span> <?php _e('Add', 'jigoshop-ecommerce'); ?></button>
    <?php Forms::text([
        'placeholder' => __('Enter attribute name...', 'jigoshop-ecommerce'),
        'name' => 'new_attribute_label',
        'id' => 'new-attribute-label',
        'value' => '',
        'classes' => ['not-active'],
    ]); ?>
    <div class="clear"></div>
</div>
<div class="form-inline">
    <button type="button" class="btn btn-default" id="add-inherited-attributes">
        <span class="glyphicon glyphicon-arrow-down"></span>
        <?php echo __('Add inherited attributes', 'jigoshop-ecommerce'); ?>
    </button>
</div>
<ul id="product-attributes" class="list-group clearfix">
    <?php foreach ($attributes as $attribute): /** @var $attribute \Jigoshop\Entity\Product\Attribute */ ?>
        <?php Render::output('admin/product/box/attributes/attribute', ['attribute' => $attribute]); ?>
    <?php endforeach; ?>
</ul>
<?php do_action('jigoshop\product\tabs\attributes', $product); ?>
