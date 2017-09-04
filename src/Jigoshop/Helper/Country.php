<?php

namespace Jigoshop\Helper;

use Jigoshop\Core\Options as CoreOptions;

/**
 * Country helper.
 *
 * @package Jigoshop\Helper
 */
class Country
{
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
            'SS' => 'South Sudan',
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
            'BV' => 'Bouvet Island',
            'GS' => 'South Georgia/Sandwich Islands',
            'HM' => 'Heard Island and McDonald Islands',
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
            'BQ' => 'Bonaire, Sint Eustatius and Saba',
            'BS' => 'Bahamas',
            'BZ' => 'Belize',
            'CA' => 'Canada',
            'CR' => 'Costa Rica',
            'CU' => 'Cuba',
            'CW' => 'Curaçao',
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
            'SX' => 'Sint Maarten (Dutch part)',
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

    private static $countriesThreeLetters = [
        'AF' => 'AFG',
        'AL' => 'ALB',
        'DZ' => 'DZA',
        'AS' => 'ASM',
        'AD' => 'AND',
        'AO' => 'AGO',
        'AI' => 'AIA',
        'AQ' => 'ATA',
        'AG' => 'ATG',
        'AR' => 'ARG',
        'AM' => 'ARM',
        'AW' => 'ABW',
        'AU' => 'AUS',
        'AT' => 'AUT',
        'AZ' => 'AZE',
        'BS' => 'BHS',
        'BH' => 'BHR',
        'BD' => 'BGD',
        'BB' => 'BRB',
        'BY' => 'BLR',
        'BE' => 'BEL',
        'BZ' => 'BLZ',
        'BJ' => 'BEN',
        'BM' => 'BMU',
        'BT' => 'BTN',
        'BO' => 'BOL',
        'BO' => 'BOL',
        'BA' => 'BIH',
        'BW' => 'BWA',
        'BV' => 'BVT',
        'BR' => 'BRA',
        'IO' => 'IOT',
        'BN' => 'BRN',
        'BN' => 'BRN',
        'BG' => 'BGR',
        'BF' => 'BFA',
        'BI' => 'BDI',
        'KH' => 'KHM',
        'CM' => 'CMR',
        'CA' => 'CAN',
        'CV' => 'CPV',
        'KY' => 'CYM',
        'CF' => 'CAF',
        'TD' => 'TCD',
        'CL' => 'CHL',
        'CN' => 'CHN',
        'CX' => 'CXR',
        'CC' => 'CCK',
        'CO' => 'COL',
        'KM' => 'COM',
        'CG' => 'COG',
        'CD' => 'COD',
        'CK' => 'COK',
        'CR' => 'CRI',
        'CI' => 'CIV',
        'CI' => 'CIV',
        'HR' => 'HRV',
        'CU' => 'CUB',
        'CY' => 'CYP',
        'CZ' => 'CZE',
        'DK' => 'DNK',
        'DJ' => 'DJI',
        'DM' => 'DMA',
        'DO' => 'DOM',
        'EC' => 'ECU',
        'EG' => 'EGY',
        'SV' => 'SLV',
        'GQ' => 'GNQ',
        'ER' => 'ERI',
        'EE' => 'EST',
        'ET' => 'ETH',
        'FK' => 'FLK',
        'FO' => 'FRO',
        'FJ' => 'FJI',
        'FI' => 'FIN',
        'FR' => 'FRA',
        'GF' => 'GUF',
        'PF' => 'PYF',
        'TF' => 'ATF',
        'GA' => 'GAB',
        'GM' => 'GMB',
        'GE' => 'GEO',
        'DE' => 'DEU',
        'GH' => 'GHA',
        'GI' => 'GIB',
        'GR' => 'GRC',
        'GL' => 'GRL',
        'GD' => 'GRD',
        'GP' => 'GLP',
        'GU' => 'GUM',
        'GT' => 'GTM',
        'GG' => 'GGY',
        'GN' => 'GIN',
        'GW' => 'GNB',
        'GY' => 'GUY',
        'HT' => 'HTI',
        'HM' => 'HMD',
        'VA' => 'VAT',
        'HN' => 'HND',
        'HK' => 'HKG',
        'HU' => 'HUN',
        'IS' => 'ISL',
        'IN' => 'IND',
        'ID' => 'IDN',
        'IR' => 'IRN',
        'IQ' => 'IRQ',
        'IE' => 'IRL',
        'IM' => 'IMN',
        'IL' => 'ISR',
        'IT' => 'ITA',
        'JM' => 'JAM',
        'JP' => 'JPN',
        'JE' => 'JEY',
        'JO' => 'JOR',
        'KZ' => 'KAZ',
        'KE' => 'KEN',
        'KI' => 'KIR',
        'KP' => 'PRK',
        'KR' => 'KOR',
        'KR' => 'KOR',
        'KW' => 'KWT',
        'KG' => 'KGZ',
        'LA' => 'LAO',
        'LV' => 'LVA',
        'LB' => 'LBN',
        'LS' => 'LSO',
        'LR' => 'LBR',
        'LY' => 'LBY',
        'LY' => 'LBY',
        'LI' => 'LIE',
        'LT' => 'LTU',
        'LU' => 'LUX',
        'MO' => 'MAC',
        'MK' => 'MKD',
        'MG' => 'MDG',
        'MW' => 'MWI',
        'MY' => 'MYS',
        'MV' => 'MDV',
        'ML' => 'MLI',
        'MT' => 'MLT',
        'MH' => 'MHL',
        'MQ' => 'MTQ',
        'MR' => 'MRT',
        'MU' => 'MUS',
        'YT' => 'MYT',
        'MX' => 'MEX',
        'FM' => 'FSM',
        'MD' => 'MDA',
        'MC' => 'MCO',
        'MN' => 'MNG',
        'ME' => 'MNE',
        'MS' => 'MSR',
        'MA' => 'MAR',
        'MZ' => 'MOZ',
        'MM' => 'MMR',
        'MM' => 'MMR',
        'NA' => 'NAM',
        'NR' => 'NRU',
        'NP' => 'NPL',
        'NL' => 'NLD',
        'AN' => 'ANT',
        'NC' => 'NCL',
        'NZ' => 'NZL',
        'NI' => 'NIC',
        'NE' => 'NER',
        'NG' => 'NGA',
        'NU' => 'NIU',
        'NF' => 'NFK',
        'MP' => 'MNP',
        'NO' => 'NOR',
        'OM' => 'OMN',
        'PK' => 'PAK',
        'PW' => 'PLW',
        'PS' => 'PSE',
        'PA' => 'PAN',
        'PG' => 'PNG',
        'PY' => 'PRY',
        'PE' => 'PER',
        'PH' => 'PHL',
        'PN' => 'PCN',
        'PL' => 'POL',
        'PT' => 'PRT',
        'PR' => 'PRI',
        'QA' => 'QAT',
        'RE' => 'REU',
        'RO' => 'ROU',
        'RU' => 'RUS',
        'RU' => 'RUS',
        'RW' => 'RWA',
        'SH' => 'SHN',
        'KN' => 'KNA',
        'LC' => 'LCA',
        'PM' => 'SPM',
        'VC' => 'VCT',
        'VC' => 'VCT',
        'VC' => 'VCT',
        'WS' => 'WSM',
        'SM' => 'SMR',
        'ST' => 'STP',
        'SA' => 'SAU',
        'SN' => 'SEN',
        'RS' => 'SRB',
        'SC' => 'SYC',
        'SL' => 'SLE',
        'SG' => 'SGP',
        'SK' => 'SVK',
        'SI' => 'SVN',
        'SB' => 'SLB',
        'SO' => 'SOM',
        'ZA' => 'ZAF',
        'GS' => 'SGS',
        'ES' => 'ESP',
        'LK' => 'LKA',
        'SD' => 'SDN',
        'SR' => 'SUR',
        'SJ' => 'SJM',
        'SZ' => 'SWZ',
        'SE' => 'SWE',
        'CH' => 'CHE',
        'SY' => 'SYR',
        'TW' => 'TWN',
        'TW' => 'TWN',
        'TJ' => 'TJK',
        'TZ' => 'TZA',
        'TH' => 'THA',
        'TL' => 'TLS',
        'TG' => 'TGO',
        'TK' => 'TKL',
        'TO' => 'TON',
        'TT' => 'TTO',
        'TT' => 'TTO',
        'TN' => 'TUN',
        'TR' => 'TUR',
        'TM' => 'TKM',
        'TC' => 'TCA',
        'TV' => 'TUV',
        'UG' => 'UGA',
        'UA' => 'UKR',
        'AE' => 'ARE',
        'GB' => 'GBR',
        'US' => 'USA',
        'UM' => 'UMI',
        'UY' => 'URY',
        'UZ' => 'UZB',
        'VU' => 'VUT',
        'VE' => 'VEN',
        'VE' => 'VEN',
        'VN' => 'VNM',
        'VN' => 'VNM',
        'VG' => 'VGB',
        'VI' => 'VIR',
        'WF' => 'WLF',
        'EH' => 'ESH',
        'YE' => 'YEM',
        'ZM' => 'ZMB',
        'ZW' => 'ZWE'
    ];

//   w
	protected static $states = [
		// Albania: Prefectures ("qarks")
		'AL' => [
			'BER' => 'Berat',
			'DIB' => 'Dibër',
			'DUR' => 'Durrës',
			'ELB' => 'Elbasan',
			'FIE' => 'Fier',
			'GJI' => 'Gjirokastër',
			'KOR' => 'Korçë',
			'KUK' => 'Kukës',
			'LEZ' => 'Lezhë',
			'SHK' => 'Shkodër',
			'TIR' => 'Tiranë',
			'VLO' => 'Vlorë'
        ],
		'AU' => [
			'ACT' => 'Australian Capital Territory',
			'NSW' => 'New South Wales',
			'NT' => 'Northern Territory',
			'QLD' => 'Queensland',
			'SA' => 'South Australia',
			'TAS' => 'Tasmania',
			'VIC' => 'Victoria',
			'WA' => 'Western Australia'
        ],
		'BR' => [
			'AC' => 'Acre',
			'AL' => 'Alagoas',
			'AM' => 'Amazonas',
			'AP' => 'Amapá',
			'BA' => 'Bahia',
			'CE' => 'Ceará',
			'DF' => 'Distrito federal',
			'ES' => 'Espírito santo',
			'GO' => 'Goiás',
			'MA' => 'Maranhão',
			'MG' => 'Minas gerais',
			'MS' => 'Mato grosso do sul',
			'MT' => 'Mato grosso',
			'PA' => 'Pará',
			'PB' => 'Paraiba',
			'PE' => 'Pernambuco',
			'PI' => 'Piauí',
			'PR' => 'Paraná',
			'RJ' => 'Rio de janeiro',
			'RN' => 'Rio grande do norte',
			'RO' => 'Rondônia',
			'RR' => 'Roraima',
			'RS' => 'Rio grande do sul',
			'SC' => 'Santa catarina',
			'SE' => 'Sergipe',
			'SP' => 'São paulo',
			'TO' => 'Tocantins'
        ],
		'CA' => [
			'AB' => 'Alberta',
			'BC' => 'British Columbia',
			'MB' => 'Manitoba',
			'NB' => 'New Brunswick',
			'NL' => 'Newfoundland',
			'NS' => 'Nova Scotia',
			'NT' => 'Northwest Territories',
			'NU' => 'Nunavut',
			'ON' => 'Ontario',
			'PE' => 'Prince Edward Island',
			'QC' => 'Quebec',
			'SK' => 'Saskatchewan',
			'YT' => 'Yukon Territory'
        ],
		// Switzerland: Cantons
		'CH' => [
			'AG' => 'Aargau',
			'AI' => 'Appenzell Innerrhoden',
			'AR' => 'Appenzell Ausserrhoden',
			'BE' => 'Bern',
			'BL' => 'Basel-Landschaft',
			'BS' => 'Basel-Stadt',
			'FR' => 'Freiburg',
			'GE' => 'Genf',
			'GL' => 'Glarus',
			'GR' => 'Graubünden',
			'JU' => 'Jura',
			'LU' => 'Luzern',
			'NE' => 'Neuenburg',
			'NW' => 'Nidwalden',
			'OW' => 'Obwalden',
			'SG' => 'St. Gallen',
			'SH' => 'Schaffhausen',
			'SO' => 'Solothurn',
			'SZ' => 'Schwyz',
			'TG' => 'Thurgau',
			'TI' => 'Tessin',
			'UR' => 'Uri',
			'VD' => 'Waadt',
			'VS' => 'Wallis',
			'ZG' => 'Zug',
			'ZH' => 'Zürich'
        ],
		// Spain: Provinces
		'ES' => [
			'AA' => 'Álava',
			'AB' => 'Albacete',
			'AN' => 'Alicante',
			'AM' => 'Almería',
			'AS' => 'Asturias',
			'AV' => 'Ávila',
			'BD' => 'Badajoz',
			'BL' => 'Baleares',
			'BR' => 'Barcelona',
			'BU' => 'Burgos',
			'CC' => 'Cáceres',
			'CD' => 'Cádiz',
			'CN' => 'Cantabria',
			'CS' => 'Castellón',
			'CE' => 'Ceuta',
			'CR' => 'Ciudad Real',
			'CO' => 'Córdoba',
			'CU' => 'Cuenca',
			'GN' => 'Gerona',
			'GD' => 'Granada',
			'GJ' => 'Guadalajara',
			'GP' => 'Guipúzcoa',
			'HL' => 'Huelva',
			'HS' => 'Huesca',
			'JA' => 'Jaén',
			'AC' => 'La Coruña',
			'LR' => 'La Rioja',
			'LP' => 'Las Palmas',
			'LN' => 'León',
			'LD' => 'Lérida',
			'LG' => 'Lugo',
			'MD' => 'Madrid',
			'MG' => 'Málaga',
			'ME' => 'Melilla',
			'MR' => 'Murcia',
			'NV' => 'Navarra',
			'OR' => 'Orense',
			'PL' => 'Palencia',
			'PV' => 'Pontevedra',
			'SL' => 'Salamanca',
			'SC' => 'Santa Cruz de Tenerife',
			'SG' => 'Segovia',
			'SV' => 'Sevilla',
			'SR' => 'Soria',
			'TG' => 'Tarragona',
			'TE' => 'Teruel',
			'TD' => 'Toledo',
			'VN' => 'Valencia',
			'VD' => 'Valladolid',
			'VZ' => 'Vizcaya',
			'ZM' => 'Zamora',
			'ZG' => 'Zaragoza'
        ],
		// Czech Republic: Regions
		'CZ' => [
			'JC' => 'Jihoceský kraj [South Bohemian Region]',
			'JM' => 'Jihomoravský kraj [South Moravian Region]',
			'KA' => 'Karlovarský kraj [Karlovy Vary Region]',
			'KR' => 'Královéhradecký kraj [Hradec Králové Region]',
			'LI' => 'Liberecký kraj [Liberec Region]',
			'MO' => 'Moravskoslezský kraj [Moravian-Silesian Region]',
			'OL' => 'Olomoucký kraj [Olomouc Region]',
			'PA' => 'Pardubický kraj [Pardubice Region]',
			'PL' => 'Plzenský kraj [Plzen Region]',
			'PR' => 'Praha (Hlavni mesto Praha) [Prague]',
			'ST' => 'Stredoceský kraj [Central Bohemian Region]',
			'US' => 'Ústecký kraj [Ústí Region]',
			'VY' => 'Vysocina',
			'ZL' => 'Zlínský kraj [Zlín Region]'
        ],
		// Germany: Federal States
		'DE' => [
			'NDS' => 'Niedersachsen',
			'BAW' => 'Baden-Württemberg',
			'BAY' => 'Bayern',
			'BER' => 'Berlin',
			'BRG' => 'Brandenburg',
			'BRE' => 'Bremen',
			'HAM' => 'Hamburg',
			'HES' => 'Hessen',
			'MEC' => 'Mecklenburg-Vorpommern',
			'NRW' => 'Nordrhein-Westfalen',
			'RHE' => 'Rheinland-Pfalz',
			'SAR' => 'Saarland',
			'SAS' => 'Sachsen',
			'SAC' => 'Sachsen-Anhalt',
			'SCN' => 'Schleswig-Holstein',
			'THE' => 'Thüringen'
        ],
		// Finland: Regions
		'FI' => [
			'ÅAL' => 'Åland',
			'EKA' => 'Etelä-Karjala [South Karelia]',
			'EPO' => 'Etelä-Pohjanmaa [South Ostrobothnia]',
			'ESA' => 'Etelä-Savo',
			'KAI' => 'Kainuu',
			'KHA' => 'Kanta-Häme',
			'KPO' => 'Keski-Pohjanmaa [Central Ostrobothnia]',
			'KSO' => 'Keski-Suomi [Central Finland]',
			'KYM' => 'Kymenlaakso (Kymmenedalen)',
			'LAP' => 'Lappi [Lapland]',
			'PHA' => 'Päijät-Häme',
			'PIR' => 'Pirkanmaa',
			'POH' => 'Pohjanmaa [Ostrobothnia]',
			'PKA' => 'Pohjois-Karjala [North Karelia]',
			'PPO' => 'Pohjois-Pohjanmaa [North Ostrobothnia]',
			'PSA' => 'Pohjois-Savo',
			'SAT' => 'Satakunta',
			'UUS' => 'Uusimaa (Nyland)',
			'VSS' => 'Varsinais-Suomi (Egentliga Finland)'
        ],
		// France: Regions
		'FR' => [
			'ALS' => 'Alsace',
			'AQU' => 'Aquitaine',
			'AUV' => 'Auvergne',
			'BAS' => 'Basse-Normandie [Lower Normandy]',
			'BOU' => 'Bourgogne [Burgundy]',
			'BRE' => 'Bretagne [Brittany]',
			'CEN' => 'Centre',
			'CHA' => 'Champagne - Ardenne',
			'COR' => 'Corse',
			'FRA' => 'Franche-Comté',
			'HAU' => 'Haute-Normandie [Upper Normandy]',
			'ILE' => 'Île-de-France',
			'LAN' => 'Languedoc - Roussillon',
			'LIM' => 'Limousin',
			'LOR' => 'Lorraine',
			'MID' => 'Midi - Pyrénées',
			'NOR' => 'Nord - Pas-de-Calais',
			'PAY' => 'Pays de la Loire',
			'PIC' => 'Picardie',
			'POI' => 'Poitou - Charentes',
			'PRO' => 'Provence - Alpes - Côte d\'Azur',
			'RHO' => 'Rhône - Alpes'
        ],
        // United Kingdom: Regions
        'GB' => [
            //England Regions
            'ENG' => 'England',
            'BKM' => 'Buckinghamshire',
            'CAM' => 'Cambridgeshire',
            'CMA' => 'Cumbria',
            'DBY' => 'Derbyshire',
            'DEV' => 'Devon',
            'DOR' => 'Dorset',
            'ESX' => 'East Sussex',
            'ESS' => 'Essex',
            'GLS' => 'Gloucestershire',
            'HAM' => 'Hampshire',
            'HRT' => 'Hertfordshire',
            'KEN' => 'Kent',
            'LAN' => 'Lancashire',
            'LEC' => 'Leicestershire',
            'LIN' => 'Lincolnshire',
            'NFK' => 'Norfolk',
            'NYK' => 'North Yorkshire',
            'NTH' => 'Northamptonshire',
            'NTT' => 'Nottinghamshire',
            'OXF' => 'Oxfordshire',
            'SOM' => 'Somerset',
            'STS' => 'Staffordshire',
            'SFK' => 'Suffolk',
            'SRY' => 'Surrey',
            'WAR' => 'Warwickshire',
            'WSX' => 'West Sussex',
            'WOR' => 'Worcestershire',
            'LND' => 'London, City of',
            'BDG' => 'Barking and Dagenham',
            'BNE' => 'Barnet',
            'BEX' => 'Bexley',
            'BEN' => 'Brent',
            'BRY' => 'Bromley',
            'CMD' => 'Camden',
            'CRY' => 'Croydon',
            'EAL' => 'Ealing',
            'ENF' => 'Enfield',
            'GRE' => 'Greenwich',
            'HCK' => 'Hackney',
            'HMF' => 'Hammersmith and Fulham',
            'HRY' => 'Haringey',
            'HRW' => 'Harrow',
            'HAV' => 'Havering',
            'HIL' => 'Hillingdon',
            'HNS' => 'Hounslow',
            'ISL' => 'Islington',
            'KEC' => 'Kensington and Chelsea',
            'KTT' => 'Kingston upon Thames',
            'LBH' => 'Lambeth',
            'LEW' => 'Lewisham',
            'MRT' => 'Merton',
            'NWM' => 'Newham',
            'RDB' => 'Redbridge',
            'RIC' => 'Richmond upon Thames',
            'SWK' => 'Southwark',
            'STN' => 'Sutton',
            'TWH' => 'Tower Hamlets',
            'WFT' => 'Waltham Forest',
            'WND' => 'Wandsworth',
            'WSM' => 'Westminster',
            'BNS' => 'Barnsley',
            'BIR' => 'Birmingham',
            'BOL' => 'Bolton',
            'BRD' => 'Bradford',
            'BUR' => 'Bury',
            'CLD' => 'Calderdale',
            'COV' => 'Coventry',
            'DNC' => 'Doncaster',
            'DUD' => 'Dudley',
            'GAT' => 'Gateshead',
            'KIR' => 'Kirklees',
            'KWL' => 'Knowsley',
            'LDS' => 'Leeds',
            'LIV' => 'Liverpool',
            'MAN' => 'Manchester',
            'NET' => 'Newcastle upon Tyne',
            'NTY' => 'North Tyneside',
            'OLD' => 'Oldham',
            'RCH' => 'Rochdale',
            'ROT' => 'Rotherham',
            'SHN' => 'St. Helens',
            'SLF' => 'Salford',
            'SAW' => 'Sandwell',
            'SFT' => 'Sefton',
            'SHF' => 'Sheffield',
            'SOL' => 'Solihull',
            'STY' => 'South Tyneside',
            'SKP' => 'Stockport',
            'SND' => 'Sunderland',
            'TAM' => 'Tameside',
            'TRF' => 'Trafford',
            'WKF' => 'Wakefield',
            'WLL' => 'Walsall',
            'WGN' => 'Wigan',
            'WRL' => 'Wirral',
            'WLV' => 'Wolverhampton',
            'BAS' => 'Bath and North East Somerset',
            'BDF' => 'Bedford',
            'BBD' => 'Blackburn with Darwen',
            'BPL' => 'Blackpool',
            'BMH' => 'Bournemouth',
            'BRC' => 'Bracknell Forest',
            'BNH' => 'Brighton and Hove',
            'BST' => 'Bristol, City of',
            'CBF' => 'Central Bedfordshire',
            'CHE' => 'Cheshire East',
            'CHW' => 'Cheshire West and Chester',
            'CON' => 'Cornwall',
            'DAL' => 'Darlington',
            'DER' => 'Derby',
            'DUR' => 'Durham, County',
            'ERY' => 'East Riding of Yorkshire',
            'HAL' => 'Halton',
            'HPL' => 'Hartlepool',
            'HEF' => 'Herefordshire',
            'IOW' => 'Isle of Wight',
            'IOS' => 'Isles of Scilly',
            'KHL' => 'Kingston upon Hull',
            'LCE' => 'Leicester',
            'LUT' => 'Luton',
            'MDW' => 'Medway',
            'MDB' => 'Middlesbrough',
            'MIK' => 'Milton Keynes',
            'NEL' => 'North East Lincolnshire',
            'NLN' => 'North Lincolnshire',
            'NSM' => 'North Somerset',
            'NBL' => 'Northumberland',
            'NGM' => 'Nottingham',
            'PTE' => 'Peterborough',
            'PLY' => 'Plymouth',
            'POL' => 'Poole',
            'POR' => 'Portsmouth',
            'RDG' => 'Reading',
            'RCC' => 'Redcar and Cleveland',
            'RUT' => 'Rutland',
            'SHR' => 'Shropshire',
            'SLG' => 'Slough',
            'SGC' => 'South Gloucestershire',
            'STH' => 'Southampton',
            'SOS' => 'Southend-on-Sea',
            'STT' => 'Stockton-on-Tees',
            'STE' => 'Stoke-on-Trent',
            'SWD' => 'Swindon',
            'TFW' => 'Telford and Wrekin',
            'THR' => 'Thurrock',
            'TOB' => 'Torbay',
            'WRT' => 'Warrington',
            'WBK' => 'West Berkshire',
            'WIL' => 'Wiltshire',
            'WNM' => 'Windsor and Maidenhead',
            'WOK' => 'Wokingham',
            'YOR' => 'York',
            //Northen Ireland Regions
            'NIR' => 'Northern Ireland',
            'ANT' => 'Antrim',
            'ARD' => 'Ards',
            'ARM' => 'Armagh',
            'BLA' => 'Ballymena',
            'BLY' => 'Ballymoney',
            'BNB' => 'Banbridge',
            'BFS' => 'Belfast',
            'CKF' => 'Carrickfergus',
            'CSR' => 'Castlereagh',
            'CLR' => 'Coleraine',
            'CKT' => 'Cookstown',
            'CGV' => 'Craigavon',
            'DRY' => 'Derry',
            'DOW' => 'Down',
            'DGN' => 'Dungannon and South Tyrone',
            'FER' => 'Fermanagh',
            'LRN' => 'Larne',
            'LMV' => 'Limavady',
            'LSB' => 'Lisburn',
            'MFT' => 'Magherafelt',
            'MYL' => 'Moyle',
            'NYM' => 'Newry and Mourne District',
            'NTA' => 'Newtownabbey',
            'NDN' => 'North Down',
            'OMH' => 'Omagh',
            'STB' => 'Strabane',
            //Scotland Regions
            'SCT' => 'Scotland',
            'ABE' => 'Aberdeen City',
            'ABD' => 'Aberdeenshire',
            'ANS' => 'Angus',
            'AGB' => 'Argyll and Bute',
            'CLK' => 'Clackmannanshire',
            'DGY' => 'Dumfries and Galloway',
            'DND' => 'Dundee City',
            'EAY' => 'East Ayrshire',
            'EDU' => 'East Dunbartonshire',
            'ELN' => 'East Lothian',
            'ERW' => 'East Renfrewshire',
            'EDH' => 'Edinburgh, City of',
            'ELS' => 'Eilean Siar',
            'FAL' => 'Falkirk',
            'FIF' => 'Fife',
            'GLG' => 'Glasgow City',
            'HLD' => 'Highland',
            'IVC' => 'Inverclyde',
            'MLN' => 'Midlothian',
            'MRY' => 'Moray',
            'NAY' => 'North Ayrshire',
            'NLK' => 'North Lanarkshire',
            'ORK' => 'Orkney Islands',
            'PKN' => 'Perth and Kinross',
            'RFW' => 'Renfrewshire',
            'SCB' => 'Scottish Borders, The',
            'ZET' => 'Shetland Islands',
            'SAY' => 'South Ayrshire',
            'SLK' => 'South Lanarkshire',
            'STG' => 'Stirling',
            'WDU' => 'West Dunbartonshire',
            'WLN' => 'West Lothian',
            //Wales	Regions
            'WAL' => 'Wales',
            'BGW' => 'Blaenau Gwent',
            'BGE' => 'Bridgend [Pen-y-bont ar Ogwr GB-POG]',
            'CAY' => 'Caerphilly [Caerffili GB-CAF]',
            'CRF' => 'Cardiff [Caerdydd GB-CRD]',
            'CMN' => 'Carmarthenshire [Sir Gaerfyrddin GB-GFY]',
            'CGN' => 'Ceredigion [Sir Ceredigion]',
            'CWY' => 'Conwy',
            'DEN' => 'Denbighshire [Sir Ddinbych GB-DDB]',
            'FLN' => 'Flintshire [Sir y Fflint GB-FFL]',
            'GWN' => 'Gwynedd',
            'AGY' => 'Isle of Anglesey [Sir Ynys Môn GB-YNM]',
            'MTY' => 'Merthyr Tydfil [Merthyr Tudful GB-MTU]',
            'MON' => 'Monmouthshire [Sir Fynwy GB-FYN]',
            'NTL' => 'Neath Port Talbot [Castell-nedd Port Talbot GB-CTL]',
            'NWP' => 'Newport [Casnewydd GB-CNW]',
            'PEM' => 'Pembrokeshire [Sir Benfro GB-BNF]',
            'POW' => 'Powys',
            'RCT' => 'Rhondda, Cynon, Taff [Rhondda, Cynon, Taf]',
            'SWA' => 'Swansea [Abertawe GB-ATA]',
            'TOF' => 'Torfaen [Tor-faen]',
            'VGL' => 'Vale of Glamorgan, The [Bro Morgannwg GB-BMG]',
            'WRX' => 'Wrexham [Wrecsam GB-WRC]',
            'CHI' => 'Channel Islands'
        ],
		// Greece: Regions
		'GR' => [
			'AOR' => 'Ágio Óros [Mount Athos]',
			'AMT' => 'Anatolikí Makedonía & Thrakí [East Macedonia & Thrace]',
			'ATT' => 'Attikí [Attica]',
			'DEL' => 'Dytikí Elláda [Western Greece]',
			'DMD' => 'Dytikí Makedonía [West Macedonia]',
			'ION' => 'Iónia Nisiá [Ionian Islands]',
			'IPI' => 'Ípiros [Epirus]',
			'KMD' => 'Kedrikí Makedonía [Central Macedonia]',
			'KRI' => 'Kríti [Crete]',
			'NAI' => 'Nótio Aigaío [South Aegean]',
			'PEL' => 'Pelopónnisos [Peloponnese]',
			'SEL' => 'Stereá Elláda [Central Greece]',
			'THE' => 'Thessalía [Thessaly]',
			'VAI' => 'Vório Aigaío [Northern Aegean]'
        ],
		'HK' => [
			'HONG KONG' => 'Hong Kong Island',
			'KOWLOONG' => 'Kowloong',
			'NEW TERRITORIES' => 'New Territories'
        ],
		// Hungary: Counties
		'HU' => [
			'BAC' => 'Bács-Kiskun',
			'BAR' => 'Baranya',
			'BEK' => 'Békés',
			'BOR' => 'Borsod-Abaúj-Zemplén',
			'BUD' => 'Budapest',
			'CSO' => 'Csongrád',
			'FEJ' => 'Fejér',
			'GYO' => 'Gyor-Moson-Sopron',
			'HAJ' => 'Hajdú-Bihar',
			'HEV' => 'Heves',
			'JAS' => 'Jász-Nagykun-Szolnok',
			'KOM' => 'Komárom-Esztergom',
			'NOG' => 'Nógrád',
			'PES' => 'Pest',
			'SOM' => 'Somogy',
			'SZA' => 'Szabolcs-Szatmár-Bereg',
			'TOL' => 'Tolna',
			'VAS' => 'Vas',
			'VES' => 'Veszprém',
			'ZAL' => 'Zala'
        ],
		// Ireland: Counties
		'IE' => [
			'G' => 'Galway (incl. Galway City)',
			'LM' => 'Leitrim',
			'MO' => 'Mayo',
			'RN' => 'Roscommon',
			'SO' => 'Sligo',
			'CW' => 'Carlow',
			'D' => 'Dublin',
			'DR' => 'Dún Laoghaire-Rathdown',
			'FG' => 'Fingal',
			'KE' => 'Kildare',
			'KK' => 'Kilkenny',
			'LS' => 'Laois',
			'LD' => 'Longford',
			'LH' => 'Louth',
			'MH' => 'Meath',
			'OY' => 'Offaly',
			'SD' => 'South Dublin',
			'WH' => 'Westmeath',
			'WX' => 'Wexford',
			'WW' => 'Wicklow',
			'CE' => 'Clare',
			'C' => 'Cork (incl. Cork City)',
			'KY' => 'Kerry',
			'LK' => 'Limerick (incl. Limerick City)',
			'NT' => 'North Tipperary',
			'ST' => 'South Tipperary',
			'WD' => 'Waterford (incl. Waterford City)',
			'CN' => 'Cavan',
			'DL' => 'Donegal',
			'MIN' => 'Monaghan'
        ],
		// Netherlands: Provinces
		'NL' => [
			'D' => 'Drenthe',
			'Fl' => 'Flevoland',
			'Fr' => 'Friesland',
			'Gld' => 'Gelderland',
			'Gr' => 'Groningen',
			'L' => 'Limburg',
			'N-B' => 'Noord-Brabant',
			'N-H' => 'Noord-Holland',
			'O' => 'Overijssel',
			'U' => 'Utrecht',
			'Z' => 'Zeeland',
			'Z-H' => 'Zuid-Holland'
        ],
		// New Zealand: Regions
		'NZ' => [
			'AUK' => 'Auckland',
			'BOP' => 'Bay of Plenty',
			'CAN' => 'Canterbury',
			'GIS' => 'Gisborne',
			'HKB' => 'Hawke\'s Bay',
			'MWT' => 'Manawatu-Wanganui',
			'MBH' => 'Marlborough',
			'NSN' => 'Nelson',
			'NTL' => 'Northland',
			'OTA' => 'Otago',
			'STL' => 'Southland',
			'TKI' => 'Taranaki',
			'TAS' => 'Tasman',
			'WKO' => 'Waikato',
			'WGN' => 'Wellington',
			'WTC' => 'West Coast'
        ],
		// Philippines: Provinces
		'PH' => [
			'ABR' => 'Abra',
			'AGN' => 'Agusan del Norte',
			'AGS' => 'Agusan del Sur',
			'AKL' => 'Aklan',
			'ALB' => 'Albay',
			'ANT' => 'Antique',
			'APA' => 'Apayao',
			'AUR' => 'Aurora',
			'BAS' => 'Basilan',
			'BAN' => 'Bataan',
			'BTN' => 'Batanes',
			'BTG' => 'Batangas',
			'BEN' => 'Benguet',
			'BIL' => 'Biliran',
			'BOH' => 'Bohol',
			'BUK' => 'Bukidnon',
			'BUL' => 'Bulacan',
			'CAG' => 'Cagayan',
			'CAN' => 'Camarines Norte',
			'CAS' => 'Camarines Sur',
			'CAM' => 'Camiguin',
			'CAP' => 'Capiz',
			'CAT' => 'Catanduanes',
			'CAV' => 'Cavite',
			'CEB' => 'Cebu',
			'COM' => 'Compostela Valley',
			'NCO' => 'Cotabato',
			'DAV' => 'Davao del Norte',
			'DAS' => 'Davao del Sur',
			'DAC' => 'Davao Occidental', // TODO: Needs to be updated when ISO code is assigned
			'DAO' => 'Davao Oriental',
			'DIN' => 'Dinagat Islands',
			'EAS' => 'Eastern Samar',
			'GUI' => 'Guimaras',
			'IFU' => 'Ifugao',
			'ILN' => 'Ilocos Norte',
			'ILS' => 'Ilocos Sur',
			'ILI' => 'Iloilo',
			'ISA' => 'Isabela',
			'KAL' => 'Kalinga',
			'LUN' => 'La Union',
			'LAG' => 'Laguna',
			'LAN' => 'Lanao del Norte',
			'LAS' => 'Lanao del Sur',
			'LEY' => 'Leyte',
			'MAG' => 'Maguindanao',
			'MAD' => 'Marinduque',
			'MAS' => 'Masbate',
			'MSC' => 'Misamis Occidental',
			'MSR' => 'Misamis Oriental',
			'MOU' => 'Mountain Province',
			'NEC' => 'Negros Occidental',
			'NER' => 'Negros Oriental',
			'NSA' => 'Northern Samar',
			'NUE' => 'Nueva Ecija',
			'NUV' => 'Nueva Vizcaya',
			'MDC' => 'Occidental Mindoro',
			'MDR' => 'Oriental Mindoro',
			'PLW' => 'Palawan',
			'PAM' => 'Pampanga',
			'PAN' => 'Pangasinan',
			'QUE' => 'Quezon',
			'QUI' => 'Quirino',
			'RIZ' => 'Rizal',
			'ROM' => 'Romblon',
			'WSA' => 'Samar',
			'SAR' => 'Sarangani',
			'SIQ' => 'Siquijor',
			'SOR' => 'Sorsogon',
			'SCO' => 'South Cotabato',
			'SLE' => 'Southern Leyte',
			'SUK' => 'Sultan Kudarat',
			'SLU' => 'Sulu',
			'SUN' => 'Surigao del Norte',
			'SUR' => 'Surigao del Sur',
			'TAR' => 'Tarlac',
			'TAW' => 'Tawi-Tawi',
			'ZMB' => 'Zambales',
			'ZAN' => 'Zamboanga del Norte',
			'ZAS' => 'Zamboanga del Sur',
			'ZSI' => 'Zamboanga Sibugay',
			'MNL' => 'Metro Manila',
        ],
		'PL' => [
			'DS' => 'dolnośląskie',
			'KP' => 'kujawsko-pomorskie',
			'LU' => 'lubelskie',
			'LB' => 'lubuskie',
			'LD' => 'łódzkie',
			'MA' => 'małopolskie',
			'MZ' => 'mazowieckie',
			'OP' => 'opolskie',
			'PK' => 'podkarpackie',
			'PD' => 'podlaskie',
			'PM' => 'pomorskie',
			'SL' => 'śląskie',
			'SK' => 'świętokrzyskie',
			'WN' => 'warmińsko-mazurskie',
			'WP' => 'wielkopolskie',
			'ZP' => 'zachodniopomorskie',
        ],
		// Romania: Counties
		'RO' => [
			'ALB' => 'Alba',
			'ARA' => 'Arad',
			'ARG' => 'Argeș',
			'BAC' => 'Bacău',
			'BIH' => 'Bihor',
			'BIS' => 'Bistrița-Năsăud',
			'BOT' => 'Botoșani',
			'BRA' => 'Brăila',
			'BRS' => 'Brașov',
			'BUC' => 'București',
			'BUZ' => 'Buzău',
			'CAL' => 'Călărași',
			'CAR' => 'Caraș-Severin',
			'CLU' => 'Cluj',
			'CON' => 'Constanța',
			'COV' => 'Covasna',
			'DAM' => 'Dâmbovița',
			'DOL' => 'Dolj',
			'GAL' => 'Galați',
			'GIU' => 'Giurgiu',
			'GOR' => 'Gorj',
			'HAR' => 'Harghita',
			'HUN' => 'Hunedoara',
			'IAL' => 'Ialomița',
			'IAS' => 'Iași',
			'ILF' => 'Ilfov',
			'MAR' => 'Maramureș',
			'MEH' => 'Mehedinți',
			'MUR' => 'Mureș',
			'NEA' => 'Neamț',
			'OLT' => 'Olt',
			'PRA' => 'Prahova',
			'SAL' => 'Sălaj',
			'SAT' => 'Satu Mare',
			'SIB' => 'Sibiu',
			'SUC' => 'Suceava',
			'TEL' => 'Teleorman',
			'TIM' => 'Timiș',
			'TUL' => 'Tulcea',
			'VAL' => 'Vâlcea',
			'VAS' => 'Vaslui',
			'VRA' => 'Vrancea'
        ],
		// Serbia: Districts
		'SR' => [
			'BOR' => 'Bor',
			'BRA' => 'Branicevo',
			'GBE' => 'Grad Beograd',
			'JAB' => 'Jablanica',
			'KOL' => 'Kolubara',
			'MAC' => 'Macva',
			'MOR' => 'Moravica',
			'NIS' => 'Nišava',
			'PCI' => 'Pcinja',
			'PIR' => 'Pirot',
			'POD' => 'Podunavlje [Danube]',
			'POM' => 'Pomoravlje',
			'RSN' => 'Rasina',
			'RSK' => 'Raška',
			'SUM' => 'Šumadija',
			'TOP' => 'Toplica',
			'ZAJ' => 'Zajecar',
			'ZLA' => 'Zlatibor',
			'JBK' => 'Južna Backa',
			'JBN' => 'Južni Banat',
			'SBK' => 'Severna Backa',
			'SBN' => 'Severni Banat',
			'SRB' => 'Srednji Banat',
			'SRE' => 'Srem',
			'ZBK' => 'Zapadna Backa [West Backa]'
        ],
		// Sweden: Counties ("län")
		'SE' => [
			'BLE' => 'Blekinge län',
			'DAL' => 'Dalarnas län',
			'GAV' => 'Gävleborgs län',
			'GOT' => 'Gotlands län',
			'HAL' => 'Hallands län',
			'JAM' => 'Jämtlands län',
			'JON' => 'Jönköpings län',
			'KAL' => 'Kalmar län',
			'KRO' => 'Kronobergs län',
			'NOR' => 'Norrbottens län',
			'ORE' => 'Örebro län',
			'OST' => 'Östergötlands län',
			'SKA' => 'Skåne län',
			'SOD' => 'Södermanlands län',
			'STO' => 'Stockholms län',
			'UPP' => 'Uppsala län',
			'VAR' => 'Värmlands län',
			'VAS' => 'Västerbottens län',
			'VNL' => 'Västernorrlands län',
			'VML' => 'Västmanlands län',
			'VGO' => 'Västra Götalands län'
        ],
		'US' => [
			'AK' => 'Alaska',
			'AL' => 'Alabama',
			'AR' => 'Arkansas',
			'AZ' => 'Arizona',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DC' => 'District Of Columbia',
			'DE' => 'Delaware',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'IA' => 'Iowa',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'MA' => 'Massachusetts',
			'MD' => 'Maryland',
			'ME' => 'Maine',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MO' => 'Missouri',
			'MS' => 'Mississippi',
			'MT' => 'Montana',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'NE' => 'Nebraska',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NV' => 'Nevada',
			'NY' => 'New York',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VA' => 'Virginia',
			'VT' => 'Vermont',
			'WA' => 'Washington',
			'WI' => 'Wisconsin',
			'WV' => 'West Virginia',
			'WY' => 'Wyoming'
        ],
		'USAF' => [
			'AA' => 'Americas',
			'AE' => 'Europe',
			'AP' => 'Pacific'
        ]
    ];

