<?php

namespace Jigoshop\Helper;

use Jigoshop\Core\Options as CoreOptions;

/**
 * Available currencies.
 *
 * @package Jigoshop\Helper
 * @author  Amadeusz Starzykiewicz
 */
class Currency
{
	/** @var CoreOptions */
	private static $options;
	private static $symbol;
	private static $code;
	private static $format;
	private static $decimals;
	private static $decimalSeparator;
	private static $thousandsSeparator;

	/**
	 * @param CoreOptions $options Options object.
	 */
	public static function setOptions($options)
	{
		self::$options = $options;
	}

	/**
	 * Returns symbol of set currency (or the one provided as argument).
	 * 
	 * @param string $currencyCode Currency code (overrides currency set in options) (optional).
	 * 
	 * @return string Currency symbol.
	 */
	public static function symbol($currencyCode = null)
	{
		if($currencyCode !== null) {
			$symbols = Currency::symbols();

			return $symbols[$currencyCode];
		}

		if (self::$symbol === null) {
			$symbols = Currency::symbols();
			self::$symbol = $symbols[self::$options->get('general.currency')];
		}

		return self::$symbol;
	}

	public static function code()
	{
		if (self::$code === null) {
			self::$code = self::$options->get('general.currency');
		}

		return self::$code;
	}

	public static function format()
	{
		if (self::$format === null) {
			self::$format = self::$options->get('general.currency_position');
		}

		return self::$format;
	}

	public static function decimals()
	{
		if (self::$decimals === null) {
			self::$decimals = self::$options->get('general.currency_decimals');
		}

		return self::$decimals;
	}

	public static function decimalSeparator()
	{
		if (self::$decimalSeparator === null) {
			self::$decimalSeparator = self::$options->get('general.currency_decimal_separator');
		}

		return self::$decimalSeparator;
	}

	public static function thousandsSeparator()
	{
		if (self::$thousandsSeparator === null) {
			self::$thousandsSeparator = self::$options->get('general.currency_thousand_separator');
		}

		return self::$thousandsSeparator;
	}

