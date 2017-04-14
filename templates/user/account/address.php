<?php
use Jigoshop\Helper\Country;

/**
 * @var $address \Jigoshop\Entity\Customer\Address
 */
?>
<dl class="dl-horizontal clearfix address">
	<dt><?= __('Name', 'jigoshop'); ?></dt>
	<dd><?= $address->getName(); ?>&nbsp;</dd>
	<dt><?= __('Address', 'jigoshop'); ?></dt>
	<dd><?= $address->getAddress(); ?>&nbsp;</dd>
	<dt><?= __('City', 'jigoshop'); ?></dt>
	<dd><?= $address->getCity(); ?>&nbsp;</dd>
	<dt><?= __('Postcode', 'jigoshop'); ?></dt>
	<dd><?= $address->getPostcode(); ?>&nbsp;</dd>
	<dt><?= __('State/province', 'jigoshop'); ?></dt>
	<dd><?= Country::getStateName($address->getCountry(), $address->getState()); ?>&nbsp;</dd>
	<dt><?= __('Country', 'jigoshop'); ?></dt>
	<dd><?= Country::getName($address->getCountry()); ?>&nbsp;</dd>
	<?php if ($address->getPhone()): ?>
		<dt><?= __('Phone', 'jigoshop'); ?></dt>
		<dd><?= $address->getPhone(); ?>&nbsp;</dd>
	<?php endif; ?>
	<?php if ($address->getEmail()): ?>
		<dt><?= __('Email', 'jigoshop'); ?></dt>
		<dd><?= $address->getEmail(); ?>&nbsp;</dd>
	<?php endif; ?>
</dl>
