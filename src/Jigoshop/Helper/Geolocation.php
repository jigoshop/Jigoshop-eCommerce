<?php
namespace Jigoshop\Helper;

use Jigoshop\Exception;

class Geolocation {
	const DB_PATH = 'source-filtered.csv';

	/**
	 * Returns country code of specified IP address.
	 * 
	 * @param string $ipAddress IP address to search database for.
	 * 
	 * @throws \Jigoshop\Exception On database read error.
	 * 
	 * @return mixed String (country code) if address found, null if not.
	 */
	public static function getCountryOfIP($ipAddress) {
		if(!file_exists(self::DB_PATH) || !is_readable(self::DB_PATH)) {
			throw new Exception(__('Unable to read geolocation database.', 'jigoshop-ecommerce'));
		}

		$decimal = self::ipToDecimal($ipAddress);

		$country = null;
		$db = fopen(self::DB_PATH, 'r');
		while(!feof($db)) {
			$line = trim(fgets($db));
			if(!$line) {
				continue;
			}

			$line = explode(',', $line);
			if($decimal >= $line[0] && $decimal <= $line[1]) {
				$country = $line[2];

				break;
			}
		}
		fclose($db);

		return $country;
	}

	/**
	 * Converts IP address to decimal.
	 * 
	 * @param string $ipAddress IP address to convert.
	 * 
	 * @throws \Jigoshop\Exception If invalid address was specified.
	 * 
	 * @return int Decimal value of IP address.
	 */
	private static function ipToDecimal($ipAddress) {
		$ipAddress = explode('.', $ipAddress);
		if(!is_array($ipAddress) || count($ipAddress) != 4) {
			throw new Exception(__('Invalid IP address specified.', 'jigoshop-ecommerce'));
		}

		$result = 0;
		for($currentOctet = 0; $currentOctet < 4; $currentOctet++) {
			$result += $ipAddress[$currentOctet] * pow(256, 3 - $currentOctet);
		}

		return $result;
	}
}