	/**
	 * @return array List of currency symbols.
	 */
	public static function symbols()
	{
		$symbols = [
			'AED' => '&#1583;&#46;&#1573;', /*'United Arab Emirates dirham'*/
			'AFN' => '&#1547;', /*'Afghanistan Afghani'*/
			'ALL' => 'Lek', /*'Albania Lek'*/
			'ANG' => '&fnof;', /*'Netherlands Antilles Guilder'*/
			'ARS' => '$', /*'Argentina Peso'*/
			'AUD' => '$', /*'Australia Dollar'*/
			'AWG' => '&fnof;', /*'Aruba Guilder'*/
			'AZN' => '&#1084;&#1072;&#1085;', /*'Azerbaijan New Manat'*/
			'BAM' => 'KM', /*'Bosnia and Herzegovina Convertible Marka'*/
			'BBD' => '$', /*'Barbados Dollar'*/
			'BGN' => '&#1083;&#1074;', /*'Bulgaria Lev'*/
			'BMD' => '$', /*'Bermuda Dollar'*/
			'BND' => '$', /*'Brunei Darussalam Dollar'*/
			'BOB' => '$b', /*'Bolivia Boliviano'*/
			'BRL' => '&#82;&#36;', /*'Brazil Real'*/
			'BSD' => '$', /*'Bahamas Dollar'*/
			'BWP' => 'P', /*'Botswana Pula'*/
			'BYR' => 'p.', /*'Belarus Ruble'*/
			'BZD' => 'BZ$', /*'Belize Dollar'*/
			'CAD' => '$', /*'Canada Dollar'*/
			'CHF' => 'CHF', /*'Switzerland Franc'*/
			'CLP' => '$', /*'Chile Peso'*/
			'CNY' => '&yen;', /*'China Yuan Renminbi'*/
			'COP' => '$', /*'Colombia Peso'*/
			'CRC' => '&#8353;', /*'Costa Rica Colon'*/
			'CUP' => '&#8369;', /*'Cuba Peso'*/
			'CZK' => 'K&#269;', /*'Czech Republic Koruna'*/
			'DKK' => 'kr', /*'Denmark Krone'*/
			'DOP' => 'RD$', /*'Dominican Republic Peso'*/
			'EEK' => 'kr', /*'Estonia Kroon'*/
			'EGP' => '&pound;', /*'Egypt Pound'*/
			'EUR' => '&euro;', /*'Euro Member Countries'*/
			'FJD' => '$', /*'Fiji Dollar'*/
			'FKP' => '&pound;', /*'Falkland Islands'*/
			'GBP' => '&pound;', /*'United Kingdom Pound'*/
			'GEL' => 'ლ', /*'Georgia Lari'*/
			'GGP' => '&pound;', /*'Guernsey Pound'*/
			'GHC' => '&cent;', /*'Ghana Cedis'*/
			'GIP' => '&cent;', /*'Gibraltar Pound'*/
			'GTQ' => 'Q', /*'Guatemala Quetzal'*/
			'GYD' => '$', /*'Guyana Dollar'*/
			'HKD' => '$', /*'Hong Kong Dollar'*/
			'HNL' => 'L', /*'Honduras Lempira'*/
			'HRK' => 'kn', /*'Croatia Kuna'*/
			'HUF' => '&#70;&#116;', /*'Hungary Forint'*/
			'IDR' => '&#82;&#112;', /*'Indonesia Rupiah'*/
			'ILS' => '&#8362;', /*'Israel Shekel'*/
			'IMP' => '&pound;', /*'Isle of Man Pound'*/
			'INR' => '&#8360;', /*'India Rupee'*/
			'IRR' => '&#65020;', /*'Iran Rial'*/
			'ISK' => 'kr', /*'Iceland Krona'*/
			'JEP' => '&pound;', /*'Jersey Pound'*/
			'JMD' => 'J$', /*'Jamaica Dollar'*/
			'JPY' => '&yen;', /*'Japan Yen'*/
			'KGS' => '&#1083;&#1074;', /*'Kyrgyzstan Som'*/
			'KHR' => '&#6107;', /*'Cambodia Riel'*/
			'KPW' => '&#8361;', /*'North Korea Won'*/
			'KRW' => '&#8361;', /*'South Korea Won'*/
			'KYD' => '$', /*'Cayman Islands Dollar'*/
			'KZT' => '&#1083;&#1074;', /*'Kazakhstan Tenge'*/
			'LAK' => '&#8365;', /*'Laos Kip'*/
			'LBP' => '&pound;', /*'Lebanon Pound'*/
			'LKR' => '&#8360;', /*'Sri Lanka Rupee'*/
			'LRD' => '$', /*'Liberia Dollar'*/
			'LTL' => 'Lt', /*'Lithuania Litas'*/
			'LVL' => 'Ls', /*'Latvia Lat'*/
			'MAD' => '&#1583;.&#1605;.', /*'Moroccan Dirham'*/
			'MKD' => '&#1076;&#1077;&#1085;', /*'Macedonia Denar'*/
			'MNT' => '&#8366;', /*'Mongolia Tughrik'*/
			'MUR' => '&#8360;', /*'Mauritius Rupee'*/
			'MXN' => '&#36;', /*'Mexico Peso'*/
			'MYR' => 'RM', /*'Malaysia Ringgit'*/
			'MZN' => 'MT', /*'Mozambique Metical'*/
			'NAD' => '$', /*'Namibia Dollar'*/
			'NGN' => '&#8358;', /*'Nigeria Naira'*/
			'NIO' => 'C$', /*'Nicaragua Cordoba'*/
			'NOK' => 'kr', /*'Norway Krone'*/
			'NPR' => '&#8360;', /*'Nepal Rupee'*/
			'NZD' => '$', /*'New Zealand Dollar'*/
			'OMR' => '&#65020;', /*'Oman Rial'*/
			'PAB' => 'B/.', /*'Panama Balboa'*/
			'PEN' => 'S/.', /*'Peru Nuevo Sol'*/
			'PHP' => '&#8369;', /*'Philippines Peso'*/
			'PKR' => '&#8360;', /*'Pakistan Rupee'*/
			'PLN' => '&#122;&#322;', /*'Poland Zloty'*/
			'PYG' => 'Gs', /*'Paraguay Guarani'*/
			'QAR' => '&#65020;', /*'Qatar Riyal'*/
			'RON' => '&#108;&#101;&#105;', /*'Romania New Leu'*/
			'RSD' => 'РСД', /*'Serbia Dinar'*/
			'RUB' => '&#1088;&#1091;&#1073;', /*'Russia Ruble'*/
			'SAR' => '&#65020;', /*'Saudi Arabia Riyal'*/
			'SBD' => '$', /*'Solomon Islands Dollar'*/
			'SCR' => '&#8360;', /*'Seychelles Rupee'*/
			'SEK' => 'kr', /*'Sweden Krona'*/
			'SGD' => '$', /*'Singapore Dollar'*/
			'SHP' => '&pound;', /*'Saint Helena Pound'*/
			'SOS' => 'S', /*'Somalia Shilling'*/
			'SRD' => '$', /*'Suriname Dollar'*/
			'SVC' => '$', /*'El Salvador Colon'*/
			'SYP' => '&pound;', /*'Syria Pound'*/
			'THB' => '&#3647;', /*'Thailand Baht'*/
			'TRL' => '&#8356;', /*'Turkey Lira'*/
			'TRY' => 'TL', /*'Turkey Lira'*/
			'TTD' => 'TT$', /*'Trinidad and Tobago Dollar'*/
			'TVD' => '$', /*'Tuvalu Dollar'*/
			'TWD' => 'NT$', /*'Taiwan New Dollar'*/
			'UAH' => '&#8372;', /*'Ukraine Hryvna'*/
			'USD' => '$', /*'United States Dollar'*/
			'UYU' => '$U', /*'Uruguay Peso'*/
			'UZS' => '&#1083;&#1074;', /*'Uzbekistan Som'*/
			'VEF' => 'Bs', /*'Venezuela Bolivar Fuerte'*/
			'VND' => '&#8363;', /*'Viet Nam Dong'*/
			'XCD' => '$', /*'East Caribbean Dollar'*/
			'YER' => '&#65020;', /*'Yemen Rial'*/
			'ZAR' => 'R', /*'South Africa Rand'*/
			'ZWD' => 'Z$', /*'Zimbabwe Dollar'*/
        ];

		ksort($symbols);

		return $symbols;
	}

