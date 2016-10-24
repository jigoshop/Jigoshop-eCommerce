<?php
use Jigoshop\Helper\Render;

/**
 * @var $id string Field ID.
 * @var $label string Field label.
 * @var $name string Field name.
 * @var $classes array List of classes to add to the field.
 * @var $placeholder string Field's placeholder.
 * @var $multiple boolean Is field supposed to accept multiple values?
 * @var $value mixed Currently selected value(s).
 * @var $tip string Tip to show to the user.
 * @var $description string Field description.
 */
$hasLabel = !empty($label);
?>
<div class="form-group <?php echo $id; ?>_field <?php echo join(' ',
    $classes); ?><?php $hidden and print ' not-active'; ?>">
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
                    <select id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="form-control <?php echo join(' ',
                        $classes); ?>" <?php $multiple and print ' multiple="multiple"'; ?>>
                        <?php foreach ($options as $option => $item): ?>
                            <?php if (isset($item['items'])): ?>
                                <optgroup label="<?php echo $option; ?>">
                                    <?php foreach ($item['items'] as $subvalue => $subitem): $subitem['disabled'] = isset($subitem['disabled']) && $subitem['disabled'] ? true : false; ?>
                                        <?php Render::output('admin/forms/select/option', array(
                                            'label' => $subitem['label'],
                                            'disabled' => $subitem['disabled'],
                                            'value' => $subvalue,
                                            'current' => $value
                                        )); ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php else: $item['disabled'] = isset($item['disabled']) && $item['disabled'] ? true : false; ?>
                                <?php Render::output('admin/forms/select/option', array(
                                    'label' => $item['label'],
                                    'disabled' => $item['disabled'],
                                    'value' => $option,
                                    'current' => $value
                                )); ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($description)): ?>
                        <span class="help-block"><?php echo $description; ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- TODO: Get rid of this and use better asset script. -->
<script type="text/javascript">
    /*<![CDATA[*/
    jQuery(function ($) {
        $("select#<?php echo $id; ?>").select2(<?php echo json_encode($args); ?>);
        $("label[for='<?php echo $id; ?>']").click(function () {
            if (!$("#<?php echo $id; ?>").select2("open")) {
                $("#<?php echo $id; ?>").select2("close");
            }
        });
    });
    /*]]>*/
</script>
