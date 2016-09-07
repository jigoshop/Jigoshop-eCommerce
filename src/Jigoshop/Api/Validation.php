<?php

namespace Jigoshop\Api;

use Jigoshop\Api\Validation\Permission;
use Jigoshop\Core\Options;

/**
 * Class Validation
 * @package Jigoshop\Api;
 * @author Krzysztof Kasowski
 */
class Validation
{
    /** @var  \WP_User */
    private $user;
    /** @var Options  */
    private $options;

    /**
     * Validation constructor.
     * @param Options $options
     */
    public function __construct(Options $options, $apiKey)
    {
        $this->options = $options;
        $this->apiKey = '';
        $this->user = wp_get_current_user();
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        $permissions = apply_filters('jigoshop\api\validation\default_permissions', array(
            Permission::MANAGE_CART,
            Permission::READ_CART,
            Permission::READ_PRODUCTS,
        ));
        if($this->user->ID) {
            if(in_array('manage_jigoshop', $this->user->get_role_caps())) {
                return array_keys(Permission::getPermisions());
            }
        }

        if($this->apiKey) {
            $allKeys = $this->options->get('advanced.api.keys', array());
            foreach($allKeys as $keyData) {
                if($keyData['key'] == $this->apiKey) {
                    if(empty($keyData['permissions'])) {
                        return array_keys(Permission::getPermisions());
                    }
                    $permissions = array_merge($permissions, $keyData['permissions']);
                }
            }
        }

        return $permissions;
    }
}