	/**
	 * @return array List of countries with selected currency.
	 */
	public static function countries()
	{
		$countries = [
			'AED' => __('United Arab Emirates dirham', 'jigoshop-ecommerce'),
			'AFN' => __('Afghanistan Afghani', 'jigoshop-ecommerce'),
			'ALL' => __('Albania Lek', 'jigoshop-ecommerce'),
			'ANG' => __('Netherlands Antilles Guilder', 'jigoshop-ecommerce'),
			'ARS' => __('Argentina Peso', 'jigoshop-ecommerce'),
			'AUD' => __('Australia Dollar', 'jigoshop-ecommerce'),
			'AWG' => __('Aruba Guilder', 'jigoshop-ecommerce'),
			'AZN' => __('Azerbaijan New Manat', 'jigoshop-ecommerce'),
			'BAM' => __('Bosnia and Herzegovina Convertible Marka', 'jigoshop-ecommerce'),
			'BBD' => __('Barbados Dollar', 'jigoshop-ecommerce'),
			'BGN' => __('Bulgaria Lev', 'jigoshop-ecommerce'),
			'BMD' => __('Bermuda Dollar', 'jigoshop-ecommerce'),
			'BND' => __('Brunei Darussalam Dollar', 'jigoshop-ecommerce'),
			'BOB' => __('Bolivia Boliviano', 'jigoshop-ecommerce'),
			'BRL' => __('Brazil Real', 'jigoshop-ecommerce'),
			'BSD' => __('Bahamas Dollar', 'jigoshop-ecommerce'),
			'BWP' => __('Botswana Pula', 'jigoshop-ecommerce'),
			'BYR' => __('Belarus Ruble', 'jigoshop-ecommerce'),
			'BZD' => __('Belize Dollar', 'jigoshop-ecommerce'),
			'CAD' => __('Canada Dollar', 'jigoshop-ecommerce'),
			'CHF' => __('Switzerland Franc', 'jigoshop-ecommerce'),
			'CLP' => __('Chile Peso', 'jigoshop-ecommerce'),
			'CNY' => __('China Yuan Renminbi', 'jigoshop-ecommerce'),
			'COP' => __('Colombia Peso', 'jigoshop-ecommerce'),
			'CRC' => __('Costa Rica Colon', 'jigoshop-ecommerce'),
			'CUP' => __('Cuba Peso', 'jigoshop-ecommerce'),
			'CZK' => __('Czech Republic Koruna', 'jigoshop-ecommerce'),
			'DKK' => __('Denmark Krone', 'jigoshop-ecommerce'),
			'DOP' => __('Dominican Republic Peso', 'jigoshop-ecommerce'),
			'EEK' => __('Estonia Kroon', 'jigoshop-ecommerce'),
			'EGP' => __('Egypt Pound', 'jigoshop-ecommerce'),
			'EUR' => __('Euro Member Countries', 'jigoshop-ecommerce'),
			'FJD' => __('Fiji Dollar', 'jigoshop-ecommerce'),
			'FKP' => __('Falkland Islands', 'jigoshop-ecommerce'),
			'GBP' => __('United Kingdom Pound', 'jigoshop-ecommerce'),
			'GEL' => __('Georgian Lari', 'jigoshop-ecommerce'),
			'GGP' => __('Guernsey Pound', 'jigoshop-ecommerce'),
			'GHC' => __('Ghana Cedis', 'jigoshop-ecommerce'),
			'GIP' => __('Gibraltar Pound', 'jigoshop-ecommerce'),
			'GTQ' => __('Guatemala Quetzal', 'jigoshop-ecommerce'),
			'GYD' => __('Guyana Dollar', 'jigoshop-ecommerce'),
			'HKD' => __('Hong Kong Dollar', 'jigoshop-ecommerce'),
			'HNL' => __('Honduras Lempira', 'jigoshop-ecommerce'),
			'HRK' => __('Croatia Kuna', 'jigoshop-ecommerce'),
			'HUF' => __('Hungary Forint', 'jigoshop-ecommerce'),
			'IDR' => __('Indonesia Rupiah', 'jigoshop-ecommerce'),
			'ILS' => __('Israel Shekel', 'jigoshop-ecommerce'),
			'IMP' => __('Isle of Man Pound', 'jigoshop-ecommerce'),
			'INR' => __('India Rupee', 'jigoshop-ecommerce'),
			'IRR' => __('Iran Rial', 'jigoshop-ecommerce'),
			'ISK' => __('Iceland Krona', 'jigoshop-ecommerce'),
			'JEP' => __('Jersey Pound', 'jigoshop-ecommerce'),
			'JMD' => __('Jamaica Dollar', 'jigoshop-ecommerce'),
			'JPY' => __('Japan Yen', 'jigoshop-ecommerce'),
			'KGS' => __('Kyrgyzstan Som', 'jigoshop-ecommerce'),
			'KHR' => __('Cambodia Riel', 'jigoshop-ecommerce'),
			'KPW' => __('North Korea Won', 'jigoshop-ecommerce'),
			'KRW' => __('South Korea Won', 'jigoshop-ecommerce'),
			'KYD' => __('Cayman Islands Dollar', 'jigoshop-ecommerce'),
			'KZT' => __('Kazakhstan Tenge', 'jigoshop-ecommerce'),
			'LAK' => __('Laos Kip', 'jigoshop-ecommerce'),
			'LBP' => __('Lebanon Pound', 'jigoshop-ecommerce'),
			'LKR' => __('Sri Lanka Rupee', 'jigoshop-ecommerce'),
			'LRD' => __('Liberia Dollar', 'jigoshop-ecommerce'),
			'LTL' => __('Lithuania Litas', 'jigoshop-ecommerce'),
			'LVL' => __('Latvia Lat', 'jigoshop-ecommerce'),
			'MAD' => __('Moroccan Dirham', 'jigoshop-ecommerce'),
			'MKD' => __('Macedonia Denar', 'jigoshop-ecommerce'),
			'MNT' => __('Mongolia Tughrik', 'jigoshop-ecommerce'),
			'MUR' => __('Mauritius Rupee', 'jigoshop-ecommerce'),
			'MXN' => __('Mexico Peso', 'jigoshop-ecommerce'),
			'MYR' => __('Malaysia Ringgit', 'jigoshop-ecommerce'),
			'MZN' => __('Mozambique Metical', 'jigoshop-ecommerce'),
			'NAD' => __('Namibia Dollar', 'jigoshop-ecommerce'),
			'NGN' => __('Nigeria Naira', 'jigoshop-ecommerce'),
			'NIO' => __('Nicaragua Cordoba', 'jigoshop-ecommerce'),
			'NOK' => __('Norway Krone', 'jigoshop-ecommerce'),
			'NPR' => __('Nepal Rupee', 'jigoshop-ecommerce'),
			'NZD' => __('New Zealand Dollar', 'jigoshop-ecommerce'),
			'OMR' => __('Oman Rial', 'jigoshop-ecommerce'),
			'PAB' => __('Panama Balboa', 'jigoshop-ecommerce'),
			'PEN' => __('Peru Nuevo Sol', 'jigoshop-ecommerce'),
			'PHP' => __('Philippines Peso', 'jigoshop-ecommerce'),
			'PKR' => __('Pakistan Rupee', 'jigoshop-ecommerce'),
			'PLN' => __('Poland Zloty &#122;&#322;', 'jigoshop-ecommerce'),
			'PYG' => __('Paraguay Guarani', 'jigoshop-ecommerce'),
			'QAR' => __('Qatar Riyal', 'jigoshop-ecommerce'),
			'RON' => __('Romania New Leu', 'jigoshop-ecommerce'),
			'RSD' => __('Serbia Dinar', 'jigoshop-ecommerce'),
			'RUB' => __('Russia Ruble', 'jigoshop-ecommerce'),
			'SAR' => __('Saudi Arabia Riyal', 'jigoshop-ecommerce'),
			'SBD' => __('Solomon Islands Dollar', 'jigoshop-ecommerce'),
			'SCR' => __('Seychelles Rupee', 'jigoshop-ecommerce'),
			'SEK' => __('Sweden Krona', 'jigoshop-ecommerce'),
			'SGD' => __('Singapore Dollar', 'jigoshop-ecommerce'),
			'SHP' => __('Saint Helena Pound', 'jigoshop-ecommerce'),
			'SOS' => __('Somalia Shilling', 'jigoshop-ecommerce'),
			'SRD' => __('Suriname Dollar', 'jigoshop-ecommerce'),
			'SVC' => __('El Salvador Colon', 'jigoshop-ecommerce'),
			'SYP' => __('Syria Pound', 'jigoshop-ecommerce'),
			'THB' => __('Thailand Baht', 'jigoshop-ecommerce'),
			'TRL' => __('Turkey Lira', 'jigoshop-ecommerce'),
			'TRY' => __('Turkey Lira', 'jigoshop-ecommerce'),
			'TTD' => __('Trinidad and Tobago Dollar', 'jigoshop-ecommerce'),
			'TVD' => __('Tuvalu Dollar', 'jigoshop-ecommerce'),
			'TWD' => __('Taiwan New Dollar', 'jigoshop-ecommerce'),
			'UAH' => __('Ukraine Hryvna', 'jigoshop-ecommerce'),
			'USD' => __('United States Dollar', 'jigoshop-ecommerce'),
			'UYU' => __('Uruguay Peso', 'jigoshop-ecommerce'),
			'UZS' => __('Uzbekistan Som', 'jigoshop-ecommerce'),
			'VEF' => __('Venezuela Bolivar Fuerte', 'jigoshop-ecommerce'),
			'VND' => __('Viet Nam Dong', 'jigoshop-ecommerce'),
			'XCD' => __('East Caribbean Dollar', 'jigoshop-ecommerce'),
			'YER' => __('Yemen Rial', 'jigoshop-ecommerce'),
			'ZAR' => __('South Africa Rand', 'jigoshop-ecommerce'),
			'ZWD' => __('Zimbabwe Dollar', 'jigoshop-ecommerce'),
        ];

		asort($countries);

		return $countries;
	}

