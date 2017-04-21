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
 * PayPal IPN payment builder class.
 */
class IPNPaymentBuilder
{
    /** @var \OxidEsales\PayPalModule\Core\Request */
    protected $_oRequest = null;

    /** @var \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter */
    protected $_oPayPalIPNPaymentSetter = null;

    /** @var \OxidEsales\PayPalModule\Model\OrderPayment */
    protected $_oOrderPayment = null;

    /** @var \OxidEsales\PayPalModule\Model\IPNPaymentValidator */
    protected $_oPayPalIPNPaymentValidator = null;

    /** @var \OxidEsales\PayPalModule\Model\IPNPaymentCreator */
    protected $_oPayPalIPNPaymentCreator = null;

    /** @var \OxidEsales\Eshop\Core\Language */
    protected $_oLang = null;

    /**
     * Sets \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter.
     *
     * @param \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter $oPayPalIPNPaymentSetter
     */
    public function setOrderPaymentSetter($oPayPalIPNPaymentSetter)
    {
        $this->_oPayPalIPNPaymentSetter = $oPayPalIPNPaymentSetter;
    }

    /**
     * Creates and sets \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter object if it was not set.
     *
     * @return \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter
     */
    public function getOrderPaymentSetter()
    {
        if (is_null($this->_oPayPalIPNPaymentSetter)) {
            $oPayPalIPNPaymentSetter = oxNew(\OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter::class);
            $this->setOrderPaymentSetter($oPayPalIPNPaymentSetter);
        }

        return $this->_oPayPalIPNPaymentSetter;
    }

    /**
     * Sets \OxidEsales\PayPalModule\Model\IPNPaymentValidator object.
     *
     * @param \OxidEsales\PayPalModule\Model\IPNPaymentValidator $oPayPalIPNPaymentValidator
     */
    public function setOrderPaymentValidator($oPayPalIPNPaymentValidator)
    {
        $this->_oPayPalIPNPaymentValidator = $oPayPalIPNPaymentValidator;
    }

    /**
     * Creates and sets \OxidEsales\PayPalModule\Model\IPNPaymentValidator object if it was not set.
     *
     * @return \OxidEsales\PayPalModule\Model\IPNPaymentValidator
     */
    public function getOrderPaymentValidator()
    {
        if (is_null($this->_oPayPalIPNPaymentValidator)) {
            $oPayPalIPNPaymentValidator = oxNew(\OxidEsales\PayPalModule\Model\IPNPaymentValidator::class);
            $this->setOrderPaymentValidator($oPayPalIPNPaymentValidator);
        }

        return $this->_oPayPalIPNPaymentValidator;
    }

    /**
     * Sets \OxidEsales\PayPalModule\Model\IPNPaymentCreator.
     *
     * @param \OxidEsales\PayPalModule\Model\IPNPaymentCreator $oePayPalIPNPaymentCreator
     */
    public function setPaymentCreator($oePayPalIPNPaymentCreator)
    {
        $this->_oPayPalIPNPaymentCreator = $oePayPalIPNPaymentCreator;
    }

    /**
     * Creates and sets \OxidEsales\PayPalModule\Model\IPNPaymentCreator object if it was not set.
     *
     * @return \OxidEsales\PayPalModule\Model\IPNPaymentCreator
     */
    public function getPaymentCreator()
    {
        if (is_null($this->_oPayPalIPNPaymentCreator)) {
            $oPayPalIPNPaymentCreator = oxNew(\OxidEsales\PayPalModule\Model\IPNPaymentCreator::class);
            $oPayPalIPNPaymentCreator->setRequest($this->getRequest());
            $this->setPaymentCreator($oPayPalIPNPaymentCreator);
        }

        return $this->_oPayPalIPNPaymentCreator;
    }

    /**
     * Sets request object.
     *
     * @param \OxidEsales\PayPalModule\Core\Request $oRequest
     */
    public function setRequest($oRequest)
    {
        $this->_oRequest = $oRequest;
    }

