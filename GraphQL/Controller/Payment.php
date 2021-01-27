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
        $builder = oxNew(GetExpressCheckoutDetailsRequestBuilder::class);
        $paypalRequest = $builder->getPayPalRequest();
        $paypalRequest->setParameter('TOKEN', $paypalToken);

        $standardPaypalController = oxNew(StandardDispatcher::class);
        $payPalService = $standardPaypalController->getPayPalCheckoutService();
        $paypalResponse = $payPalService->getExpressCheckoutDetails($paypalRequest);

        return [
            'token' => $paypalToken,
            'payerId' => $paypalResponse->getPayerId(),
        ];
    }
}