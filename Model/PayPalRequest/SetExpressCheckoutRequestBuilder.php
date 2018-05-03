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

namespace OxidEsales\PayPalModule\Model\PayPalRequest;

/**
 * PayPal request builder class for set express checkout
 */
class SetExpressCheckoutRequestBuilder
{
    /**
     * PayPal Request.
     *
     * @var \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    protected $payPalRequest = null;

    /**
     * PayPal Config.
     *
     * @var \OxidEsales\PayPalModule\Core\Config
     */
    protected $payPalConfig = null;

    /**
     * Basket object.
     *
     * @var \OxidEsales\PayPalModule\Model\Basket
     */
    protected $basket = null;

    /**
     * User object.
     *
     * @var \OxidEsales\PayPalModule\Model\User
     */
    protected $user = null;

    /**
     * Language object.
     *
     * @var \OxidEsales\Eshop\Core\Language
     */
    protected $lang = null;

    /**
     * Url to return to after PayPal payment is done.
     *
     * @var string
     */
    protected $returnUrl = null;

    /**
     * Url to return to if PayPal payment was canceled.
     *
     * @var string
     */
    protected $cancelUrl = null;

    /**
     * Url for PayPal CallBack.
     *
     * @var string
     */
    protected $callBackUrl = null;

    /**
     * Show basket items in PayPal.
     *
     * @var bool
     */
    protected $showCartInPayPal = false;

    /**
     * Transaction mode: Sale|Authorization.
     *
     * @var string
     */
    protected $transactionMode;

    /**
     * Maximum possible delivery costs value.
     *
     * @var double
     */
    protected $maxDeliveryAmount = 0;


    /**
     * Sets max delivery amount.
     *
     * @param double $maxDeliveryAmount
     */
    public function setMaxDeliveryAmount($maxDeliveryAmount)
    {
        $this->maxDeliveryAmount = $maxDeliveryAmount;
    }

    /**
     * Return max delivery amount.
     *
     * @return double
     */
    public function getMaxDeliveryAmount()
    {
        return $this->maxDeliveryAmount;
    }

    /**
     * Sets PayPal request object.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request
     */
    public function setPayPalRequest($request)
    {
        $this->payPalRequest = $request;
    }

    /**
     * Returns PayPal request object; initiates if not set.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function getPayPalRequest()
    {
        if ($this->payPalRequest === null) {
            $this->payPalRequest = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class);
        }

        return $this->payPalRequest;
    }

    /**
     * Returns config object.
     *
     * @param \OxidEsales\PayPalModule\Core\Config $config
     */
    public function setPayPalConfig($config)
    {
        $this->payPalConfig = $config;
    }

    /**
     * Returns config object.
     *
     * @return \OxidEsales\PayPalModule\Core\Config
     *
     * @throws \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException
     */
    public function getPayPalConfig()
    {
        if (!$this->payPalConfig) {
            /** @var \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException $exception */
            $exception = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException::class);
            throw $exception;
        }

