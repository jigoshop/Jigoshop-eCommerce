<?php
/**
 *
 */
?>
<div class="tooltip-inline-badge"></div>
<div class="tooltip-inline-input">
    <div class="col-xs-6">
        <input class="form-control" style="width: 100%" type="text" name="<?php printf('%s[content]', $name); ?>" value="<?= $value['content']; ?>">
    </div>
    <div class="col-xs-6">
        <input class="form-control" style="width: 100%" type="text" name="<?php printf('%s[sidebar]', $name); ?>" value="<?= $value['sidebar']; ?>">
    </div>
</div>
