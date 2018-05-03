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



/**
 * Testing oxAccessRightException class.
 */
class PayPalOrderTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     *  Setup: Prepare data - create need tables
     */
    public function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpayments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_order`');

        parent::setUp();
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\PayPalOrder::getOrderId()
     * Tests adding / getting PayPal Order Payment history item
     */
    public function testSetGet()
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('123');
        $this->assertEquals('123', $order->getOrderId());
    }


    /**
     * Test case for \OxidEsales\PayPalModule\Model\PayPalOrder::save()
     * Tests adding / getting PayPal Order Payment history item
     */
    public function testSavePayPalPayPalOrder_insert()
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('123');
        $order->setPaymentStatus('pending');
        $order->setCapturedAmount(24.13);
        $order->setRefundedAmount(12.13);
        $order->setVoidedAmount(15.13);
        $order->setTotalOrderSum(299.99);
        $order->setCurrency('LTU');
        $order->setTransactionMode('Sale');

        $order->save();

        $orderLoaded = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $orderLoaded->load($order->getOrderId());

        $this->assertEquals('123', $orderLoaded->getOrderId());
        $this->assertEquals('pending', $orderLoaded->getPaymentStatus());
        $this->assertEquals(24.13, $orderLoaded->getCapturedAmount());
        $this->assertEquals(12.13, $orderLoaded->getRefundedAmount());
        $this->assertEquals(15.13, $orderLoaded->getVoidedAmount());
        $this->assertEquals(299.99, $orderLoaded->getTotalOrderSum());
        $this->assertEquals('LTU', $orderLoaded->getCurrency());
        $this->assertEquals('Sale', $orderLoaded->getTransactionMode());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\PayPalOrder::save()
     * Tests adding / getting PayPal Order Payment history item
     */
    public function testSavePayPalPayPalOrder_update()
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId('123');
        $order->setPaymentStatus('pending');
        $order->setCapturedAmount(24.13);
        $order->setRefundedAmount(12.13);
        $order->setVoidedAmount(15.13);
        $order->setTotalOrderSum(299.99);
        $order->setCurrency('LTU');
        $order->setTransactionMode('Sale');
        $order->save();

        $orderLoaded = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $orderLoaded->load('123');
        $orderLoaded->setPaymentStatus('completed');
        $orderLoaded->save();

        $orderLoaded = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $orderLoaded->load('123');
        $this->assertEquals('completed', $orderLoaded->getPaymentStatus());
        $this->assertEquals(24.13, $orderLoaded->getCapturedAmount());
        $this->assertEquals(12.13, $orderLoaded->getRefundedAmount());
        $this->assertEquals(15.13, $orderLoaded->getVoidedAmount());
        $this->assertEquals(299.99, $orderLoaded->getTotalOrderSum());
        $this->assertEquals('LTU', $orderLoaded->getCurrency());
        $this->assertEquals('Sale', $orderLoaded->getTransactionMode());
    }


    /**
     * Test case for \OxidEsales\PayPalModule\Model\PayPalOrder::addRefundedAmount()
     * Tests adding amount to PayPal refunded amount
     */
    public function testAddRefundedAmountWhenEmpty()
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->addRefundedAmount(100.29);
        $order->addRefundedAmount(899.70);

        $this->assertEquals(999.99, $order->getRefundedAmount());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\PayPalOrder::addCapturedAmount()
     * Tests adding amount to PayPal refunded amount
     */
    public function testAddCapturedAmountWhenEmpty()
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->addCapturedAmount(100.29);
        $order->addCapturedAmount(899.70);

        $this->assertEquals(999.99, $order->getCapturedAmount());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\PayPalOrder::getPaymentStatus()
     * Tests PayPal payment status getter
     */
    public function testGetPayPalPaymentStatusWhenStatusEmpty()
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $this->assertEquals("completed", $order->getPaymentStatus());
    }
}