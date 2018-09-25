<?php

namespace Jigoshop\Frontend\Page\Account;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer\CompanyAddress;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Frontend\Page\PageInterface;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Helper\Validation;
use Jigoshop\Service\CustomerServiceInterface;
use WPAL\Wordpress;

class EditAddress implements PageInterface
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var Messages */
	private $messages;
	/** @var CustomerServiceInterface */
	private $customerService;

	public function __construct(Wordpress $wp, Options $options, CustomerServiceInterface $customerService, Messages $messages)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->customerService = $customerService;
		$this->messages = $messages;

		Styles::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/css/vendors/select2.css');
//		Styles::add('jigoshop.user.account', \JigoshopInit::getUrl().'/assets/css/user/account.css', ['jigoshop.vendors.select2']);
//		Styles::add('jigoshop.user.account.edit_address', \JigoshopInit::getUrl().'/assets/css/user/account/edit_address.css', ['jigoshop.user.account']);

		Scripts::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/js/vendors/select2.js', ['jquery']);
		Scripts::add('jigoshop.vendors.bs_tab_trans_tooltip_collapse', \JigoshopInit::getUrl().'/assets/js/vendors/bs_tab_trans_tooltip_collapse.js', ['jquery']);
		Scripts::add('jigoshop.user.account', \JigoshopInit::getUrl() . '/assets/js/user/account.js', ['jquery', 'jigoshop.helpers']);
		$this->wp->doAction('jigoshop\account\edit_address\assets', $wp);
	}

	public function action()
	{
		if (isset($_POST['action']) && $_POST['action'] == 'save_address') {
			$customer = $this->customerService->getCurrent();
			switch ($this->wp->getQueryParameter('edit-address')) {
				case 'shipping':
					$address = $customer->getShippingAddress();
					break;
				case 'billing':
				default:
					$address = $customer->getBillingAddress();
					break;
			}

			$errors = [];
			if ($address instanceof CompanyAddress) {
				$address->setCompany(trim(htmlspecialchars(strip_tags($_POST['address']['company']))));
				$address->setVatNumber(trim(htmlspecialchars(strip_tags($_POST['address']['euvatno']))));
			}

			$address->setPhone(trim(htmlspecialchars(strip_tags($_POST['address']['phone']))));
			$address->setFirstName(trim(htmlspecialchars(strip_tags($_POST['address']['first_name']))));
			$address->setLastName(trim(htmlspecialchars(strip_tags($_POST['address']['last_name']))));
			$address->setAddress(trim(htmlspecialchars(strip_tags($_POST['address']['address']))));
			$address->setCity(trim(htmlspecialchars(strip_tags($_POST['address']['city']))));

			$country = trim(htmlspecialchars(strip_tags($_POST['address']['country'])));
			if (!Country::exists($country)) {
				$errors[] = sprintf(__('Country "%s" does not exists.', 'jigoshop-ecommerce'), $country);
			} else {
				$address->setCountry($country);
			}

			$postcode = trim(htmlspecialchars(strip_tags($_POST['address']['postcode'])));
			if ($this->options->get('shopping.validate_zip') && !Validation::isPostcode($postcode, $address->getCountry())) {
				$errors[] = __('Postcode is not valid!', 'jigoshop-ecommerce');
			} else {
				$address->setPostcode($postcode);
			}

			$state = trim(htmlspecialchars(strip_tags($_POST['address']['state'])));
			if (Country::hasStates($address->getCountry()) && !Country::hasState($address->getCountry(), $state)) {
				$errors[] = sprintf(__('Country "%s" does not have state "%s".', 'jigoshop-ecommerce'), Country::getName($address->getCountry()), $state);
			} else {
				$address->setState($state);
			}

			$email = trim(htmlspecialchars(strip_tags($_POST['address']['email'])));
			if (!Validation::isEmail($email)) {
				$errors[] = __('Invalid email address', 'jigoshop-ecommerce');
			} else {
				$address->setEmail($email);
			}

			if (!empty($errors)) {
				$this->messages->addError(join('<br/>', $errors), false);
			} else {
				$this->customerService->save($customer);
				$this->messages->addNotice(__('Address saved.', 'jigoshop-ecommerce'));
				$this->wp->redirectTo($this->options->getPageId(Pages::ACCOUNT));
			}
		}
	}

	public function render()
	{
		if (!$this->wp->isUserLoggedIn()) {
			return Render::get('user/login', []);
		}

		$customer = $this->customerService->getCurrent();
		switch ($this->wp->getQueryParameter('edit-address')) {
			case 'shipping':
				$address = $customer->getShippingAddress();
				break;
			case 'billing':
			default:
				$address = $customer->getBillingAddress();
				break;
		}

		return Render::get('user/account/edit_address', [
			'messages' => $this->messages,
			'customer' => $customer,
			'address' => $address,
			'myAccountUrl' => $this->wp->getPermalink($this->options->getPageId(Pages::ACCOUNT)),
        ]);
	}
}
