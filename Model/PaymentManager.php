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
use OxidEsales\Eshop\Application\Model\User as EshopUserModel;
use OxidEsales\Eshop\Application\Model\UserBasket as EshopUserBasketModel;
use OxidEsales\Eshop\Application\Model\DeliverySetList as EshopDeliverySetListModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Exception\ArticleException as EshopArticleException;
use OxidEsales\Eshop\Core\Exception\StandardException as EshopStandardException;
use OxidEsales\PayPalModule\Core\Config as PayPalConfig;
use OxidEsales\PayPalModule\Core\Exception\PayPalException;
use OxidEsales\PayPalModule\Core\PayPalService;
use OxidEsales\PayPalModule\Model\PayPalRequest\GetExpressCheckoutDetailsRequestBuilder;
use OxidEsales\PayPalModule\Model\PayPalRequest\SetExpressCheckoutRequestBuilder;
use OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails;
use OxidEsales\PayPalModule\Model\Response\ResponseSetExpressCheckout;
use OxidEsales\PayPalModule\Core\PayPalCheckValidator;

/**
 * Class \OxidEsales\PayPalModule\Model\PaymentManager.
 */
class PaymentManager
{
    public const PAYPAL_SERVICE_TYPE_STANDARD = 1;
    public const PAYPAL_SERVICE_TYPE_EXPRESS = 2;

    /** @var PayPalService */
    private $payPalService;

    /** @var PayPalConfig */
    private $payPalConfig;

    public function __construct(PayPalService $payPalService)
    {
        $this->payPalService = $payPalService;
        $this->payPalConfig = oxNew(PayPalConfig::class);
    }

    public function setStandardCheckout(
        Basket $basket,
        ?User $user,
        string $returnUrl,
        string $cancelUrl,
        bool $showCartInPayPal,
        string $deliveryAddressId
    ): ResponseSetExpressCheckout {
        $builder = oxNew(SetExpressCheckoutRequestBuilder::class);

        if ($deliveryAddressId && $user) {
            $user->setSelectedAddressId($deliveryAddressId);
            $basket->setUser($user);
        }
        $basket->setPayment("oxidpaypal");
        $basket->onUpdate();
        $basket->calculateBasket(true);

        $this->validatePayment($user, $basket);

        $builder->setPayPalConfig($this->payPalConfig);
        $builder->setBasket($basket);
        $builder->setUser($user);
        $builder->setReturnUrl($returnUrl);
        $builder->setCancelUrl($cancelUrl);
        $showCartInPayPal = $showCartInPayPal && !$basket->isFractionQuantityItemsPresent();
        $builder->setShowCartInPayPal($showCartInPayPal);
        $builder->setTransactionMode($this->getTransactionMode($basket, $this->payPalConfig));

        $request = $builder->buildStandardCheckoutRequest();

        return $this->payPalService->setExpressCheckout($request);
    }

