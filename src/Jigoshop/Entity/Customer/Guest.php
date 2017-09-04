<?php

namespace Jigoshop\Entity\Customer;

use Jigoshop\Entity\Customer;
use Jigoshop\Exception;
use Monolog\Registry;

class Guest extends Customer
{
	public function getId()
	{
		return null;
	}

	public function setId($id)
	{
		if (WP_DEBUG) {
			throw new Exception(__('Guest customer cannot be updated!', 'jigoshop-ecommerce'));
		}

		Registry::getInstance(JIGOSHOP_LOGGER)->addDebug('Guest customer cannot be updated!');
	}

	public function getLogin()
	{
		return '';
	}

	public function setLogin($login)
	{
		if (WP_DEBUG) {
			throw new Exception(__('Guest customer cannot be updated!', 'jigoshop-ecommerce'));
		}

		Registry::getInstance(JIGOSHOP_LOGGER)->addDebug('Guest customer cannot be updated!');
	}

	public function setName($name)
	{
		if (WP_DEBUG) {
			throw new Exception(__('Guest customer cannot be updated!', 'jigoshop-ecommerce'));
		}

		Registry::getInstance(JIGOSHOP_LOGGER)->addDebug('Guest customer cannot be updated!');
	}

	public function __toString()
	{
		return $this->getName();
	}

	public function getName()
	{
		return __('Guest', 'jigoshop-ecommerce');
	}

	public function getStateToSave()
	{
		$state = parent::getStateToSave();
		unset($state['id'], $state['login'], $state['email'], $state['name']);
		$state['billing'] = serialize($state['billing']);
		$state['shipping'] = serialize($state['shipping']);

		return $state;
	}

    /**
     * Used by json_encode method to proprly
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $state = parent::jsonSerialize();
        unset($state['id'], $state['login'], $state['email'], $state['name']);

        return $state;
    }
}
