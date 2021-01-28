<?php

declare(strict_types=1);

namespace OxidEsales\PayPalModule\GraphQL\Subscriber;

use OxidEsales\GraphQL\Storefront\Basket\Event\BeforePlaceOrder as OriginalEvent;
use OxidEsales\GraphQL\Storefront\Basket\Service\Basket as BasketService;
use OxidEsales\GraphQL\Storefront\Basket\Service\BasketRelationService;
use OxidEsales\GraphQL\Storefront\Shared\Infrastructure\Basket as SharedBasketInfrastructure;
use OxidEsales\PayPalModule\Controller\StandardDispatcher;
use OxidEsales\PayPalModule\GraphQL\DataType\BasketExtendType;
use OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder;
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
        $userBasket = $this->basketService->getAuthenticatedCustomerBasket((string)$event->getBasketId());
        $paymentMethod = $this->basketRelationService->payment($userBasket);

        if (!is_null($paymentMethod) && $paymentMethod->getId()->val() === 'oxidpaypal') {
            $sessionBasket = $this->sharedBasketInfra->getBasket($userBasket);

            $extendUserBasket = new BasketExtendType();
            $token = $extendUserBasket->paypalToken($userBasket);

            $builder = oxNew(GetExpressCheckoutDetailsRequestBuilder::class);
            $paypalRequest = $builder->getPayPalRequest();
            $paypalRequest->setParameter('TOKEN', $token);

            $standardPaypalController = oxNew(StandardDispatcher::class);
            $payPalService = $standardPaypalController->getPayPalCheckoutService();
            $paypalResponse = $payPalService->getExpressCheckoutDetails($paypalRequest);
            $payerId = $paypalResponse->getPayerId();

            // In order to be able to finalize order, using PayPal as payment method,
            // we need to prepare the following session variables.
            $session = \OxidEsales\Eshop\Core\Registry::getSession();
            $session->setBasket($sessionBasket);
            $session->setVariable('oepaypal-token', $token);
            $session->setVariable('oepaypal-payerId', $payerId);
        }

        return $event;
    }

    public static function getSubscribedEvents()
    {
        return [
            OriginalEvent::NAME => 'handle'
        ];
    }
}