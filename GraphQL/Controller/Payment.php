<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\PayPalModule\GraphQL\Controller;

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\GraphQL\Storefront\Basket\Service\Basket as AccountBasketService;
use OxidEsales\GraphQL\Storefront\Shared\Infrastructure\Basket as SharedBasketInfrastructure;
use OxidEsales\PayPalModule\Controller\StandardDispatcher;
use OxidEsales\PayPalModule\Core\Exception\PayPalException;
use OxidEsales\PayPalModule\Model\PaymentValidator;
use OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder;
use OxidEsales\PayPalModule\Model\PayPalRequest\SetExpressCheckoutRequestBuilder;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Query;

final class Payment
{
    /**
     * @Query
     *
     * @return string[]
     */
    public function paypalApprovalProcess(
        string $basketId,
        string $returnUrl = null,
        string $cancelUrl = null,
        bool $displayBasketInPayPal = true
    ): array {
        $basket = ContainerFactory::getInstance()
            ->getContainer()
            ->get(AccountBasketService::class)
            ->getAuthenticatedCustomerBasket($basketId);

        $basketModel = ContainerFactory::getInstance()
            ->getContainer()
            ->get(SharedBasketInfrastructure::class)
            ->getCalculatedBasket($basket);

        $validator = oxNew(PaymentValidator::class);
        $validator->setUser($basketModel->getUser());
        $validator->setConfig(EshopRegistry::getConfig());
        $validator->setPrice($basketModel->getPrice()->getPrice());

        if (!$validator->isPaymentValid()) {
            /** @var PayPalException $exception */
            $exception = oxNew(PayPalException::class);
            $exception->setMessage(EshopRegistry::getLang()->translateString('OEPAYPAL_PAYMENT_NOT_VALID'));

            throw $exception;
        }

        $standardPaypalController = oxNew(StandardDispatcher::class);
        $paypalConfig             = $standardPaypalController->getPayPalConfig();

        $requestBuilder = oxNew(SetExpressCheckoutRequestBuilder::class);
        $requestBuilder->setPayPalConfig($paypalConfig);
        $requestBuilder->setBasket($basketModel);
        $requestBuilder->setUser($standardPaypalController->getUser());


//        $requestBuilder->setReturnUrl($returnUrl);
//        $requestBuilder->setCancelUrl($cancelUrl);

        $requestBuilder->setReturnUrl($returnUrl ?? $standardPaypalController->getReturnUrl());
        $requestBuilder->setCancelUrl($cancelUrl ?? $standardPaypalController->getCancelUrl());

        $displayBasketInPayPal = $displayBasketInPayPal && !$basketModel->isFractionQuantityItemsPresent();
        $requestBuilder->setShowCartInPayPal($displayBasketInPayPal);
        $requestBuilder->setTransactionMode($standardPaypalController->getTransactionMode($basketModel));

        $paypalRequest  = $requestBuilder->buildStandardCheckoutRequest();
        $payPalService  = $standardPaypalController->getPayPalCheckoutService();
        $paypalResponse = $payPalService->setExpressCheckout($paypalRequest);

        // 1. save the token for the basket.
        // 2. hook to event before placeOrder and set token to session
        // 3. check how placeOrder brake and whats wrong.

        return [
            'token'          => $paypalResponse->getToken(),
            'paypalLoginUrl' => $paypalConfig->getPayPalCommunicationUrl($paypalResponse->getToken()),
        ];
    }
}