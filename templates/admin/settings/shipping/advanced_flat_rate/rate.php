<?php
/**
 *
 */
?>
<li class="list-group-item">
    <h4 class="list-group-item-heading clearfix">
        <span class="title"><?php printf('%s - %s', $value['label'], $value['cost']); ?></span>
        <button type="button" class="remove-rate btn btn-default pull-right"
                title="<?php _e('Remove', 'jigoshop'); ?>">
            <span class="glyphicon glyphicon-remove"></span>
        </button>
        <button type="button" class="toggle-rate btn btn-default pull-right"
                title="<?php _e('Expand', 'jigoshop'); ?>">
            <span class="glyphicon glyphicon-collapse-down"></span>
        </button>
    </h4>
    <div class="list-group-item-text row clearfix" style="display: none">
        <div class="col-sm-6">
            <?php \Jigoshop\Helper\Forms::text(array(
                'name' => sprintf('%s[%s][label]', $name, $id),
                'label' => __('Label', 'jigoshop'),
                'value' => $value['label'],
                'classes' => array('input-label')
            )); ?>
            <?php \Jigoshop\Helper\Forms::text(array(
                'name' => sprintf('%s[%s][cost]', $name, $id),
                'label' => __('Cost', 'jigoshop'),
                'value' => $value['cost'],
                'classes' => array('input-cost')
            )); ?>
        </div>
        <div class="col-sm-6">
            <?php \Jigoshop\Helper\Forms::select(array(
                'name' => sprintf('%s[%s][continents]', $name, $id),
                'label' => __('Continents', 'jigoshop'),
                'value' => $value['continents'],
                'multiple' => true,
                'options' => array_merge(
                    array('' => __('None', 'jigoshop')),
                    \Jigoshop\Shipping\AdvancedFlatRate::getContinets()
                ),
                'classes' => array('continents-select')
            )); ?>
            <?php \Jigoshop\Helper\Forms::select(array(
                'name' => sprintf('%s[%s][countries]', $name, $id),
                'label' => __('Countries', 'jigoshop'),
                'value' => $value['countries'],
                'multiple' => true,
                'options' => array_merge(
                    array('' => __('None', 'jigoshop')),
                    \Jigoshop\Helper\Country::getAll()
                ),
                'classes' => array('country-select')
            )); ?>
            <?php \Jigoshop\Helper\Forms::select(array(
                'name' => sprintf('%s[%s][states]', $name, $id),
                'label' => __('States', 'jigoshop'),
                'value' => $value['states'],
                'multiple' => true,
                'options' => \Jigoshop\Helper\Country::getStates($value['country']),
                'classes' => array('states-select'),
                'placeholder' => __('All states', 'jigoshop'),
            )); ?>
            <?php \Jigoshop\Helper\Forms::text(array(
                'name' => sprintf('%s[%s][postcode]', $name, $id),
                'label' => __('Postcode', ''),
                'value' => $value['postcode'],
                'placeholder' => __('All postcodes', 'jigoshop'),
                'description' => __('123* means all postcodes which begin with 123', 'jigoshop')
            )); ?>
        </div>
    </div>
</li>
