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
 * @var $data array Key-value pairs for data attributes.
 */
$hasLabel = !empty($label);
?>
<div class="form-group <?= $id; ?>_field <?= join(' ',
    $classes); ?><?php $hidden and print ' not-active'; ?>">
    <div class="row">
        <div class="col-sm-<?= $size; ?>">
            <?php if ($hasLabel): $size -= 2; ?>
                <label for="<?= $id; ?>" class="col-xs-12 col-sm-2 margin-top-bottom-9">
                    <?= $label; ?>
                </label>
            <?php endif; ?>
            <div class="col-xs-12 col-sm-<?= $size ?> clearfix">
                <div class="tooltip-inline-badge">
                    <?php if (!empty($tip)): ?>
                        <span data-toggle="tooltip" class="badge margin-top-bottom-9" data-placement="top"
                              title="<?= $tip; ?>">?</span>
                    <?php endif; ?>
                </div>
                <div class="tooltip-inline-input">
                    <select id="<?= $id; ?>" name="<?= $name; ?>" class="form-control <?= join(' ',
                        $classes); ?>" <?php $multiple and print ' multiple="multiple"'; ?>
                    <?php 
                    if(isset($data) && is_array($data)) {
                        foreach($data as $dataKey => $dataValue) {
                            echo sprintf(' data-%s="%s"', $dataKey, $dataValue);
                        }
                    }
                    ?>
                    >
                        <?php foreach ($options as $option => $item): ?>
                            <?php if (isset($item['items'])): ?>
                                <optgroup label="<?= isset($item['label']) ? $item['label'] : $option; ?>">
                                    <?php foreach ($item['items'] as $subvalue => $subitem): $subitem['disabled'] = isset($subitem['disabled']) && $subitem['disabled'] ? true : false; ?>
                                        <?php Render::output('admin/forms/select/option', [
                                            'label' => $subitem['label'],
                                            'disabled' => $subitem['disabled'],
                                            'value' => $subvalue,
                                            'current' => $value
                                        ]); ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php else: $item['disabled'] = isset($item['disabled']) && $item['disabled'] ? true : false; ?>
                                <?php Render::output('admin/forms/select/option', [
                                    'label' => $item['label'],
                                    'disabled' => $item['disabled'],
                                    'value' => $option,
                                    'current' => $value
                                ]); ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($description)): ?>
                        <span class="help-block"><?= $description; ?></span>
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
        $("select#<?= $id; ?>").select2(<?= json_encode($args); ?>);
        $("label[for='<?= $id; ?>']").click(function () {
            if (!$("#<?= $id; ?>").select2("open")) {
                $("#<?= $id; ?>").select2("close");
            }
        });
    });
    /*]]>*/
</script>
