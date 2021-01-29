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

use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\GraphQL\Storefront\Basket\DataType\Basket as BasketDataType;
use OxidEsales\GraphQL\Storefront\Basket\Service\BasketRelationService;
use OxidEsales\GraphQL\Storefront\Shared\Infrastructure\Basket as SharedBasketInfrastructure;
use OxidEsales\PayPalModule\Core\Exception\PayPalException;
use OxidEsales\PayPalModule\GraphQL\Exception\WrongPaymentMethod;
use OxidEsales\PayPalModule\Model\PaymentValidator;

final class Basket
{
    /** @var BasketRelationService */
    private $basketRelationService;

    /** @var SharedBasketInfrastructure */
    private $sharedBasketInfrastructure;

    public function __construct(
        BasketRelationService $basketRelationService,
        SharedBasketInfrastructure $sharedBasketInfrastructure
    ) {
        $this->basketRelationService = $basketRelationService;
        $this->sharedBasketInfrastructure = $sharedBasketInfrastructure;
    }

    public function checkBasketPaymentMethodIsPayPal(BasketDataType $basket): bool
    {
        $paymentMethod = $this->basketRelationService->payment($basket);
        $result = true;

        if (!is_null($paymentMethod) && $paymentMethod->getId()->val() === 'oxidpaypal') {
            $result = false;
        }

        return $result;
    }

    /**
     * @throws WrongPaymentMethod
     * @throws PayPalException
     */
    public function validateBasketData(BasketDataType $basket): void
    {
        if (!$this->checkBasketPaymentMethodIsPayPal($basket)) {
            throw new WrongPaymentMethod();
        }

        $basketModel = $this->sharedBasketInfrastructure->getCalculatedBasket($basket);

        $validator = oxNew(PaymentValidator::class);
        $validator->setUser($basketModel->getUser());
        $validator->setConfig(Registry::getConfig());
        $validator->setPrice($basketModel->getPrice()->getPrice());

        if (!$validator->isPaymentValid()) {
            throw new PayPalException('OEPAYPAL_PAYMENT_NOT_VALID');
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
}