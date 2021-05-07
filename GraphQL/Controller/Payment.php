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

namespace OxidEsales\PayPalModule\GraphQL\Controller;

use OxidEsales\GraphQL\Storefront\Basket\Service\Basket as StorefrontBasketService;
use OxidEsales\PayPalModule\GraphQL\DataType\PayPalCommunicationInformation;
use OxidEsales\PayPalModule\GraphQL\DataType\PayPalTokenStatus;
use OxidEsales\PayPalModule\GraphQL\Exception\GraphQLServiceNotFound;
use OxidEsales\PayPalModule\GraphQL\Service\Basket as BasketService;
use OxidEsales\PayPalModule\GraphQL\Service\Payment as PaymentService;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Right;
use TheCodingMachine\GraphQLite\Types\ID;

final class Payment
{
    /** @var PaymentService */
    private $paymentService;

    /** @var BasketService */
    private $basketService;

    /** @var StorefrontBasketService */
    private $storefrontBasketService;

    public function __construct(
        PaymentService $paymentService,
        BasketService $basketService,
        StorefrontBasketService $storefrontBasketService = null
    ) {
        $this->paymentService = $paymentService;
        $this->basketService = $basketService;
        $this->storefrontBasketService = $storefrontBasketService;
    }

    /**
     * @Query
     * @Logged()
     */
    public function paypalApprovalProcess(
        ID $basketId,
        string $returnUrl,
        string $cancelUrl,
        bool $displayBasketInPayPal
    ): PayPalCommunicationInformation {
        $this->validateState();

        $basket = $this->storefrontBasketService->getAuthenticatedCustomerBasket($basketId);

        // validate if basket payment method is correct
        $this->basketService->validateBasketPaymentMethod($basket);

        $communicationInformation = $this->paymentService->getPayPalCommunicationInformation(
            $basket,
            $returnUrl,
            $cancelUrl,
            $displayBasketInPayPal
        );

        $this->basketService->updateBasketToken($basket, $communicationInformation->getToken());

        return $communicationInformation;
    }

    /**
     * @Query
     * @Right("PAYPAL_EXPRESS_APPROVAL")
     */
    public function paypalExpressApprovalProcess(
        ID $basketId,
        string $returnUrl,
        string $cancelUrl,
        bool $displayBasketInPayPal
    ): PayPalCommunicationInformation
    {
        $this->validateState();

        $basket = $this->storefrontBasketService->getAuthenticatedCustomerBasket($basketId);

        // validate if pp express payment is available
        $this->basketService->validateBasketExpressPaymentMethod();

        $communicationInformation = $this->paymentService->getPayPalExpressCommunicationInformation(
            $basket,
            $returnUrl,
            $cancelUrl,
            $displayBasketInPayPal
        );

        $this->basketService->updateExpressBasketInformation(
            $basket,
            $communicationInformation->getToken()
        );

        return $communicationInformation;
    }

    /**
     * @Query()
     * @Right("PAYPAL_TOKEN_STATUS")
     */
    public function paypalTokenStatus(string $paypalToken): PayPalTokenStatus
    {
        return $this->paymentService->getPayPalTokenStatus($paypalToken);
    }

    protected function validateState(): void
    {
        if (is_null($this->storefrontBasketService)) {
            throw GraphQLServiceNotFound::byServiceName(StorefrontBasketService::class);
        }
    }
}