	protected static $euCountries = [
		'AT' => 'Austria',
		'BE' => 'Belgium',
		'BG' => 'Bulgaria',
		'CY' => 'Cyprus',
		'CZ' => 'Czech Republic',
		'DK' => 'Denmark',
		'EE' => 'Estonia',
		'FI' => 'Finland',
		'FR' => 'France',
		'DE' => 'Germany',
		'GR' => 'Greece',
		'HU' => 'Hungary',
		'IE' => 'Ireland',
		'IT' => 'Italy',
		'LV' => 'Latvia',
		'LT' => 'Lithuania',
		'LU' => 'Luxembourg',
		'MT' => 'Malta',
		'NL' => 'Netherlands',
		'PL' => 'Poland',
		'PT' => 'Portugal',
		'RO' => 'Romania',
		'SK' => 'Slovakia',
		'SI' => 'Slovenia',
		'ES' => 'Spain',
		'SE' => 'Sweden',
		'GB' => 'United Kingdom'
    ];

	/** @var CoreOptions */
	private static $options;
	private static $cache = [];

	/**
	 * @param CoreOptions $options Options object.
	 */
	public static function setOptions($options)
	{
		self::$options = $options;
	}


    /**
     * @return array
     */
    public static function getContinents()
    {
        if(!isset(self::$cache['continents'])) {
            $continets = array_map(function($item) {
                return __($item, 'jigoshop-ecommerce');
            }, self::$continents);
            asort($continets, SORT_LOCALE_STRING);
            self::$cache['continents'] = $continets;
        }

        return self::$cache['continents'];
    }

