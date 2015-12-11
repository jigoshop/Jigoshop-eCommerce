<?php

namespace Jigoshop\Admin\Helper;

class Migration
{
	/**
	 * @return string - file name for logs migration
	 */
	static function getLogsFile()
	{
		return '/migration_information.log';
	}

	/**
	 * Save information for log migration
	 *
	 * @param string $txt message content
	 * @param bool $header add <hr />
	 */
	static function saveLog($txt, $header = false)
	{
		$dir_log = rtrim(JIGOSHOP_LOG_DIR . self::getLogsFile());
		file_put_contents($dir_log, file_get_contents($dir_log) . "\r\n" . ($header ? "<hr />" : '') . $txt . self::getDate());
	}

	/**
	 * @return string data for saveLog method
	 */
	static function getDate()
	{
		return ' <span style="font-size: 10px; color: grey;">(' . date("d-m-Y H:i:s") . ')</span>';
	}
}
