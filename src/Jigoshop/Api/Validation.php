<?php

namespace Jigoshop\Api;

use Jigoshop\Api\Validation\InvalidKey;
use Jigoshop\Api\Validation\InvalidUserId;
use Jigoshop\Api\Validation\Permission;

/**
 * Class Validation
 * @package Jigoshop\Api;
 * @author Krzysztof Kasowski
 */
class Validation
{
    /** @var array[] */
    private $keys;
    /** @var string[] */
    private $headers;
    /** @var mixed[] */
    private $currentKeyData;

    /**
     * Validation constructor.
     * @param mixed[] $keys
     * @param string[] $headers
     */
    public function __construct($keys, $headers)
    {
        $this->keys = $keys;
        $this->headers = $headers;
    }

    public function checkRequest($method, $uri)
    {
        if (isset($this->headers['JIGOSHOP-API-USER-ID'], $this->headers['JIGOSHOP-API-SIGNATURE'], $this->headers['JIGOSHOP-API-TIMESTAMP'])) {
            $keyData = $this->getCurrentKeyData();
            if(time() - $this->headers['JIGOSHOP-API-TIMESTAMP'] > 5) {
                //throw
                return false;
            }

            if(hash('sha256', $keyData['key'].$this->headers['JIGOSHOP-API-TIMESTAMP'].$method.$uri) != $this->headers['JIGOSHOP-API-SIGNATURE']) {
                //throw
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        $default = apply_filters('jigoshop\api\validation\default_permissions', array(
            Permission::MANAGE_CART,
            Permission::READ_CART,
            Permission::READ_PRODUCTS,
        ));
        $permissions = array_merge($default, $this->getCurrentUserPermissions(), $this->getKeyPermissions());

        return apply_filters('jigoshop\api\validation\permissions', $permissions);
    }

    private function getCurrentUserPermissions()
    {
        $user = wp_get_current_user();
        if ($user->ID) {
            if (in_array('manage_jigoshop', $user->get_role_caps())) {
                return array_keys(Permission::getPermisions());
            }
        }
    }

    /**
     * @return string[]
     */
    private function getKeyPermissions()
    {
        $keyData = $this->getCurrentKeyData();

        return !empty($keyData) ? $keyData['permissions'] : [];
    }

    /**
     * @return mixed[]
     */
    private function getCurrentKeyData()
    {
        if($this->currentKeyData == null) {
            if (isset($this->headers['JIGOSHOP-API-USER-ID'])) {
                foreach ($this->keys as $keyData) {
                    if ($keyData['user_id'] == $this->headers['JIGOSHOP-API-USER-ID']) {
                        return $keyData;
                    }
                }
            } else {
                $this->currentKeyData = [];
            }
        }

        return $this->currentKeyData;
    }
}