    /**
     * @param string $countryCode
     * @return string
     */
    public static function getContinentByCountry($countryCode)
    {
        foreach (self::$countries as $continent => $countries) {
            if(isset($countries[$countryCode])) {
                return $continent;
            }
        }

        return '';
    }

	/**
	 * Returns list of available countries with translated names.
	 *
	 * Safe to use multiple times (uses cache to speed-up).
	 *
	 * @return array List of translated countries.
	 */
	public static function getAll()
	{
		if (!isset(self::$cache['countries'])) {
			$countries = array_map(function($countries) {
                return array_map(function ($item){
                    return __($item, 'jigoshop-ecommerce');
                }, $countries);
            }, self::$countries);
			$countries = array_merge(...array_values($countries));
			asort($countries, SORT_LOCALE_STRING);

			self::$cache['countries'] = $countries;
		}

		return self::$cache['countries'];
	}

	/**
	 * Returns list of all allowed countries with translated names.
	 *
	 * Safe to use multiple times (uses cache to speed-up).
	 *
	 * @return array List of allowed translated countries.
	 */
	public static function getAllowed()
	{
		if (!isset(self::$cache['allowed'])) {
			$countries = self::getAll();

			if (self::$options->get('shopping.restrict_selling_locations')) {
				$allowed = self::$options->get('shopping.selling_locations');
				$countries = array_intersect_key($countries, array_flip($allowed));
			}

			self::$cache['allowed'] = $countries;
		}

		return self::$cache['allowed'];
	}

