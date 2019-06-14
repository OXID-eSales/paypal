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
 * PayPal oxOrder class
 *
 * @mixin \OxidEsales\Eshop\Application\Model\Order
 */
class Order extends Order_parent
{
    /** Transaction was finished successfully. */
    const OEPAYPAL_TRANSACTION_STATUS_OK = 'OK';

    /** Transaction is not finished or failed. */
    const OEPAYPAL_TRANSACTION_STATUS_NOT_FINISHED = 'NOT_FINISHED';

    /**
     * PayPal order information
     *
     * @var \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected $payPalOrder = null;

    /**
     * Loads order associated with current PayPal order
     *
     * @return bool
     */
    public function loadPayPalOrder()
    {
        $orderId = \OxidEsales\Eshop\Core\Registry::getSession()->getVariable("sess_challenge");

        // if order is not created yet - generating it
        if ($orderId === null) {
            $orderId = \OxidEsales\Eshop\Core\UtilsObject::getInstance()->generateUID();
            $this->setId($orderId);
            $this->save();
            \OxidEsales\Eshop\Core\Registry::getSession()->setVariable("sess_challenge", $orderId);
        }

        return $this->load($orderId);
    }

    /**
     * Updates order number.
     *
     * @return bool
     */
    public function oePayPalUpdateOrderNumber()
    {
        if ($this->oxorder__oxordernr->value) {
            $updated = (bool) oxNew(\OxidEsales\Eshop\Core\Counter::class)->update($this->_getCounterIdent(), $this->oxorder__oxordernr->value);
        } else {
            $updated = $this->_setNumber();
        }

        return $updated;
    }

    /**
     * Delete order created by current PayPal ordering process
     *
     * @return bool
     */
    public function deletePayPalOrder()
    {
        $result = false;
        if ($this->loadPayPalOrder()) {
            $this->getPayPalOrder()->delete();

            $result = $this->delete();
        }

        return $result;
    }

    /**
     * Delete order together with PayPal order data.
     *
     * @param string $oxId
     *
     * @return bool
     */
    public function delete($oxId = null)
    {
        $this->getPayPalOrder($oxId)->delete();

        return parent::delete($oxId);
    }

    /**
     * Updates order transaction status, ID and date.
     *
     * @param string $transactionId Order transaction ID
     */
    protected function setPaymentInfoPayPalOrder($transactionId)
    {
        // set transaction ID and payment date to order
        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();

        $query = 'update oxorder set oxtransid=' . $db->quote($transactionId) . ' where oxid=' . $db->quote($this->getId());
        $db->execute($query);

        //updating order object
        $this->oxorder__oxtransid = new \OxidEsales\Eshop\Core\Field($transactionId);
    }

    /**
     * Finalizes PayPal order.
     *
     * @param \OxidEsales\PayPalModule\Model\Response\ResponseDoExpressCheckoutPayment $result          PayPal results array.
     * @param \OxidEsales\Eshop\Application\Model\Basket                               $basket          Basket object.
     * @param string                                                                   $transactionMode Transaction mode Sale|Authorization.
     */
    public function finalizePayPalOrder($result, $basket, $transactionMode)
    {
        $utilsDate = \OxidEsales\Eshop\Core\Registry::getUtilsDate();
        $date = date('Y-m-d H:i:s', $utilsDate->getTime());

        // set order status, transaction ID and payment date to order
        $this->setPaymentInfoPayPalOrder($result->getTransactionId());

        $currency = $result->getCurrencyCode();
        if (!$currency) {
            $currency = $this->getOrderCurrency()->name;
        }

        // PayPal order info
        $payPalOrder = $this->getPayPalOrder();
        $payPalOrder->setOrderId($this->getId());
        $payPalOrder->setPaymentStatus('pending');
        $payPalOrder->setTransactionMode($transactionMode);
        $payPalOrder->setCurrency($currency);
        $payPalOrder->setTotalOrderSum($basket->getPrice()->getBruttoPrice());
        if ($transactionMode == 'Sale') {
            $payPalOrder->setCapturedAmount($basket->getPrice()->getBruttoPrice());
        }
        $payPalOrder->save();

        $orderPayment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $orderPayment->setTransactionId($result->getTransactionId());
        $orderPayment->setCorrelationId($result->getCorrelationId());
        $orderPayment->setDate($date);
        $orderPayment->setAction(($transactionMode == 'Sale') ? 'capture' : 'authorization');
        $orderPayment->setStatus($result->getPaymentStatus());
        $orderPayment->setAmount($result->getAmount());
        $orderPayment->setCurrency($result->getCurrencyCode());

        //Adding payment information
        $paymentList = $this->getPayPalOrder()->getPaymentList();
        $paymentList->addPayment($orderPayment);

        //setting order payment status after
        $paymentStatusCalculator = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator::class);
        $paymentStatusCalculator->setOrder($this->getPayPalOrder());
        $this->getPayPalOrder()->setPaymentStatus($paymentStatusCalculator->getStatus(), $this);
        $this->getPayPalOrder()->save();

