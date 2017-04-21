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

use OxidEsales\PayPalModule\Controller\OrderController;

/**
 * Testing oePayPalOxState class.
 */
class OrderManagerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testGetOrder_noOrderSetWithPaymentSet_orderCreatedFomPayment()
    {
        $sOrderId = '_orderId';
        $oOrderPayment = $this->_prepareOrderPayment($sOrderId);

        $oPayPalOrderManager = new \OxidEsales\PayPalModule\Model\OrderManager();
        $oPayPalOrderManager->setOrderPayment($oOrderPayment);

        $oOrderFromManager = $oPayPalOrderManager->getOrder();
        $sOrderIdFromManager = $oOrderFromManager->getOrderid();

        $this->assertEquals($sOrderId, $sOrderIdFromManager, 'Order id from manager is not same as in payment.');
    }

    public function testGetOrder_noOrderSetNoPaymentSet_noOrderCreated()
    {
        $oPayPalOrderManager = new \OxidEsales\PayPalModule\Model\OrderManager();
        $oOrderFromManager = $oPayPalOrderManager->getOrder();

        $this->assertEquals(null, $oOrderFromManager, 'No order should be created as no order set and no payment set.');
    }

    /**
     * Check if correct Order Payment object passed to Calculator.
     * Check if correct Order Status is stored afterword.
     */
    public function testUpdateOrderStatus_withOrderPaymentWithOrder_orderStatusFromCalculator()
    {
        $sOrderId = '_orderId';
        $oOrder = $this->_prepareOrder($sOrderId);
        $oOrderPayment = $this->_prepareOrderPayment($sOrderId);
        $OrderCalculatedStatus = 'completed';
        $oPayPalOrderPaymentStatusCalculator = $this->_preparePayPalOrderPaymentStatusCalculator($oOrderPayment, $oOrder, $OrderCalculatedStatus);

        $oPayPalOrderManager = new \OxidEsales\PayPalModule\Model\OrderManager();
        $oPayPalOrderManager->setOrderPayment($oOrderPayment);
        $oPayPalOrderManager->setOrder($oOrder);
        $oPayPalOrderManager->setOrderPaymentStatusCalculator($oPayPalOrderPaymentStatusCalculator);
        $blOrderUpdated = $oPayPalOrderManager->updateOrderStatus();

        $oPayPalOrderManager->getOrder();
        $sOrderNewStatus = $oOrder->getPaymentStatus();
        $this->assertEquals($OrderCalculatedStatus, $sOrderNewStatus, 'Order status did not change to calculator calculated.');
        $this->assertTrue($blOrderUpdated, 'Order should be updated, and return indicates this with true.');
    }

    /**
     * Check if correct Order Payment object passed to Calculator.
     * Check if correct Order Status is stored afterword.
     */
    public function testUpdateOrderStatus_noOrderPaymentWithOrder_orderStatusFromCalculator()
    {
        $sOrderId = '_orderId';
        $oOrder = $this->_prepareOrder($sOrderId);
        $oOrderPayment = null;
        $OrderCalculatedStatus = 'completed';
        $oPayPalOrderPaymentStatusCalculator = $this->_preparePayPalOrderPaymentStatusCalculator($oOrderPayment, $oOrder, $OrderCalculatedStatus);

        $oPayPalOrderManager = new \OxidEsales\PayPalModule\Model\OrderManager();
        $oPayPalOrderManager->setOrder($oOrder);
        $oPayPalOrderManager->setOrderPaymentStatusCalculator($oPayPalOrderPaymentStatusCalculator);
        $blOrderUpdated = $oPayPalOrderManager->updateOrderStatus();

        $oPayPalOrderManager->getOrder();
        $sOrderNewStatus = $oOrder->getPaymentStatus();
        $this->assertEquals($OrderCalculatedStatus, $sOrderNewStatus, 'Order status did not change to calculator calculated.');
        $this->assertTrue($blOrderUpdated, 'Order should be updated, and return indicates this with true.');
    }

    /**
     * Check if correct Order Payment object passed to Calculator.
     * Check if correct Order Status is stored afterword.
     */
    public function testUpdateOrderStatus_withOrderPaymentNoOrder_orderStatusFromCalculator()
    {
        $sOrderId = '_orderId';
        $oOrder = $this->_prepareOrder($sOrderId);
        $oOrderPayment = $this->_prepareOrderPayment($sOrderId);
        $OrderCalculatedStatus = 'completed';
        $oPayPalOrderPaymentStatusCalculator = $this->_preparePayPalOrderPaymentStatusCalculator($oOrderPayment, $oOrder, $OrderCalculatedStatus);

        // Mock order manager to check if order is created from given payment. This prevents from database usage.
        $oPayPalOrderManager = $this->getMock(\OxidEsales\PayPalModule\Model\OrderManager::class, array('_getOrderFromPayment'));
        $oPayPalOrderManager->expects($this->once())->method('_getOrderFromPayment')->with($oOrderPayment)->will($this->returnValue($oOrder));
        $oPayPalOrderManager->setOrderPayment($oOrderPayment);
        $oPayPalOrderManager->setOrderPaymentStatusCalculator($oPayPalOrderPaymentStatusCalculator);
        $blOrderUpdated = $oPayPalOrderManager->updateOrderStatus();

        $oPayPalOrderManager->getOrder();
        $sOrderNewStatus = $oOrder->getPaymentStatus();
        $this->assertEquals($OrderCalculatedStatus, $sOrderNewStatus, 'Order status did not change to calculator calculated.');
        $this->assertTrue($blOrderUpdated, 'Order should be updated, and return indicates this with true.');
    }

    /**
     * Check if correct Order Payment object passed to Calculator.
     * Check if correct Order Status is stored afterword.
     */
    public function testUpdateOrderStatus_noOrderPaymentNoOrder_orderStatusFromCalculator()
    {
        $oPayPalOrderManager = new \OxidEsales\PayPalModule\Model\OrderManager();
        $blOrderUpdated = $oPayPalOrderManager->updateOrderStatus();

        $this->assertFalse($blOrderUpdated, 'Order should be updated, and return indicates this with true.');
    }

    /**
     * Create order with status different than order status calculator returns.
     *
     * @param string $sOrderId order id.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected function _prepareOrder($sOrderId)
    {
        $oOrder = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $oOrder->setPaymentStatus('pending');
        $oOrder->setOrderId($sOrderId);

        return $oOrder;
    }

    /**
     * Create order payment with some transaction id and same order id as order in _prepareOrder().
     *
     * @param string $sOrderId order id.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function _prepareOrderPayment($sOrderId)
    {
        $oOrderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oOrderPayment->setTransactionId('_asdadsd45a4sd5a4sd54a5');
        $oOrderPayment->setOrderId($sOrderId);

        return $oOrderPayment;
    }

    /**
     * Mock order payment calculator.
     * Check if called with correct parameters.
     * Mock return calculated state. Order state should change according to this one.
     *
     * @param \OxidEsales\PayPalModule\Model\OrderPayment $oOrderPayment
     * @param OrderController                             $oOrder
     * @param string                                      $OrderCalculatedStatus
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator
     */
    protected function _preparePayPalOrderPaymentStatusCalculator($oOrderPayment, $oOrder, $OrderCalculatedStatus)
    {
        $oPayPalOrderPaymentStatusCalculator = $this->getMock(
            \OxidEsales\PayPalModule\Model\OrderPaymentStatusCalculator::class,
            array('setOrderPayment', 'setOrder', 'getStatus')
        );
        $oPayPalOrderPaymentStatusCalculator->expects($this->any())->method('setOrderPayment')->with($oOrderPayment);
        $oPayPalOrderPaymentStatusCalculator->expects($this->once())->method('setOrder')->with($oOrder);
        $oPayPalOrderPaymentStatusCalculator->expects($this->any())->method('getStatus')->will($this->returnValue($OrderCalculatedStatus));

        return $oPayPalOrderPaymentStatusCalculator;
    }
}
