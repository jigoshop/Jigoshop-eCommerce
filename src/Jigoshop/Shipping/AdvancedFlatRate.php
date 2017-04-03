<?php

namespace Jigoshop\Shipping;

use Jigoshop\Core\Types;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Helper\Country;
use Jigoshop\Helper\Options;
use Jigoshop\Helper\Render;
use Jigoshop\Integration;
use Jigoshop\Exception;
use Jigoshop\Service\CartServiceInterface;
use WPAL\Wordpress;

/**
 * Class Method
 * @package Jigoshop\Extension\AddFlatRate\Common;
 * @author Krzysztof Kasowski
 */
class AdvancedFlatRate implements MultipleMethod
{
    const ID = 'advanced_flat_rate';
    /** @var  array */
    private $settings;
    /** @var  Rate[] */
    private $rates;
    /** @var  int */
    private $rate;
    /** @var  CartServiceInterface */
    private $cartService;

    /**
     * Method constructor.
     */
    public function __construct(Wordpress $wp, CartServiceInterface $cartService)
    {
        Options::setDefaults('shipping.' . self::ID, array(
            'enabled' => false,
            'title' => '',
            'taxable' => false,
            'fee' => 0,
            'available_for' => 'all',
            'countries' => array(),
            'rates' => array()
        ));
        $this->settings = Options::getOptions('shipping.' . self::ID);
        $this->cartService = $cartService;
    }

    /**
     * @return string ID of shipping method.
     */
    public function getId()
    {
        return self::ID;
    }

    /**
     * @return string Name of method.
     */
    public function getName()
    {
        return is_admin() ? __('Advanced flat rate', 'jigoshop') : $this->settings['title'];
    }

    /**
     * @return string Customizable title of method.
     */
    public function getTitle()
    {
        return $this->settings['title'];
    }

    /**
     * @return bool Whether current method is enabled and able to work.
     */
    public function isEnabled()
    {
        $cart = $this->cartService->getCurrent();
        $post = get_post();

        if ($post === null || $post->post_type != Types::ORDER) {
            $customer = $cart->getCustomer();
        } else {
            $customer = unserialize(get_post_meta($post->ID, 'customer', true));
        }

        return $this->settings['enabled'] && ($this->settings['available_for'] === 'all' || in_array($customer->getShippingAddress()->getCountry(),
                    $this->settins['countries']));
    }

    /**
     * @return bool Whether current method is taxable.
     */
    public function isTaxable()
    {
        return $this->settings['taxable'];
    }

    /**
     * @return array List of options to display on Shipping settings page.
     */
    public function getOptions()
    {
        return array(
            array(
                'name' => sprintf('[%s][enabled]', self::ID),
                'type' => 'checkbox',
                'title' => __('Enable', 'jigoshop'),
                'checked' => $this->settings['enabled'],
                'classes' => array('switch-medium'),
            ),
            array(
                'name' => sprintf('[%s][title]', self::ID),
                'type' => 'text',
                'title' => __('Title', 'jigoshop'),
                'value' => $this->settings['title'],
            ),
            array(
                'name' => sprintf('[%s][taxable]', self::ID),
                'type' => 'checkbox',
                'title' => __('Is taxable?', 'jigoshop'),
                'checked' => $this->settings['taxable'],
                'classes' => array('switch-medium'),
            ),
            array(
                'name' => sprintf('[%s][fee]', self::ID),
                'type' => 'number',
                'title' => __('Fee', 'jigoshop'),
                'value' => $this->settings['fee'],
            ),
            array(
                'name' => sprintf('[%s][available_for]', self::ID),
                'id' => 'advanced_flat_rate_available_for',
                'title' => __('Available for', 'jigoshop'),
                'type' => 'select',
                'value' => $this->settings['available_for'],
                'options' => array(
                    'all' => __('All allowed countries', 'jigoshop'),
                    'specific' => __('Selected countries', 'jigoshop'),
                ),
            ),
            array(
                'name' => sprintf('[%s][countries]', self::ID),
                'id' => 'advanced_flat_rate_countries',
                'title' => __('Select countries', 'jigoshop'),
                'type' => 'select',
                'value' => $this->settings['countries'],
                'options' => Country::getAllowed(),
                'multiple' => true,
                'hidden' => $this->settings['available_for'] == 'all',
            ),
            array(
                'name' => sprintf('[%s][rates]', self::ID),
                'title' => __('Rates', 'jigoshop'),
                'type' => 'user_defined',
                'value' => $this->settings['rates'],
                'display' => function ($field) {
                    $rates = $field['value'];
                    for($i = 0; $i < count($rates); $i++) {
                        if(isset($rates[$i]['country']) && $rates[$i]['country'] && (!isset($rates[$i]['countries']) || empty($rates[$i]['countries']))) {
                            $country = $rates[$i]['country'];
                            if(!empty($rates[$i]['states'])) {
                                $rates[$i]['states'] = array_map(function($state) use ($country) {
                                    return $country.':'.$state;
                                }, $rates[$i]['states']);
                            } else {
                                $rates['countries'] = [$country];
                            }
                        };
                    }
                    Render::output('admin/settings/shipping/advanced_flat_rate', [
                        'name' => $field['name'],
                        'values' => $rates,
                    ]);
                }
            ),
        );
    }

