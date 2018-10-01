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

use OxidEsales\PayPalModule\Controller\OrderController;

/**
 * Testing oePayPalOxState class.
 */
class OrderManagerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testGetOrder_noOrderSetWithPaymentSet_orderCreatedFomPayment()
    {
        $orderId = '_orderId';
        $orderPayment = $this->prepareOrderPayment($orderId);

        $payPalOrderManager = new \OxidEsales\PayPalModule\Model\OrderManager();
        $payPalOrderManager->setOrderPayment($orderPayment);

        $orderFromManager = $payPalOrderManager->getOrder();
        $orderIdFromManager = $orderFromManager->getOrderid();

        $this->assertEquals($orderId, $orderIdFromManager, 'Order id from manager is not same as in payment.');
    }

    public function testGetOrder_noOrderSetNoPaymentSet_noOrderCreated()
    {
        $payPalOrderManager = new \OxidEsales\PayPalModule\Model\OrderManager();
        $orderFromManager = $payPalOrderManager->getOrder();

        $this->assertEquals(null, $orderFromManager, 'No order should be created as no order set and no payment set.');
    }

    /**
     * Check if correct Order Payment object passed to Calculator.
     * Check if correct Order Status is stored afterword.
     */
    public function testUpdateOrderStatus_withOrderPaymentWithOrder_orderStatusFromCalculator()
    {
        $orderId = '_orderId';
        $order = $this->prepareOrder($orderId);
        $orderPayment = $this->prepareOrderPayment($orderId);
        $OrderCalculatedStatus = 'completed';
        $payPalOrderPaymentStatusCalculator = $this->preparePayPalOrderPaymentStatusCalculator($orderPayment, $order, $OrderCalculatedStatus);

        $payPalOrderManager = new \OxidEsales\PayPalModule\Model\OrderManager();
        $payPalOrderManager->setOrderPayment($orderPayment);
        $payPalOrderManager->setOrder($order);
        $payPalOrderManager->setOrderPaymentStatusCalculator($payPalOrderPaymentStatusCalculator);
        $orderUpdated = $payPalOrderManager->updateOrderStatus();

        $payPalOrderManager->getOrder();
        $orderNewStatus = $order->getPaymentStatus();
        $this->assertEquals($OrderCalculatedStatus, $orderNewStatus, 'Order status did not change to calculator calculated.');
        $this->assertTrue($orderUpdated, 'Order should be updated, and return indicates this with true.');
    }

    /**
     * Check if correct Order Payment object passed to Calculator.
     * Check if correct Order Status is stored afterword.
     */
    public function testUpdateOrderStatus_noOrderPaymentWithOrder_orderStatusFromCalculator()
    {
        $orderId = '_orderId';
        $order = $this->prepareOrder($orderId);
        $orderPayment = null;
        $OrderCalculatedStatus = 'completed';
        $payPalOrderPaymentStatusCalculator = $this->preparePayPalOrderPaymentStatusCalculator($orderPayment, $order, $OrderCalculatedStatus);

        $payPalOrderManager = new \OxidEsales\PayPalModule\Model\OrderManager();
        $payPalOrderManager->setOrder($order);
        $payPalOrderManager->setOrderPaymentStatusCalculator($payPalOrderPaymentStatusCalculator);
        $orderUpdated = $payPalOrderManager->updateOrderStatus();

        $payPalOrderManager->getOrder();
        $orderNewStatus = $order->getPaymentStatus();
        $this->assertEquals($OrderCalculatedStatus, $orderNewStatus, 'Order status did not change to calculator calculated.');
        $this->assertTrue($orderUpdated, 'Order should be updated, and return indicates this with true.');
    }

    /**
     * Check if correct Order Payment object passed to Calculator.
     * Check if correct Order Status is stored afterword.
     */
    public function testUpdateOrderStatus_withOrderPaymentNoOrder_orderStatusFromCalculator()
    {
        $orderId = '_orderId';
        $order = $this->prepareOrder($orderId);
        $orderPayment = $this->prepareOrderPayment($orderId);
        $OrderCalculatedStatus = 'completed';
        $payPalOrderPaymentStatusCalculator = $this->preparePayPalOrderPaymentStatusCalculator($orderPayment, $order, $OrderCalculatedStatus);

        // Mock order manager to check if order is created from given payment. This prevents from database usage.
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\OrderManager::class);
        $mockBuilder->setMethods(['getOrderFromPayment']);
        $payPalOrderManager = $mockBuilder->getMock();
        $payPalOrderManager->expects($this->once())->method('getOrderFromPayment')->with($orderPayment)->will($this->returnValue($order));
        $payPalOrderManager->setOrderPayment($orderPayment);
        $payPalOrderManager->setOrderPaymentStatusCalculator($payPalOrderPaymentStatusCalculator);
        $orderUpdated = $payPalOrderManager->updateOrderStatus();

        $payPalOrderManager->getOrder();
        $orderNewStatus = $order->getPaymentStatus();
        $this->assertEquals($OrderCalculatedStatus, $orderNewStatus, 'Order status did not change to calculator calculated.');
        $this->assertTrue($orderUpdated, 'Order should be updated, and return indicates this with true.');
    }

    /**
     * Check if correct Order Payment object passed to Calculator.
     * Check if correct Order Status is stored afterword.
     */
    public function testUpdateOrderStatus_noOrderPaymentNoOrder_orderStatusFromCalculator()
    {
        $payPalOrderManager = new \OxidEsales\PayPalModule\Model\OrderManager();
        $orderUpdated = $payPalOrderManager->updateOrderStatus();

        $this->assertFalse($orderUpdated, 'Order should be updated, and return indicates this with true.');
    }

    /**
     * Create order with status different than order status calculator returns.
     *
     * @param string $orderId order id.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected function prepareOrder($orderId)
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setPaymentStatus('pending');
        $order->setOrderId($orderId);

        return $order;
    }

    /**
     * Create order payment with some transaction id and same order id as order in _prepareOrder().
     *
     * @param string $orderId order id.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function prepareOrderPayment($orderId)
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setTransactionId('_asdadsd45a4sd5a4sd54a5');
        $orderPayment->setOrderId($orderId);

        return $orderPayment;
    }

    /**
     * Mock order payment calculator.
     * Check if called with correct parameters.
     * Mock return calculated state. Order state should change according to this one.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $orderPayment
     * @param OrderController                             $order
     * @param string                                      $OrderCalculatedStatus
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator
     */
    protected function preparePayPalOrderPaymentStatusCalculator($orderPayment, $order, $OrderCalculatedStatus)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator::class);
        $mockBuilder->setMethods(['setOrderPayment', 'setOrder', 'getStatus']);
        $payPalOrderPaymentStatusCalculator = $mockBuilder->getMock();
        $payPalOrderPaymentStatusCalculator->expects($this->any())->method('setOrderPayment')->with($orderPayment);
        $payPalOrderPaymentStatusCalculator->expects($this->once())->method('setOrder')->with($order);
        $payPalOrderPaymentStatusCalculator->expects($this->any())->method('getStatus')->will($this->returnValue($OrderCalculatedStatus));

        return $payPalOrderPaymentStatusCalculator;
    }
}
