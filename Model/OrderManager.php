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
 * Class \OxidEsales\PayPalModule\Model\OrderManager.
 */
class OrderManager
{
    /**
     * @var \OxidEsales\PayPalModule\Model\OrderPayment::class
     */
    protected $orderPayment = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected $order = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator
     */
    protected $orderPaymentStatusCalculator = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\OrderPaymentListCalculator
     */
    protected $orderPaymentListCalculator = null;

    /**
     * Sets order payment.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $orderPayment
     */
    public function setOrderPayment($orderPayment)
    {
        $this->orderPayment = $orderPayment;
    }

    /**
     * Returns order payment.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment::class
     */
    public function getOrderPayment()
    {
        return $this->orderPayment;
    }

    /**
     * Sets order.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * Create object \OxidEsales\PayPalModule\Model\PayPalOrder.
     * If Order is not set, create order from Order Payment.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    public function getOrder()
    {
        if ($this->order === null) {
            $orderPayment = $this->getOrderPayment();
            $order = $this->getOrderFromPayment($orderPayment);
            $this->setOrder($order);
        }

        return $this->order;
    }

    /**
     * Sets \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator $orderPaymentStatusCalculator
     */
    public function setOrderPaymentStatusCalculator($orderPaymentStatusCalculator)
    {
        $this->orderPaymentStatusCalculator = $orderPaymentStatusCalculator;
    }

    /**
     * Returns \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator
     */
    public function getOrderPaymentStatusCalculator()
    {
        if (is_null($this->orderPaymentStatusCalculator)) {
            $orderPaymentStatusCalculator = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator::class);
            $this->setOrderPaymentStatusCalculator($orderPaymentStatusCalculator);
        }

        return $this->orderPaymentStatusCalculator;
    }

    /**
     * Sets \OxidEsales\PayPalModule\Model\OrderPaymentListCalculator.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPaymentListCalculator $orderPaymentListCalculator
     */
    public function setOrderPaymentListCalculator($orderPaymentListCalculator)
    {
        $this->orderPaymentListCalculator = $orderPaymentListCalculator;
    }

    /**
     * Returns \OxidEsales\PayPalModule\Model\OrderPaymentListCalculator.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentListCalculator
     */
    public function getOrderPaymentListCalculator()
    {
        if (is_null($this->orderPaymentListCalculator)) {
            $orderPaymentListCalculator = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentListCalculator::class);
            $this->setOrderPaymentListCalculator($orderPaymentListCalculator);
        }

        return $this->orderPaymentListCalculator;
    }

    /**
     * Update order manager to status get from order status calculator.
     *
     * @return bool
     */
    public function updateOrderStatus()
    {
        $orderUpdated = false;
        $order = $this->getOrder();

        if (!is_null($order)) {
            $orderPayment = $this->getOrderPayment();
            $order = $this->recalculateAmounts($order);
            $newOrderStatus = $this->calculateOrderStatus($orderPayment, $order);
            $this->persistNewOrderStatus($order, $newOrderStatus);
            $orderUpdated = true;
        }

        return $orderUpdated;
    }

    /**
     * Recalculate order amounts from connected PayPal payment list.
     * This is especially needed if some new PayPal order payment
     * entry was created by IPN handler or if we e.g. got a void
     * on an existing authorization by IPN.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder $order
     *
     * @return mixed
     */
    protected function recalculateAmounts($order)
    {
        $paymentList = $order->getPaymentList();
        $orderPaymentListCalculator = $this->getOrderPaymentListCalculator();
        $orderPaymentListCalculator->setPaymentList($paymentList);
        $orderPaymentListCalculator->calculate();

        $order->setCapturedAmount($orderPaymentListCalculator->getCapturedAmount());
        $order->setVoidedAmount($orderPaymentListCalculator->getVoidedAmount());
        $order->setRefundedAmount($orderPaymentListCalculator->getRefundedAmount());
        $order->save();

        return $order;
    }

    /**
     * Wrapper for order payment calculator.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $orderPayment Order payment to set to calculator.
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder  $order        Order to be set to validator.
     *
     * @return null|string
     */
    protected function calculateOrderStatus($orderPayment, $order)
    {
        $orderPaymentStatusCalculator = $this->getOrderPaymentStatusCalculator();
        $orderPaymentStatusCalculator->setOrderPayment($orderPayment);
        $orderPaymentStatusCalculator->setOrder($order);

        $newOrderStatus = $orderPaymentStatusCalculator->getStatus();

        return $newOrderStatus;
    }

    /**
     * Update order to given status.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder $order          Order to be updated.
     * @param string                                     $newOrderStatus New order status.
     */
    protected function persistNewOrderStatus($order, $newOrderStatus)
    {
        $order->setPaymentStatus($newOrderStatus);
        $order->save();
    }

    /**
     * Load order by order id from order payment.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $orderPayment order payment to get order id.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder|null
     */
    protected function getOrderFromPayment($orderPayment)
    {
        $orderId = null;
        $order = null;
        if (!is_null($orderPayment)) {
            $orderId = $orderPayment->getOrderId();
        }
        if (!is_null($orderId)) {
            $order = oxNew(\OxidEsales\PayPalModule\Model\PayPalOrder::class);
            $order->setOrderId($orderId);
            $order->load();
        }

        return $order;
    }
}
