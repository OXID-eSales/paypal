<?php
/**
 * This file is part of OXID eSales PayPal module.
 *
 * OXID eSales PayPal module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2018
 */

declare(strict_types=1);

namespace OxidEsales\PayPalModule\GraphQL\Subscriber;

use OxidEsales\GraphQL\Storefront\Basket\Event\BeforePlaceOrder as OriginalEvent;
use OxidEsales\GraphQL\Storefront\Basket\Service\Basket as StorefrontBasketService;
use OxidEsales\GraphQL\Storefront\Basket\Service\BasketRelationService;
use OxidEsales\GraphQL\Storefront\Shared\Infrastructure\Basket as SharedBasketInfrastructure;
use OxidEsales\PayPalModule\GraphQL\DataType\BasketExtendType;
use OxidEsales\PayPalModule\GraphQL\Exception\BasketCommunication;
use OxidEsales\PayPalModule\GraphQL\Service\Basket as BasketService;
use OxidEsales\PayPalModule\GraphQL\Service\Payment as PaymentService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforePlaceOrder implements EventSubscriberInterface
{
    /** @var StorefrontBasketService */
    private $storefrontBasketService;

    /** @var SharedBasketInfrastructure */
    private $sharedBasketInfra;

    /** @var BasketRelationService */
    private $basketRelationService;

    /** @var PaymentService */
    private $paymentService;

    /** @var BasketService */
    private $basketService;

    public function __construct(
        StorefrontBasketService $storefrontBasketService,
        SharedBasketInfrastructure $sharedBasketInfra,
        BasketRelationService $basketRelationService,
        PaymentService $paymentService,
        BasketService $basketService
    ) {
        $this->storefrontBasketService = $storefrontBasketService;
        $this->sharedBasketInfra = $sharedBasketInfra;
        $this->basketRelationService  = $basketRelationService;
        $this->paymentService = $paymentService;
        $this->basketService = $basketService;
    }

    public function handle(OriginalEvent $event): OriginalEvent
    {
        $userBasket = $this->storefrontBasketService->getAuthenticatedCustomerBasket((string)$event->getBasketId());
        if ($this->basketService->checkBasketPaymentMethodIsPayPal($userBasket)) {
            $extendUserBasket = new BasketExtendType();

            $token = $extendUserBasket->paypalToken($userBasket);
            if (!$token) {
                BasketCommunication::notStarted($userBasket->id()->val());
            }

            $tokenStatus = $this->paymentService->getPayPalTokenStatus($token);
            if (!$tokenStatus->getStatus()) {
                BasketCommunication::notConfirmed($userBasket->id()->val());
            }

            // In order to be able to finalize order, using PayPal as payment method,
            // we need to prepare the following session variables.
            $session = \OxidEsales\Eshop\Core\Registry::getSession();
            $session->setBasket($this->sharedBasketInfra->getBasket($userBasket));
            $session->setVariable('oepaypal-token', $token);
            $session->setVariable('oepaypal-payerId', $this->paymentService->getPayerId($token));
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