<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Core\Messages;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

/**
 * General tab definition.
 *
 * @package Jigoshop\Admin\Settings
 */
class GeneralTab implements TabInterface
{
	const SLUG = 'general';

	/** @var array */
	private $options;
	/** @var  Messages */
	private $messages;

	public function __construct(Wordpress $wp, Options $options, Messages $messages)
	{
		$this->options = $options->get(self::SLUG);
		$this->messages = $messages;
		$wp->addAction('admin_enqueue_scripts', function (){
			if (!isset($_GET['tab']) || $_GET['tab'] != GeneralTab::SLUG) {
				return;
			}

			$states = [];
			foreach (Country::getAllStates() as $country => $stateList) {
				foreach ($stateList as $code => $state) {
					$states[$country][] = ['id' => $code, 'text' => $state];
				}
			}

			Scripts::add('jigoshop.admin.settings.general', \JigoshopInit::getUrl().'/assets/js/admin/settings/general.js', ['jquery'], ['page' => 'jigoshop_page_jigoshop_settings']);
			Scripts::localize('jigoshop.admin.settings.general', 'jigoshop_admin_general', [
				'states' => $states,
            ]);
		});
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('General', 'jigoshop');
	}

	/**
	 * @return string Tab slug.
	 */
	public function getSlug()
	{
		return self::SLUG;
	}

	/**
	 * @return array List of items to display.
	 */
	public function getSections()
	{
		return [
			[
				'title' => __('Main', 'jigoshop'),
				'id' => 'main',
				'fields' => [
					[
						'id' => 'country',
						'name' => '[country]',
						'title' => __('Shop location (country)', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['country'],
						'options' => Country::getAll(),
                    ],
					[
						'id' => 'state',
						'name' => '[state]',
						'title' => __('Shop location (state)', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['state'],
                    ],
					[
						'name' => '[email]',
						'title' => __('Administrator e-mail', 'jigoshop'),
						'type' => 'text',
						'tip' => __('The email address used to send all Jigoshop related emails, such as order confirmations and notices.', 'jigoshop'),
						'value' => $this->options['email'],
                    ],
					[
						'name' => '[show_message]',
						'id' => 'show_message',
						'title' => __('Display custom message?', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['show_message'],
						'tip' => __('Add custom message on top of each page of your website.', 'jigoshop'),
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[message]',
						'id' => 'custom_message',
						'title' => __('Message text', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['message'],
						'classes' => [$this->options['show_message'] ? '' : 'not-active'],
                    ],
					[
						'name' => '[demo_store]',
						'id' => 'demo_store',
						'title' => __('Demo store', 'jigoshop'),
						'type' => 'checkbox',
						'checked' => $this->options['demo_store'],
						'tip' => __('Enable this option to show a banner at the top of every page stating this shop is currently in testing mode.', 'jigoshop'),
						'classes' => ['switch-medium'],
                    ],
                ],
            ],
			[
				'title' => __('Pricing', 'jigoshop'),
				'id' => 'pricing',
				'fields' => [
					[
						'name' => '[currency]',
						'title' => __('Currency', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['currency'],
						'options' => Currency::countries(),
                    ],
					[
						'name' => '[currency_position]',
						'title' => __('Currency position', 'jigoshop'),
						'type' => 'select',
						'value' => $this->options['currency_position'],
						'options' => Currency::positions(),
                    ],
					[
						'name' => '[currency_decimals]',
						'title' => __('Number of decimals', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['currency_decimals'],
                    ],
					[
						'name' => '[currency_thousand_separator]',
						'title' => __('Thousands separator', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['currency_thousand_separator'],
                    ],
					[
						'name' => '[currency_decimal_separator]',
						'title' => __('Decimal separator', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['currency_decimal_separator'],
                    ],
                ],
            ],
			[
				'title' => __('Company details', 'jigoshop'),
				'description' => __('These details, alongside shop location, will be used for invoicing and emails.', 'jigoshop'),
				'id' => 'company',
				'fields' => [
					[
						'name' => '[company_name]',
						'title' => __('Name', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['company_name'],
                    ],
					[
						'name' => '[company_address_1]',
						'title' => __('Address (first line)', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['company_address_1'],
                    ],
					[
						'name' => '[company_address_2]',
						'title' => __('Address (second line)', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['company_address_2'],
                    ],
					[
						'name' => '[company_tax_number]',
						'title' => __('Tax number', 'jigoshop'),
						'description' => __('Add your tax registration label before the registration number and it will be printed as well. eg. <code>VAT Number: 88888888</code>', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['company_tax_number'],
                    ],
					[
						'name' => '[company_phone]',
						'title' => __('Phone number', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['company_phone'],
                    ],
					[
						'name' => '[company_email]',
						'title' => __('Email', 'jigoshop'),
						'type' => 'text',
						'tip' => __('A representative e-mail company - department of orders, customer service, contact.', 'jigoshop'),
						'value' => $this->options['company_email'],
                    ],
                ],
            ],
			[
				'title' => __('Emails', 'jigoshop'),
				'id' => 'emails',
				'fields' => [
					[
						'name' => '[emails][from]',
						'title' => __('From name', 'jigoshop'),
						'description' => __('Name shown in all Jigoshop emails.', 'jigoshop'),
						'type' => 'text',
						'value' => $this->options['emails']['from'],
                    ],
					[
						'name' => '[emails][footer]',
						'title' => __('Footer', 'jigoshop'),
						'description' => __('The email footer used in all Jigoshop emails.', 'jigoshop'),
						'type' => 'textarea',
						'value' => $this->options['emails']['footer'],
                    ],
                ],
            ],
        ];
	}

	/**
	 * Validate and sanitize input values.
	 *
	 * @param array $settings Input fields.
	 *
	 * @return array Sanitized and validated output.
	 * @throws ValidationException When some items are not valid.
	 */
	public function validate($settings)
	{
		$settings['show_message'] = $settings['show_message'] == 'on';
		$settings['demo_store'] = $settings['demo_store'] == 'on';

		if(!in_array($settings['country'], array_keys(Country::getAll()))) {
			$this->messages->addError(__('Invalid shop location (country), please select again.', 'jigoshop'));
			$settings['country'] = '';
		}

		return $settings;
	}
}
