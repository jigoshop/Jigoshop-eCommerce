<?php
/**
 *
 */
$availableStates = [];
foreach (\Jigoshop\Helper\Country::getAll() as $countryCode => $countryName) {
    if(\Jigoshop\Helper\Country::hasStates($countryCode)) {
        $availableStates[$countryName] = [];
        foreach (\Jigoshop\Helper\Country::getStates($countryCode) as $stateCode => $stateName) {
            $availableStates[$countryName][$countryCode . ':' . $stateCode] = $stateName;
        }
    }
}
?>
<li class="list-group-item">
    <h4 class="list-group-item-heading clearfix">
        <span class="handle ui-sortable-handle"></span>
        <span class="title"><?php printf('%s - %s', $value['label'], $value['cost']); ?></span>
        <button type="button" class="remove-rate btn btn-default pull-right"
                title="<?php _e('Remove', 'jigoshop-ecommerce'); ?>">
            <span class="glyphicon glyphicon-remove"></span>
        </button>
        <button type="button" class="toggle-rate btn btn-default pull-right"
                title="<?php _e('Expand', 'jigoshop-ecommerce'); ?>">
            <span class="glyphicon glyphicon-collapse-down"></span>
        </button>
    </h4>
    <div class="list-group-item-text row clearfix" style="display: none">
        <div class="col-sm-6">
            <?php \Jigoshop\Helper\Forms::text([
                'name' => sprintf('%s[%s][label]', $name, $id),
                'label' => __('Label', 'jigoshop-ecommerce'),
                'value' => $value['label'],
                'classes' => ['input-label']
            ]); ?>
            <?php \Jigoshop\Helper\Forms::number([
                'name' => sprintf('%s[%s][cost]', $name, $id),
                'label' => __('Cost', 'jigoshop-ecommerce'),
                'value' => $value['cost'],
                'classes' => ['input-cost']
            ],"currency"); ?>
        </div>
        <div class="col-sm-6">
            <?php \Jigoshop\Helper\Forms::select([
                'name' => sprintf('%s[%s][continents]', $name, $id),
                'label' => __('Continents', 'jigoshop-ecommerce'),
                'value' => $value['continents'],
                'multiple' => true,
                'options' => \Jigoshop\Helper\Country::getContinents(),
                'classes' => ['continents']
            ]); ?>
            <?php \Jigoshop\Helper\Forms::select([
                'name' => sprintf('%s[%s][countries]', $name, $id),
                'label' => __('Countries', 'jigoshop-ecommerce'),
                'value' => $value['countries'],
                'multiple' => true,
                'options' => \Jigoshop\Helper\Country::getAll(),
                'classes' => ['countries']
            ]); ?>
            <?php \Jigoshop\Helper\Forms::select([
                'name' => sprintf('%s[%s][states]', $name, $id),
                'label' => __('States', 'jigoshop-ecommerce'),
                'value' => $value['states'],
                'multiple' => true,
                'options' => $availableStates,
                'classes' => ['states'],
                'placeholder' => __('All states', 'jigoshop-ecommerce'),
            ]); ?>
            <?php \Jigoshop\Helper\Forms::text([
                'name' => sprintf('%s[%s][postcode]', $name, $id),
                'label' => __('Postcode', 'jigoshop-ecommerce'),
                'value' => $value['postcode'],
                'placeholder' => __('All postcodes', 'jigoshop-ecommerce'),
                'description' => __('123* means all postcodes which begin with 123', 'jigoshop-ecommerce'),
                'classes' => ['postcode']

            ]); ?>
            <?php \Jigoshop\Helper\Forms::checkbox([
                'name' => sprintf('%s[%s][rest_of_the_world]', $name, $id),
                'label' => __('Rest of the world', 'jigoshop-ecommerce'),
                'checked' => $value['rest_of_the_world'],
                'description' => __('', 'jigoshop-ecommerce'),
                'classes' => ['rest-of-the-world']
            ]); ?>
            <?php \Jigoshop\Helper\Forms::hidden([
                'name' => sprintf('%s[rates_order][]', \Jigoshop\Shipping\AdvancedFlatRate::ID),
                'value' => $id
            ]); ?>
        </div>
    </div>
</li>
