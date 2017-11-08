<?php
use Jigoshop\Helper\Country;

/**
 * @var $address \Jigoshop\Entity\Customer\Address
 */
?>
<dl class="dl-horizontal clearfix address">
    <?php if ($address instanceof \Jigoshop\Entity\Customer\CompanyAddress): ?>
        <dt><?= __('Company', 'jigoshop-ecommerce'); ?></dt>
        <dd><?= $address->getCompany(); ?></dd>
        <?php if ($address->getVatNumber() != ''): ?>
            <dt><?= __('VAT number', 'jigoshop-ecommerce'); ?></dt>
            <dd><?= $address->getVatNumber(); ?></dd>
        <?php endif; ?>
    <?php endif; ?>
	<dt><?= __('Name', 'jigoshop-ecommerce'); ?></dt>
	<dd><?= $address->getName(); ?>&nbsp;</dd>
	<dt><?= __('Address', 'jigoshop-ecommerce'); ?></dt>
	<dd><?= $address->getAddress(); ?>&nbsp;</dd>
	<dt><?= __('City', 'jigoshop-ecommerce'); ?></dt>
	<dd><?= $address->getCity(); ?>&nbsp;</dd>
	<dt><?= __('Postcode', 'jigoshop-ecommerce'); ?></dt>
	<dd><?= $address->getPostcode(); ?>&nbsp;</dd>
	<dt><?= __('State/province', 'jigoshop-ecommerce'); ?></dt>
	<dd><?= Country::getStateName($address->getCountry(), $address->getState()); ?>&nbsp;</dd>
	<dt><?= __('Country', 'jigoshop-ecommerce'); ?></dt>
	<dd><?= Country::getName($address->getCountry()); ?>&nbsp;</dd>
	<?php if ($address->getPhone()): ?>
		<dt><?= __('Phone', 'jigoshop-ecommerce'); ?></dt>
		<dd><?= $address->getPhone(); ?>&nbsp;</dd>
	<?php endif; ?>
	<?php if ($address->getEmail()): ?>
		<dt><?= __('Email', 'jigoshop-ecommerce'); ?></dt>
		<dd><?= $address->getEmail(); ?>&nbsp;</dd>
	<?php endif; ?>
</dl>
