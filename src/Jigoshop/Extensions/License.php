<?php

namespace Jigoshop\Extensions;

use Jigoshop\Admin\Licences;
use Jigoshop\Licence;

/**
 * Class License
 * @package Jigoshop\Extensions;
 * @author Krzysztof Kasowski
 */
class License
{
    private $keys;

    /**
     * License constructor.
     */
    public function __construct()
    {
        $this->keys = $this->getKeys();
//        add_filter('plugins_api', array($this, 'getUpdateData'), 20, 3);
//        add_filter('pre_set_site_transient_update_plugins', array($this, 'checkUpdates'));
//        add_action('in_plugin_update_message-'.$this->plugin_slug, array($this, 'updateMessage'), 10, 2);
    }

    /**
     * Returns a set of licence keys for this site from the options table
     *
     * @return array
     */
    private function getKeys()
    {
        return get_option(Licence::VALIDATOR_PREFIX.'licence_keys');
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function check($id)
    {
        return true;
        //return (isset($this->keys[$id]['status']) && $this->keys[$id]['status']);
    }

//    /**
//     * Activate Jigoshop Licence API request
//     *
//     * @param string $id
//     * @param string $key
//     * @param string $email
//     *
//     * @return boolean
//     */
//    private function activate($id, $key, $email)
//    {
//        // POST data to send to the Jigoshop Licencing API
//        $args = array(
//            'email' => $email,
//            'licence_key' => $key,
//            'product_id' => $id,
//            'instance' => $this->generateServerInstance()
//        );
//
//        // Send request for detailed information
//        return $this->request('activation', $args);
//    }
//
//    /**
//     * Deactivate Jigoshop Licence API request
//     *
//     * @param string $id
//     * @param string $key
//     * @param string $email
//     *
//     * @return boolean
//     */
//    private function deactivate($id, $key, $email)
//    {
//        // POST data to send to the Jigoshop Licencing API
//        $args = array(
//            'email' => $email,
//            'licence_key' => $key,
//            'product_id' => $id,
//            'instance' => $this->generateServerInstance()
//        );
//
//        // Send request for detailed information
//        return $this->request('deactivation', $args);
//    }
//
//    /**
//     * Plugin Version and update Information for a Jigoshop Licence API request
//     *
//     * @param string $id
//     * @param string $key
//     * @param string $email
//     *
//     * @return boolean
//     */
//    private function getUpdateVersion($id, $key, $email)
//    {
//        // POST data to send to the Jigoshop Licencing API
//        $args = array(
//            'email' => $email,
//            'licence_key' => $key,
//            'product_id' => $id,
//            'instance' => $this->generateServerInstance()
//        );
//
//        // Send request for detailed information
//        return $this->request('update_version', $args);
//    }
//
//    /**
//     * Prepare a request and send it to the Jigoshop Licence API on the selling shop
//     *
//     * @param string $action
//     * @param array  $args
//     *
//     * @return boolean
//     */
//    private function request($action, $args)
//    {
//        $request = wp_remote_post(
//            $this->home_shop_url.'?licence-api='.$action, array(
//                'method' => 'POST',
//                'timeout' => 45,
//                'redirection' => 5,
//                'httpversion' => '1.0',
//                'blocking' => true,
//                'headers' => array(),
//                'body' => $args,
//                'cookies' => array(),
//                'sslverify' => false,
//            )
//        );
//
//        // Make sure the request was successful
//        if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
//            // Request failed
//            return false;
//        }
//
//        // Read server response and return it
//        return json_decode(wp_remote_retrieve_body($request));
//    }
//
//
//    /**
//     * Instance key for current WP installation
//     *
//     * @return string
//     */
//    private function generateServerInstance()
//    {
//        return $_SERVER['SERVER_ADDR'].'@'.$_SERVER['HTTP_HOST'];
//    }
}