	/**
	 * @param string $symbol
	 */
	public static function setSymbol($symbol)
	{
		self::$symbol = $symbol;
	}

	/**
	 * @param string $code
	 */
	public static function setCode($code)
	{
		self::$code = $code;
	}

	/**
	 * @param string $format
	 */
	public static function setFormat($format)
	{
		self::$format = $format;
	}

	public static function positions()
	{
		$symbol = self::symbol();
		$separator = self::decimalSeparator();
		$code = self::code();

		return [
			'%1$s%3$s' => sprintf('%1$s0%2$s00', $symbol, $separator),// symbol.'0'.separator.'00'
			'%1$s %3$s' => sprintf('%1$s 0%2$s00', $symbol, $separator),// symbol.' 0'.separator.'00'
			'%3$s%1$s' => sprintf('0%2$s00%1$s', $symbol, $separator),// '0'.separator.'00'.symbol
			'%3$s %1$s' => sprintf('0%2$s00 %1$s', $symbol, $separator),// '0'.separator.'00 '.symbol
			'%2$s%3$s' => sprintf('%1$s0%2$s00', $code, $separator),// code.'0'.separator.'00'
			'%2$s %3$s' => sprintf('%1$s 0%2$s00', $code, $separator),// code.' 0'.separator.'00'
			'%3$s%2$s' => sprintf('0%2$s00%1$s', $code, $separator),// '0'.separator.'00'.code
			'%3$s %2$s' => sprintf('0%2$s00 %1$s', $code, $separator),// '0'.separator.'00 '.code
			'%1$s%3$s%2$s' => sprintf('%1$s0%2$s00%3$s', $symbol, $separator, $code),// symbol.'0'.separator.'00'.code
			'%1$s %3$s %2$s' => sprintf('%1$s 0%2$s00 %3$s', $symbol, $separator, $code),// symbol.' 0'.separator.'00 '.code
			'%2$s%3$s%1$s' => sprintf('%3$s0%2$s00%1$s', $symbol, $separator, $code),// code.'0'.separator.'00'.symbol
			'%2$s %3$s %1$s' => sprintf('%3$s 0%2$s00 %1$s', $symbol, $separator, $code),// code.' 0'.separator.'00 '.symbol
        ];
	}

    public static function addStaticFilters()
    {
        if(self::code() == 'CZK') {
            $round = function($price) {
                return round($price);
            };
            add_filter('jigoshop\product\get_price', $round, 99);
            add_filter('jigoshop\shipping\get_price', $round, 99);
            add_filter('jigoshop\service\tax\get_tax', function($tax) {
                return array_map(function($item) {
                    return round($item);
                }, $tax);
            }, 99);
            add_filter('jigoshop\entity\coupon\get_discount', function($discount) /** @var $discount Discount */{
                $discount->setAmount(round($discount->getAmount()));

                return $discount;
            }, 99);
        }
	}
}