	/**
	 * Returns translated name of a country.
	 *
	 * If country does not exists - returns empty string.
	 *
	 * @param $countryCode string Country code for name.
	 *
	 * @return string Country translated name.
	 */
	public static function getName($countryCode)
	{
		if (self::exists($countryCode)) {
			$all = self::getAll();

			return $all[$countryCode];
		}

		return '';
	}

    /**
     * Returns three letter code of a country.
     * 
     * If country does not exists - return empty string.
     * 
     * @param $countryCode string Two letter country code.
     * 
     * @return string Three letter country code.
     */
    public static function getThreeLetterCountryCode($countryCode) {
        return isset(self::$countriesThreeLetters[$countryCode]) ? self::$countriesThreeLetters[$countryCode] : '';
    }

	/**
	 * @param $countryCode string Country code to check.
	 *
	 * @return bool Whether the country exists.
	 */
	public static function exists($countryCode)
	{
        $countries = array_merge(...array_values(self::$countries));

		return isset($countries[$countryCode]);
	}

	/**
	 * @param $countryCode string Country code to check.
	 *
	 * @return bool Whether the country is allowed.
	 */
	public static function isAllowed($countryCode)
	{
		$allowed = self::getAllowed();

		return isset($allowed[$countryCode]);
	}

	public static function getStateName($countryCode, $stateCode)
	{
		if (!self::hasState($countryCode, $stateCode)) {
			return $stateCode;
		}

		return self::$states[$countryCode][$stateCode];
	}

