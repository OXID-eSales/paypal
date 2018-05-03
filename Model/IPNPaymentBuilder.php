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
 * PayPal IPN payment builder class.
 */
class IPNPaymentBuilder
{
    /** @var \OxidEsales\PayPalModule\Core\Request */
    protected $request = null;

    /** @var \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter */
    protected $payPalIPNPaymentSetter = null;

    /** @var \OxidEsales\PayPalModule\Model\OrderPayment */
    protected $orderPayment = null;

    /** @var \OxidEsales\PayPalModule\Model\IPNPaymentValidator */
    protected $payPalIPNPaymentValidator = null;

    /** @var \OxidEsales\PayPalModule\Model\IPNPaymentCreator */
    protected $payPalIPNPaymentCreator = null;

    /** @var \OxidEsales\Eshop\Core\Language */
    protected $lang = null;

    /**
     * Sets \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter.
     *
     * @param \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter $payPalIPNPaymentSetter
     */
    public function setOrderPaymentSetter($payPalIPNPaymentSetter)
    {
        $this->payPalIPNPaymentSetter = $payPalIPNPaymentSetter;
    }

    /**
     * Creates and sets \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter object if it was not set.
     *
     * @return \OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter
     */
    public function getOrderPaymentSetter()
    {
        if (is_null($this->payPalIPNPaymentSetter)) {
            $payPalIPNPaymentSetter = oxNew(\OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter::class);
            $this->setOrderPaymentSetter($payPalIPNPaymentSetter);
        }

        return $this->payPalIPNPaymentSetter;
    }

    /**
     * Sets \OxidEsales\PayPalModule\Model\IPNPaymentValidator object.
     *
     * @param \OxidEsales\PayPalModule\Model\IPNPaymentValidator $payPalIPNPaymentValidator
     */
    public function setOrderPaymentValidator($payPalIPNPaymentValidator)
    {
        $this->payPalIPNPaymentValidator = $payPalIPNPaymentValidator;
    }

    /**
     * Creates and sets \OxidEsales\PayPalModule\Model\IPNPaymentValidator object if it was not set.
     *
     * @return \OxidEsales\PayPalModule\Model\IPNPaymentValidator
     */
    public function getOrderPaymentValidator()
    {
        if (is_null($this->payPalIPNPaymentValidator)) {
            $payPalIPNPaymentValidator = oxNew(\OxidEsales\PayPalModule\Model\IPNPaymentValidator::class);
            $this->setOrderPaymentValidator($payPalIPNPaymentValidator);
        }

        return $this->payPalIPNPaymentValidator;
    }

    /**
     * Sets \OxidEsales\PayPalModule\Model\IPNPaymentCreator.
     *
     * @param \OxidEsales\PayPalModule\Model\IPNPaymentCreator $oePayPalIPNPaymentCreator
     */
    public function setPaymentCreator($oePayPalIPNPaymentCreator)
    {
        $this->payPalIPNPaymentCreator = $oePayPalIPNPaymentCreator;
    }

    /**
     * Creates and sets \OxidEsales\PayPalModule\Model\IPNPaymentCreator object if it was not set.
     *
     * @return \OxidEsales\PayPalModule\Model\IPNPaymentCreator
     */
    public function getPaymentCreator()
    {
        if (is_null($this->payPalIPNPaymentCreator)) {
            $payPalIPNPaymentCreator = oxNew(\OxidEsales\PayPalModule\Model\IPNPaymentCreator::class);
            $payPalIPNPaymentCreator->setRequest($this->getRequest());
            $this->setPaymentCreator($payPalIPNPaymentCreator);
        }

        return $this->payPalIPNPaymentCreator;
    }

    /**
     * Sets request object.
     *
     * @param \OxidEsales\PayPalModule\Core\Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Returns \OxidEsales\PayPalModule\Core\Request.
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets oxLang object
     *
     * @param \OxidEsales\Eshop\Core\Language $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * Returns oxLang object.
     *
     * @return \OxidEsales\Eshop\Core\Language
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Create payment from given request.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment|null
     */
    public function buildPayment()
    {
        $return = null;

        // Setter forms request payment from request parameters.
        $requestOrderPayment = $this->prepareRequestOrderPayment();

        // Create order payment from database to check if it match created from request.
        $orderPayment = $this->loadOrderPayment($requestOrderPayment->getTransactionId());

        // Only need validate if there is order in database.
        // If request payment do not have matching payment with order return null.
        if ($orderPayment->getOrderId()) {
            // Validator change request payment by adding information if it is valid.
            $orderPayment = $this->addPaymentValidationInformation($requestOrderPayment, $orderPayment);
            $orderPayment = $this->changePaymentStatusInfo($requestOrderPayment, $orderPayment);
            $orderPayment->save();
            $return = $orderPayment;
        } else {
            //IPN request might be for a transaction that does not yet exist in the shop database.
            $paymentCreator = $this->getPaymentCreator();
            $return = $paymentCreator->handleOrderPayment($requestOrderPayment);
        }

        return $return;
    }

    /**
     * Load order payment from transaction id.
     *
     * @param string $transactionId transaction id to load object.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment|null
     */
    protected function loadOrderPayment($transactionId)
    {
        $orderPayment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $orderPayment->loadByTransactionId($transactionId);

        return $orderPayment;
    }

    /**
     * Wrapper to set parameters to order payment from request.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function prepareRequestOrderPayment()
    {
        $requestOrderPayment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $request = $this->getRequest();

        $requestPaymentSetter = $this->getOrderPaymentSetter();
        $requestPaymentSetter->setRequest($request);
        $requestPaymentSetter->setRequestOrderPayment($requestOrderPayment);

        $requestOrderPayment = $requestPaymentSetter->getRequestOrderPayment();

        return $requestOrderPayment;
    }

    /**
     * Adds payment validation information.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $requestOrderPayment
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $orderPayment
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function addPaymentValidationInformation($requestOrderPayment, $orderPayment)
    {
        $lang = $this->getLang();

        $paymentValidator = $this->getOrderPaymentValidator();
        $paymentValidator->setRequestOrderPayment($requestOrderPayment);
        $paymentValidator->setOrderPayment($orderPayment);
        $paymentValidator->setLang($lang);

        $paymentIsValid = $paymentValidator->isValid();
        if (!$paymentIsValid) {
            $orderPayment->setIsValid($paymentIsValid);
            $comment = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentComment::class);
            $comment->setComment($paymentValidator->getValidationFailureMessage());
            $orderPayment->addComment($comment);
        }

        return $orderPayment;
    }

    /**
     * Add Payment Status information to object from database from object created from from PayPal request.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $requestOrderPayment
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $orderPayment
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function changePaymentStatusInfo($requestOrderPayment, $orderPayment)
    {
        $orderPayment->setStatus($requestOrderPayment->getStatus());

        return $orderPayment;
    }
}
