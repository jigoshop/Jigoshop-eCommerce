<?php
use Jigoshop\Admin\Helper\Forms;

/**
 * @var $value mixed Option value.
 * @var $label string Option label.
 * @var $disabled boolean Whether item is disabled.
 * @var $current mixed Currently selected value(s).
 */
?>
<option value="<?= $value; ?>" <?= Forms::selected($value, $current); ?> <?= Forms::disabled($disabled); ?>><?= $label; ?></option>
