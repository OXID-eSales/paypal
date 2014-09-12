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
 * @copyright (C) OXID eSales AG 2003-2013
 */

require_once realpath('.') . '/unit/OxidTestCase.php';
require_once realpath('.') . '/unit/test_config.inc.php';


/**
 * Testing oxAccessRightException class.
 */
class Unit_oePayPal_models_oePayPalPayPalOrderTest extends OxidTestCase
{

    /**
     *  Setup: Prepare data - create need tables
     */
    public function setUp()
    {
        oxDb::getDb()->execute('TRUNCATE `oepaypal_orderpayments`');
        oxDb::getDb()->execute('TRUNCATE `oepaypal_order`');
    }

    /**
     * Test case for oePayPalPayPalOrder::getOrderId()
     * Tests adding / getting PayPal Order Payment history item
     *
     * @return null
     */
    public function testSetGet()
    {
        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('123');
        $this->assertEquals('123', $oOrder->getOrderId());
    }


    /**
     * Test case for oePayPalPayPalOrder::save()
     * Tests adding / getting PayPal Order Payment history item
     *
     * @return null
     */
    public function testSavePayPalPayPalOrder_insert()
    {
        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('123');
        $oOrder->setPaymentStatus('pending');
        $oOrder->setCapturedAmount(24.13);
        $oOrder->setRefundedAmount(12.13);
        $oOrder->setVoidedAmount(15.13);
        $oOrder->setTotalOrderSum(299.99);
        $oOrder->setCurrency('LTU');
        $oOrder->setTransactionMode('Sale');

        $oOrder->save();

        $oOrderLoaded = new oePayPalPayPalOrder();
        $oOrderLoaded->load($oOrder->getOrderId());

        $this->assertEquals('123', $oOrderLoaded->getOrderId());
        $this->assertEquals('pending', $oOrderLoaded->getPaymentStatus());
        $this->assertEquals(24.13, $oOrderLoaded->getCapturedAmount());
        $this->assertEquals(12.13, $oOrderLoaded->getRefundedAmount());
        $this->assertEquals(15.13, $oOrderLoaded->getVoidedAmount());
        $this->assertEquals(299.99, $oOrderLoaded->getTotalOrderSum());
        $this->assertEquals('LTU', $oOrderLoaded->getCurrency());
        $this->assertEquals('Sale', $oOrderLoaded->getTransactionMode());
    }

    /**
     * Test case for oePayPalPayPalOrder::save()
     * Tests adding / getting PayPal Order Payment history item
     *
     * @return null
     */
    public function testSavePayPalPayPalOrder_update()
    {
        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId('123');
        $oOrder->setPaymentStatus('pending');
        $oOrder->setCapturedAmount(24.13);
        $oOrder->setRefundedAmount(12.13);
        $oOrder->setVoidedAmount(15.13);
        $oOrder->setTotalOrderSum(299.99);
        $oOrder->setCurrency('LTU');
        $oOrder->setTransactionMode('Sale');
        $oOrder->save();

        $oOrderLoaded = new oePayPalPayPalOrder();
        $oOrderLoaded->load('123');
        $oOrderLoaded->setPaymentStatus('completed');
        $oOrderLoaded->save();

        $oOrderLoaded = new oePayPalPayPalOrder();
        $oOrderLoaded->load('123');
        $this->assertEquals('completed', $oOrderLoaded->getPaymentStatus());
        $this->assertEquals(24.13, $oOrderLoaded->getCapturedAmount());
        $this->assertEquals(12.13, $oOrderLoaded->getRefundedAmount());
        $this->assertEquals(15.13, $oOrderLoaded->getVoidedAmount());
        $this->assertEquals(299.99, $oOrderLoaded->getTotalOrderSum());
        $this->assertEquals('LTU', $oOrderLoaded->getCurrency());
        $this->assertEquals('Sale', $oOrderLoaded->getTransactionMode());
    }


    /**
     * Test case for oePayPalPayPalOrder::addRefundedAmount()
     * Tests adding amount to PayPal refunded amount
     *
     * @return null
     */
    public function testAddRefundedAmountWhenEmpty()
    {
        $oOrder = new oePayPalPayPalOrder();
        $oOrder->addRefundedAmount(100.29);
        $oOrder->addRefundedAmount(899.70);

        $this->assertEquals(999.99, $oOrder->getRefundedAmount());
    }

    /**
     * Test case for oePayPalPayPalOrder::addCapturedAmount()
     * Tests adding amount to PayPal refunded amount
     *
     * @return null
     */
    public function testAddCapturedAmountWhenEmpty()
    {
        $oOrder = new oePayPalPayPalOrder();
        $oOrder->addCapturedAmount(100.29);
        $oOrder->addCapturedAmount(899.70);

        $this->assertEquals(999.99, $oOrder->getCapturedAmount());
    }

    /**
     * Test case for oePayPalPayPalOrder::getPaymentStatus()
     * Tests PayPal payment status getter
     *
     * @return null
     */
    public function testGetPayPalPaymentStatusWhenStatusEmpty()
    {
        $oOrder = new oePayPalPayPalOrder();
        $this->assertEquals("completed", $oOrder->getPaymentStatus());
    }
}