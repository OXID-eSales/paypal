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
 * PayPal IPN request payment setter class.
 */
class IPNRequestPaymentSetter
{
    /** @var string PayPal action that triggered this transaction. */
    const CAPTURE_ACTION        = 'capture';
    const REFUND_ACTION         = 'refund';
    const AUTHORIZATION_ACTION  = 'authorization';

    /** @var string Sandbox mode parameter name. */
    const PAYPAL_SANDBOX = 'test_ipn';

    /** @var string String PayPal payment status parameter name. */
    const PAYPAL_PAYMENT_STATUS = 'payment_status';

    /** @var string PayPal transaction id. */
    const PAYPAL_TRANSACTION_ID = 'txn_id';

    /** @var string PayPal whole price including payment and shipment. */
    const MC_GROSS = 'mc_gross';

    /** @var string PayPal payment currency. */
    const MC_CURRENCY = 'mc_currency';

    /** @var string PayPal payment date. */
    const PAYMENT_DATE = 'payment_date';

    /** @var string PayPal payment correlation id. */
    const CORRELATION_ID = 'correlation_id';

    /** @var string PayPal payment ipn tracking id, might come instead of correlation id. */
    const IPN_TRACK_ID = 'ipn_track_id';

    /** @var string PayPal status for successful refund. */
    const PAYPAL_STATUS_REFUND_DONE = 'Refunded';

    /**
     * @var \OxidEsales\PayPalModule\Core\Request
     */
    protected $request = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected $requestOrderPayment = null;

    /**
     * Sets request object to get params for IPN request.
     *
     * @param \OxidEsales\PayPalModule\Core\Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Gets request object to get params for IPN request.
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets request order payment object.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $orderPayment
     */
    public function setRequestOrderPayment($orderPayment)
    {
        $this->requestOrderPayment = $orderPayment;
    }

    /**
     * Returns order payment object.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    public function getRequestOrderPayment()
    {
        $this->prepareOrderPayment($this->requestOrderPayment);

        return $this->requestOrderPayment;
    }

    /**
     * Prepare PayPal payment. Fill up with request values.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $requestOrderPayment order to set params.
     */
    protected function prepareOrderPayment($requestOrderPayment)
    {
        $request = $this->getRequest();

        $requestOrderPayment->setStatus($request->getRequestParameter(self::PAYPAL_PAYMENT_STATUS));
        $requestOrderPayment->setTransactionId($request->getRequestParameter(self::PAYPAL_TRANSACTION_ID));
        $requestOrderPayment->setCurrency($request->getRequestParameter(self::MC_CURRENCY));
        $requestOrderPayment->setAmount($this->getAmount());
        $requestOrderPayment->setAction($this->getAction());

        $correlationId = $request->getRequestParameter(self::CORRELATION_ID);
        if (!$correlationId) {
            $correlationId = $request->getRequestParameter(self::IPN_TRACK_ID);
        }
        $requestOrderPayment->setCorrelationId($correlationId);

        $date = 0 < strlen($request->getRequestParameter(self::PAYMENT_DATE)) ?
                date('Y-m-d H:i:s', strtotime($request->getRequestParameter(self::PAYMENT_DATE))) : null;
        $requestOrderPayment->setDate($date);
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

        if ((0 > $rawAmount) && (self::PAYPAL_STATUS_REFUND_DONE == $status)) {
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
