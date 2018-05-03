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
 * PayPal IPN payment creator class.
 * Handles inserting of new IPN data into database including
 * updating parent transactions if needed.
 */
class IPNPaymentCreator
{
    /** @var string IPN request parameter names. */
    const PAYPAL_IPN_AUTH_ID     = 'auth_id';

    /** @var string IPN request parameter names. */
    const PAYPAL_IPN_AUTH_STATUS = 'auth_status';

    /** @var string IPN request parameter names. */
    const PAYPAL_IPN_PARENT_TRANSACTION_ID = 'parent_txn_id';

    /** @var string String PayPal transaction entity. */
    const PAYPAL_IPN_TRANSACTION_ENTITY = 'transaction_entity';

    /** @var string Class only handles IPN transaction payments. */
    const HANDLE_TRANSACTION_ENTITY = 'payment';

    /** @var string PayPal payment comment. */
    const PAYPAL_IPN_MEMO = 'memo';

    /** @var \OxidEsales\PayPalModule\Core\Request */
    protected $request = null;

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
     * Handling of IPN data that has not yet a paypal order payment
     * entry in shop database.
     * Checks if information from IPN is relevant for Shop. That is the case
     * if the related PayPal parent transaction can be found in table oepaypal_orderpayments.
     *
     * If data is relevant, we need to
     * - create paypal order payment, store it in database and return it.
     * - if it is a refund, update the parent transaction's refunded amount
     * - update authorization status (can be Pending, In Progress, Completed)
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $requestOrderPayment
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment|null
     */
    public function handleOrderPayment($requestOrderPayment)
    {
        $return = null;

        $authId              = $this->getRequest()->getRequestParameter(self::PAYPAL_IPN_AUTH_ID);
        $transactionEntity   = $this->getRequest()->getRequestParameter(self::PAYPAL_IPN_TRANSACTION_ENTITY);
        $parentTransactionId = $this->getRequest()->getRequestParameter(self::PAYPAL_IPN_PARENT_TRANSACTION_ID);

        $orderPaymentAuthorization = $this->loadOrderPayment($authId);
        $orderPaymentParent        = $this->loadOrderPayment($parentTransactionId);
        $orderId                   = $orderPaymentParent->getOrderId();

        if ((self::HANDLE_TRANSACTION_ENTITY == $transactionEntity) && $orderId) {
            $requestOrderPayment->setOrderId($orderId);
            $requestOrderPayment->save();
            $requestOrderPayment = $this->addRequestPaymentComment($requestOrderPayment);

            if (\OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter::REFUND_ACTION == $requestOrderPayment->getAction()) {
                $this->updateParentTransactionRefundAmount($orderPaymentParent, $requestOrderPayment->getAmount());
            }
            if ((\OxidEsales\PayPalModule\Model\IPNRequestPaymentSetter::AUTHORIZATION_ACTION == $orderPaymentAuthorization->getAction())
                && ($orderId == $orderPaymentAuthorization->getOrderId())
            ) {
                $this->updateOrderPaymentAuthorizationStatus($orderPaymentAuthorization);
            }
            $return = $requestOrderPayment;
        }

        return $return;
    }

    /**
     * Update the parent transaction's refund amount.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $orderPaymentParent
     * @param float                                       $amount
     */
    private function updateParentTransactionRefundAmount($orderPaymentParent, $amount)
    {
        $orderPaymentParent->addRefundedAmount($amount);
        $orderPaymentParent->save();
    }

    /**
     * The status of the related authorization transaction might have changed.
     * We need to update that transaction status as well if we got a value in IPN request data.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $orderPaymentAuthorization Authorization PayPal order payment
     */
    private function updateOrderPaymentAuthorizationStatus($orderPaymentAuthorization)
    {
        $authStatus = $this->getRequest()->getRequestParameter(self::PAYPAL_IPN_AUTH_STATUS);

        if ($authStatus) {
            $orderPaymentAuthorization->setStatus($authStatus);
            $orderPaymentAuthorization->save();
        }
    }

    /**
     * Load order payment from transaction id.
     *
     * @param string $transactionId transaction id to load object.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment|null
     */
    private function loadOrderPayment($transactionId)
    {
        $orderPayment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $orderPayment->loadByTransactionId($transactionId);

        return $orderPayment;
    }

    /**
     * Add comment for request payment if comment exists.
     * Function must only be called when the payment object this
     * comment is related to is already stored in the database and has an oxid.
     * Comment will be immediately saved to database.
     *
     * @param  \OxidEsales\PayPalModule\Model\OrderPayment $paypalOrderPayment
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    private function addRequestPaymentComment($paypalOrderPayment)
    {
        $request = $this->getRequest();
        $memo    = $request->getRequestParameter(self::PAYPAL_IPN_MEMO);

        if ($memo) {
            $comment = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentComment::class);
            $comment->setComment($memo);
            $paypalOrderPayment->addComment($comment);
            if (0 < $paypalOrderPayment->getId()) {
                $paypalOrderPayment->save();
            }
        }
        return $paypalOrderPayment;
    }
}
