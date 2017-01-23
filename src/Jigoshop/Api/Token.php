<?php

namespace Jigoshop\Api;

/**
 * Class Token
 * @package Jigoshop\Api;
 * @author Krzysztof Kasowski
 */
class Token
{
    /** @var  int  */
    private $iat;
    /** @var  int  */
    private $exp;
    /** @var  string  */
    private $jti;
    /** @var  string  */
    private $sub;
    /** @var  array  */
    private $permissions = [];

    /**
     * @return int
     */
    public function getIat()
    {
        return $this->iat;
    }

    /**
     * @param int $iat
     */
    public function setIat($iat)
    {
        $this->iat = $iat;
    }

    /**
     * @return int
     */
    public function getExp()
    {
        return $this->exp;
    }

    /**
     * @param int $exp
     */
    public function setExp($exp)
    {
        $this->exp = $exp;
    }

    /**
     * @return string
     */
    public function getJti()
    {
        return $this->jti;
    }

    /**
     * @param string $jti
     */
    public function setJti($jti)
    {
        $this->jti = $jti;
    }

    /**
     * @return string
     */
    public function getSub()
    {
        return $this->sub;
    }

    /**
     * @param string $sub
     */
    public function setSub($sub)
    {
        $this->sub = $sub;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @param string $permission
     */
    public function addPermission($permission)
    {
        if(!in_array($permission, $this->permissions)) {
            $this->permissions[] = $permission;
        }
    }

    /**
     * @param string $permission
     *
     * @return bool
     */
    public function hasPermission($permission)
    {
        if(Permission::exists($permission)) {
            if(empty($this->permissions) || in_array($permission, $this->permissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \stdClass $state
     */
    public function restoreState($state)
    {
        $this->iat = $state->iat;
        $this->exp = $state->exp;
        $this->jti = $state->jti;
        $this->sub = $state->sub;
        $this->permissions = $state->permissions;
    }
}