<?php
/**
 *   ./////////////////////////////.
 *  //////////////////////////////////
 *  ///////////    ///////////////////
 *  ////////     /////////////////////
 *  //////    ////////////////////////
 *  /////    /////////    ////////////
 *  //////     /////////     /////////
 *  /////////     /////////    ///////
 *  ///////////    //////////    /////
 *  ////////////////////////    //////
 *  /////////////////////    /////////
 *  ///////////////////    ///////////
 *  //////////////////////////////////
 *   `//////////////////////////////`
 *
 * Plugin Name:         Jigoshop eCommerce
 * Plugin URI:          http://www.jigoshop.com/
 * Description:         Jigoshop, a WordPress eCommerce plugin that works.
 * Author:              Jigoshop
 * Author URI:          http://www.jigoshop.com
 * Version:             2.1
 * Requires at least:   4.0
 * Tested up to:        4.7.3
 * Text Domain:         jigoshop
 * Domain Path:         /languages/
 *
 * DISCLAIMER
 * Do not edit or add directly to this file if you wish to upgrade Jigoshop to newer
 * versions in the future. If you wish to customise Jigoshop core for your needs,
 * please use our GitHub repository to publish essential changes for consideration.
 *
 * @package             Jigoshop
 * @category            Core
 * @author              Jigoshop
 * @copyright           Copyright © 2011-2014 Jigoshop.
 * @license             GNU General Public License v3
 */

// Define plugin directory for inclusions
if (!defined('JIGOSHOP_DIR')) {
    /**
     * @deprecated 2.0:2.1 Use Jigoshop::getDir() instead
     */
	define('JIGOSHOP_DIR', dirname(__FILE__));
}
// Define plugin URL for assets
if (!defined('JIGOSHOP_URL')) {
    /**
     * @deprecated 2.0:2.1 Use Jigoshop::getUrl() instead
     */
	define('JIGOSHOP_URL', plugins_url('', __FILE__));
}
// Define plugin base name
if (!defined('JIGOSHOP_BASE_NAME')) {
    /**
     * @deprecated 2.0:2.1 Use Jigoshop::getBaseName() instead
     */
	define('JIGOSHOP_BASE_NAME', plugin_basename(__FILE__));
}
if (!defined('WP_DEBUG')) {
	define('WP_DEBUG', false);
}

define('JIGOSHOP_REQUIRED_MEMORY', 64);
define('JIGOSHOP_REQUIRED_WP_MEMORY', 40);
define('JIGOSHOP_PHP_VERSION', '5.6');
define('JIGOSHOP_WORDPRESS_VERSION', '3.8');

if(version_compare(PHP_VERSION, JIGOSHOP_PHP_VERSION, '<')){
	function jigoshop_required_version(){
		echo '<div class="error"><p>'.
			sprintf(__('<strong>Error!</strong> Jigoshop requires at least PHP %s! Your version is: %s. Please upgrade.', 'jigoshop'), JIGOSHOP_PHP_VERSION, PHP_VERSION).
			'</p></div>';
	}
	add_action('admin_notices', 'jigoshop_required_version');
	return;
}

include ABSPATH.WPINC.'/version.php';
/** @noinspection PhpUndefinedVariableInspection */
if(version_compare($wp_version, JIGOSHOP_WORDPRESS_VERSION, '<')){
	function jigoshop_required_wordpress_version()
	{
		include ABSPATH.WPINC.'/version.php';
		/** @noinspection PhpUndefinedVariableInspection */
		echo '<div class="error"><p>'.
			sprintf(__('<strong>Error!</strong> Jigoshop requires at least WordPress %s! Your version is: %s. Please upgrade.', 'jigoshop'), JIGOSHOP_WORDPRESS_VERSION, $wp_version).
			'</p></div>';
	}
	add_action('admin_notices', 'jigoshop_required_wordpress_version');
	return;
}

$ini_memory_limit = ini_get('memory_limit');
preg_match('/^(\d+)(\w*)?$/', $ini_memory_limit, $memory);
$memory_limit = $memory[1];
if (isset($memory[2])) {
	switch (strtoupper($memory[2])) {
		/** @noinspection PhpMissingBreakStatementInspection */
		case 'M':
			$memory_limit *= 1024 * 1024;
		case 'K':
			$memory_limit *= 1024;
	}
}
if($memory_limit < JIGOSHOP_REQUIRED_MEMORY*1024*1024){
	function jigoshop_required_memory_warning()
	{
		$ini_memory_limit = ini_get('memory_limit');
		echo '<div class="error"><p>'.
			sprintf(__('<strong>Warning!</strong> Jigoshop requires at least %sM of memory! Your system currently has: %s.', 'jigoshop'), JIGOSHOP_REQUIRED_MEMORY, $ini_memory_limit).
			'</p></div>';
	}
	add_action('admin_notices', 'jigoshop_required_memory_warning');
}

preg_match('/^(\d+)(\w*)?$/', WP_MEMORY_LIMIT, $memory);
$memory_limit = $memory[1];
if (isset($memory[2])) {
	switch (strtoupper($memory[2])) {
		/** @noinspection PhpMissingBreakStatementInspection */
		case 'M':
			$memory_limit *= 1024 * 1024;
		case 'K':
			$memory_limit *= 1024;
	}
}

if($memory_limit < JIGOSHOP_REQUIRED_WP_MEMORY*1024*1024){
	function jigoshop_required_wp_memory_warning()
	{
		echo '<div class="error"><p>'.
			sprintf(__('<strong>Warning!</strong> Jigoshop requires at least %sM of memory for WordPress! Your system currently has: %s. <a href="%s" target="_blank">How to change?</a>', 'jigoshop'),
                JIGOSHOP_REQUIRED_WP_MEMORY, WP_MEMORY_LIMIT, 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP').
			'</p></div>';
	}
	add_action('admin_notices', 'jigoshop_required_wp_memory_warning');
}
require_once(__DIR__.'/src/JigoshopInit.php');
$jigoshop = new JigoshopInit(__FILE__);
add_action('plugins_loaded', array($jigoshop, 'onLoad'), 20);
add_action('init', array($jigoshop, 'onInit'), 0);
register_activation_hook(__FILE__, array($jigoshop, 'update'));