        return $this->payPalConfig;
    }

    /**
     * Sets Basket object.
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $basket
     */
    public function setBasket($basket)
    {
        $this->basket = $basket;
    }

    /**
     * Returns basket object.
     *
     * @return \OxidEsales\Eshop\Application\Model\Basket
     *
     * @throws \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException
     */
    public function getBasket()
    {
        if (is_null($this->basket)) {
            /** @var \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException $exception */
            $exception = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException::class);
            throw $exception;
        }

        return $this->basket;
    }

    /**
     * Sets User object
     *
     * @param \OxidEsales\Eshop\Application\Model\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Returns User object
     *
     * @return \OxidEsales\PayPalModule\Model\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets Language object
     *
     * @param \OxidEsales\Eshop\Core\Language $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * Returns Language object.
     *
     * @return \OxidEsales\Eshop\Core\Language
     */
    public function getLang()
    {
        if (is_null($this->lang)) {
            $this->lang = $this->getPayPalConfig()->getLang();
        }

        return $this->lang;
    }

    /**
     * Sets CallBack url.
     *
     * @param string $callBackUrl
     */
    public function setCallBackUrl($callBackUrl)
    {
        $this->callBackUrl = $callBackUrl;
    }

    /**
     * Returns CallBack url.
     *
     * @return string
     */
    public function getCallBackUrl()
    {
        return $this->callBackUrl;
    }

    /**
     * Sets Cancel Url.
     *
     * @param string $cancelUrl
     */
    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;
    }

    /**
     * Returns Cancel Url.
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    /**
     * Sets Return Url.
     *
     * @param string $returnUrl
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * Returns Return Url.
     *
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * Sets whether to show basket in PayPal.
     *
     * @param string $showCartInPayPal
     */
    public function setShowCartInPayPal($showCartInPayPal)
    {
        $this->showCartInPayPal = $showCartInPayPal;
    }

    /**
     * Returns whether to show basket in PayPal.
     *
     * @return string
     */
    public function getShowCartInPayPal()
    {
        return $this->showCartInPayPal;
    }

    /**
     * Sets Transaction mode.
     *
     * @param string $transactionMode
     */
    public function setTransactionMode($transactionMode)
    {
        $this->transactionMode = $transactionMode;
    }

    /**
     * Returns Transaction mode.
     *
     * @return string $transactionMode
     */
    public function getTransactionMode()
    {
        return $this->transactionMode;
    }

    /**
     * Builds PayPal request for express checkout.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function buildExpressCheckoutRequest()
    {
        $this->addBaseParams();
        $this->addCallBackUrl();
        $this->addBasketParams();
        $this->addDescriptionParams();
        $this->turnOffShippingAddressCollection();
        $this->setMaximumOrderAmount();

        if ($this->getShowCartInPayPal()) {
            $this->addBasketItemParams();
        } else {
            $this->addBasketGrandTotalParams();
        }
        $this->addAddressParams();

        return $this->getPayPalRequest();
    }

    /**
     * Builds PayPal request for standard checkout.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function buildStandardCheckoutRequest()
    {
        $this->addBaseParams();
        $this->addBasketParams();
        $this->addDescriptionParams();
        $this->disableSelectingDifferentAddressInPayPal();
        $this->setMaximumOrderAmount();

        if ($this->getShowCartInPayPal()) {
            $this->addBasketItemParams();
        } else {
            $this->addBasketGrandTotalParams();
        }
        $this->addAddressParams();

        return $this->getPayPalRequest();
    }

    /**
     * Sets base parameters to request.
     */
    public function addBaseParams()
    {
        $request = $this->getPayPalRequest();
        $payPalConfig = $this->getPayPalConfig();

        $request->setParameter("CALLBACKVERSION", "84.0");
        $request->setParameter("LOCALECODE", $this->getLang()->translateString("OEPAYPAL_LOCALE"));
        // enabled guest buy (Buyer does not need to create a PayPal account to check out)
        $request->setParameter("SOLUTIONTYPE", ($payPalConfig->isGuestBuyEnabled() ? "Sole" : "Mark"));
        $request->setParameter("BRANDNAME", $payPalConfig->getBrandName());
        $request->setParameter("CARTBORDERCOLOR", $payPalConfig->getBorderColor());

        $request->setParameter("RETURNURL", $this->getReturnUrl());
        $request->setParameter("CANCELURL", $this->getCancelUrl());

        if ($logoImage = $payPalConfig->getLogoUrl()) {
            $request->setParameter("LOGOIMG", $logoImage);
        }

        $request->setParameter("PAYMENTREQUEST_0_PAYMENTACTION", $this->getTransactionMode());
    }

    /**
     * Adds callback parameters to request.
     */
    public function addCallBackUrl()
    {
        $request = $this->getPayPalRequest();

        $request->setParameter("CALLBACK", $this->getCallbackUrl());
        $request->setParameter("CALLBACKTIMEOUT", 6);
    }

    /**
     * Turn off shipping address collection.
     */
    public function turnOffShippingAddressCollection()
    {
        $this->getPayPalRequest()->setParameter("NOSHIPPING", "2");
    }

    /**
     * Disables selecting different address in PayPal side.
     */
    public function disableSelectingDifferentAddressInPayPal()
    {
        $this->getPayPalRequest()->setParameter("ADDROVERRIDE", "1");
    }

    /**
     * Calculating maximum order amount
     * and adding all used discounts (needed because of bug in PayPal - somehow it substract discount from MAXAMT)
     * additionally +1 as PayPal recommends this value a little bit greater than original.
     */
    public function setMaximumOrderAmount()
    {
        $basket = $this->getBasket();
        $request = $this->getPayPalRequest();

        $request->setParameter("MAXAMT", $this->formatFloat(($basket->getPrice()->getBruttoPrice() + $basket->getDiscountSumPayPalBasket() + $this->getMaxDeliveryAmount() + 1)));
    }

    /**
     * Sets basket parameters to request.
     */
    public function addBasketParams()
    {
        $request = $this->getPayPalRequest();
        $basket = $this->getBasket();

        $virtualBasket = $basket->isVirtualPayPalBasket();

        // only downloadable products? missing getter on oxBasket yet
        $request->setParameter("NOSHIPPING", $virtualBasket ? "1" : "0");

        if ($virtualBasket) {
            $request->setParameter("REQCONFIRMSHIPPING", "0");
        }
        // passing basket VAT (tax) value. It is required as in Net mode articles are without VAT, but basket is with VAT.
        // PayPal need this value to check if all articles sum match basket sum.
        if ($basket->isCalculationModeNetto()) {
            $request->setParameter("PAYMENTREQUEST_0_TAXAMT", $this->formatFloat($basket->getPayPalBasketVatValue()));
        }

        $request->setParameter("PAYMENTREQUEST_0_AMT", $this->formatFloat($basket->getPrice()->getBruttoPrice()));
        $request->setParameter("PAYMENTREQUEST_0_CURRENCYCODE", $basket->getBasketCurrency()->name);
        $request->setParameter("PAYMENTREQUEST_0_ITEMAMT", $this->formatFloat($basket->getSumOfCostOfAllItemsPayPalBasket()));
        $request->setParameter("PAYMENTREQUEST_0_SHIPPINGAMT", $this->formatFloat($basket->getDeliveryCosts()));
        $request->setParameter("PAYMENTREQUEST_0_SHIPDISCAMT", $this->formatFloat($basket->getDiscountSumPayPalBasket() * -1));

        $delivery = oxNew(\OxidEsales\Eshop\Application\Model\DeliverySet::class);
        $deliveryName = ($delivery->load($basket->getShippingId())) ? $delivery->oxdeliveryset__oxtitle->value : "#1";

        $request->setParameter("L_SHIPPINGOPTIONISDEFAULT0", "true");
        $request->setParameter("L_SHIPPINGOPTIONNAME0", $deliveryName);
        $request->setParameter("L_SHIPPINGOPTIONAMOUNT0", $this->formatFloat($basket->getDeliveryCosts()));
    }

    /**
     * Sets transaction description parameters.
     */
    public function addDescriptionParams()
    {
        $basket = $this->getBasket();
        $config = $this->getPayPalConfig();
        $request = $this->getPayPalRequest();

        // description
        $shopNameFull = $config->getBrandName();
        $shopName = substr($shopNameFull, 0, 70);
        if ($shopNameFull != $shopName) {
            $shopName .= "...";
        }

        $subj = sprintf($this->getLang()->translateString("OEPAYPAL_ORDER_SUBJECT"), $shopName, $basket->getFPrice(), $basket->getBasketCurrency()->name);
        $request->setParameter("PAYMENTREQUEST_0_DESC", $subj);
        $request->setParameter("PAYMENTREQUEST_0_CUSTOM", $subj);
    }

    /**
     * Sets basket items parameters to request.
     */
    public function addBasketItemParams()
    {
        $basket = $this->getBasket();
        $lang = $this->getLang();
        $request = $this->getPayPalRequest();

        $pos = 0;
        foreach ($basket->getContents() as $basketItem) {
            $request->setParameter("L_PAYMENTREQUEST_0_NAME{$pos}", \OxidEsales\Eshop\Core\Str::getStr()->html_entity_decode($basketItem->getTitle()));
            $request->setParameter("L_PAYMENTREQUEST_0_AMT{$pos}", $this->formatFloat($basketItem->getUnitPrice()->getPrice()));
            $request->setParameter("L_PAYMENTREQUEST_0_QTY{$pos}", (int) $basketItem->getAmount());
            $request->setParameter("L_PAYMENTREQUEST_0_ITEMURL{$pos}", $basketItem->getLink());

            $basketProduct = $basketItem->getArticle();
            $request->setParameter("L_PAYMENTREQUEST_0_NUMBER{$pos}", $basketProduct->oxarticles__oxartnum->value);

            $pos++;
        }

        //adding payment costs as product
        if ($basket->getPayPalPaymentCosts() > 0) {
            $paymentTitle = $lang->translateString("OEPAYPAL_SURCHARGE") . " " . $lang->translateString("OEPAYPAL_TYPE_OF_PAYMENT");
            $request->setParameter("L_PAYMENTREQUEST_0_NAME{$pos}", $paymentTitle);
            $request->setParameter("L_PAYMENTREQUEST_0_AMT{$pos}", $this->formatFloat($basket->getPayPalPaymentCosts()));
            $request->setParameter("L_PAYMENTREQUEST_0_QTY{$pos}", 1);

            $pos++;
        }

        //adding wrapping as product
        if ($basket->getPayPalWrappingCosts() > 0) {
            $request->setParameter("L_PAYMENTREQUEST_0_NAME{$pos}", $lang->translateString("OEPAYPAL_GIFTWRAPPER"));
            $request->setParameter("L_PAYMENTREQUEST_0_AMT{$pos}", $this->formatFloat($basket->getPayPalWrappingCosts()));
            $request->setParameter("L_PAYMENTREQUEST_0_QTY{$pos}", 1);

            $pos++;
        }

        //adding greeting card as product
        if ($basket->getPayPalGiftCardCosts() > 0) {
            $request->setParameter("L_PAYMENTREQUEST_0_NAME{$pos}", $lang->translateString("OEPAYPAL_GREETING_CARD"));
            $request->setParameter("L_PAYMENTREQUEST_0_AMT{$pos}", $this->formatFloat($basket->getPayPalGiftCardCosts()));
            $request->setParameter("L_PAYMENTREQUEST_0_QTY{$pos}", 1);

            $pos++;
        }
    }

    /**
     * Sets basket Grand Total params to request.
     */
    public function addBasketGrandTotalParams()
    {
        $basket = $this->getBasket();
        $request = $this->getPayPalRequest();

        $request->setParameter("L_PAYMENTREQUEST_0_NAME0", $this->getLang()->translateString("OEPAYPAL_GRAND_TOTAL"));
        $request->setParameter("L_PAYMENTREQUEST_0_AMT0", $this->formatFloat($basket->getSumOfCostOfAllItemsPayPalBasket()));
        $request->setParameter("L_PAYMENTREQUEST_0_QTY0", 1);
    }

    /**
     * Sets Address parameters to request.
     *
     * @return null
     */
    public function addAddressParams()
    {
        $user = $this->getUser();
        if (!$user) {
            return;
        }
        $request = $this->getPayPalRequest();

        $request->setParameter("EMAIL", $user->oxuser__oxusername->value);

        $addressId = $user->getSelectedAddressId();
        if ($addressId) {
            $address = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
            $address->load($addressId);

            $request->setParameter("PAYMENTREQUEST_0_SHIPTONAME", \OxidEsales\Eshop\Core\Str::getStr()->html_entity_decode($address->oxaddress__oxfname->value . " " . $address->oxaddress__oxlname->value));
            $request->setParameter("PAYMENTREQUEST_0_SHIPTOSTREET", \OxidEsales\Eshop\Core\Str::getStr()->html_entity_decode($address->oxaddress__oxstreet->value . " " . $address->oxaddress__oxstreetnr->value));
            $request->setParameter("PAYMENTREQUEST_0_SHIPTOCITY", $address->oxaddress__oxcity->value);
            $request->setParameter("PAYMENTREQUEST_0_SHIPTOZIP", $address->oxaddress__oxzip->value);
            $request->setParameter("PAYMENTREQUEST_0_SHIPTOPHONENUM", $address->oxaddress__oxfon->value);

            $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
            $country->load($address->oxaddress__oxcountryid->value);
            $request->setParameter("PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE", $country->oxcountry__oxisoalpha2->value);

            if ($address->oxaddress__oxstateid->value) {
                $state = oxNew(\OxidEsales\Eshop\Application\Model\State::class);
                $state->load($address->oxaddress__oxstateid->value);
                $request->setParameter("PAYMENTREQUEST_0_SHIPTOSTATE", $state->oxstates__oxisoalpha2->value);
            }
        } else {
            $request->setParameter("PAYMENTREQUEST_0_SHIPTONAME", \OxidEsales\Eshop\Core\Str::getStr()->html_entity_decode($user->oxuser__oxfname->value . " " . $user->oxuser__oxlname->value));
            $request->setParameter("PAYMENTREQUEST_0_SHIPTOSTREET", \OxidEsales\Eshop\Core\Str::getStr()->html_entity_decode($user->oxuser__oxstreet->value . " " . $user->oxuser__oxstreetnr->value));
            $request->setParameter("PAYMENTREQUEST_0_SHIPTOCITY", $user->oxuser__oxcity->value);
            $request->setParameter("PAYMENTREQUEST_0_SHIPTOZIP", $user->oxuser__oxzip->value);
            $request->setParameter("PAYMENTREQUEST_0_SHIPTOPHONENUM", $user->oxuser__oxfon->value);

            $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
            $country->load($user->oxuser__oxcountryid->value);
            $request->setParameter("PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE", $country->oxcountry__oxisoalpha2->value);

            if ($user->oxuser__oxstateid->value) {
                $state = oxNew(\OxidEsales\Eshop\Application\Model\State::class);
                $state->load($user->oxuser__oxstateid->value);
                $request->setParameter("PAYMENTREQUEST_0_SHIPTOSTATE", $state->oxstates__oxisoalpha2->value);
            }
        }
    }

    /**
     * Formats given float/int value into PayPal friendly form
     *
     * @param float $in value to format
     *
     * @return string
     */
    protected function formatFloat($in)
    {
        return sprintf("%.2f", $in);
    }
}
