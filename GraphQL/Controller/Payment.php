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
use OxidEsales\GraphQL\Storefront\Shared\Infrastructure\Basket as SharedBasketInfrastructure;
use OxidEsales\PayPalModule\Controller\StandardDispatcher;
use OxidEsales\PayPalModule\GraphQL\Service\Payment as PaymentService;
use OxidEsales\PayPalModule\Model\PayPalRequest\SetExpressCheckoutRequestBuilder;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class Payment
{
    /** @var PaymentService */
    private $paymentService;

    /** @var StorefrontBasketService */
    private $storefrontBasketService;

    /** @var SharedBasketInfrastructure */
    private $sharedBasketInfrastructure;

    public function __construct(
        PaymentService $paymentService,
        StorefrontBasketService $storefrontBasketService,
        SharedBasketInfrastructure $sharedBasketInfrastructure
    ) {
        $this->paymentService = $paymentService;
        $this->storefrontBasketService = $storefrontBasketService;
        $this->sharedBasketInfrastructure = $sharedBasketInfrastructure;
    }

    /**
     * @Query
     * @Logged()
     *
     * @return string[]
     */
    public function paypalApprovalProcess(
        string $basketId,
        string $returnUrl,
        string $cancelUrl,
        bool $displayBasketInPayPal
    ): array {
        $basket = $this->storefrontBasketService->getAuthenticatedCustomerBasket($basketId);
        $basketModel = $this->sharedBasketInfrastructure->getCalculatedBasket($basket);

        // validate basket user, address and delivery stuff
        $this->paymentService->validateBasketData($basket);

        $standardPaypalController = oxNew(StandardDispatcher::class);
        $paypalConfig = $standardPaypalController->getPayPalConfig();

        $requestBuilder = oxNew(SetExpressCheckoutRequestBuilder::class);
        $requestBuilder->setPayPalConfig($paypalConfig);
        $requestBuilder->setBasket($basketModel);
        $requestBuilder->setUser($standardPaypalController->getUser());

        $requestBuilder->setReturnUrl($returnUrl);
        $requestBuilder->setCancelUrl($cancelUrl);

        $displayBasketInPayPal = $displayBasketInPayPal && !$basketModel->isFractionQuantityItemsPresent();
        $requestBuilder->setShowCartInPayPal($displayBasketInPayPal);
        $requestBuilder->setTransactionMode($standardPaypalController->getTransactionMode($basketModel));

        $paypalRequest = $requestBuilder->buildStandardCheckoutRequest();
        $payPalService = $standardPaypalController->getPayPalCheckoutService();
        $paypalResponse = $payPalService->setExpressCheckout($paypalRequest);

        $token = $paypalResponse->getToken();

        $userBasketModel = $basket->getEshopModel();
        $userBasketModel->assign([
            'OEPAYPAL_PAYMENT_TOKEN' => $token
        ]);
        $userBasketModel->save();

        return [
            'token' => $token,
            'paypalLoginUrl' => $paypalConfig->getPayPalCommunicationUrl($token),
        ];
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