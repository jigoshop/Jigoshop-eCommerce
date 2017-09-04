<?php
use Jigoshop\Helper\Forms;
?>

<tr data-attribute-id="<?php echo $attribute->getId(); ?>" <?php echo ($inherited?'data-attribute-inherited="1"':''); ?> <?php echo (is_object($inheritedFrom)?'data-attribute-inherited-from="' . $inheritedFrom->getId() . '"':'') ?>>
	<td><?php echo $attribute->getLabel(); ?></td>
	<td><?php echo $attribute->getSlug(); ?></td>
	<td><?php echo $attribute::getTypes()[$attribute->getType()]; ?></td>
	<td><?php echo (is_object($inheritedFrom)?$inheritedFrom->getName():'-'); ?></td>
	<td>
		<?php 
		Forms::checkbox([
			'name' => sprintf('attributesEnabled[%s]', $attribute->getId()),
			'classes' => ['switch-medium'],
			'checked' => $attributeEnabled
		]);
		?>
	</td>
	<td>
		<?php
		Forms::hidden([
			'name' => sprintf('attributes[%s]', $attribute->getId()),
			'value' => (is_object($inheritedFrom)?1:0)
		]);
		?>
		<button type="submit" class="btn btn-default attributeRemoveButton">
			<span class="glyphicon glyphicon-remove"></span>
		</button>
	</td>
</tr>