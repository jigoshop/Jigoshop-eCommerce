<?php

namespace Jigoshop\Helper;

use Monolog\Registry;

class Migration
{
	/**
	 * Checks whether a migration tool must be displayed.
	 * If someone did not use before JigoShop 1.x does not need migration tools in JigoShop2
	 *
	 * @return boolean
	 */
	public static function needMigrationTool()
	{
		//TODO sprawdzenie czy jest js1, czy tabele sa, czy plugin wlaczony itd. Możemy dodać force. Możemy dać ukrywanie po np poprawnym przeprowadzeniu migracji. oprzec to na gecie, zapisac do bazy
		return true;
	}
}
