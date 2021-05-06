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

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\EshopCommunity\Internal\Framework\Event\AbstractShopAwareEventSubscriber;
use OxidEsales\GraphQL\Storefront\Basket\Event\BeforeBasketPayments as BeforeBasketPaymentsEvent;
use OxidEsales\GraphQL\Storefront\Basket\Service\Basket as StorefrontBasketService;
use OxidEsales\PayPalModule\Core\Config as PayPalConfig;
use OxidEsales\PayPalModule\GraphQL\Exception\GraphQLServiceNotFound;
use OxidEsales\PayPalModule\GraphQL\Service\Basket as BasketService;
use OxidEsales\PayPalModule\GraphQL\Service\BasketExtendType;

class BeforeBasketPayments extends AbstractShopAwareEventSubscriber
{
    /** @var BasketService */
    private $basketService;

    /** @var StorefrontBasketService */
    private $storefrontBasketService;

    public function __construct(
        BasketService $basketService,
        StorefrontBasketService $storefrontBasketService = null
    ) {
        $this->basketService           = $basketService;
        $this->storefrontBasketService = $storefrontBasketService;
    }

    public function handle(BeforeBasketPaymentsEvent $event): BeforeBasketPaymentsEvent
    {
        $this->validateState();

        $basketDataType = $this->storefrontBasketService->getAuthenticatedCustomerBasket($event->getBasketId());
        if ($this->basketService->checkBasketPaymentMethodIsPayPal($basketDataType)) {
            $extendUserBasket = new BasketExtendType();
            $session = EshopRegistry::getSession();
            $session->setVariable(
                PayPalConfig::OEPAYPAL_TRIGGER_NAME,
                $extendUserBasket->paypalServiceType($basketDataType)
            );
        }

        return $event;
    }

    public static function getSubscribedEvents()
    {
        return [
            'OxidEsales\GraphQL\Storefront\Basket\Event\BeforeBasketPayments' => 'handle'
        ];
    }

    protected function validateState(): void
    {
        if (is_null($this->storefrontBasketService)) {
            throw GraphQLServiceNotFound::byServiceName(StorefrontBasketService::class);
        }
    }
}
