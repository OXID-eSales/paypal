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

namespace OxidEsales\PayPalModule\Model;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\PayPalModule\Core\Config;
use OxidEsales\PayPalModule\Core\Exception\PayPalException;
use OxidEsales\PayPalModule\Core\PayPalService;
use OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder;
use OxidEsales\PayPalModule\Model\PayPalRequest\SetExpressCheckoutRequestBuilder;
use OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails;
use OxidEsales\PayPalModule\Model\Response\ResponseSetExpressCheckout;

/**
 * Class \OxidEsales\PayPalModule\Model\PaymentManager.
 */
class PaymentManager
{

    /** @var PayPalService */
    private $payPalService;

    public function __construct(PayPalService $payPalService)
    {
        $this->payPalService = $payPalService;
    }

    public function setExpressCheckout(
        Basket $basket,
        $user,
        string $returnUrl,
        string $cancelUrl,
        bool $showCartInPayPal
    ): ResponseSetExpressCheckout {
        $payPalConfig = oxNew(Config::class);
        $builder = oxNew(SetExpressCheckoutRequestBuilder::class);

        $basket->setPayment("oxidpaypal");
        $basket->onUpdate();
        $basket->calculateBasket(true);

        $validator = oxNew(PaymentValidator::class);
        $validator->setUser($user);
        $validator->setConfig(Registry::getConfig());
        $validator->setPrice($basket->getPrice()->getPrice());

        if (!$validator->isPaymentValid()) {
            $message = Registry::getLang()->translateString("OEPAYPAL_PAYMENT_NOT_VALID");
            throw oxNew(PayPalException::class, $message);
        }

        $builder->setPayPalConfig($payPalConfig);
        $builder->setBasket($basket);
        $builder->setUser($user);
        $builder->setReturnUrl($returnUrl);
        $builder->setCancelUrl($cancelUrl);
        $showCartInPayPal = $showCartInPayPal && !$basket->isFractionQuantityItemsPresent();
        $builder->setShowCartInPayPal($showCartInPayPal);
        $builder->setTransactionMode($this->getTransactionMode($basket, $payPalConfig));

        $request = $builder->buildStandardCheckoutRequest();

        return $this->payPalService->setExpressCheckout($request);
    }

    public function getExpressCheckoutDetails(?string $token = null): ResponseGetExpressCheckoutDetails
    {
        $builder = oxNew(GetExpressCheckoutDetailsRequestBuilder::class);

        if ($token) {
            $builder->setToken($token);
        }

        $request = $builder->buildRequest();

        return $this->payPalService->getExpressCheckoutDetails($request);
    }

    protected function getTransactionMode(Basket $basket, Config $payPalConfig): string
    {
        $transactionMode = $payPalConfig->getTransactionMode();

        if ($transactionMode == "Automatic") {
            $outOfStockValidator = new OutOfStockValidator();
            $outOfStockValidator->setBasket($basket);
            $outOfStockValidator->setEmptyStockLevel($payPalConfig->getEmptyStockLevel());

            $transactionMode = ($outOfStockValidator->hasOutOfStockArticles()) ? "Authorization" : "Sale";

            return $transactionMode;
        }

        return $transactionMode;
    }
}