    /**
     * @return array List of applicable tax classes.
     */
    public function getTaxClasses()
    {
        return array('standard');
    }

    /**
     * Validates and returns properly sanitized options.
     *
     * @param $settings array Input options.
     *
     * @return array Sanitized result.
     */
    public function validateOptions($settings)
    {
        $settings['enabled'] = $settings['enabled'] == 'on';
        $settings['taxable'] = $settings['taxable'] == 'on';
        if (isset($settings['rates'])) {
            $settings['rates'] = array_values($settings['rates']);
            for ($i = 0; $i < count($settings['rates']); $i++) {
                $settings['rates'][$i] = array_merge(array(
                    'label' => '',
                    'cost' => 0,
                    'continents' => [],
                    'countries' => [],
                    'states' => [],
                    'postcode' => ''
                ), $settings['rates'][$i]);
                $settings['rates'][$i]['cost'] = (float)$settings['rates'][$i]['cost'];
            }
        }

        return $settings;
    }

    /**
     * Checks whether current method is the one specified with selected rule.
     *
     * @param \Jigoshop\Shipping\Method $method Method to check.
     * @param Rate $rate Rate to check.
     *
     * @return boolean Is this the method?
     */
    public function is(\Jigoshop\Shipping\Method $method, $rate = null)
    {
        return $method->getId() == $this->getId() && $rate instanceof Rate && $rate->getId() == $this->getShippingRate();
    }

    /**
     * @param OrderInterface $order Order to calculate shipping for.
     *
     * @return float Calculates value of shipping for the order.
     * @throws Exception On error.
     */
    public function calculate(OrderInterface $order)
    {
        if ($this->rate !== null) {
            $rates = $this->getRates($order);
            if (empty($rates)) {
                throw new Exception(sprintf(__('%s - There are no rates to calculate, rate is empty',
                    'jigoshop_add_flat_rate_shipping'),
                    $this->getName()));
            }
            if (!isset($rates[$this->rate])) {
                throw new Exception(sprintf(__('%s - No rates have been choose', 'jigoshop_add_flat_rate_shipping'),
                    $this->getName()));
            }
            return $rates[$this->rate]->getPrice();
        } else {
            throw new Exception(sprintf(__('%s - There was an error during calculating rate, please try again.',
                'jigoshop_add_flat_rate_shipping'), $this->getName()));
        }
    }

    /**
     * @return array Minimal state to fully identify shipping method.
     */
    public function getState()
    {
        return array(
            'id' => $this->getId(),
            'rate' => $this->getShippingRate()
        );
    }

    /**
     * Restores shipping method state.
     *
     * @param array $state State to restore.
     */
    public function restoreState(array $state)
    {
        if (isset($state['rate'])) {
            $this->setShippingRate($state['rate']);
        }
    }

    /**
     * Returns list of available shipping rates.
     *
     * @param OrderInterface $order
     *
     * @return array List of available shipping rates.
     */
    public function getRates($order)
    {
        if ($this->rates == null) {
            $this->rates = array();
            foreach ($this->settings['rates'] as $key => $rawRate) {
                $address = $order->getCustomer()->getShippingAddress();
                if ($rawRate['country'] != '' && $rawRate['country'] != $address->getCountry()) {
                    continue;
                }
                if (count($rawRate['states']) != 0 && !in_array($address->getState(), $rawRate['states'])) {
                    continue;
                }
                $code = str_replace('*', '(.*)', str_replace(['-', ' '], '', strtoupper($rawRate['postcode'])));
                if ($code != '' && preg_match('/^'.$code.'$/', str_replace(['-', ' '], '', strtoupper($address->getPostcode()))) == false) {
                    continue;
                }

                $rate = new Rate();
                $rate->setId($key);
                $rate->setName($rawRate['label']);
                $rate->setPrice($rawRate['cost'] + (1 * $this->settings['fee']));
                $rate->setMethod($this);
                $this->rates[$key] = $rate;
            }
        }

        return $this->rates;
    }

    /**
     * @param $rate int Rate to use.
     */
    public function setShippingRate($rate)
    {
        $this->rate = $rate;
    }

    /**
     * @return int Currently used rate.
     */
    public function getShippingRate()
    {
        return $this->rate;
    }

