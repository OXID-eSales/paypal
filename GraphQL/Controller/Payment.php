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
use OxidEsales\PayPalModule\GraphQL\Service\Payment as PaymentService;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class Payment
{
    /** @var PaymentService */
    private $paymentService;

    /** @var StorefrontBasketService */
    private $storefrontBasketService;

    public function __construct(
        PaymentService $paymentService,
        StorefrontBasketService $storefrontBasketService
    ) {
        $this->paymentService = $paymentService;
        $this->storefrontBasketService = $storefrontBasketService;
    }

    /**
     * @Query
     * @Logged()
     */
    public function paypalApprovalProcess(
        string $basketId,
        string $returnUrl,
        string $cancelUrl,
        bool $displayBasketInPayPal
    ): PayPalCommunicationInformation {
        $basket = $this->storefrontBasketService->getAuthenticatedCustomerBasket($basketId);

        // validate basket user, address and delivery stuff
        $this->paymentService->validateBasketData($basket);

        $communicationInformation = $this->paymentService->getPayPalCommunicationInformation(
            $basket,
            $returnUrl,
            $cancelUrl,
            $displayBasketInPayPal
        );

        $this->paymentService->updateBasketToken($basket, $communicationInformation->getToken());

        return $communicationInformation;
    }

    /**
     * @Query()
     * @Logged()
     *
     * @return string[]
     */
    public function paypalPaymentStatus(string $paypalToken): array
    {
        return [
            'token' => $paypalToken,
            'confirmed' => $this->paymentService->isPaymentConfirmed($paypalToken)
        ];
    }
}