        //clear PayPal identification
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $session->deleteVariable('oepaypal');
        $session->deleteVariable("oepaypal-payerId");
        $session->deleteVariable("oepaypal-userId");
        $session->deleteVariable('oepaypal-basketAmount');
    }

    /**
     * Paypal specific status checking.
     *
     * If status comes as OK, lets check real paypal payment state,
     * and if really ok, so lets set it, otherwise dont change status.
     *
     * @param string $status order transaction status
     */
    protected function _setOrderStatus($status)
    {
        $paymentTypeObject = $this->getPaymentType();
        $paymentType = $paymentTypeObject ? $paymentTypeObject->getFieldData('oxpaymentsid') : null;
        if ($paymentType != 'oxidpaypal' || $status != self::OEPAYPAL_TRANSACTION_STATUS_OK) {
            parent::_setOrderStatus($status);
        }
    }

    /**
     * Update order oxpaid to current time.
     */
    public function markOrderPaid()
    {
        parent::_setOrderStatus(self::OEPAYPAL_TRANSACTION_STATUS_OK);

        $db = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $utilsDate = \OxidEsales\Eshop\Core\Registry::getUtilsDate();
        $date = date('Y-m-d H:i:s', $utilsDate->getTime());

        $query = 'update oxorder set oxpaid=? where oxid=?';
        $db->execute($query, array($date, $this->getId()));

        //updating order object
        $this->oxorder__oxpaid = new \OxidEsales\Eshop\Core\Field($date);
    }

    /**
     * Checks if delivery set used for current order is available and active.
     * Throws exception if not available
     *
     * @param \OxidEsales\Eshop\Application\Model\Basket $basket basket object
     *
     * @return int
     */
    public function validateDelivery($basket)
    {
        if ($basket->getPaymentId() == 'oxidpaypal') {
            $shippingId = $basket->getShippingId();
            $basketPrice = $basket->getPrice()->getBruttoPrice();
            $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
            if (!$user->loadUserPayPalUser()) {
                $user = $this->getUser();
            }

            $validState = null;
            if (!$this->isPayPalPaymentValid($user, $basketPrice, $shippingId)) {
                $validState = self::ORDER_STATE_INVALIDDELIVERY;
            }
        } else {
            $validState = parent::validateDelivery($basket);
        }

        return $validState;
    }

    /**
     * Returns PayPal order object.
     *
     * @param string $oxId
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder|null
     */
    public function getPayPalOrder($oxId = null)
    {
        if (is_null($this->payPalOrder)) {
            $orderId = is_null($oxId) ? $this->getId() : $oxId;
            $order = oxNew(\OxidEsales\PayPalModule\Model\PayPalOrder::class);
            $order->load($orderId);
            $this->payPalOrder = $order;
        }

        return $this->payPalOrder;
    }

    /**
     * Get payment status
     *
     * @return string
     */
    public function getPayPalPaymentStatus()
    {
        return $this->getPayPalOrder()->getPaymentStatus();
    }

    /**
     * Returns PayPal Authorization id.
     *
     * @return string
     */
    public function getAuthorizationId()
    {
        return $this->oxorder__oxtransid->value;
    }

    /**
     * Checks whether PayPal payment is available.
     *
     * @param object $user
     * @param double $basketPrice
     * @param string $shippingId
     *
     * @return bool
     */
    protected function isPayPalPaymentValid($user, $basketPrice, $shippingId)
    {
        $valid = true;

        $payPalPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payPalPayment->load('oxidpaypal');
        if (!$payPalPayment->isValidPayment(null, null, $user, $basketPrice, $shippingId)) {
            $valid = $this->isEmptyPaymentValid($user, $basketPrice, $shippingId);
        }

        return $valid;
    }

    /**
     * Checks whether Empty payment is available.
     *
     * @param object $user
     * @param double $basketPrice
     * @param string $shippingId
     *
     * @return bool
     */
    protected function isEmptyPaymentValid($user, $basketPrice, $shippingId)
    {
        $valid = true;

        $emptyPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $emptyPayment->load('oxempty');
        if (!$emptyPayment->isValidPayment(null, null, $user, $basketPrice, $shippingId)) {
            $valid = false;
        }

        return $valid;
    }
}
