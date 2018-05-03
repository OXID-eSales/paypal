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

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

/**
 * Testing oxAccessRightException class.
 */
class OrderPaymentListTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     *  Setup: Prepare data - create need tables
     */
    protected function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpayments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_order`');
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\OrderPayment::oePayPalOrderPaymentList()
     * Gets PayPal Order Payment history list
     */
    public function testLoadOrderPayments()
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setPaymentId(1);
        $orderPayment->setOrderId("123");
        $orderPayment->setAmount(50);
        $orderPayment->setAction("OEPAYPAL_STATUS_COMPLETED");
        $orderPayment->setDate("2012-04-13 12:13:15");
        $orderPayment->save();

        $orderPayment->setPaymentId(2);
        $orderPayment->setDate("2012-02-01");
        $orderPayment->save();

        $orderPayment->setPaymentId(3);
        $orderPayment->setDate("2012-01-15");
        $orderPayment->save();

        $orderPaymentList = new \OxidEsales\PayPalModule\Model\OrderPaymentList();
        $orderPaymentList->load("123");

        $this->assertEquals(3, count($orderPaymentList));

        $i = 1;
        foreach ($orderPaymentList as $orderPayment) {
            $this->assertEquals($i++, $orderPayment->getPaymentId());
        }
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\OrderPayment::hasFailedPayment()
     * Checks if list has failed payments
     */
    public function testHasFailedPayment()
    {
        $orderPaymentList = new \OxidEsales\PayPalModule\Model\OrderPaymentList();

        $orderPaymentList->load("order");
        $this->assertFalse($orderPaymentList->hasFailedPayment());

        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId("order");
        $orderPayment->setStatus("Completed");
        $orderPayment->save();

        $orderPaymentList->load("order");
        $this->assertFalse($orderPaymentList->hasFailedPayment());

        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId("order");
        $orderPayment->setStatus("Failed");
        $orderPayment->save();

        $orderPaymentList->load("order");
        $this->assertTrue($orderPaymentList->hasFailedPayment());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\OrderPayment::hasPendingPayment()
     * Checks if list has pending payments
     */
    public function testHasPendingPayment()
    {
        $orderPaymentList = new \OxidEsales\PayPalModule\Model\OrderPaymentList();

        $orderPaymentList->load("order");
        $this->assertFalse($orderPaymentList->hasPendingPayment());

        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId("order");
        $orderPayment->setStatus("Completed");
        $orderPayment->save();

        $orderPaymentList->load("order");
        $this->assertFalse($orderPaymentList->hasPendingPayment());

        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId("order");
        $orderPayment->setStatus("Pending");
        $orderPayment->save();

        $orderPaymentList->load("order");
        $this->assertTrue($orderPaymentList->hasPendingPayment());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\OrderPayment::hasPendingPayment()
     * Checks if list has pending payments
     */
    public function testAddPayment()
    {
        $orderPaymentList = new \OxidEsales\PayPalModule\Model\OrderPaymentList();
        $orderPaymentList->load("order");

        $this->assertEquals(0, count($orderPaymentList));

        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId("order");
        $orderPayment->save();

        $orderPaymentList = new \OxidEsales\PayPalModule\Model\OrderPaymentList();
        $orderPaymentList->load("order");

        $this->assertEquals(1, count($orderPaymentList));

        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setDate('2013-01-12');
        $orderPayment->setAction('Pending');

        $orderPaymentList->addPayment($orderPayment);

        $orderPaymentList = new \OxidEsales\PayPalModule\Model\OrderPaymentList();
        $orderPaymentList->load("order");

        $this->assertEquals(2, count($orderPaymentList));
    }
}