<?php
// Define required data for tests
define('JIGOSHOP_DIR', dirname(__FILE__).'/..');
define('JIGOSHOP_URL', 'http://test.url.com/');
function __($text, $module){
	return 'test';
}

require_once(JIGOSHOP_DIR.'/vendor/autoload.php');
$loader = new \Composer\Autoload\ClassLoader();
$loader->register();
require_once('TestCase.php');