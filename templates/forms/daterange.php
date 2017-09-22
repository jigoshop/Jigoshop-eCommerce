<?php
/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $placeholder string Field's placeholder.
 * @var $value mixed Current value.
 * @var $tip string Tip to show to the user.
 * @var $size int Size of form widget.
 * @var string $startDate Date of sale (start).
 * @var string $endDate Date of sale (end).
 * @var $data array Key-value pairs for data attributes.
 */
$hasLabel = !empty($label);
?>
<div class="form-group <?= $id; ?>_field <?= join(' ',
    $classes); ?> padding-bottom-5">
    <div class="row">
        <div class="col-sm-<?= $size; ?>">
            <?php if ($hasLabel): $size -= 2; ?>
                <label for="<?= $id; ?>" class="col-xs-12 col-sm-2 margin-top-bottom-9">
                    <?= $label; ?>
                </label>
            <?php endif; ?>
            <div class="col-xs-2 col-sm-1 text-right">
                <?php if (!empty($tip)): ?>
                    <span data-toggle="tooltip" class="badge margin-top-bottom-9" data-placement="top"
                          title="<?= $tip; ?>">?</span>
                <?php endif; ?>
            </div>
            <div class="col-xs-<?= $size - 2 ?> col-sm-<?= $size - 1 ?> <?= $id; ?>-form input-daterange input-group"
                id="<?= $id; ?>">
                <input type="<?= $type; ?>" id="<?= $id; ?>-from" name="<?= $name['from']; ?>"
                       class="form-control <?= join(' ', $classes); ?>" placeholder="<?= $placeholder; ?>"
                       value="<?= $value['from']; ?>"
                <?php 
                if(isset($data['from']) && is_array($data['from'])) {
                    foreach($data['from'] as $dataKey => $dataValue) {
                        echo sprintf(' data-%s="%s"', $dataKey, $dataValue);
                    }
                }
                ?>
                />
                <span class="input-group-addon"><?php _e('to', 'jigoshop-ecommerce'); ?></span>
                <input type="<?= $type; ?>" id="<?= $id; ?>-to" name="<?= $name['to']; ?>"
                       class="form-control <?= join(' ', $classes); ?>" placeholder="<?= $placeholder; ?>"
                       value="<?= $value['to']; ?>"
                <?php 
                if(isset($data['to']) && is_array($data['to'])) {
                    foreach($data['to'] as $dataKey => $dataValue) {
                        echo sprintf(' data-%s="%s"', $dataKey, $dataValue);
                    }
                }
                ?>
                />
            </div>
        </div>
    </div>
</div>
<script>
    <?php //TODO move this to separated file. ?>
    jQuery('.<?= $id; ?>-form').datepicker({
        autoclose: true,
        todayHighlight: true,
        container: '#<?= $id; ?>',
        orientation: 'top left',
        todayBtn: 'linked',
        startDate: <?= $startDate ? "'" . $startDate . "'" : 'false'; ?>,
        endDate: <?= $endDate ? "'" . $endDate . "'" : 'false'; ?>,
    });
</script>