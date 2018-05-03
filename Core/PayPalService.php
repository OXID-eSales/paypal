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
    protected $caller = null;

    /**
     * PayPal Caller.
     *
     * @var \OxidEsales\PayPalModule\Core\Config
     */
    protected $payPalConfig = null;

    /**
     * PayPal IPN config.
     *
     * @var \OxidEsales\PayPalModule\Core\IpnConfig
     */
    protected $payPalIpnConfig = null;

    /**
     * PayPal config setter.
     *
     * @param \OxidEsales\PayPalModule\Core\Config $payPalConfig
     */
    public function setPayPalConfig($payPalConfig)
    {
        $this->payPalConfig = $payPalConfig;
    }

    /**
     * PayPal config getter.
     *
     * @return \OxidEsales\PayPalModule\Core\Config
     */
    public function getPayPalConfig()
    {
        if (is_null($this->payPalConfig)) {
            $this->setPayPalConfig(oxNew(\OxidEsales\PayPalModule\Core\Config::class));
        }

        return $this->payPalConfig;
    }

    /**
     * PayPal Ipn config setter.
     *
     * @param \OxidEsales\PayPalModule\Core\IpnConfig $payPalIpnConfig
     */
    public function setPayPalIpnConfig($payPalIpnConfig)
    {
        $this->payPalIpnConfig = $payPalIpnConfig;
    }

    /**
     * PayPal config getter.
     *
     * @return \OxidEsales\PayPalModule\Core\IpnConfig
     */
    public function getPayPalIpnConfig()
    {
        if (is_null($this->payPalIpnConfig)) {
            $this->setPayPalIPnConfig(oxNew(\OxidEsales\PayPalModule\Core\IpnConfig::class));
        }

        return $this->payPalIpnConfig;
    }
    
    /**
     * PayPal caller setter.
     *
     * @param \OxidEsales\PayPalModule\Core\Caller $caller
     */
    public function setCaller($caller)
    {
        $this->caller = $caller;
    }

    /**
     * PayPal caller getter.
     *
     * @return \OxidEsales\PayPalModule\Core\Caller
     */
    public function getCaller()
    {
        if (is_null($this->caller)) {

            /**
             * @var \OxidEsales\PayPalModule\Core\Caller $caller
             */
            $caller = oxNew(\OxidEsales\PayPalModule\Core\Caller::class);

            $config = $this->getPayPalConfig();

            $caller->setParameter('VERSION', '84.0');
            $caller->setParameter('PWD', $config->getPassword());
            $caller->setParameter('USER', $config->getUserName());
            $caller->setParameter('SIGNATURE', $config->getSignature());

            $curl = oxNew(\OxidEsales\PayPalModule\Core\Curl::class);
            $curl->setDataCharset($config->getCharset());
            $curl->setHost($config->getHost());
            $curl->setUrlToCall($config->getApiUrl());

            $caller->setCurl($curl);

            if ($config->isLoggingEnabled()) {
                $logger = oxNew(\OxidEsales\PayPalModule\Core\Logger::class);
                $logger->setLoggerSessionId(\OxidEsales\Eshop\Core\Registry::getSession()->getId());
                $caller->setLogger($logger);
            }

            $this->setCaller($caller);
        }

        return $this->caller;
    }

    /**
     * Executes "SetExpressCheckout". Returns response object from PayPal.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request
     *
     * @return \OxidEsales\PayPalModule\Model\Response\ResponseSetExpressCheckout
     */
    public function setExpressCheckout($request)
    {
        $caller = $this->getCaller();
        $caller->setRequest($request);

        $response = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseSetExpressCheckout::class);
        $response->setData($caller->call('SetExpressCheckout'));

        return $response;
    }

    /**
     * Executes "GetExpressCheckoutDetails". Returns response object from PayPal.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request
     *
     * @return \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails
     */
    public function getExpressCheckoutDetails($request)
    {
        $caller = $this->getCaller();
        $caller->setRequest($request);

        $response = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails::class);
        $response->setData($caller->call('GetExpressCheckoutDetails'));

        return $response;
    }

    /**
     * Executes "DoExpressCheckoutPayment". Returns response object from PayPal.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request
     *
     * @return \OxidEsales\PayPalModule\Model\Response\ResponseDoExpressCheckoutPayment
     */
    public function doExpressCheckoutPayment($request)
    {
        $caller = $this->getCaller();
        $caller->setRequest($request);

        $response = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseDoExpressCheckoutPayment::class);
        $response->setData($caller->call('DoExpressCheckoutPayment'));

        return $response;
    }

    /**
     * Executes PayPal callback request
     *
     * @return string
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
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request
     *
     * @return \OxidEsales\PayPalModule\Model\Response\Response
     */
    public function doVoid($request)
    {
        $caller = $this->getCaller();
        $caller->setRequest($request);

        $response = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseDoVoid::class);
        $response->setData($caller->call('DoVoid'));

        return $response;
    }

    /**
     * Executes "RefundTransaction". Returns response array from PayPal
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request
     *
     * @return \OxidEsales\PayPalModule\Model\Response\Response
     */
    public function refundTransaction($request)
    {
        $caller = $this->getCaller();
        $caller->setRequest($request);

        $response = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseDoRefund::class);
        $response->setData($caller->call('RefundTransaction'));

        return $response;
    }

    /**
     * Executes "DoCapture". Returns response array from PayPal
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request request
     *
     * @return \OxidEsales\PayPalModule\Model\Response\Response
     */
    public function doCapture($request)
    {
        $caller = $this->getCaller();
        $caller->setRequest($request);

        $response = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseDoCapture::class);
        $response->setData($caller->call('DoCapture'));

        return $response;
    }

    /**
     * Executes "DoReauthorization". Returns response array from PayPal.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request
     *
     * @return \OxidEsales\PayPalModule\Model\Response\Response
     */
    public function doReAuthorization($request)
    {
        $caller = $this->getCaller();
        $caller->setRequest($request);

        $response = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseDoReAuthorize::class);
        $response->setData($caller->call('DoReauthorization'));

        return $response;
    }

    /**
     * Executes call to PayPal IPN.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request
     * @param string                                                     $charset
     *
     * @return \OxidEsales\PayPalModule\Model\Response\Response
     */
    public function doVerifyWithPayPal($request, $charset)
    {
        $caller = $this->getCaller();
        $caller->setRequest($request);

        $caller = $this->getCaller();
        $curl = $caller->getCurl();
        $curl->setConnectionCharset($charset);
        $curl->setDataCharset($charset);
        $curl->setHost($this->getPayPalIpnConfig()->getIpnHost());
        $curl->setUrlToCall($this->getPayPalIpnConfig()->getIPNResponseUrl());

        $response = oxNew(\OxidEsales\PayPalModule\Model\Response\ResponseDoVerifyWithPayPal::class);
        $response->setData($caller->call());

        return $response;
    }

    /**
     * Set parameter to caller by it's key.
     *
     * @param string $key
     * @param string $value
     *
     * @deprecated still use in callback.
     */
    public function setParameter($key, $value)
    {
        $this->getCaller()->setParameter($key, $value);
    }
}
