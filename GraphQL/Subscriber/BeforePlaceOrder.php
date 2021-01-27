<?php

declare(strict_types=1);

namespace OxidEsales\PayPalModule\GraphQL\Subscriber;

use OxidEsales\GraphQL\Storefront\Basket\Event\BeforePlaceOrder as OriginalEvent;
use OxidEsales\GraphQL\Storefront\Basket\Service\Basket as BasketService;
use OxidEsales\GraphQL\Storefront\Basket\Service\BasketRelationService;
use OxidEsales\GraphQL\Storefront\Shared\Infrastructure\Basket as SharedBasketInfrastructure;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforePlaceOrder implements EventSubscriberInterface
{
    /** @var BasketService */
    private $basketService;

    /** @var SharedBasketInfrastructure */
    private $sharedBasketInfra;

    /** @var BasketRelationService */
    private $basketRelationService;

    public function __construct(
        BasketService $basketService,
        SharedBasketInfrastructure $sharedBasketInfra,
        BasketRelationService $basketRelationService
    ) {
        $this->basketService = $basketService;
        $this->sharedBasketInfra = $sharedBasketInfra;
        $this->basketRelationService  = $basketRelationService;
    }

    public function handle(OriginalEvent $event): OriginalEvent
    {


        return $event;
    }

    public static function getSubscribedEvents()
    {
        return [
            OriginalEvent::NAME => 'handle'
        ];
    }
}