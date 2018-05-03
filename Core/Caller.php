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
 * PayPal caller service class
 */
class Caller
{
    /**
     * Service call parameters.
     *
     * @var array
     */
    protected $params = array();

    /**
     * PayPal logger.
     *
     * @var \OxidEsales\PayPalModule\Core\Logger
     */
    protected $logger = null;

    /**
     * PayPal curl object.
     *
     * @var \OxidEsales\PayPalModule\Core\Curl
     */
    protected $curl = null;

    /**
     * Setter for logger.
     *
     * @param \OxidEsales\PayPalModule\Core\Logger $logger logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Getter for PayPal logger.
     *
     * @return \OxidEsales\PayPalModule\Core\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Sets PayPal curl object.
     *
     * @param \OxidEsales\PayPalModule\Core\Curl $payPalCurl PayPal curl object.
     */
    public function setCurl($payPalCurl)
    {
        $this->curl = $payPalCurl;
    }

    /**
     * Returns curl instance
     *
     * @return \OxidEsales\PayPalModule\Core\Curl
     */
    public function getCurl()
    {
        if (is_null($this->curl)) {
            $curl = oxNew(\OxidEsales\PayPalModule\Core\Curl::class);
            $this->setCurl($curl);
        }

        return $this->curl;
    }

    /**
     * PayPal request parameters setter.
     *
     * @param string $paramName  parameter name
     * @param mixed  $paramValue parameter value
     */
    public function setParameter($paramName, $paramValue)
    {
        $this->params[$paramName] = $paramValue;
    }

    /**
     * PayPal request parameters setter.
     *
     * @param array $parameters parameters to use to build request.
     */
    public function setParameters($parameters)
    {
        $this->params = array_merge($this->params, $parameters);
    }

    /**
     * Returns PayPal request parameters array.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->params;
    }

    /**
     * Calls given remote PayPal method.
     *
     * @param string $methodName .
     *
     * @return array
     */
    public function call($methodName = null)
    {
        $this->setMethod($methodName);

        $curl = $this->getCurl();
        $curl->setParameters($this->getParameters());

        $this->log($this->getParameters(), 'Request to PayPal');

        $response = $curl->execute();

        $this->log($response, 'Response from PayPal');

        $this->validateResponse($response);

        return $response;
    }

    /**
     * Set method name to execute like DoExpressCheckoutPayment or GetExpressCheckoutDetails.
     *
     * @param string $name Name of a method
     */
    protected function setMethod($name)
    {
        if (!is_null($name)) {
            $this->setParameter("METHOD", $name);
        }
    }

    /**
     * Validates response from PayPal errors.
     *
     * @param array $response
     *
     * @throws \OxidEsales\PayPalModule\Core\Exception\PayPalResponseException if response has error from PayPal
     */
    protected function validateResponse($response)
    {
        if ('Failure' == $response['ACK']) {
            /**
             * @var \OxidEsales\PayPalModule\Core\Exception\PayPalResponseException $exception
             */
            $exception = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalResponseException::class, $response['L_LONGMESSAGE0'], $response['L_ERRORCODE0']);
            throw $exception;
        }
    }

    /**
     * Outputs given request data.
     *
     * @param string $methodName
     *
     * @return string
     */
    public function getCallBackResponse($methodName)
    {
        $this->setParameter("METHOD", $methodName);

        $curl = $this->getCurl();
        $curl->setParameters($this->getParameters());
        $request = $curl->getQuery();

        $this->log($request, 'Callback response from eShop to PayPal');

        return $request;
    }

    /**
     * Logs given request and responds parameters to log file.
     *
     * @param mixed  $value request / response parameters
     * @param string $title section title in log file
     */
    public function log($value, $title = '')
    {
        if (!is_null($this->getLogger())) {
            $this->getLogger()->setTitle($title);
            $this->getLogger()->log($value);
        }
    }

    /**
     * Set parameter from request.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request request
     */
    public function setRequest(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request)
    {
        $this->setParameters($request->getData());
    }
}