    /**
     * Returns \OxidEsales\PayPalModule\Core\Request.
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    public function getRequest()
    {
        return $this->_oRequest;
    }

    /**
     * Sets oxLang object
     *
     * @param \OxidEsales\Eshop\Core\Language $oLang
     */
    public function setLang($oLang)
    {
        $this->_oLang = $oLang;
    }

    /**
     * Returns oxLang object.
     *
     * @return \OxidEsales\Eshop\Core\Language
     */
    public function getLang()
    {
        return $this->_oLang;
    }

    /**
     * Create payment from given request.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment|null
     */
    public function buildPayment()
    {
        $oReturn = null;

        // Setter forms request payment from request parameters.
        $oRequestOrderPayment = $this->_prepareRequestOrderPayment();

        // Create order payment from database to check if it match created from request.
        $oOrderPayment = $this->_loadOrderPayment($oRequestOrderPayment->getTransactionId());

        // Only need validate if there is order in database.
        // If request payment do not have matching payment with order return null.
        if ($oOrderPayment->getOrderId()) {
            // Validator change request payment by adding information if it is valid.
            $oOrderPayment = $this->_addPaymentValidationInformation($oRequestOrderPayment, $oOrderPayment);
            $oOrderPayment = $this->_changePaymentStatusInfo($oRequestOrderPayment, $oOrderPayment);
            $oOrderPayment->save();
            $oReturn = $oOrderPayment;
        } else {
            //IPN request might be for a transaction that does not yet exist in the shop database.
            $oPaymentCreator = $this->getPaymentCreator();
            $oReturn = $oPaymentCreator->handleOrderPayment($oRequestOrderPayment);
        }

        return $oReturn;
    }

    /**
     * Load order payment from transaction id.
     *
     * @param string $sTransactionId transaction id to load object.
     * @param string $sCorrelationId correlation id to load object.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment|null
     */
    protected function _loadOrderPayment($sTransactionId)
    {
        $oOrderPayment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $oOrderPayment->loadByTransactionId($sTransactionId);

        return $oOrderPayment;
    }

    /**
     * Wrapper to set parameters to order payment from request.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function _prepareRequestOrderPayment()
    {
        $oRequestOrderPayment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $oRequest = $this->getRequest();

        $oRequestPaymentSetter = $this->getOrderPaymentSetter();
        $oRequestPaymentSetter->setRequest($oRequest);
        $oRequestPaymentSetter->setRequestOrderPayment($oRequestOrderPayment);

        $oRequestOrderPayment = $oRequestPaymentSetter->getRequestOrderPayment();

        return $oRequestOrderPayment;
    }

    /**
     * Adds payment validation information.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $oRequestOrderPayment
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $oOrderPayment
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function _addPaymentValidationInformation($oRequestOrderPayment, $oOrderPayment)
    {
        $oLang = $this->getLang();

        $oPaymentValidator = $this->getOrderPaymentValidator();
        $oPaymentValidator->setRequestOrderPayment($oRequestOrderPayment);
        $oPaymentValidator->setOrderPayment($oOrderPayment);
        $oPaymentValidator->setLang($oLang);

        $blPaymentIsValid = $oPaymentValidator->isValid();
        if (!$blPaymentIsValid) {
            $oOrderPayment->setIsValid($blPaymentIsValid);
            $oComment = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentComment::class);
            $oComment->setComment($oPaymentValidator->getValidationFailureMessage());
            $oOrderPayment->addComment($oComment);
        }

        return $oOrderPayment;
    }

    /**
     * Add Payment Status information to object from database from object created from from PayPal request.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $oRequestOrderPayment
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $oOrderPayment
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function _changePaymentStatusInfo($oRequestOrderPayment, $oOrderPayment)
    {
        $oOrderPayment->setStatus($oRequestOrderPayment->getStatus());

        return $oOrderPayment;
    }
}