    public function setExpressCheckout(
        Basket $basket,
        ?User $user,
        string $returnUrl,
        string $cancelUrl,
        string $callbackUrl,
        bool $showCartInPayPal,
        string $shippingId = ''
    ): ResponseSetExpressCheckout
    {
        $builder = oxNew(SetExpressCheckoutRequestBuilder::class);
        $basket->setPayment('oxidpaypal');
        $basket->setShipping($shippingId);

        //calculate basket
        $mobileDefaultShippingId = $this->payPalConfig->getMobileECDefaultShippingId();
        $prevOptionValue = Registry::getConfig()->getConfigParam('blCalculateDelCostIfNotLoggedIn');
        Registry::getConfig()->setConfigParam('blCalculateDelCostIfNotLoggedIn', false);
        if (!$this->payPalConfig->isDeviceMobile()) {
            $builder->setCallBackUrl($callbackUrl);
            $builder->setMaxDeliveryAmount($this->payPalConfig->getMaxPayPalDeliveryAmount());
        } elseif (!empty($mobileDefaultShippingId) && ($shippingId === $mobileDefaultShippingId)) {
            Registry::getConfig()->setConfigParam('blCalculateDelCostIfNotLoggedIn', true);
        }

        $basket->onUpdate();
        $basket->calculateBasket(true);

        Registry::getConfig()->setConfigParam('blCalculateDelCostIfNotLoggedIn', $prevOptionValue);

        $this->validatePayment($user, $basket, true);

        $builder->setPayPalConfig($this->payPalConfig);
        $builder->setBasket($basket);
        $builder->setUser($user);
        $builder->setReturnUrl($returnUrl);
        $builder->setCancelUrl($cancelUrl);
        $showCartInPayPal = $showCartInPayPal && !$basket->isFractionQuantityItemsPresent();
        $builder->setShowCartInPayPal($showCartInPayPal);
        $builder->setTransactionMode($this->getTransactionMode($basket, $this->payPalConfig));

        $request = $builder->buildExpressCheckoutRequest();

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

    public function validatePayment(?User $user, Basket $basket, bool $isExpressCheckout = false): void
    {
        $validator = oxNew(PaymentValidator::class);
        $validator->setUser($user);
        $validator->setConfig(Registry::getConfig());
        $validator->setPrice($basket->getPrice()->getPrice());

        if ($isExpressCheckout) {
            $validator->setCheckCountry(false);
        }

        if (!$validator->isPaymentValid()) {
            $message = Registry::getLang()->translateString("OEPAYPAL_PAYMENT_NOT_VALID");
            throw oxNew(PayPalException::class, $message);
        }
    }

    public function getTransactionMode(Basket $basket, PayPalConfig $payPalConfig): string
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

    public function validateApprovedBasketAmount(float $currentAmount, float $approvedAmount): bool
    {
        $payPalCheckValidator = oxNew(PayPalCheckValidator::class);
        $payPalCheckValidator->setNewBasketAmount($currentAmount);
        $payPalCheckValidator->setOldBasketAmount($approvedAmount);

        return $payPalCheckValidator->isPayPalCheckValid();
    }

    public function initializeUserData(ResponseGetExpressCheckoutDetails $details, string $authenticatedUserId): EshopUserModel
    {
        $userEmail = $details->getEmail();

        /** @var EshopUserModel $user */
        $authenticatedUser = oxNew(EshopUserModel::class);
        $authenticatedUserExists = false;
        if ($authenticatedUser->load($authenticatedUserId)) {
            $userEmail = $authenticatedUser->getFieldData('oxusername');
            $authenticatedUserExists = true;
        }

        $user = oxNew(EshopUserModel::class);
        if ($userId = $user->isRealPayPalUser($userEmail)) {
            // if user exist
            $user->load($userId);

            if (!$authenticatedUserExists) {
                if ($user->hasNoInvoiceAddress()) {
                    //this can only happen when user was registered via graphql, is anonymous and did not yet set invoice data
                    $user->setInvoiceDataFromPayPalResult($details);
                } elseif (!$user->isSamePayPalUser($details)) {
                    $exception = new EshopStandardException();
                    $exception->setMessage('OEPAYPAL_ERROR_USER_ADDRESS');
                    throw $exception;
                }
            } elseif ($user->hasNoInvoiceAddress()) {
                //this can only happen when user was registered via graphql, is logged in and did not yet set invoice data
                $user->setInvoiceDataFromPayPalResult($details);
            } elseif (!$user->isSameAddressUserPayPalUser($details) || !$user->isSameAddressPayPalUser($details)) {
                // user has selected different address in PayPal (not equal with usr shop address)
                // so adding PayPal address as new user address to shop user account
                $address = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
                $address->createPayPalAddress($details, $userId);
                $user->setSelectedAddressId($address->getId());
            } else {
                // user uses billing address for shipping
                $user->setSelectedAddressId(null);
            }
        } else {
            $user->setId($authenticatedUserId);
            $user->createPayPalUser($details);
            $user->load($authenticatedUserId);
        }

        $user->setAnonymousUserId($authenticatedUserId);

        return $user;
    }

    public function extractShippingId(
        string $shippingOptionName,
        ?EshopUserModel $user = null,
        array $deliverySetList = null
    ): ?string
    {
        $result = null;
        $shippingOptionName = $this->reencodeHtmlEntities($shippingOptionName);
        $name = trim(str_replace(Registry::getLang()->translateString("OEPAYPAL_PRICE"), "", $shippingOptionName));

        if (!$deliverySetList) {
            $delSetList = $this->getDeliverySetList($user);
            $deliverySetList = $this->makeUniqueNames($delSetList);
        }

        if (is_array($deliverySetList)) {
            $flipped = array_flip($deliverySetList);
            $result = $flipped[$name];
        }

        return $result;
    }

    public function getDeliverySetList(EshopUserModel $user): array
    {
        $delSetList = oxNew(EshopDeliverySetListModel::class);

        return $delSetList->getDeliverySetList($user, $this->getUserShippingCountryId($user));
    }

    public function getUserShippingCountryId(EshopUserModel $user): string
    {
        if ($user->getSelectedAddressId() && $user->getSelectedAddress()) {
            $countryId = $user->getSelectedAddress()->getFieldData('oxcountryid');
        } else {
            $countryId = (string) $user->getFieldData('oxcountryid');
        }

        return $countryId;
    }


    public function reencodeHtmlEntities(string $input): string
    {
        $charset = $this->payPalConfig->getCharset();

        return htmlentities(html_entity_decode($input, ENT_QUOTES, $charset), ENT_QUOTES, $charset);
    }

    /**
     * @var EshopDeliverySetListModel[] $deliverySetList
     */
    public function makeUniqueNames(array $deliverySetList): array
    {
        $result = [];
        $nameCounts = [];

        foreach ($deliverySetList as $deliverySet) {
            $deliverySetName = trim($deliverySet->oxdeliveryset__oxtitle->value);

            if (isset($nameCounts[$deliverySetName])) {
                $nameCounts[$deliverySetName] += 1;
            } else {
                $nameCounts[$deliverySetName] = 1;
            }

            $suffix = ($nameCounts[$deliverySetName] > 1) ? " (" . $nameCounts[$deliverySetName] . ")" : '';
            $result[$deliverySet->oxdeliveryset__oxid->value] = $this->reencodeHtmlEntities($deliverySetName . $suffix);
        }

        return $result;
    }

    public function prepareCallback(string $basketId): Basket
    {
        $sessionBasket = oxNew(Basket::class);
        $userBasket = oxNew(EshopUserBasketModel::class);
        if (!$userBasket->load($basketId)) {
            return $sessionBasket;
        }

        foreach ($userBasket->getItems() as $basketItem) {
            try {
                $selectList = $basketItem->getSelList();

                $sessionBasket->addToBasket(
                    $basketItem->getFieldData('oxartid'),
                    $basketItem->getFieldData('oxamount'),
                    $selectList,
                    $basketItem->getPersParams(),
                    true
                );
            } catch (EshopArticleException $exception) {
                // caught and ignored
            }
        }

        //calculate the basket
        Registry::getConfig()->setConfigParam('blCalculateDelCostIfNotLoggedIn', true);
        if ($userBasket->getFieldData('OEGQL_DELIVERYMETHODID')) {
            $sessionBasket->setShipping($userBasket->getFieldData('OEGQL_DELIVERYMETHODID'));
        }
        $sessionBasket->calculateBasket(true);

        return $sessionBasket;
    }

    /**
     * We do not have the session stored when using the graphql API.
     * Callback needs to use the basket id instead.
     */
    public function getGraphQLCallBackUrl(string $basketId): string
    {
        return Registry::getConfig()->getSslShopUrl() . "index.php?lang=" . Registry::getLang()->getBaseLanguage() .
                   "&basketid=" . $basketId .
                   "&shp=" . Registry::getConfig()->getShopId() .
                   "&cl=oepaypalexpresscheckoutdispatcher&fnc=processGraphQLCallBack";
    }
}
