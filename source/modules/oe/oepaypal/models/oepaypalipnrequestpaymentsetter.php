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
 * PayPal IPN request payment setter class.
 */
class oePayPalIPNRequestPaymentSetter
{
    /**
     * PayPal action that triggered this transaction.
     *
     * @var string
     */
    const CAPTURE_ACTION        = 'capture';
    const REFUND_ACTION         = 'refund';
    const AUTHORIZATION_ACTION  = 'authorization';

    /**
     * Sandbox mode parameter name.
     *
     * @var string
     */
    const PAYPAL_SANDBOX = 'test_ipn';

    /**
     * String PayPal payment status parameter name.
     *
     * @var string
     */
    const PAYPAL_PAYMENT_STATUS = 'payment_status';

    /**
     * String PayPal transaction id.
     *
     * @var string
     */
    const PAYPAL_TRANSACTION_ID = 'txn_id';

    /**
     * String PayPal whole price including payment and shipment.
     *
     * @var string
     */
    const MC_GROSS = 'mc_gross';

    /**
     * String PayPal payment currency.
     *
     * @var string
     */
    const MC_CURRENCY = 'mc_currency';

    /**
     * String PayPal payment date.
     *
     * @var string
     */
    const PAYMENT_DATE = 'payment_date';

    /**
     * String PayPal payment correlation id.
     *
     * @var string
     */
    const CORRELATION_ID = 'correlation_id';

    /**
     * String PayPal payment ipn tracking id, might come instead of correlation id.
     *
     * @var string
     */
    const IPN_TRACK_ID = 'ipn_track_id';

    /**
     * String PayPal payment comment.
     *
     * @var string
     */
    const PAYPAL_IPN_MEMO = 'memo';

    /**
     * String PayPal status for successful refund.
     *
     * @var string
     */
    const PAYPAL_STATUS_REFUND_DONE = 'Refunded';

    /**
     * @var oePayPalRequest
     */
    protected $_oRequest = null;

    /**
     * @var oePayPalOrderPayment
     */
    protected $_oRequestOrderPayment = null;

    /**
     * Sets request object to get params for IPN request.
     *
     * @param oePayPalRequest $oRequest
     */
    public function setRequest($oRequest)
    {
        $this->_oRequest = $oRequest;
    }

    /**
     * Gets request object to get params for IPN request.
     *
     * @return oePayPalRequest
     */
    public function getRequest()
    {
        return $this->_oRequest;
    }

    /**
     * Sets request order payment object.
     *
     * @param oePayPalOrderPayment $oOrderPayment
     */
    public function setRequestOrderPayment($oOrderPayment)
    {
        $this->_oRequestOrderPayment = $oOrderPayment;
    }

    /**
     * Returns order payment object.
     *
     * @return oePayPalOrderPayment
     */
    public function getRequestOrderPayment()
    {
        $this->_prepareOrderPayment($this->_oRequestOrderPayment);

        return $this->_oRequestOrderPayment;
    }

    /**
     * Add comment for request payment if comment exists.
     *
     * @param  oePayPalOrderPayment $oRequestOrderPayment
     * @return oePayPalOrderPayment
     */
    public function addRequestPaymentComment($requestOrderPayment)
    {
        $request = $this->getRequest();
        $memo    = $request->getRequestParameter(self::PAYPAL_IPN_MEMO);

        if (!empty($memo)) {
            $comment = oxNew('oePayPalOrderPaymentComment');
            $comment->setComment($memo);
            $requestOrderPayment->addComment($comment);
            if (0 < $requestOrderPayment->getId()) {
                $requestOrderPayment->save();
            }
        }
        return $requestOrderPayment;
    }

    /**
     * Prepare PayPal payment. Fill up with request values.
     *
     * @param oePayPalOrderPayment $oRequestOrderPayment order to set params.
     */
    protected function _prepareOrderPayment($oRequestOrderPayment)
    {
        $oRequest = $this->getRequest();

        $oRequestOrderPayment->setStatus($oRequest->getRequestParameter(self::PAYPAL_PAYMENT_STATUS));
        $oRequestOrderPayment->setTransactionId($oRequest->getRequestParameter(self::PAYPAL_TRANSACTION_ID));
        $oRequestOrderPayment->setCurrency($oRequest->getRequestParameter(self::MC_CURRENCY));
        $oRequestOrderPayment->setAmount($this->getAmount());
        $oRequestOrderPayment->setAction($this->getAction());

        $correlationId = $oRequest->getRequestParameter(self::CORRELATION_ID);
        if (empty($correlationId)) {
            $correlationId = $oRequest->getRequestParameter(self::IPN_TRACK_ID);
        }
        $oRequestOrderPayment->setCorrelationId($correlationId);

        $date = 0 < strlen($oRequest->getRequestParameter(self::PAYMENT_DATE)) ?
                date('Y-m-d H:i:s', strtotime($oRequest->getRequestParameter(self::PAYMENT_DATE))) : null;
        $oRequestOrderPayment->setDate($date);
    }

    /**
     * Get PayPal action from request, we might have a refund.
     *
     * @return string
     */
    protected function getAction()
    {
        $action    = self::CAPTURE_ACTION;
        $request   = $this->getRequest();
        $rawAmount = $request->getRequestParameter(self::MC_GROSS);
        $status    = $request->getRequestParameter(self::PAYPAL_PAYMENT_STATUS);

        if ( (0 > $rawAmount) && (self::PAYPAL_STATUS_REFUND_DONE == $status) ) {
            $action = self::REFUND_ACTION;
        }

        return $action;
    }

    /**
     * Get amount from request.
     *
     * @return number
     */
    protected function getAmount()
    {
        $request = $this->getRequest();
        return !is_null($request->getRequestParameter(self::MC_GROSS)) ? abs($request->getRequestParameter(self::MC_GROSS)) : null;
    }

}
