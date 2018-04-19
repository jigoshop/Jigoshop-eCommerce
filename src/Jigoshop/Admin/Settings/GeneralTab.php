<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Admin\Pages;
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
			if (isset($_GET['tab']) && $_GET['tab'] != GeneralTab::SLUG) {
				return;
			}

			$states = [];
			foreach (Country::getAllStates() as $country => $stateList) {
				foreach ($stateList as $code => $state) {
					$states[$country][] = ['id' => $code, 'text' => $state];
				}
			}

            $currency = [];
            foreach (Currency::countries() as $key => $value) {
                $symbols = Currency::symbols();
                $symbol = $symbols[$key];
                $separator = Currency::decimalSeparator();
                $code = $key;

                $currency[$key] = [
                    ['id' => '%1$s%3$s', 'text' => html_entity_decode(sprintf('%1$s0%2$s00', $symbol, $separator))],// symbol.'0'.separator.'00'
                    ['id' => '%1$s %3$s', 'text'  => html_entity_decode(sprintf('%1$s 0%2$s00', $symbol, $separator))],// symbol.' 0'.separator.'00'
                    ['id' => '%3$s%1$s', 'text'  => html_entity_decode(sprintf('0%2$s00%1$s', $symbol, $separator))],// '0'.separator.'00'.symbol
                    ['id' => '%3$s %1$s', 'text'  => html_entity_decode(sprintf('0%2$s00 %1$s', $symbol, $separator))],// '0'.separator.'00 '.symbol
                    ['id' => '%2$s%3$s', 'text'  => html_entity_decode(sprintf('%1$s0%2$s00', $code, $separator))],// code.'0'.separator.'00'
                    ['id' => '%2$s %3$s', 'text'  => html_entity_decode(sprintf('%1$s 0%2$s00', $code, $separator))],// code.' 0'.separator.'00'
                    ['id' => '%3$s%2$s', 'text'  => html_entity_decode(sprintf('0%2$s00%1$s', $code, $separator))],// '0'.separator.'00'.code
                    ['id' => '%3$s %2$s', 'text'  => html_entity_decode(sprintf('0%2$s00 %1$s', $code, $separator))],// '0'.separator.'00 '.code
                    ['id' => '%1$s%3$s%2$s', 'text'  => html_entity_decode(sprintf('%1$s0%2$s00%3$s', $symbol, $separator, $code))],// symbol.'0'.separator.'00'.code
                    ['id' => '%1$s %3$s %2$s', 'text'  => html_entity_decode(sprintf('%1$s 0%2$s00 %3$s', $symbol, $separator, $code))],// symbol.' 0'.separator.'00 '.code
                    ['id' => '%2$s%3$s%1$s', 'text'  => html_entity_decode(sprintf('%3$s0%2$s00%1$s', $symbol, $separator, $code))],// code.'0'.separator.'00'.symbol
                    ['id' => '%2$s %3$s %1$s', 'text'  => html_entity_decode(sprintf('%3$s 0%2$s00 %1$s', $symbol, $separator, $code))],// code.' 0'.separator.'00 '.symbol
                ];
            }

			Scripts::add('jigoshop.admin.settings.general', \JigoshopInit::getUrl().'/assets/js/admin/settings/general.js', ['jquery'], ['page' => 'jigoshop_page_jigoshop_settings']);
			Scripts::localize('jigoshop.admin.settings.general', 'jigoshop_admin_general', [
				'states' => $states,
                'currency' => $currency,
            ]);
		});
	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('General', 'jigoshop-ecommerce');
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
				'title' => __('Main', 'jigoshop-ecommerce'),
				'id' => 'main',
				'fields' => [
					[
						'id' => 'country',
						'name' => '[country]',
						'title' => __('Shop location (country)', 'jigoshop-ecommerce'),
						'type' => 'select',
						'value' => $this->options['country'],
						'options' => Country::getAll(),
                    ],
					[
						'id' => 'state',
						'name' => '[state]',
						'title' => __('Shop location (state)', 'jigoshop-ecommerce'),
						'type' => 'text',
						'value' => $this->options['state'],
                    ],
					[
						'name' => '[email]',
						'title' => __('Administrator e-mail', 'jigoshop-ecommerce'),
						'type' => 'text',
						'tip' => __('The email address used to send all Jigoshop related emails, such as order confirmations and notices.', 'jigoshop-ecommerce'),
						'value' => $this->options['email'],
                    ],
					[
						'name' => '[show_message]',
						'id' => 'show_message',
						'title' => __('Display custom message?', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->options['show_message'],
						'tip' => __('Add custom message on top of each page of your website.', 'jigoshop-ecommerce'),
						'classes' => ['switch-medium'],
                    ],
					[
						'name' => '[message]',
						'id' => 'custom_message',
						'title' => __('Message text', 'jigoshop-ecommerce'),
						'type' => 'text',
						'value' => $this->options['message'],
						'classes' => [$this->options['show_message'] ? '' : 'not-active'],
                    ],
					[
						'name' => '[demo_store]',
						'id' => 'demo_store',
						'title' => __('Demo store', 'jigoshop-ecommerce'),
						'type' => 'checkbox',
						'checked' => $this->options['demo_store'],
						'tip' => __('Enable this option to show a banner at the top of every page stating this shop is currently in testing mode.', 'jigoshop-ecommerce'),
						'classes' => ['switch-medium'],
                    ],
                ],
            ],
			[
				'title' => __('Pricing', 'jigoshop-ecommerce'),
				'id' => 'pricing',
				'fields' => [
					[
					    'id' => 'currency',
						'name' => '[currency]',
						'title' => __('Currency', 'jigoshop-ecommerce'),
						'type' => 'select',
						'value' => $this->options['currency'],
						'options' => Currency::countries(),
                    ],
					[
					    'id' => 'currency_position',
						'name' => '[currency_position]',
						'title' => __('Currency position', 'jigoshop-ecommerce'),
						'type' => 'text',
						'value' => $this->options['currency_position'],
						//'options' => Currency::positions(),
                    ],
					[
						'name' => '[currency_decimals]',
						'title' => __('Number of decimals', 'jigoshop-ecommerce'),
						'type' => 'text',
						'value' => $this->options['currency_decimals'],
                    ],
					[
						'name' => '[currency_thousand_separator]',
						'title' => __('Thousands separator', 'jigoshop-ecommerce'),
						'type' => 'text',
						'value' => $this->options['currency_thousand_separator'],
                    ],
					[
						'name' => '[currency_decimal_separator]',
						'title' => __('Decimal separator', 'jigoshop-ecommerce'),
						'type' => 'text',
						'value' => $this->options['currency_decimal_separator'],
                    ],
                ],
            ],
			[
				'title' => __('Company details', 'jigoshop-ecommerce'),
				'description' => __('These details, alongside shop location, will be used for invoicing and emails.', 'jigoshop-ecommerce'),
				'id' => 'company',
				'fields' => [
					[
						'name' => '[company_name]',
						'title' => __('Name', 'jigoshop-ecommerce'),
						'type' => 'text',
						'value' => $this->options['company_name'],
                    ],
					[
						'name' => '[company_address_1]',
						'title' => __('Address (first line)', 'jigoshop-ecommerce'),
						'type' => 'text',
						'value' => $this->options['company_address_1'],
                    ],
					[
						'name' => '[company_address_2]',
						'title' => __('Address (second line)', 'jigoshop-ecommerce'),
						'type' => 'text',
						'value' => $this->options['company_address_2'],
                    ],
					[
						'name' => '[company_tax_number]',
						'title' => __('Tax number', 'jigoshop-ecommerce'),
						'description' => __('Add your tax registration label before the registration number and it will be printed as well. eg. <code>VAT Number: 88888888</code>', 'jigoshop-ecommerce'),
						'type' => 'text',
						'value' => $this->options['company_tax_number'],
                    ],
					[
						'name' => '[company_phone]',
						'title' => __('Phone number', 'jigoshop-ecommerce'),
						'type' => 'text',
						'value' => $this->options['company_phone'],
                    ],
					[
						'name' => '[company_email]',
						'title' => __('Email', 'jigoshop-ecommerce'),
						'type' => 'text',
						'tip' => __('A representative e-mail company - department of orders, customer service, contact.', 'jigoshop-ecommerce'),
						'value' => $this->options['company_email'],
                    ],
                ],
            ],
			[
				'title' => __('Emails', 'jigoshop-ecommerce'),
				'id' => 'emails',
				'fields' => [
					[
						'name' => '[emails][from]',
						'title' => __('From name', 'jigoshop-ecommerce'),
						'description' => __('Name shown in all Jigoshop emails.', 'jigoshop-ecommerce'),
						'type' => 'text',
						'value' => $this->options['emails']['from'],
                    ],
					[
						'name' => '[emails][footer]',
						'title' => __('Footer', 'jigoshop-ecommerce'),
						'description' => __('The email footer used in all Jigoshop emails.', 'jigoshop-ecommerce'),
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
			$this->messages->addError(__('Invalid shop location (country), please select again.', 'jigoshop-ecommerce'));
			$settings['country'] = '';
		}

		return $settings;
	}
}
