<?php
/**
 * @var string $name
 * @var array $values
 */
?>
<div id="advanced-flat-rate" class="tooltip-inline-input">
    <div class="col-xs-12">
        <ul class="list-group clearfix">
            <?php for ($i = 0; $i < count($values); $i++) : ?>
                <?php \Jigoshop\Helper\Render::output('admin/settings/shipping/advanced_flat_rate/rate', [
                    'id' => $i,
                    'name' => $name,
                    'value' => $values[$i]
                ]); ?>
            <?php endfor; ?>
        </ul>
        <a href="#" class="add-rate btn btn-primary"><?php _e('Add Rate', 'jigoshop'); ?></a>
    </div>
</div>
<script type="text/template" id="tmpl-advanced-flat-rate">
    <?php ob_start(); ?>
    <?php \Jigoshop\Helper\Render::output('admin/settings/shipping/advanced_flat_rate/rate', [
        'id' => '{{{ data.id }}}',
        'name' => $name,
        'value' => [
            'label' => __('New rate', 'jigoshop'),
            'cost' => '0',
            'continents' => [],
            'countries' => [],
            'states' => [],
            'postcode' => '',
            'rest-of-the-world' => false,
        ]
    ]); ?>
    <?= preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", ob_get_clean()); ?>
</script>
