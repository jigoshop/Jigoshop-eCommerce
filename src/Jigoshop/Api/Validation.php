<?php

namespace Jigoshop\Api;

use Jigoshop\Api\Validation\InvalidKey;
use Jigoshop\Api\Validation\InvalidUserId;
use Jigoshop\Api\Validation\Permission;
use Jigoshop\Exception;

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

    /**
     * @param string $method
     * @param string $uri
     *
     * @return bool
     */
    public function checkRequest($method, $uri)
    {
        //TODO: add more secure auth
        if (isset($this->headers['JIGOSHOP-API-KEY'])) {
            $keyData = $this->getCurrentKeyData();
            if(empty($keyData)) {
                throw new Exception(sprintf(__('Invalid key: %s', 'jigoshop'),$this->headers['JIGOSHOP-API-KEY']));
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

        return array();
    }

    /**
     * @return string[]
     */
    private function getKeyPermissions()
    {
        $keyData = $this->getCurrentKeyData();

        return !empty($keyData) && !empty($keyData['permissions']) ? $keyData['permissions'] : array_keys(Permission::getPermisions());
    }

    /**
     * @return mixed[]
     */
    private function getCurrentKeyData()
    {
        if($this->currentKeyData == null) {
            if (isset($this->headers['JIGOSHOP-API-KEY'])) {
                foreach ($this->keys as $keyData) {
                    if ($keyData['key'] == $this->headers['JIGOSHOP-API-KEY']) {
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