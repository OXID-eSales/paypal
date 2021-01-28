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

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\GraphQL\Storefront\Basket\DataType\Basket as BasketDataType;
use OxidEsales\PayPalModule\Core\Exception\PayPalException;
use OxidEsales\PayPalModule\GraphQL\Infrastructure\Request as RequestInfrastructure;
use OxidEsales\GraphQL\Storefront\Shared\Infrastructure\Basket as SharedBasketInfrastructure;
use OxidEsales\PayPalModule\Model\PaymentValidator;

final class Payment
{
    /** @var RequestInfrastructure */
    private $requestInfrastructure;

    /** @var SharedBasketInfrastructure */
    private $sharedBasketInfrastructure;

    public function __construct(
        RequestInfrastructure $requestInfrastructure,
        SharedBasketInfrastructure $sharedBasketInfrastructure
    ) {
        $this->requestInfrastructure = $requestInfrastructure;
        $this->sharedBasketInfrastructure = $sharedBasketInfrastructure;
    }

    public function isPaymentConfirmed(string $token): bool
    {
        return $this->getPayerId($token) ? true : false;
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

    /**
     * @throws PayPalException
     */
    public function validateBasketData(BasketDataType $basket)
    {
        $basketModel = $this->sharedBasketInfrastructure->getCalculatedBasket($basket);

        $validator = oxNew(PaymentValidator::class);
        $validator->setUser($basketModel->getUser());
        $validator->setConfig(Registry::getConfig());
        $validator->setPrice($basketModel->getPrice()->getPrice());

        if (!$validator->isPaymentValid()) {
            /** @var PayPalException $exception */
            $exception = oxNew(PayPalException::class);
            $exception->setMessage(Registry::getLang()->translateString('OEPAYPAL_PAYMENT_NOT_VALID'));

            throw $exception;
        }
    }
}