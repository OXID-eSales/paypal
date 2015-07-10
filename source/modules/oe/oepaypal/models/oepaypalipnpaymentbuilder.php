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
 * @copyright (C) OXID eSales AG 2003-2014
 */

/**
 * PayPal IPN payment builder class.
 */
class oePayPalIPNPaymentBuilder
{
    /** @var oePayPalRequest */
    protected $_oRequest = null;

    /** @var oePayPalIPNRequestPaymentSetter */
    protected $_oPayPalIPNPaymentSetter = null;

    /** @var oePayPalOrderPayment */
    protected $_oOrderPayment = null;

    /** @var oePayPalIPNPaymentValidator */
    protected $_oPayPalIPNPaymentValidator = null;

    /** @var oePayPalIPNPaymentCreator */
    protected $_oPayPalIPNPaymentCreator = null;

    /** @var oxLang */
    protected $_oLang = null;

    /**
     * Sets oePayPalIPNRequestPaymentSetter.
     *
     * @param oePayPalIPNRequestPaymentSetter $oPayPalIPNPaymentSetter
     */
    public function setOrderPaymentSetter($oPayPalIPNPaymentSetter)
    {
        $this->_oPayPalIPNPaymentSetter = $oPayPalIPNPaymentSetter;
    }

    /**
     * Creates and sets oePayPalIPNRequestPaymentSetter object if it was not set.
     *
     * @return oePayPalIPNRequestPaymentSetter
     */
    public function getOrderPaymentSetter()
    {
        if (is_null($this->_oPayPalIPNPaymentSetter)) {
            $oPayPalIPNPaymentSetter = oxNew('oePayPalIPNRequestPaymentSetter');
            $this->setOrderPaymentSetter($oPayPalIPNPaymentSetter);
        }

        return $this->_oPayPalIPNPaymentSetter;
    }

    /**
     * Sets oePayPalIPNPaymentValidator object.
     *
     * @param oePayPalIPNPaymentValidator $oPayPalIPNPaymentValidator
     */
    public function setOrderPaymentValidator($oPayPalIPNPaymentValidator)
    {
        $this->_oPayPalIPNPaymentValidator = $oPayPalIPNPaymentValidator;
    }

    /**
     * Creates and sets oePayPalIPNPaymentValidator object if it was not set.
     *
     * @return oePayPalIPNPaymentValidator
     */
    public function getOrderPaymentValidator()
    {
        if (is_null($this->_oPayPalIPNPaymentValidator)) {
            $oPayPalIPNPaymentValidator = oxNew('oePayPalIPNPaymentValidator');
            $this->setOrderPaymentValidator($oPayPalIPNPaymentValidator);
        }

        return $this->_oPayPalIPNPaymentValidator;
    }

    /**
     * Sets oePayPalIPNPaymentCreator.
     *
     * @param oePayPalIPNPaymentCreator $oePayPalIPNPaymentCreator
     */
    public function setPaymentCreator($oePayPalIPNPaymentCreator)
    {
        $this->_oPayPalIPNPaymentCreator = $oePayPalIPNPaymentCreator;
    }

    /**
     * Creates and sets oePayPalIPNPaymentCreator object if it was not set.
     *
     * @return oePayPalIPNPaymentCreator
     */
    public function getPaymentCreator()
    {
        if (is_null($this->_oPayPalIPNPaymentCreator)) {
            $oPayPalIPNPaymentCreator = oxNew('oePayPalIPNPaymentCreator');
            $oPayPalIPNPaymentCreator->setRequest($this->getRequest());
            $this->setPaymentCreator($oPayPalIPNPaymentCreator);
        }

        return $this->_oPayPalIPNPaymentCreator;
    }

    /**
     * Sets request object.
     *
     * @param oePayPalRequest $oRequest
     */
    public function setRequest($oRequest)
    {
        $this->_oRequest = $oRequest;
    }

    /**
     * Returns oePayPalRequest.
     *
     * @return oePayPalRequest
     */
    public function getRequest()
    {
        return $this->_oRequest;
    }

    /**
     * Sets oxLang object
     *
     * @param oxLang $oLang
     */
    public function setLang($oLang)
    {
        $this->_oLang = $oLang;
    }

    /**
     * Returns oxLang object.
     *
     * @return oxLang
     */
    public function getLang()
    {
        return $this->_oLang;
    }

    /**
     * Create payment from given request.
     *
     * @return oePayPalOrderPayment|null
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
     * @return oePayPalOrderPayment|null
     */
    protected function _loadOrderPayment($sTransactionId)
    {
        $oOrderPayment = oxNew('oePayPalOrderPayment');
        $oOrderPayment->loadByTransactionId($sTransactionId);

        return $oOrderPayment;
    }

    /**
     * Wrapper to set parameters to order payment from request.
     *
     * @return oePayPalOrderPayment
     */
    protected function _prepareRequestOrderPayment()
    {
        $oRequestOrderPayment = oxNew('oePayPalOrderPayment');
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
     * @param oePayPalOrderPayment $oRequestOrderPayment
     * @param oePayPalOrderPayment $oOrderPayment
     *
     * @return oePayPalOrderPayment
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
            $oComment = oxNew('oePayPalOrderPaymentComment');
            $oComment->setComment($oPaymentValidator->getValidationFailureMessage());
            $oOrderPayment->addComment($oComment);
        }

        return $oOrderPayment;
    }

    /**
     * Add Payment Status information to object from database from object created from from PayPal request.
     *
     * @param oePayPalOrderPayment $oRequestOrderPayment
     * @param oePayPalOrderPayment $oOrderPayment
     *
     * @return oePayPalOrderPayment
     */
    protected function _changePaymentStatusInfo($oRequestOrderPayment, $oOrderPayment)
    {
        $oOrderPayment->setStatus($oRequestOrderPayment->getStatus());

        return $oOrderPayment;
    }
}
