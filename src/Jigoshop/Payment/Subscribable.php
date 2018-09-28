<?php

namespace Jigoshop\Payment;

/**
 * @package Jigoshop\Payment;
 * @author Krzysztof Kasowski
 */
interface Subscribable
{
    /**
     * @param \Jigoshop\Extension\Subscriptions\Entity\Subscription $subscription
     *
     * @return string
     */
    public function processSubscription(\Jigoshop\Extension\Subscriptions\Entity\Subscription $subscription);
}