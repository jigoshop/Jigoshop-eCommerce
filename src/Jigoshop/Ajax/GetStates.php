<?php

namespace Jigoshop\Ajax;

use Jigoshop\Exception;
use Jigoshop\Helper\Country;

/**
 * Class FindState
 * @package Jigoshop\Ajax;
 * @author Krzysztof Kasowski
 */
class GetStates implements Processable
{
    /**
     * @return array
     */
    public function process()
    {
        if (!isset($_POST['country'])) {
            throw new Exception('Wrong Form');
        }
        $states = [];
        if(Country::hasStates($_POST['country'])) {
            $states = Country::getStates($_POST['country']);
        }
        $response = [
            'success' => true,
            'states' => $states,
        ];

        return $response;
    }
}