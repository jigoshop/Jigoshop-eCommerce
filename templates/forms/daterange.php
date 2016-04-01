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
 */
$hasLabel = !empty($label);
?>
<div class="form-group <?php echo $id; ?>_field <?php echo join(' ',
    $classes); ?> padding-bottom-5">
    <div class="row">
        <div class="col-sm-<?php echo $size; ?>">
            <?php if ($hasLabel): $size -= 2; ?>
                <label for="<?php echo $id; ?>" class="col-xs-12 col-sm-2 margin-top-bottom-9">
                    <?php echo $label; ?>
                </label>
            <?php endif; ?>
            <div class="col-xs-2 col-sm-1 text-right">
                <?php if (!empty($tip)): ?>
                    <span data-toggle="tooltip" class="badge margin-top-bottom-9" data-placement="top"
                          title="<?php echo $tip; ?>">?</span>
                <?php endif; ?>
            </div>
            <div class="col-xs-<?php echo $size - 2 ?> col-sm-<?php echo $size - 1 ?> <?php echo $id; ?>-form input-daterange input-group"
                id="<?php echo $id; ?>">
                <input type="<?php echo $type; ?>" id="<?php echo $id; ?>-from" name="<?php echo $name['from']; ?>"
                       class="form-control <?php echo join(' ', $classes); ?>" placeholder="<?php echo $placeholder; ?>"
                       value="<?php echo $value['from']; ?>"/>
                <span class="input-group-addon"><?php _e('to', 'jigoshop'); ?></span>
                <input type="<?php echo $type; ?>" id="<?php echo $id; ?>-to" name="<?php echo $name['to']; ?>"
                       class="form-control <?php echo join(' ', $classes); ?>" placeholder="<?php echo $placeholder; ?>"
                       value="<?php echo $value['to']; ?>"/>
            </div>
        </div>
    </div>
</div>
<script>
    <?php //TODO move this to separated file. ?>
    jQuery('.<?php echo $id; ?>-form').datepicker({
        autoclose: true,
        todayHighlight: true,
        container: '#<?php echo $id; ?>',
        orientation: 'top left',
        todayBtn: 'linked',
        startDate: <?php echo $startDate ? "'" . $startDate . "'" : 'false'; ?>,
        endDate: <?php echo $endDate ? "'" . $endDate . "'" : 'false'; ?>,
    });
</script>