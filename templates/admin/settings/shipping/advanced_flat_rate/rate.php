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
            <?php \Jigoshop\Helper\Forms::text([
                'name' => sprintf('%s[%s][label]', $name, $id),
                'label' => __('Label', 'jigoshop'),
                'value' => $value['label'],
                'classes' => ['input-label']
            ]); ?>
            <?php \Jigoshop\Helper\Forms::text([
                'name' => sprintf('%s[%s][cost]', $name, $id),
                'label' => __('Cost', 'jigoshop'),
                'value' => $value['cost'],
                'classes' => ['input-cost']
            ]); ?>
        </div>
        <div class="col-sm-6">
            <?php \Jigoshop\Helper\Forms::select([
                'name' => sprintf('%s[%s][continents]', $name, $id),
                'label' => __('Continents', 'jigoshop'),
                'value' => $value['continents'],
                'multiple' => true,
                'options' => \Jigoshop\Helper\Country::getContinents(),
                'classes' => ['continents']
            ]); ?>
            <?php \Jigoshop\Helper\Forms::select([
                'name' => sprintf('%s[%s][countries]', $name, $id),
                'label' => __('Countries', 'jigoshop'),
                'value' => $value['countries'],
                'multiple' => true,
                'options' => \Jigoshop\Helper\Country::getAll(),
                'classes' => ['countries']
            ]); ?>
            <?php \Jigoshop\Helper\Forms::select([
                'name' => sprintf('%s[%s][states]', $name, $id),
                'label' => __('States', 'jigoshop'),
                'value' => $value['states'],
                'multiple' => true,
                'options' => $availableStates,
                'classes' => ['states'],
                'placeholder' => __('All states', 'jigoshop'),
            ]); ?>
            <?php \Jigoshop\Helper\Forms::text([
                'name' => sprintf('%s[%s][postcode]', $name, $id),
                'label' => __('Postcode', 'jigoshop'),
                'value' => $value['postcode'],
                'placeholder' => __('All postcodes', 'jigoshop'),
                'description' => __('123* means all postcodes which begin with 123', 'jigoshop'),
                'classes' => ['postcode']

            ]); ?>
            <?php \Jigoshop\Helper\Forms::checkbox([
                'name' => sprintf('%s[%s][rest_of_the_world]', $name, $id),
                'label' => __('Rest of the world', 'jigoshop'),
                'checked' => $value['rest_of_the_world'],
                'description' => __('asasdsa', 'jigoshop'),
                'classes' => ['rest-of-the-world']
            ]); ?>
        </div>
    </div>
</li>