	/**
	 * Returns list of all defined states.
	 *
	 * @return array List of all states.
	 */
	public static function getAllStates()
	{
		return self::$states;
	}

	/**
	 * Returns list of states defined for selected country.
	 *
	 * If country has no states - empty array is returned.
	 *
	 * @param $countryCode string Country code to fetch data for.
	 *
	 * @return array List of states.
	 */
	public static function getStates($countryCode)
	{
		if (self::hasStates($countryCode)) {
			return self::$states[$countryCode];
		}

		return [];
	}

	/**
	 * @param $countryCode string Country code to check.
	 *
	 * @return bool Whether the country has defined states.
	 */
	public static function hasStates($countryCode)
	{
		return isset(self::$states[$countryCode]);
	}

	/**
	 * @param $countryCode string Country code to check.
	 * @param $stateCode   string State code to check.
	 *
	 * @return bool Whether the country has defined states.
	 */
	public static function hasState($countryCode, $stateCode)
	{
		return self::hasStates($countryCode) && isset(self::$states[$countryCode][$stateCode]);
	}

	/**
	 * @param $countryCode string Country code to check.
	 *
	 * @return bool Whether the country is from European Union.
	 */
	public static function isEU($countryCode)
	{
		return isset(self::$euCountries[$countryCode]);
	}

    /**
     * @param string $continentCode
     * @return array
     */
    public static function getCountriesForContinent($continentCode)
    {
        if(isset(self::$countries[$continentCode])) {
            $countries = self::getAll();

            return array_intersect_key($countries, self::$countries[$continentCode]);
        }

        return [];
	}
}
