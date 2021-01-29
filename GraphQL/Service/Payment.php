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

namespace OxidEsales\PayPalModule\GraphQL\Service;

use OxidEsales\GraphQL\Storefront\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Storefront\Basket\Service\BasketRelationService;
use OxidEsales\GraphQL\Storefront\Shared\Infrastructure\Basket as SharedBasketInfrastructure;
use OxidEsales\PayPalModule\Core\Config as PayPalConfig;
use OxidEsales\PayPalModule\GraphQL\DataType\PayPalCommunicationInformation;
use OxidEsales\PayPalModule\GraphQL\DataType\PayPalTokenStatus;
use OxidEsales\PayPalModule\GraphQL\Infrastructure\Request as RequestInfrastructure;

final class Payment
{
    /** @var RequestInfrastructure */
    private $requestInfrastructure;

    /** @var SharedBasketInfrastructure */
    private $sharedBasketInfrastructure;

    /** @var BasketRelationService */
    private $basketRelationService;

    /** @var Basket */
    private $basketService;

    public function __construct(
        RequestInfrastructure $requestInfrastructure,
        SharedBasketInfrastructure $sharedBasketInfrastructure,
        BasketRelationService $basketRelationService,
        Basket $basketService
    ) {
        $this->requestInfrastructure = $requestInfrastructure;
        $this->sharedBasketInfrastructure = $sharedBasketInfrastructure;
        $this->basketRelationService = $basketRelationService;
        $this->basketService = $basketService;
    }

    public function getPayPalTokenStatus(string $paypalToken): PayPalTokenStatus
    {
        $communicationConfirmed = $this->getPayerId($paypalToken) ? true : false;

        return new PayPalTokenStatus(
            $paypalToken,
            $communicationConfirmed
        );
    }

    public function getPayerId(string $token): ?string
    {
        $builder = $this->requestInfrastructure->getGetExpressCheckoutRequestBuilder();
        $paypalRequest = $builder->getPayPalRequest();
        $paypalRequest->setParameter('TOKEN', $token);

        $standardPaypalController = $this->requestInfrastructure->getStandardDispatcher();
        $payPalService = $standardPaypalController->getPayPalCheckoutService();
        $paypalResponse = $payPalService->getExpressCheckoutDetails($paypalRequest);

        return $paypalResponse->getPayerId();
    }

    public function getPayPalCommunicationInformation(
        BasketDataType $basket,
        string $returnUrl,
        string $cancelUrl,
        bool $displayBasketInPayPal
    ): PayPalCommunicationInformation {
        $sessionBasket = $this->sharedBasketInfrastructure->getCalculatedBasket($basket);
        $standardPaypalController = $this->requestInfrastructure->getStandardDispatcher();

        $requestBuilder = $this->requestInfrastructure->getSetExpressCheckoutRequestBuilder();
        $requestBuilder->setPayPalConfig($this->getPayPalConfig());
        $requestBuilder->setBasket($sessionBasket);
        $requestBuilder->setUser($standardPaypalController->getUser());

        $requestBuilder->setReturnUrl($returnUrl);
        $requestBuilder->setCancelUrl($cancelUrl);

        $displayBasketInPayPal = $displayBasketInPayPal && !$sessionBasket->isFractionQuantityItemsPresent();
        $requestBuilder->setShowCartInPayPal($displayBasketInPayPal);
        $requestBuilder->setTransactionMode($standardPaypalController->getTransactionMode($sessionBasket));

        $paypalRequest = $requestBuilder->buildStandardCheckoutRequest();
        $payPalService = $standardPaypalController->getPayPalCheckoutService();
        $paypalResponse = $payPalService->setExpressCheckout($paypalRequest);

        $token = $paypalResponse->getToken();

        return new PayPalCommunicationInformation(
            $token,
            $this->getPayPalCommunicationUrl($token)
        );
    }

    public function getPayPalConfig(): PayPalConfig
    {
        $standardPaypalController = $this->requestInfrastructure->getStandardDispatcher();
        return $standardPaypalController->getPayPalConfig();
    }

    public function getPayPalCommunicationUrl($token): string
    {
        $payPalConfig = $this->getPayPalConfig();
        return $payPalConfig->getPayPalCommunicationUrl($token);
    }
}