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
 * PayPal request builder class for do express checkout payment
 */
class DoExpressCheckoutPaymentRequestBuilder
{
    /**
     * @var \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    protected $request = null;

    /**
     * @var \OxidEsales\PayPalModule\Core\Config
     */
    protected $payPalConfig = null;

    /**
     * @var \OxidEsales\Eshop\Application\Model\Basket
     */
    protected $basket = null;

    /**
     * @var \OxidEsales\Eshop\Application\Model\User
     */
    protected $user = null;

    /**
     * @var \OxidEsales\Eshop\Core\Session
     */
    protected $session = null;

    /**
     * @var sTransactionMode : Sale or Authorization
     */
    protected $transactionMode;

    /**
     * @var \OxidEsales\Eshop\Application\Model\Order
     */
    protected $order = null;

    /**
     * @var \OxidEsales\Eshop\Core\Language
     */
    protected $lang = null;

    /**
     * Sets request object.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Returns request object.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function getRequest()
    {
        if ($this->request === null) {
            $this->request = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class);
        }

        return $this->request;
    }

    /**
     * Returns request object
     *
     * @param \OxidEsales\PayPalModule\Core\Config $config
     */
    public function setPayPalConfig($config)
    {
        $this->payPalConfig = $config;
    }

    /**
     * Returns \OxidEsales\PayPalModule\Core\Config object.
     *
     * @return \OxidEsales\PayPalModule\Core\Config
     */
    public function getPayPalConfig()
    {
        return $this->payPalConfig;
    }

    /**
     * Sets basket.
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
            /**
             * @var \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException $exception
             */
            $exception = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException::class);
            throw $exception;
        }

        return $this->basket;
    }

    /**
     * Sets order object.
     *
     * @param \OxidEsales\Eshop\Application\Model\Order $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Tries to return basket object, but if fails throws exception.
     *
     * @return \OxidEsales\Eshop\Application\Model\Order
     *
     * @throws \OxidEsales\PayPalModule\Core\Exception\PayPalResponseException
     */
    public function getOrder()
    {
        if (is_null($this->order)) {
            /** @var \OxidEsales\PayPalModule\Core\Exception\PayPalResponseException $exception */
            $exception = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalResponseException::class, 'OEPAYPAL_ORDER_ERROR');
            throw $exception;
        }

        return $this->order;
    }

    /**
     * Sets session.
     *
     * @param \OxidEsales\Eshop\Core\Session $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }

    /**
     * Returns session.
     *
     * @return \OxidEsales\Eshop\Core\Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Returns request object.
     *
     * @param \OxidEsales\Eshop\Core\Language $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * Returns request object.
     *
     * @return \OxidEsales\Eshop\Core\Language
     */
    public function getLang()
    {
        if ($this->lang === null) {
            $this->lang = $this->getPayPalConfig()->getLang();
        }

        return $this->lang;
    }

    /**
     * Sets transaction mode.
     *
     * @param string $transactionMode
     */
    public function setTransactionMode($transactionMode)
    {
        $this->transactionMode = $transactionMode;
    }

    /**
     * Returns transaction mode.
     *
     * @return string $transactionMode
     */
    public function getTransactionMode()
    {
        return $this->transactionMode;
    }

    /**
     * Sets User object.
     *
     * @param \OxidEsales\PayPalModule\Model\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Returns User object
     *
     * @return \OxidEsales\Eshop\Application\Model\User
     *
     * @throws \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException
     */
    public function getUser()
    {
        if (is_null($this->user)) {
            /**
             * @var \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException $exception
             */
            $exception = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException::class);
            throw $exception;
        }

        return $this->user;
    }

    /**
     * Sets base parameters to request.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function buildRequest()
    {
        $this->addBaseParams();
        $this->addAddressParams();

        return $this->getRequest();
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
        $request = $this->getRequest();

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
     * Sets basic parameters to request.
     */
    public function addBaseParams()
    {
        $order = $this->getOrder();
        $config = $this->getPayPalConfig();
        $basket = $this->getBasket();
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $lang = $this->getLang();
        $request = $this->getRequest();

        $request->setParameter("TOKEN", $session->getVariable("oepaypal-token"));
        $request->setParameter("PAYERID", $session->getVariable("oepaypal-payerId"));

        $request->setParameter("PAYMENTREQUEST_0_PAYMENTACTION", $this->getTransactionMode());
        $request->setParameter("PAYMENTREQUEST_0_AMT", $this->formatFloat($basket->getPrice()->getBruttoPrice()));
        $request->setParameter("PAYMENTREQUEST_0_CURRENCYCODE", $basket->getBasketCurrency()->name);
        // IPN notify URL for PayPal
        if (!$config->suppressIPNCallbackUrl()) {
            $request->setParameter("PAYMENTREQUEST_0_NOTIFYURL", $config->getIPNCallbackUrl());
        }   

        // payment description
        $subj = sprintf($lang->translateString("OEPAYPAL_ORDER_CONF_SUBJECT"), $order->oxorder__oxordernr->value);
        $request->setParameter("PAYMENTREQUEST_0_DESC", $subj);
        $request->setParameter("PAYMENTREQUEST_0_CUSTOM", $subj);

        // Please do not change this place.
        // It is important to guarantee the future development of this OXID eShop extension and to keep it free of charge.
        // Thanks!
        $request->setParameter("BUTTONSOURCE", $config->getPartnerCode());
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
