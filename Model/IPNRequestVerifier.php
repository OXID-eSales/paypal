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
    protected $_oRequest = null;

    /**
     * Shop owner email - PayPal ID.
     *
     * @var string
     */
    protected $_sShopOwner = null;

    /**
     * PayPal Service
     *
     * @var \OxidEsales\PayPalModule\Core\PayPalService
     */
    protected $_oCommunicationService = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\IPNRequestValidator
     */
    protected $_oIPNRequestValidator = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    protected $_oPayPalRequest = null;

    /**
     * @var string
     */
    protected $_sFailureMessage = null;

    /**
     * Set object \OxidEsales\PayPalModule\Core\Request.
     *
     * @param \OxidEsales\PayPalModule\Core\Request $oRequest object to set.
     */
    public function setRequest($oRequest)
    {
        $this->_oRequest = $oRequest;
    }

    /**
     * Create object \OxidEsales\PayPalModule\Core\Request to get PayPal request information.
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    public function getRequest()
    {
        return $this->_oRequest;
    }

    /**
     * Sets shop owner.
     *
     * @param string $sShopOwner
     */
    public function setShopOwner($sShopOwner)
    {
        $this->_sShopOwner = $sShopOwner;
    }

    /**
     * Returns shop owner.
     *
     * @return string
     */
    public function getShopOwner()
    {
        return $this->_sShopOwner;
    }

    /**
     * Sets oeIPNCallerService.
     *
     * @param \OxidEsales\PayPalModule\Core\PayPalService $oCallerService object to set..
     */
    public function setCommunicationService($oCallerService)
    {
        $this->_oCommunicationService = $oCallerService;
    }

    /**
     * Getter for the PayPal service
     *
     * @return \OxidEsales\PayPalModule\Core\PayPalService
     */
    public function getCommunicationService()
    {
        if ($this->_oCommunicationService === null) {
            $this->_oCommunicationService = oxNew(\OxidEsales\PayPalModule\Core\PayPalService::class);
        }

        return $this->_oCommunicationService;
    }

    /**
     * Sets IPN request validator.
     *
     * @param \OxidEsales\PayPalModule\Model\IPNRequestValidator $oIPNRequestValidator
     */
    public function setIPNRequestValidator($oIPNRequestValidator)
    {
        $this->_oIPNRequestValidator = $oIPNRequestValidator;
    }

    /**
     * Returns IPN request validator object.
     *
     * @return \OxidEsales\PayPalModule\Model\IPNRequestValidator
     */
    public function getIPNRequestValidator()
    {
        if ($this->_oIPNRequestValidator === null) {
            $this->_oIPNRequestValidator = oxNew(\OxidEsales\PayPalModule\Model\IPNRequestValidator::class);
        }

        return $this->_oIPNRequestValidator;
    }

    /**
     * Sets request object.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $oPayPalRequest
     */
    public function setPayPalRequest($oPayPalRequest)
    {
        $this->_oPayPalRequest = $oPayPalRequest;
    }

    /**
     * Return, create object to call PayPal with.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    public function getPayPalRequest()
    {
        if (is_null($this->_oPayPalRequest)) {
            $this->_oPayPalRequest = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class);
        }

        return $this->_oPayPalRequest;
    }

    /**
     * Sets failure message.
     *
     * @param string $sFailureMessage
     */
    public function setFailureMessage($sFailureMessage)
    {
        $this->_sFailureMessage = $sFailureMessage;
    }

    /**
     * Returns failure message.
     *
     * @return string
     */
    public function getFailureMessage()
    {
        return $this->_sFailureMessage;
    }

    /**
     * IPN handling function.
     *  - verify with PayPal.
     *
     * @return bool
     */
    public function requestCorrect()
    {
        $oRequest = $this->getRequest();
        $aRawRequestData = $oRequest->getPost();

        $oResponseDoVerifyWithPayPal = $this->_doVerifyWithPayPal($aRawRequestData);

        $oIPNRequestValidator = $this->getIPNRequestValidator();
        $oIPNRequestValidator->setPayPalRequest($aRawRequestData);
        $oIPNRequestValidator->setPayPalResponse($oResponseDoVerifyWithPayPal);
        $oIPNRequestValidator->setShopOwnerUserName($this->getShopOwner());

        $blRequestCorrect = $oIPNRequestValidator->isValid();
        if (!$blRequestCorrect) {
            $sFailureMessage = $oIPNRequestValidator->getValidationFailureMessage();
            $this->setFailureMessage($sFailureMessage);
        }

        return $blRequestCorrect;
    }

    /**
     * Call PayPal to check if IPN request originally from PayPal.
     *
     * @param array $aRequestData data of request.
     *
     * @return \OxidEsales\PayPalModule\Model\Response\ResponseDoVerifyWithPayPal
     */
    protected function _doVerifyWithPayPal($aRequestData)
    {
        $oCallerService = $this->getCommunicationService();
        $oPayPalPayPalRequest = $this->getPayPalRequest();
        foreach ($aRequestData as $sRequestParameterName => $sRequestParameterValue) {
            $oPayPalPayPalRequest->setParameter($sRequestParameterName, $sRequestParameterValue);
        }
        $oResponseDoVerifyWithPayPal = $oCallerService->doVerifyWithPayPal($oPayPalPayPalRequest, $aRequestData['charset']);

        return $oResponseDoVerifyWithPayPal;
    }
}
