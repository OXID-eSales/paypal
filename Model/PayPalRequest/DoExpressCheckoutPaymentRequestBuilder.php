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
 * @copyright (C) OXID eSales AG 2003-2017
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
    protected $_oRequest = null;

    /**
     * @var \OxidEsales\PayPalModule\Core\Config
     */
    protected $_oPayPalConfig = null;

    /**
     * @var \OxidEsales\Eshop\Application\Model\Basket
     */
    protected $_oBasket = null;

    /**
     * @var \OxidEsales\Eshop\Application\Model\User
     */
    protected $_oUser = null;

    /**
     * @var \OxidEsales\Eshop\Core\Session
     */
    protected $_oSession = null;

    /**
     * @var sTransactionMode : Sale or Authorization
     */
    protected $_sTransactionMode;

    /**
     * @var \OxidEsales\Eshop\Application\Model\Order
     */
    protected $_oOrder = null;

    /**
     * @var \OxidEsales\Eshop\Core\Language
     */
    protected $_oLang = null;

    /**
     * Sets request object.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $oRequest
     */
    public function setRequest($oRequest)
    {
        $this->_oRequest = $oRequest;
    }

    /**
     * Returns request object.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function getRequest()
    {
        if ($this->_oRequest === null) {
            $this->_oRequest = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class);
        }

        return $this->_oRequest;
    }

    /**
     * Returns request object
     *
     * @param \OxidEsales\PayPalModule\Core\Config $oConfig
     */
    public function setPayPalConfig($oConfig)
    {
        $this->_oPayPalConfig = $oConfig;
    }

    /**
     * Returns \OxidEsales\PayPalModule\Core\Config object.
     *
     * @return \OxidEsales\PayPalModule\Core\Config
     */
    public function getPayPalConfig()
    {
        return $this->_oPayPalConfig;
    }

    /**
     * Sets basket.
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $oBasket
     */
    public function setBasket($oBasket)
    {
        $this->_oBasket = $oBasket;
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
        if (is_null($this->_oBasket)) {
            /**
             * @var \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException $oException
             */
            $oException = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException::class);
            throw $oException;
        }

        return $this->_oBasket;
    }

    /**
     * Sets order object.
     *
     * @param \OxidEsales\Eshop\Application\Model\Order $oOrder
     */
    public function setOrder($oOrder)
    {
        $this->_oOrder = $oOrder;
    }

    /**
     * Tries to return basket object, but if fails throws exception.
     *
     * @return \OxidEsales\Eshop\Application\Model\Basket
     *
     * @throws \OxidEsales\PayPalModule\Core\Exception\PayPalResponseException
     */
    public function getOrder()
    {
        if (is_null($this->_oOrder)) {
            /**
             * @var \OxidEsales\PayPalModule\Core\Exception\PayPalResponseException $oException
             */
            $oException = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalResponseException::class);
            $oException->setMessage('OEPAYPAL_ORDER_ERROR');
            throw $oException;
        }

        return $this->_oOrder;
    }

    /**
     * Sets session.
     *
     * @param \OxidEsales\Eshop\Core\Session $oSession
     */
    public function setSession($oSession)
    {
        $this->_oSession = $oSession;
    }

    /**
     * Returns session.
     *
     * @return \OxidEsales\Eshop\Core\Session
     */
    public function getSession()
    {
        return $this->_oSession;
    }

    /**
     * Returns request object.
     *
     * @param \OxidEsales\Eshop\Core\Language $oLang
     */
    public function setLang($oLang)
    {
        $this->_oLang = $oLang;
    }

    /**
     * Returns request object.
     *
     * @return \OxidEsales\Eshop\Core\Language
     */
    public function getLang()
    {
        if ($this->_oLang === null) {
            $this->_oLang = $this->getPayPalConfig()->getLang();
        }

        return $this->_oLang;
    }

    /**
     * Sets transaction mode.
     *
     * @param string $sTransactionMode
     */
    public function setTransactionMode($sTransactionMode)
    {
        $this->_sTransactionMode = $sTransactionMode;
    }

    /**
     * Returns transaction mode.
     *
     * @return string $sTransactionMode
     */
    public function getTransactionMode()
    {
        return $this->_sTransactionMode;
    }

    /**
     * Sets User object.
     *
     * @param \OxidEsales\PayPalModule\Model\User $oUser
     */
    public function setUser($oUser)
    {
        $this->_oUser = $oUser;
    }

    /**
     * Returns User object
     *
     * @return \OxidEsales\PayPalModule\Model\User
     *
     * @throws \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException
     */
    public function getUser()
    {
        if (is_null($this->_oUser)) {
            /**
             * @var \OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException $oException
             */
            $oException = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalMissingParameterException::class);
            throw $oException;
        }

        return $this->_oUser;
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
     */
    public function addAddressParams()
    {
        $oUser = $this->getUser();
        if (!$oUser) {
            return;
        }
        $oRequest = $this->getRequest();

        $sAddressId = $oUser->getSelectedAddressId();
        if ($sAddressId) {
            $oAddress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
            $oAddress->load($sAddressId);

            $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTONAME", getStr()->html_entity_decode($oAddress->oxaddress__oxfname->value . " " . $oAddress->oxaddress__oxlname->value));
            $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTOSTREET", getStr()->html_entity_decode($oAddress->oxaddress__oxstreet->value . " " . $oAddress->oxaddress__oxstreetnr->value));
            $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTOCITY", $oAddress->oxaddress__oxcity->value);
            $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTOZIP", $oAddress->oxaddress__oxzip->value);
            $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTOPHONENUM", $oAddress->oxaddress__oxfon->value);

            $oCountry = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
            $oCountry->load($oAddress->oxaddress__oxcountryid->value);
            $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE", $oCountry->oxcountry__oxisoalpha2->value);

            if ($oAddress->oxaddress__oxstateid->value) {
                $oState = oxNew(\OxidEsales\Eshop\Application\Model\State::class);
                $oState->load($oAddress->oxaddress__oxstateid->value);
                $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTOSTATE", $oState->oxstates__oxisoalpha2->value);
            }
        } else {
            $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTONAME", getStr()->html_entity_decode($oUser->oxuser__oxfname->value . " " . $oUser->oxuser__oxlname->value));
            $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTOSTREET", getStr()->html_entity_decode($oUser->oxuser__oxstreet->value . " " . $oUser->oxuser__oxstreetnr->value));
            $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTOCITY", $oUser->oxuser__oxcity->value);
            $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTOZIP", $oUser->oxuser__oxzip->value);
            $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTOPHONENUM", $oUser->oxuser__oxfon->value);

            $oCountry = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
            $oCountry->load($oUser->oxuser__oxcountryid->value);
            $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE", $oCountry->oxcountry__oxisoalpha2->value);

            if ($oUser->oxuser__oxstateid->value) {
                $oState = oxNew(\OxidEsales\Eshop\Application\Model\State::class);
                $oState->load($oUser->oxuser__oxstateid->value);
                $oRequest->setParameter("PAYMENTREQUEST_0_SHIPTOSTATE", $oState->oxstates__oxisoalpha2->value);
            }
        }
    }

    /**
     * Sets basic parameters to request.
     */
    public function addBaseParams()
    {
        $oOrder = $this->getOrder();
        $oConfig = $this->getPayPalConfig();
        $oBasket = $this->getBasket();
        $oSession = $this->getSession();
        $oLang = $this->getLang();
        $oRequest = $this->getRequest();

        $oRequest->setParameter("TOKEN", $oSession->getVariable("oepaypal-token"));
        $oRequest->setParameter("PAYERID", $oSession->getVariable("oepaypal-payerId"));

        $oRequest->setParameter("PAYMENTREQUEST_0_PAYMENTACTION", $this->getTransactionMode());
        $oRequest->setParameter("PAYMENTREQUEST_0_AMT", $this->_formatFloat($oBasket->getPrice()->getBruttoPrice()));
        $oRequest->setParameter("PAYMENTREQUEST_0_CURRENCYCODE", $oBasket->getBasketCurrency()->name);
        // IPN notify URL for PayPal
        $oRequest->setParameter("PAYMENTREQUEST_0_NOTIFYURL", $oConfig->getIPNCallbackUrl());

        // payment description
        $sSubj = sprintf($oLang->translateString("OEPAYPAL_ORDER_CONF_SUBJECT"), $oOrder->oxorder__oxordernr->value);
        $oRequest->setParameter("PAYMENTREQUEST_0_DESC", $sSubj);
        $oRequest->setParameter("PAYMENTREQUEST_0_CUSTOM", $sSubj);

        // Please do not change this place.
        // It is important to guarantee the future development of this OXID eShop extension and to keep it free of charge.
        // Thanks!
        $oRequest->setParameter("BUTTONSOURCE", $oConfig->getPartnerCode());
    }

    /**
     * Formats given float/int value into PayPal friendly form
     *
     * @param float $fIn value to format
     *
     * @return string
     */
    protected function _formatFloat($fIn)
    {
        return sprintf("%.2f", $fIn);
    }
}
