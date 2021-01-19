<?php

declare(strict_types=1);

namespace OxidEsales\PayPalModule\Subscriber;

use OxidEsales\GraphQL\Storefront\Basket\Event\BeforePlaceOrder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Order implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            BeforePlaceOrder::NAME => 'GraphQLBeforePlaceOrder'
        ];
    }

    public function GraphQLBeforePlaceOrder(BeforePlaceOrder $placeOrder)
    {
        $basketId = $placeOrder->getBasketId();
    }
}