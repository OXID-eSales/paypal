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
 * Class \OxidEsales\PayPalModule\Model\OrderManager.
 */
class OrderManager
{
    /**
     * @var \OxidEsales\PayPalModule\Model\OrderPayment::class
     */
    protected $_oOrderPayment = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected $_oOrder = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator
     */
    protected $_oOrderPaymentStatusCalculator = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\OrderPaymentListCalculator
     */
    protected $_oOrderPaymentListCalculator = null;

    /**
     * Sets order payment.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $oOrderPayment
     */
    public function setOrderPayment($oOrderPayment)
    {
        $this->_oOrderPayment = $oOrderPayment;
    }

    /**
     * Returns order payment.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment::class
     */
    public function getOrderPayment()
    {
        return $this->_oOrderPayment;
    }

    /**
     * Sets order.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder $oOrder
     */
    public function setOrder($oOrder)
    {
        $this->_oOrder = $oOrder;
    }

    /**
     * Create object \OxidEsales\PayPalModule\Model\PayPalOrder.
     * If Order is not set, create order from Order Payment.
     *
     * @return object
     */
    public function getOrder()
    {
        if ($this->_oOrder === null) {
            $oOrderPayment = $this->getOrderPayment();
            $oOrder = $this->_getOrderFromPayment($oOrderPayment);
            $this->setOrder($oOrder);
        }

        return $this->_oOrder;
    }

    /**
     * Sets \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator $oOrderPaymentStatusCalculator
     */
    public function setOrderPaymentStatusCalculator($oOrderPaymentStatusCalculator)
    {
        $this->_oOrderPaymentStatusCalculator = $oOrderPaymentStatusCalculator;
    }

    /**
     * Returns \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator
     */
    public function getOrderPaymentStatusCalculator()
    {
        if (is_null($this->_oOrderPaymentStatusCalculator)) {
            $oOrderPaymentStatusCalculator = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator::class);
            $this->setOrderPaymentStatusCalculator($oOrderPaymentStatusCalculator);
        }

        return $this->_oOrderPaymentStatusCalculator;
    }

    /**
     * Sets \OxidEsales\PayPalModule\Model\OrderPaymentListCalculator.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPaymentListCalculator $oOrderPaymentListCalculator
     */
    public function setOrderPaymentListCalculator($oOrderPaymentListCalculator)
    {
        $this->_oOrderPaymentListCalculator = $oOrderPaymentListCalculator;
    }

    /**
     * Returns \OxidEsales\PayPalModule\Model\OrderPaymentListCalculator.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentListCalculator
     */
    public function getOrderPaymentListCalculator()
    {
        if (is_null($this->_oOrderPaymentListCalculator)) {
            $oOrderPaymentListCalculator = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentListCalculator::class);
            $this->setOrderPaymentListCalculator($oOrderPaymentListCalculator);
        }

        return $this->_oOrderPaymentListCalculator;
    }

    /**
     * Update order manager to status get from order status calculator.
     *
     * @return bool
     */
    public function updateOrderStatus()
    {
        $blOrderUpdated = false;
        $oOrder = $this->getOrder();

        if (!is_null($oOrder)) {
            $oOrderPayment = $this->getOrderPayment();
            $oOrder = $this->recalculateAmounts($oOrder);
            $sNewOrderStatus = $this->_calculateOrderStatus($oOrderPayment, $oOrder);
            $this->_updateOrderStatus($oOrder, $sNewOrderStatus);
            $blOrderUpdated = true;
        }

        return $blOrderUpdated;
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
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $oOrderPayment Order payment to set to calculator.
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder  $oOrder        Order to be set to validator.
     *
     * @return null|string
     */
    protected function _calculateOrderStatus($oOrderPayment, $oOrder)
    {
        $oOrderPaymentStatusCalculator = $this->getOrderPaymentStatusCalculator();
        $oOrderPaymentStatusCalculator->setOrderPayment($oOrderPayment);
        $oOrderPaymentStatusCalculator->setOrder($oOrder);

        $sNewOrderStatus = $oOrderPaymentStatusCalculator->getStatus();

        return $sNewOrderStatus;
    }

    /**
     * Update order to given status.
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder $oOrder          Order to be updated.
     * @param string                                     $sNewOrderStatus New order status.
     */
    protected function _updateOrderStatus($oOrder, $sNewOrderStatus)
    {
        $oOrder->setPaymentStatus($sNewOrderStatus);
        $oOrder->save();
    }

    /**
     * Load order by order id from order payment.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $oOrderPayment order payment to get order id.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder|null
     */
    protected function _getOrderFromPayment($oOrderPayment)
    {
        $sOrderId = null;
        $oOrder = null;
        if (!is_null($oOrderPayment)) {
            $sOrderId = $oOrderPayment->getOrderId();
        }
        if (!is_null($sOrderId)) {
            $oOrder = oxNew(\OxidEsales\PayPalModule\Model\PayPalOrder::class);
            $oOrder->setOrderId($sOrderId);
            $oOrder->load();
        }

        return $oOrder;
    }
}
