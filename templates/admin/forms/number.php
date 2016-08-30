<?php
use Jigoshop\Admin\Helper\Forms;

/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $type string Field type.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $placeholder string Field's placeholder.
 * @var $value mixed Current value.
 * @var $disabled bool Is field disabled?
 * @var $tip string Tip to show to the user.
 * @var $description string Field description.
 * @var $hidden boolean Whether the field is hidden.
 * @var $size int Size of form widget.
 * @var int $min Minimal value of input
 * @var int $max Maximal value of input
 * @var int $step Step of number
 */
$hasLabel = !empty($label);
?>
<div class="form-group <?php echo $id; ?>_field <?php echo join(' ',
    $classes); ?><?php $hidden and print ' not-active'; ?> padding-bottom-5">
    <div class="row">
        <div class="col-sm-<?php echo $size; ?>">
            <?php if ($hasLabel): $size -= 2; ?>
                <label for="<?php echo $id; ?>" class="col-xs-12 col-sm-2 margin-top-bottom-9">
                    <?php echo $label; ?>
                </label>
            <?php endif; ?>
            <div class="col-xs-12 col-sm-<?php echo $size ?> clearfix">
                <div class="tooltip-inline-badge">
                    <?php if (!empty($tip)): ?>
                        <span data-toggle="tooltip" class="badge margin-top-bottom-9" data-placement="top"
                              title="<?php echo $tip; ?>">?</span>
                    <?php endif; ?>
                </div>
                <div class="tooltip-inline-input">
                    <input type="<?php echo $type; ?>" id="<?php echo $id; ?>" name="<?php echo $name; ?>"
                           class="form-control <?php echo join(' ', $classes); ?>"
                           placeholder="<?php echo $placeholder; ?>" step="<?php echo $step; ?>"
                           value="<?php echo $value; ?>"<?php echo($min === false ? '' : ' min="' . $min . '"') ?><?php echo($max === false ? '' : ' max="' . $max . '"') ?><?php echo Forms::disabled($disabled); ?>/>
                    <?php if (!empty($description)): ?>
                        <span class="help-block"><?php echo $description; ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
