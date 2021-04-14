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

use OxidEsales\Eshop\Core\Registry as EshopRegistry;
use OxidEsales\GraphQL\Storefront\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Storefront\Basket\Service\BasketRelationService;
use OxidEsales\GraphQL\Storefront\Payment\DataType\Payment as PaymentDataType;
use OxidEsales\GraphQL\Storefront\Payment\Exception\PaymentNotFound;
use OxidEsales\GraphQL\Storefront\Payment\Exception\UnavailablePayment;
use OxidEsales\GraphQL\Storefront\Payment\Service\Payment as StorefrontPaymentService;
use OxidEsales\PayPalModule\Core\Config as PayPalConfig;
use OxidEsales\PayPalModule\GraphQL\Exception\GraphQLServiceNotFound;
use OxidEsales\PayPalModule\GraphQL\Exception\PaymentValidation;
use OxidEsales\PayPalModule\Model\PaymentManager;

final class Basket
{
    /** @var BasketRelationService */
    private $basketRelationService;

    /** @var StorefrontPaymentService */
    private $storefrontPaymentService;

    /** @var PayPalConfig */
    private $paypalConfig;

    public function __construct(
        BasketRelationService $basketRelationService = null,
        StorefrontPaymentService $storefrontPaymentService = null,
        PayPalConfig $paypalConfig
    ) {
        $this->basketRelationService    = $basketRelationService;
        $this->storefrontPaymentService = $storefrontPaymentService;
        $this->paypalConfig             = $paypalConfig;
    }

    public function checkBasketPaymentMethodIsPayPal(BasketDataType $basket): bool
    {
        $this->validateState();

        $paymentMethod = $this->basketRelationService->payment($basket);
        $result = false;

        if (!is_null($paymentMethod) && $paymentMethod->getId()->val() === 'oxidpaypal') {
            $result = true;
        }

        return $result;
    }

    /**
     * @throws PaymentValidation
     */
    public function validateBasketPaymentMethod(BasketDataType $basket): void
    {
        if (!$this->checkBasketPaymentMethodIsPayPal($basket)) {
            throw PaymentValidation::paymentMethodIsNotPaypal();
        }

        if (!$this->paypalConfig->isStandardCheckoutEnabled()) {
            throw UnavailablePayment::byId('oxidpaypal');
        }
    }

    public function validateBasketExpressPaymentMethod(): void
    {
        if (!$this->paypalConfig->isExpressCheckoutEnabled()) {
            throw UnavailablePayment::byId('oxidpaypal');
        }

        try {
            $payment = $this->storefrontPaymentService->payment('oxidpaypal');
        } catch (PaymentNotFound $e) {
            throw UnavailablePayment::byId('oxidpaypal');
        }

        if (!$payment instanceof PaymentDataType) {
            throw UnavailablePayment::byId('oxidpaypal');
        }
    }

    public function updateBasketToken(BasketDataType $basket, string $token): void
    {
        /**
         * @TODO: check if we can/need to revoke the old token.
         */

        $userBasketModel = $basket->getEshopModel();
        $userBasketModel->assign([
            'OEPAYPAL_PAYMENT_TOKEN' => $token
        ]);
        $userBasketModel->save();
    }

    public function updateExpressBasketInformation(BasketDataType $basket, string $token): void
    {
        $userBasketModel = $basket->getEshopModel();
        $userBasketModel->assign(
            [
                'OEPAYPAL_PAYMENT_TOKEN' => $token,
                'OEPAYPAL_SERVICE_TYPE'  => PaymentManager::PAYPAL_SERVICE_TYPE_EXPRESS,
                'OEGQL_PAYMENTID'        => 'oxidpaypal'
            ]
        );
        $userBasketModel->save();

        EshopRegistry::getSession()->setVariable(
            PayPalConfig::OEPAYPAL_TRIGGER_NAME,
            PaymentManager::PAYPAL_SERVICE_TYPE_EXPRESS
        );
    }

    protected function validateState(): void
    {
        if (is_null($this->basketRelationService)) {
            throw GraphQLServiceNotFound::byServiceName(BasketRelationService::class);
        }

        if (is_null($this->storefrontPaymentService)) {
            throw GraphQLServiceNotFound::byServiceName(StorefrontPaymentService::class);
        }
    }
}