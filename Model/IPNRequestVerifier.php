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

/**
 * PayPal IPN request verifier class.
 */
class IPNRequestVerifier
{
    /**
     * PayPal \OxidEsales\PayPalModule\Core\Request
     *
     * @var \OxidEsales\PayPalModule\Core\Request
     */
    protected $request = null;

    /**
     * Shop owner email - PayPal ID.
     *
     * @var string
     */
    protected $shopOwner = null;

    /**
     * PayPal Service
     *
     * @var \OxidEsales\PayPalModule\Core\PayPalService
     */
    protected $communicationService = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\IPNRequestValidator
     */
    protected $ipnRequestValidator = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    protected $payPalRequest = null;

    /**
     * @var array
     */
    protected $failureMessage = null;

    /**
     * Set object \OxidEsales\PayPalModule\Core\Request.
     *
     * @param \OxidEsales\PayPalModule\Core\Request $request object to set.
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Create object \OxidEsales\PayPalModule\Core\Request to get PayPal request information.
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets shop owner.
     *
     * @param string $shopOwner
     */
    public function setShopOwner($shopOwner)
    {
        $this->shopOwner = $shopOwner;
    }

    /**
     * Returns shop owner.
     *
     * @return string
     */
    public function getShopOwner()
    {
        return $this->shopOwner;
    }

    /**
     * Sets oeIPNCallerService.
     *
     * @param \OxidEsales\PayPalModule\Core\PayPalService $callerService object to set..
     */
    public function setCommunicationService($callerService)
    {
        $this->communicationService = $callerService;
    }

    /**
     * Getter for the PayPal service
     *
     * @return \OxidEsales\PayPalModule\Core\PayPalService
     */
    public function getCommunicationService()
    {
        if ($this->communicationService === null) {
            $this->communicationService = oxNew(\OxidEsales\PayPalModule\Core\PayPalService::class);
        }

        return $this->communicationService;
    }

    /**
     * Sets IPN request validator.
     *
     * @param \OxidEsales\PayPalModule\Model\IPNRequestValidator $ipnRequestValidator
     */
    public function setIPNRequestValidator($ipnRequestValidator)
    {
        $this->ipnRequestValidator = $ipnRequestValidator;
    }

    /**
     * Returns IPN request validator object.
     *
     * @return \OxidEsales\PayPalModule\Model\IPNRequestValidator
     */
    public function getIPNRequestValidator()
    {
        if ($this->ipnRequestValidator === null) {
            $this->ipnRequestValidator = oxNew(\OxidEsales\PayPalModule\Model\IPNRequestValidator::class);
        }

        return $this->ipnRequestValidator;
    }

    /**
     * Sets request object.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $payPalRequest
     */
    public function setPayPalRequest($payPalRequest)
    {
        $this->payPalRequest = $payPalRequest;
    }

    /**
     * Return, create object to call PayPal with.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function getPayPalRequest()
    {
        if (is_null($this->payPalRequest)) {
            $this->payPalRequest = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class);
        }

        return $this->payPalRequest;
    }

    /**
     * Sets failure message.
     *
     * @param array $failureMessage
     */
    public function setFailureMessage($failureMessage)
    {
        $this->failureMessage = $failureMessage;
    }

    /**
     * Returns failure message.
     *
     * @return array
     */
    public function getFailureMessage()
    {
        return $this->failureMessage;
    }

    /**
     * IPN handling function.
     *  - verify with PayPal.
     *
     * @return bool
     */
    public function requestCorrect()
    {
        $request = $this->getRequest();
        $rawRequestData = $request->getPost();

        $responseDoVerifyWithPayPal = $this->doVerifyWithPayPal($rawRequestData);

        $ipnRequestValidator = $this->getIPNRequestValidator();
        $ipnRequestValidator->setPayPalRequest($rawRequestData);
        $ipnRequestValidator->setPayPalResponse($responseDoVerifyWithPayPal);
        $ipnRequestValidator->setShopOwnerUserName($this->getShopOwner());

        $requestCorrect = $ipnRequestValidator->isValid();
        if (!$requestCorrect) {
            $failureMessage = $ipnRequestValidator->getValidationFailureMessage();
            $this->setFailureMessage($failureMessage);
        }

        return $requestCorrect;
    }

    /**
     * Call PayPal to check if IPN request originally from PayPal.
     *
     * @param array $requestData data of request.
     *
     * @return \OxidEsales\PayPalModule\Model\Response\Response
     */
    protected function doVerifyWithPayPal($requestData)
    {
        $callerService = $this->getCommunicationService();
        $payPalPayPalRequest = $this->getPayPalRequest();
        foreach ($requestData as $requestParameterName => $requestParameterValue) {
            $payPalPayPalRequest->setParameter($requestParameterName, $requestParameterValue);
        }
        $responseDoVerifyWithPayPal = $callerService->doVerifyWithPayPal($payPalPayPalRequest, $requestData['charset']);

        return $responseDoVerifyWithPayPal;
    }
}
