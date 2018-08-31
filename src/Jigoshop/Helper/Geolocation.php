<?php
namespace Jigoshop\Helper;

use JigoshopInit;
use Jigoshop\Exception;

class Geolocation {
	const DB_PATH = 'assets/other/geolocation.csv';

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
		if(!file_exists(self::getDbPath()) || !is_readable(self::getDbPath())) {
			throw new Exception(__('Unable to read geolocation database.', 'jigoshop-ecommerce'));
		}

		$decimal = self::ipToDecimal($ipAddress);

		$country = null;
		$db = fopen(self::getDbPath(), 'r');
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
	 * Returns complete path to location database file.
	 * 
	 * @return string Database path.
	 */
	private static function getDbPath() {
		return sprintf('%s/%s', JigoshopInit::getDir(), self::DB_PATH);
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