<?php

namespace Jigoshop\Payment;

use Jigoshop\Entity\Order;

/**
 * @package Jigoshop\Payment;
 * @author Krzysztof Kasowski
 */
interface RenderPayInterface
{
    /**
     * @param Order $order
     *
     * @return string
     */
    public function renderPay($order);
}