    /** @var array  */
    private static $continents = [
        'AF' => 'Africa',
        'AN' => 'Antarctica',
        'AS' => 'Asia',
        'EU' => 'Europe',
        'NA' => 'North America',
        'OC' => 'Oceania',
        'SA' => 'South America',
    ];

    /**
     * @return array
     */
    public static function getContinets()
    {
        return self::$continents;
    }

    /** @var array  */
    private static $countries = [
        'AF' => [
            'AO' => 'Angola',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'BJ' => 'Benin',
            'BW' => 'Botswana',
            'CD' => 'Congo (Kinshasa)',
            'CF' => 'Central African Republic',
            'CG' => 'Congo (Brazzaville)',
            'CI' => 'Ivory Coast',
            'CM' => 'Cameroon',
            'CV' => 'Cape Verde',
            'DJ' => 'Djibouti',
            'DZ' => 'Algeria',
            'EG' => 'Egypt',
            'EH' => 'Western Sahara',
            'ER' => 'Eritrea',
            'ET' => 'Ethiopia',
            'GA' => 'Gabon',
            'GH' => 'Ghana',
            'GM' => 'Gambia',
            'GN' => 'Guinea',
            'GQ' => 'Equatorial Guinea',
            'GW' => 'Guinea-Bissau',
            'KE' => 'Kenya',
            'KM' => 'Comoros',
            'LR' => 'Liberia',
            'LS' => 'Lesotho',
            'LY' => 'Libya',
            'MA' => 'Morocco',
            'MG' => 'Madagascar',
            'ML' => 'Mali',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'MW' => 'Malawi',
            'MZ' => 'Mozambique',
            'NA' => 'Namibia',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'RE' => 'Reunion',
            'RW' => 'Rwanda',
            'SC' => 'Seychelles',
            'SD' => 'Sudan',
            'SH' => 'Saint Helena',
            'SL' => 'Sierra Leone',
            'SN' => 'Senegal',
            'SO' => 'Somalia',
            'SS' => '',
            'ST' => 'Sao Tome and Principe',
            'SZ' => 'Swaziland',
            'TD' => 'Chad',
            'TG' => 'Togo',
            'TN' => 'Tunisia',
            'TZ' => 'Tanzania',
            'UG' => 'Uganda',
            'YT' => 'Mayotte',
            'ZA' => 'South Africa',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        ],
        'AN' => [
            'AQ' => 'Antarctica',
            'BV' => '',
            'GS' => 'South Georgia/Sandwich Islands',
            'HM' => '',
            'TF' => 'French Southern Territories',
        ],
        'AS' => [
            'AE' => 'United Arab Emirates',
            'AF' => 'Afghanistan',
            'AM' => 'Armenia',
            'AZ' => 'Azerbaijan',
            'BD' => 'Bangladesh',
            'BH' => 'Bahrain',
            'BN' => 'Brunei',
            'BT' => 'Bhutan',
            'CC' => 'Cocos (Keeling) Islands',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CY' => 'Cyprus',
            'GE' => 'Georgia',
            'HK' => 'Hong Kong',
            'ID' => 'Indonesia',
            'IL' => 'Israel',
            'IN' => 'India',
            'IO' => 'British Indian Ocean Territory',
            'IQ' => 'Iraq',
            'IR' => 'Iran',
            'JO' => 'Jordan',
            'JP' => 'Japan',
            'KG' => 'Kyrgyzstan',
            'KH' => 'Cambodia',
            'KP' => 'North Korea',
            'KR' => 'South Korea',
            'KW' => 'Kuwait',
            'KZ' => 'Kazakhstan',
            'LA' => 'Laos',
            'LB' => 'Lebanon',
            'LK' => 'Sri Lanka',
            'MM' => 'Myanmar',
            'MN' => 'Mongolia',
            'MO' => 'Macao S.A.R., China',
            'MV' => 'Maldives',
            'MY' => 'Malaysia',
            'NP' => 'Nepal',
            'OM' => 'Oman',
            'PH' => 'Philippines',
            'PK' => 'Pakistan',
            'PS' => 'Palestinian Territory',
            'QA' => 'Qatar',
            'SA' => 'Saudi Arabia',
            'SG' => 'Singapore',
            'SY' => 'Syria',
            'TH' => 'Thailand',
            'TJ' => 'Tajikistan',
            'TL' => 'Timor-Leste',
            'TM' => 'Turkmenistan',
            'TW' => 'Taiwan',
            'UZ' => 'Uzbekistan',
            'VN' => 'Viet nam',
            'YE' => 'Yemen',
        ],
        'EU' => [
            'AD' => 'Andorra',
            'AL' => 'Albania',
            'AT' => 'Austria',
            'AX' => 'Aland Islands',
            'BA' => 'Bosnia and Herzegovina',
            'BE' => 'Belgium',
            'BG' => 'Bulgaria',
            'BY' => 'Belarus',
            'CH' => 'Switzerland',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DE' => 'Germany',
            'DK' => 'Denmark',
            'EE' => 'Estonia',
            'ES' => 'Spain',
            'FI' => 'Finland',
            'FO' => 'Faroe Islands',
            'FR' => 'France',
            'GB' => 'United Kingdom',
            'GG' => 'Guernsey',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'HR' => 'Croatia',
            'HU' => 'Hungary',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IS' => 'Iceland',
            'IT' => 'Italy',
            'JE' => 'Jersey',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'LV' => 'Latvia',
            'MC' => 'Monaco',
            'MD' => 'Moldova',
            'ME' => 'Montenegro',
            'MK' => 'Macedonia',
            'MT' => 'Malta',
            'NL' => 'Netherlands',
            'NO' => 'Norway',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'RO' => 'Romania',
            'RS' => 'Serbia',
            'RU' => 'Russia',
            'SE' => 'Sweden',
            'SI' => 'Slovenia',
            'SJ' => 'Svalbard and Jan Mayen',
            'SK' => 'Slovakia',
            'SM' => 'San Marino',
            'TR' => 'Turkey',
            'UA' => 'Ukraine',
            'VA' => 'Vatican',
        ],
        'NA' => [
            'AG' => 'Antigua and Barbuda',
            'AI' => 'Anguilla',
            'AN' => 'Netherlands Antilles',
            'AW' => 'Aruba',
            'BB' => 'Barbados',
            'BL' => 'Saint Barthélemy',
            'BM' => 'Bermuda',
            'BQ' => '',
            'BS' => 'Bahamas',
            'BZ' => 'Belize',
            'CA' => 'Canada',
            'CR' => 'Costa Rica',
            'CU' => 'Cuba',
            'CW' => '',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'GD' => 'Grenada',
            'GL' => 'Greenland',
            'GP' => 'Guadeloupe',
            'GT' => 'Guatemala',
            'HN' => 'Honduras',
            'HT' => 'Haiti',
            'JM' => 'Jamaica',
            'KN' => 'Saint Kitts and Nevis',
            'KY' => 'Cayman Islands',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin (French part)',
            'MQ' => 'Martinique',
            'MS' => 'Montserrat',
            'MX' => 'Mexico',
            'NI' => 'Nicaragua',
            'PA' => 'Panama',
            'PM' => 'Saint Pierre and Miquelon',
            'PR' => 'Puerto Rico',
            'SV' => 'El Salvador',
            'SX' => '',
            'TC' => 'Turks and Caicos Islands',
            'TT' => 'Trinidad and Tobago',
            'US' => 'United States',
            'VC' => 'Saint Vincent and the Grenadines',
            'VG' => 'British Virgin Islands',
            'VI' => 'U.S. Virgin Islands',
        ],
        'OC' => [
            'AS' => 'American Samoa',
            'AU' => 'Australia',
            'CK' => 'Cook Islands',
            'FJ' => 'Fiji',
            'FM' => 'Micronesia',
            'GU' => 'Guam',
            'KI' => 'Kiribati',
            'MH' => 'Marshall Islands',
            'MP' => 'Northern Mariana Islands',
            'NC' => 'New Caledonia',
            'NF' => 'Norfolk Island',
            'NR' => 'Nauru',
            'NU' => 'Niue',
            'NZ' => 'New Zealand',
            'PF' => 'French Polynesia',
            'PG' => 'Papua New Guinea',
            'PN' => 'Pitcairn',
            'PW' => 'Palau',
            'SB' => 'Solomon Islands',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TV' => 'Tuvalu',
            'UM' => 'US Minor Outlying Islands',
            'VU' => 'Vanuatu',
            'WF' => 'Wallis and Futuna',
            'WS' => 'Samoa',
        ],
        'SA' => [
            'AR' => 'Argentina',
            'BO' => 'Bolivia',
            'BR' => 'Brazil',
            'CL' => 'Chile',
            'CO' => 'Colombia',
            'EC' => 'Ecuador',
            'FK' => 'Falkland Islands',
            'GF' => 'French Guiana',
            'GY' => 'Guyana',
            'PE' => 'Peru',
            'PY' => 'Paraguay',
            'SR' => 'Suriname',
            'UY' => 'Uruguay',
            'VE' => 'Venezuela',
        ],
    ];

    /**
     * @param string $code
     * @return arrayby
     */
    public static function getCountriesByContinent($code)
    {
        if(isset(self::$countries[$code])) {
            return self::$countries[$code];
        }

        return [];
    }
}