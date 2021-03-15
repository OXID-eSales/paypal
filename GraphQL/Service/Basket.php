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
use OxidEsales\PayPalModule\GraphQL\Exception\GraphQLServiceNotFound;
use OxidEsales\PayPalModule\GraphQL\Exception\PaymentValidation;
use OxidEsales\PayPalModule\Model\PaymentManager;

final class Basket
{
    /** @var BasketRelationService */
    private $basketRelationService;

    public function __construct(
        BasketRelationService $basketRelationService = null
    ) {
        $this->basketRelationService = $basketRelationService;
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
    }

    protected function validateState(): void
    {
        if (is_null($this->basketRelationService)) {
            throw GraphQLServiceNotFound::byServiceName(BasketRelationService::class);
        }
    }
}