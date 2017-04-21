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
        $oOrderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oOrderPayment->setPaymentId(1);
        $oOrderPayment->setOrderId("123");
        $oOrderPayment->setAmount(50);
        $oOrderPayment->setAction("OEPAYPAL_STATUS_COMPLETED");
        $oOrderPayment->setDate("2012-04-13 12:13:15");
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId(2);
        $oOrderPayment->setDate("2012-02-01");
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId(3);
        $oOrderPayment->setDate("2012-01-15");
        $oOrderPayment->save();

        $oOrderPaymentList = new \OxidEsales\PayPalModule\Model\OrderPaymentList();
        $oOrderPaymentList->load("123");

        $this->assertEquals(3, count($oOrderPaymentList));

        $i = 1;
        foreach ($oOrderPaymentList as $oOrderPayment) {
            $this->assertEquals($i++, $oOrderPayment->getPaymentId());
        }
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\OrderPayment::hasFailedPayment()
     * Checks if list has failed payments
     */
    public function testHasFailedPayment()
    {
        $oOrderPaymentList = new \OxidEsales\PayPalModule\Model\OrderPaymentList();

        $oOrderPaymentList->load("order");
        $this->assertFalse($oOrderPaymentList->hasFailedPayment());

        $oOrderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oOrderPayment->setOrderId("order");
        $oOrderPayment->setStatus("Completed");
        $oOrderPayment->save();

        $oOrderPaymentList->load("order");
        $this->assertFalse($oOrderPaymentList->hasFailedPayment());

        $oOrderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oOrderPayment->setOrderId("order");
        $oOrderPayment->setStatus("Failed");
        $oOrderPayment->save();

        $oOrderPaymentList->load("order");
        $this->assertTrue($oOrderPaymentList->hasFailedPayment());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\OrderPayment::hasPendingPayment()
     * Checks if list has pending payments
     */
    public function testHasPendingPayment()
    {
        $oOrderPaymentList = new \OxidEsales\PayPalModule\Model\OrderPaymentList();

        $oOrderPaymentList->load("order");
        $this->assertFalse($oOrderPaymentList->hasPendingPayment());

        $oOrderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oOrderPayment->setOrderId("order");
        $oOrderPayment->setStatus("Completed");
        $oOrderPayment->save();

        $oOrderPaymentList->load("order");
        $this->assertFalse($oOrderPaymentList->hasPendingPayment());

        $oOrderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oOrderPayment->setOrderId("order");
        $oOrderPayment->setStatus("Pending");
        $oOrderPayment->save();

        $oOrderPaymentList->load("order");
        $this->assertTrue($oOrderPaymentList->hasPendingPayment());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\OrderPayment::hasPendingPayment()
     * Checks if list has pending payments
     */
    public function testAddPayment()
    {
        $oOrderPaymentList = new \OxidEsales\PayPalModule\Model\OrderPaymentList();
        $oOrderPaymentList->load("order");

        $this->assertEquals(0, count($oOrderPaymentList));

        $oOrderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oOrderPayment->setOrderId("order");
        $oOrderPayment->save();

        $oOrderPaymentList = new \OxidEsales\PayPalModule\Model\OrderPaymentList();
        $oOrderPaymentList->load("order");

        $this->assertEquals(1, count($oOrderPaymentList));

        $oOrderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oOrderPayment->setDate('2013-01-12');
        $oOrderPayment->setAction('Pending');

        $oOrderPaymentList->addPayment($oOrderPayment);

        $oOrderPaymentList = new \OxidEsales\PayPalModule\Model\OrderPaymentList();
        $oOrderPaymentList->load("order");

        $this->assertEquals(2, count($oOrderPaymentList));
    }
}