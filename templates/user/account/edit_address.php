<?php
use Jigoshop\Entity\Customer;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Render;

/**
 * @var $customer Customer
 * @var $address Customer\Address
 * @var $messages \Jigoshop\Core\Messages Messages container.
 * @var $myAccountUrl string URL to my account.
 */
?>
<h1><?php _e('My account &raquo; Edit address', 'jigoshop'); ?></h1>
<?php Render::output('shop/messages', ['messages' => $messages]); ?>
<form class="" role="form" method="post">
	<?php if ($address instanceof Customer\CompanyAddress): ?>
	<?php \Jigoshop\Helper\Forms::text([
		'name' => 'address[company]',
		'label' => __('Company', 'jigoshop'),
		'value' => $address->getCompany(),
        ]); ?>
	<?php \Jigoshop\Helper\Forms::text([
		'name' => 'address[euvatno]',
		'label' => __('VAT number', 'jigoshop'),
		'value' => $address->getVatNumber(),
        ]); ?>
	<?php endif; ?>
	<?php \Jigoshop\Helper\Forms::text([
		'name' => 'address[first_name]',
		'label' => __('First name', 'jigoshop'),
		'value' => $address->getFirstName(),
    ]); ?>
	<?php \Jigoshop\Helper\Forms::text([
		'name' => 'address[last_name]',
		'label' => __('Last name', 'jigoshop'),
		'value' => $address->getLastName(),
    ]); ?>
	<?php \Jigoshop\Helper\Forms::text([
		'name' => 'address[address]',
		'label' => __('Address', 'jigoshop'),
		'value' => $address->getAddress(),
    ]); ?>
	<?php \Jigoshop\Helper\Forms::text([
		'name' => 'address[city]',
		'label' => __('City', 'jigoshop'),
		'value' => $address->getCity(),
    ]); ?>
	<?php \Jigoshop\Helper\Forms::text([
		'name' => 'address[postcode]',
		'label' => __('Postcode', 'jigoshop'),
		'value' => $address->getPostcode(),
    ]); ?>
	<?php \Jigoshop\Helper\Forms::field(Country::hasStates($address->getCountry()) ? 'select' : 'text', [
		'name' => 'address[state]',
		'label' => __('State/province', 'jigoshop'),
		'value' => $address->getState(),
		'options' => Country::getStates($address->getCountry()),
    ]); ?>
	<?php \Jigoshop\Helper\Forms::select([
		'name' => 'address[country]',
		'label' => __('Country', 'jigoshop'),
		'value' => $address->getCountry(),
		'options' => Country::getAllowed(),
    ]); ?>
	<?php \Jigoshop\Helper\Forms::text([
		'name' => 'address[phone]',
		'label' => __('Phone', 'jigoshop'),
		'value' => $address->getPhone(),
    ]); ?>
	<?php \Jigoshop\Helper\Forms::text([
		'name' => 'address[email]',
		'label' => __('Email', 'jigoshop'),
		'value' => $address->getEmail(),
    ]); ?>
	<a href="<?= $myAccountUrl; ?>" class="btn btn-default"><?php _e('Go back to My account', 'jigoshop'); ?></a>
	<button class="btn btn-success pull-right" name="action" value="save_address"><?php _e('Save', 'jigoshop'); ?></button>
</form>
