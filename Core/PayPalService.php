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

namespace OxidEsales\PayPalModule\Core;

/**
 * PayPal Service class
 */
class PayPalService
{
    /**
     * PayPal Caller
     *
     * @var \OxidEsales\PayPalModule\Core\Caller
     */
    protected $_oCaller = null;

    /**
     * PayPal Caller.
     *
     * @var \OxidEsales\PayPalModule\Core\Config
     */
    protected $_oPayPalConfig = null;

    /**
     * PayPal config setter.
     *
     * @param \OxidEsales\PayPalModule\Core\Config $oPayPalConfig
     */
    public function setPayPalConfig($oPayPalConfig)
    {
        $this->_oPayPalConfig = $oPayPalConfig;
    }

    /**
     * PayPal config getter.
     *
     * @return \OxidEsales\PayPalModule\Core\Config
     */
    public function getPayPalConfig()
    {
        if (is_null($this->_oPayPalConfig)) {
            $this->setPayPalConfig(oxNew(\OxidEsales\PayPalModule\Core\Config::class));
        }

        return $this->_oPayPalConfig;
    }

    /**
     * PayPal caller setter.
     *
     * @param \OxidEsales\PayPalModule\Core\Caller $oCaller
     */
    public function setCaller($oCaller)
    {
        $this->_oCaller = $oCaller;
    }

    /**
     * PayPal caller getter.
     *
     * @return \OxidEsales\PayPalModule\Core\Caller
     */
    public function getCaller()
    {
        if (is_null($this->_oCaller)) {

            /**
             * @var \OxidEsales\PayPalModule\Core\Caller $oCaller
             */
            $oCaller = oxNew(\OxidEsales\PayPalModule\Core\Caller::class);

            $oConfig = $this->getPayPalConfig();

            $oCaller->setParameter('VERSION', '84.0');
            $oCaller->setParameter('PWD', $oConfig->getPassword());
            $oCaller->setParameter('USER', $oConfig->getUserName());
            $oCaller->setParameter('SIGNATURE', $oConfig->getSignature());

            $oCurl = oxNew(\OxidEsales\PayPalModule\Core\Curl::class);
            $oCurl->setDataCharset($oConfig->getCharset());
            $oCurl->setHost($oConfig->getHost());
            $oCurl->setUrlToCall($oConfig->getApiUrl());

            $oCaller->setCurl($oCurl);

            if ($oConfig->isLoggingEnabled()) {
                $oLogger = oxNew(\OxidEsales\PayPalModule\Core\Logger::class);
                $oLogger->setLoggerSessionId(\OxidEsales\Eshop\Core\Registry::getSession()->getId());
                $oCaller->setLogger($oLogger);
            }

            $this->setCaller($oCaller);
        }

        return $this->_oCaller;
    }

    /**
     * Executes "SetExpressCheckout". Returns response object from PayPal.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $oRequest
     *
     * @return \OxidEsales\PayPalModule\Model\Response\ResponseSetExpressCheckout
     */
    public function setExpressCheckout($oRequest)
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest($oRequest);

        $oResponse = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseSetExpressCheckout::class);
        $oResponse->setData($oCaller->call('SetExpressCheckout'));

        return $oResponse;
    }

    /**
     * Executes "GetExpressCheckoutDetails". Returns response object from PayPal.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $oRequest
     *
     * @return \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails
     */
    public function getExpressCheckoutDetails($oRequest)
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest($oRequest);

        $oResponse = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails::class);
        $oResponse->setData($oCaller->call('GetExpressCheckoutDetails'));

        return $oResponse;
    }

    /**
     * Executes "DoExpressCheckoutPayment". Returns response object from PayPal.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $oRequest
     *
     * @return \OxidEsales\PayPalModule\Model\Response\ResponseDoExpressCheckoutPayment
     */
    public function doExpressCheckoutPayment($oRequest)
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest($oRequest);

        $oResponse = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseDoExpressCheckoutPayment::class);
        $oResponse->setData($oCaller->call('DoExpressCheckoutPayment'));

        return $oResponse;
    }

    /**
     * Executes PayPal callback request
     */
    public function callbackResponse()
    {
        // cleanup
        $this->getCaller()->setParameter("VERSION", null);
        $this->getCaller()->setParameter("PWD", null);
        $this->getCaller()->setParameter("USER", null);
        $this->getCaller()->setParameter("SIGNATURE", null);

        return $this->getCaller()->getCallBackResponse("CallbackResponse");
    }

    /**
     * Executes "DoVoid". Returns response array from PayPal
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $oRequest
     *
     * @return \OxidEsales\PayPalModule\Model\Response\Response
     */
    public function doVoid($oRequest)
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest($oRequest);

        $oResponse = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseDoVoid::class);
        $oResponse->setData($oCaller->call('DoVoid'));

        return $oResponse;
    }

    /**
     * Executes "RefundTransaction". Returns response array from PayPal
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $oRequest
     *
     * @return \OxidEsales\PayPalModule\Model\Response\Response
     */
    public function refundTransaction($oRequest)
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest($oRequest);

        $oResponse = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseDoRefund::class);
        $oResponse->setData($oCaller->call('RefundTransaction'));

        return $oResponse;
    }

    /**
     * Executes "DoCapture". Returns response array from PayPal
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $oRequest request
     *
     * @return \OxidEsales\PayPalModule\Model\Response\Response
     */
    public function doCapture($oRequest)
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest($oRequest);

        $oResponse = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseDoCapture::class);
        $oResponse->setData($oCaller->call('DoCapture'));

        return $oResponse;
    }

    /**
     * Executes "DoReauthorization". Returns response array from PayPal.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $oRequest
     *
     * @return \OxidEsales\PayPalModule\Model\Response\Response
     */
    public function doReAuthorization($oRequest)
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest($oRequest);

        $oResponse = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseDoReAuthorize::class);
        $oResponse->setData($oCaller->call('DoReauthorization'));

        return $oResponse;
    }

    /**
     * Executes call to PayPal IPN.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $oRequest
     * @param string                 $sCharset
     *
     * @return \OxidEsales\PayPalModule\Model\Response\Response
     */
    public function doVerifyWithPayPal($oRequest, $sCharset)
    {
        $oCaller = $this->getCaller();
        $oCaller->setRequest($oRequest);

        $oCaller = $this->getCaller();
        $oCurl = $oCaller->getCurl();
        $oCurl->setConnectionCharset($sCharset);
        $oCurl->setDataCharset($sCharset);
        $oCurl->setUrlToCall($this->getPayPalConfig()->getIPNResponseUrl());

        $oResponse = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseDoVerifyWithPayPal::class);
        $oResponse->setData($oCaller->call());

        return $oResponse;
    }

    /**
     * Set parameter to caller by it's key.
     *
     * @param string $sKey
     * @param string $sValue
     *
     * @deprecated still use in callback.
     */
    public function setParameter($sKey, $sValue)
    {
        return $this->getCaller()->setParameter($sKey, $sValue);
    }
}
