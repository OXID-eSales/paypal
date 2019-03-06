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
 * PayPal PayPalOrder class
 */
class PayPalOrder extends \OxidEsales\PayPalModule\Core\Model
{
    /** Completion status */
    const PAYPAL_ORDER_STATE_COMPLETED = 'completed';

    /**
     * List of order payments.
     *
     * @var \OxidEsales\PayPalModule\Model\OrderPaymentList
     */
    protected $paymentList = null;

    /**
     * Sets order id.
     *
     * @param string $orderId
     */
    public function setId($orderId)
    {
        $this->setOrderId($orderId);
    }

    /**
     * Returns order id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->getOrderId();
    }

    /**
     * Set PayPal order Id.
     *
     * @param string $orderId
     */
    public function setOrderId($orderId)
    {
        $this->setValue('oepaypal_orderid', $orderId);
    }

    /**
     * Set PayPal order Id.
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->getValue('oepaypal_orderid');
    }

    /**
     * Set PayPal captured amount.
     *
     * @param double $amount
     */
    public function setCapturedAmount($amount)
    {
        $this->setValue('oepaypal_capturedamount', $amount);
    }

    /**
     * Adds given amount to PayPal captured amount.
     *
     * @param double $amount
     */
    public function addCapturedAmount($amount)
    {
        $this->setCapturedAmount($amount + $this->getCapturedAmount());
    }

    /**
     * Get PayPal captured amount.
     *
     * @return double
     */
    public function getCapturedAmount()
    {
        return (double) $this->getValue('oepaypal_capturedamount');
    }

    /**
     * Set PayPal refunded amount.
     *
     * @param double $amount
     */
    public function setRefundedAmount($amount)
    {
        $this->setValue('oepaypal_refundedamount', $amount);
    }

    /**
     * Adds given amount to PayPal refunded amount.
     *
     * @param double $amount
     */
    public function addRefundedAmount($amount)
    {
        $this->setRefundedAmount($amount + $this->getRefundedAmount());
    }

    /**
     * Get PayPal refunded amount.
     *
     * @return double
     */
    public function getRefundedAmount()
    {
        return (double) $this->getValue('oepaypal_refundedamount');
    }

    /**
     * Returns not yet captured (remaining) order sum.
     *
     * @return double
     */
    public function getRemainingRefundAmount()
    {
        return round($this->getCapturedAmount() - $this->getRefundedAmount(), 2);
    }

    /**
     * Set PayPal refunded amount.
     *
     * @param double $amount
     */
    public function setVoidedAmount($amount)
    {
        $this->setValue('oepaypal_voidedamount', $amount);
    }

    /**
     * Get PayPal refunded amount.
     *
     * @return double
     */
    public function getVoidedAmount()
    {
        return (double) $this->getValue('oepaypal_voidedamount');
    }

    /**
     * Set transaction mode.
     *
     * @param string $mode
     */
    public function setTransactionMode($mode)
    {
        $this->setValue('oepaypal_transactionmode', $mode);
    }

    /**
     * Get transaction mode.
     *
     * @return string
     */
    public function getTransactionMode()
    {
        return $this->getValue('oepaypal_transactionmode');
    }

    /**
     * Set payment status.
     *
     * @param string                                    $status
     * @param \OxidEsales\Eshop\Application\Model\Order $order Shop order object
     */
    public function setPaymentStatus($status, \OxidEsales\Eshop\Application\Model\Order $order = null)
    {
        $this->setValue('oepaypal_paymentstatus', $status);

        // if payment completed, set order paid
        if ($status == \OxidEsales\PayPalModule\Model\PayPalOrder::PAYPAL_ORDER_STATE_COMPLETED) {
            if (is_null($order)) {
                $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
                $order->load($this->getOrderId());
            }
            $order->markOrderPaid();
        }
    }

    /**
     * Get payment status.
     *
     * @return string
     */
    public function getPaymentStatus()
    {
        $state = $this->getValue('oepaypal_paymentstatus');
        if (empty($state)) {
            $state = self::PAYPAL_ORDER_STATE_COMPLETED;
        }

        return $state;
    }

    /**
     * Sets total order sum.
     *
     * @param double $amount
     */
    public function setTotalOrderSum($amount)
    {
        $this->setValue('oepaypal_totalordersum', $amount);
    }

    /**
     * Returns total order sum.
     *
     * @return string
     */
    public function getTotalOrderSum()
    {
        return $this->getValue('oepaypal_totalordersum');
    }

    /**
     * Returns not yet captured (remaining) order sum.
     *
     * @return string
     */
    public function getRemainingOrderSum()
    {
        return $this->getTotalOrderSum() - $this->getCapturedAmount();
    }

    /**
     * Set order currency.
     *
     * @param string $status
     */
    public function setCurrency($status)
    {
        $this->setValue('oepaypal_currency', $status);
    }

    /**
     * Returns order currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->getValue('oepaypal_currency');
    }

    /**
     * Adds new payment.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $payment order payment
     */
    public function addPayment(\OxidEsales\PayPalModule\Model\OrderPayment $payment)
    {
        $paymentList = $this->getPaymentList();
        $paymentList->addPayment($payment);
        $this->setPaymentList(null);
    }

    /**
     * Return database gateway.
     *
     * @return \OxidEsales\PayPalModule\Model\DbGateways\PayPalOrderDbGateway|\OxidEsales\PayPalModule\Core\ModelDbGateway
     */
    protected function getDbGateway()
    {
        if (is_null($this->dbGateway)) {
            $this->setDbGateway(oxNew(\OxidEsales\PayPalModule\Model\DbGateways\PayPalOrderDbGateway::class));
        }

        return $this->dbGateway;
    }

    /**
     * Return order payment list.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentList
     */
    public function getPaymentList()
    {
        if (is_null($this->paymentList)) {
            $paymentList = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentList::class);
            $paymentList->load($this->getOrderId());
            $this->setPaymentList($paymentList);
        }

        return $this->paymentList;
    }

    /**
     * Return order payment list.
     *
     * @param oePayPal $paymentList Payment list.
     */
    public function setPaymentList($paymentList)
    {
        $this->paymentList = $paymentList;
    }
}
