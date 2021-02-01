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
use OxidEsales\GraphQL\Storefront\Shared\Infrastructure\Basket as SharedBasketInfrastructure;
use OxidEsales\PayPalModule\GraphQL\DataType\PayPalCommunicationInformation;
use OxidEsales\PayPalModule\GraphQL\DataType\PayPalTokenStatus;
use OxidEsales\PayPalModule\GraphQL\Infrastructure\Request as RequestInfrastructure;

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

    public function getPayPalTokenStatus(string $paypalToken): PayPalTokenStatus
    {
        /**
         * @TODO: this method name is not correct. We cannot decide about token
         * status by checking if it have payer id available
         */

        $communicationConfirmed = $this->getPayerId($paypalToken) ? true : false;

        return new PayPalTokenStatus(
            $paypalToken,
            $communicationConfirmed
        );
    }

    public function getPayerId(string $token): ?string
    {
        $paymentManager = $this->requestInfrastructure->getPaymentManager();
        $details = $paymentManager->getExpressCheckoutDetails($token);

        return $details->getPayerId();
    }

    public function getPayPalCommunicationInformation(
        BasketDataType $basket,
        string $returnUrl,
        string $cancelUrl,
        bool $displayBasketInPayPal
    ): PayPalCommunicationInformation {
        $paymentManager = $this->requestInfrastructure->getPaymentManager();
        $standardPaypalController = $this->requestInfrastructure->getStandardDispatcher();

        $response = $paymentManager->setExpressCheckout(
            $this->sharedBasketInfrastructure->getCalculatedBasket($basket),
            $standardPaypalController->getUser(),
            $returnUrl,
            $cancelUrl,
            $displayBasketInPayPal
        );

        $token = $response->getToken();

        return new PayPalCommunicationInformation(
            $token,
            $this->getPayPalCommunicationUrl($token)
        );
    }

    public function getPayPalCommunicationUrl($token): string
    {
        $payPalConfig = $this->requestInfrastructure->getPayPalConfig();
        return $payPalConfig->getPayPalCommunicationUrl($token);
    }
}