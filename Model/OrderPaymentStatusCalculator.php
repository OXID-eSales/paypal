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
 * Class for calculation PayPal order statuses after IPN and order creations.
 * Also calculates statuses for suggestion on void, refund, capture operation on PayPal order.
 */
class OrderPaymentStatusCalculator
{
    /**
     * PayPal Order.
     *
     * @var \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected $order = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\OrderPayment::class
     */
    protected $orderPayment = null;

    /**
     * Set PayPal Order.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder $order PayPal order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Return PayPal Order.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Sets PayPal OrderPayment.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $orderPayment
     */
    public function setOrderPayment($orderPayment)
    {
        $this->orderPayment = $orderPayment;
    }

    /**
     * Return PayPal OrderPayment.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment::class
     */
    public function getOrderPayment()
    {
        return $this->orderPayment;
    }

    /**
     * Return status for suggestion on void operation.
     *
     * @return bool
     */
    protected function getSuggestStatusOnVoid()
    {
        $status = 'canceled';

        if ($this->getOrder()->getCapturedAmount() > 0) {
            $status = 'completed';
        }

        return $status;
    }

    /**
     * Return true if order statuses can be changed automatically.
     *
     * @return bool
     */
    protected function isOrderPaymentStatusFinal()
    {
        $orderPaymentStatus = $this->getOrder()->getPaymentStatus();

        return $orderPaymentStatus == 'failed' || $orderPaymentStatus == 'canceled';
    }

    /**
     * Returns order payment status which should be set after order creation or IPN.
     *
     * @return string|null
     */
    public function getStatus()
    {
        if (is_null($this->getOrder())) {
            return;
        }

        $status = $this->getOrderPaymentStatusFinal();

        if (is_null($status)) {
            $status = $this->getOrderPaymentStatusPaymentValid();
        }
        if (is_null($status)) {
            $status = $this->getOrderPaymentStatusPayments();
        }

        return $status;
    }

    /**
     * Returns order suggestion for payment status on given action and on given payment.
     *
     * @param string $action - action with order payment: void, refund, capture, refund_partial, capture_partial
     *
     * @return string|null
     */
    public function getSuggestStatus($action)
    {
        if (is_null($this->getOrder())) {
            return;
        }

        $status = $this->getOrderPaymentStatusPaymentValid();
        if (is_null($status)) {
            $status = $this->getStatusByAction($action);
        }

        return $status;
    }

    /**
     * Returns order payment status if order has final status.
     *
     * @return string|null
     */
    protected function getOrderPaymentStatusFinal()
    {
        $status = null;
        if ($this->isOrderPaymentStatusFinal()) {
            $status = $this->getOrder()->getPaymentStatus();
        }

        return $status;
    }

    /**
     * Returns order payment status by checking if set payment is valid.
     *
     * @return string|null
     */
    protected function getOrderPaymentStatusPaymentValid()
    {
        $status = null;
        $orderPayment = $this->getOrderPayment();
        if (isset($orderPayment) && !$orderPayment->getIsValid()) {
            $status = 'failed';
        }

        return $status;
    }

    /**
     * Returns order payment status calculated from existing payments.
     *
     * @return string|null
     */
    protected function getOrderPaymentStatusPayments()
    {
        $status = 'completed';
        $paymentList = $this->getOrder()->getPaymentList();

        if ($paymentList->hasPendingPayment()) {
            $status = 'pending';
        } elseif ($paymentList->hasFailedPayment()) {
            $status = 'failed';
        }

        return $status;
    }

    /**
     * Returns order suggestion for payment status on given action.
     *
     * @param string $action performed action.
     *
     * @return string
     */
    protected function getStatusByAction($action)
    {
        $status = null;
        switch ($action) {
            case 'void':
                $status = $this->getSuggestStatusOnVoid();
                break;
            case 'refund_partial':
            case 'reauthorize':
                $status = $this->getOrder()->getPaymentStatus();
                break;
            case 'refund':
            case 'capture':
            case 'capture_partial':
                $status = 'completed';
                break;
            default:
                $status = 'completed';
        }

        return $status;
    }
}
