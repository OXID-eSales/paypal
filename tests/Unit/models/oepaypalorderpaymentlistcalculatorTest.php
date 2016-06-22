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
 * @copyright (C) OXID eSales AG 2003-2014
 */


/**
 * Testing ePayPalOrderPaymentListCalculator class.
 */
class Unit_oePayPal_models_oePayPalOrderPaymentListCalculatorTest extends OxidTestCase
{
    /**
     *  Setup: Prepare data - create need tables
     */
    protected function setUp()
    {
        parent::setUp();

        oxDb::getDb()->execute('TRUNCATE `oepaypal_orderpayments`');
        oxDb::getDb()->execute('TRUNCATE `oepaypal_order`');
    }

    /**
     * Test case that no payment list is set.
     *
     * @return null
     */
    public function testCalculateNoPaymentList()
    {
        $oListCalculator = oxNew('oePayPalOrderPaymentListCalculator');
        $oListCalculator->calculate();

        $this->assertEquals('0.0', $oListCalculator->getCapturedAmount());
        $this->assertEquals('0.0', $oListCalculator->getVoidedAmount());
        $this->assertEquals('0.0', $oListCalculator->getRefundedAmount());
    }

    /**
     * Test case that a payment list is set.
     *
     * @return null
     */
    public function testCapturedAmountCalculateWithPaymentList()
    {
        $oOrderPaymentList = $this->createOrderPaymentList();

        $oListCalculator = oxNew('oePayPalOrderPaymentListCalculator');
        $oListCalculator->setPaymentList($oOrderPaymentList);
        $oListCalculator->calculate();

        $this->assertEquals('11.22', $oListCalculator->getCapturedAmount());
    }

    /**
     * Test case that a payment list is set.
     * Void data is taken from voided Authorization.
     *
     * @return null
     */
    public function testVoidedAmountCalculateWithPaymentList()
    {
        $oOrderPaymentList = $this->createOrderPaymentList();

        $oListCalculator = oxNew('oePayPalOrderPaymentListCalculator');
        $oListCalculator->setPaymentList($oOrderPaymentList);
        $oListCalculator->calculate();

        $this->assertEquals('44.33', $oListCalculator->getVoidedAmount());
    }

    /**
     * Test case that a payment list is set.
     *
     * @return null
     */
    public function testRefundedAmountCalculateWithPaymentList()
    {
        $oOrderPaymentList = $this->createOrderPaymentList();

        $oListCalculator = oxNew('oePayPalOrderPaymentListCalculator');
        $oListCalculator->setPaymentList($oOrderPaymentList);
        $oListCalculator->calculate();

        $this->assertEquals('6.78', $oListCalculator->getRefundedAmount());
    }

    /**
     * Test case that a payment list is set.
     * Voided amount comes from void action.
     *
     * @return null
     */
    public function testCapturedAmountCalculateWithPaymentListAndVoidAction()
    {
        $oOrderPaymentList = $this->createOrderPaymentListContainingVoidAction();

        $oListCalculator = oxNew('oePayPalOrderPaymentListCalculator');
        $oListCalculator->setPaymentList($oOrderPaymentList);
        $oListCalculator->calculate();

        $this->assertEquals('30.00', $oListCalculator->getCapturedAmount());
    }

    /**
     * Test case that a payment list is set.
     * Voided amount comes from void action.
     *
     * @return null
     */
    public function testVoidedAmountCalculateWithPaymentListAndVoidAction()
    {
        $oOrderPaymentList = $this->createOrderPaymentListContainingVoidAction();

        $oListCalculator = oxNew('oePayPalOrderPaymentListCalculator');
        $oListCalculator->setPaymentList($oOrderPaymentList);
        $oListCalculator->calculate();

        $this->assertEquals('11.00', $oListCalculator->getVoidedAmount());
    }

    /**
     * Test case that a payment list is set.
     * Voided amount comes from void action.
     *
     * @return null
     */
    public function testRefundedAmountCalculateWithPaymentListAndVoidAction()
    {
        $oOrderPaymentList = $this->createOrderPaymentListContainingVoidAction();

        $oListCalculator = oxNew('oePayPalOrderPaymentListCalculator');
        $oListCalculator->setPaymentList($oOrderPaymentList);
        $oListCalculator->calculate();

        $this->assertEquals('10.00', $oListCalculator->getRefundedAmount());
    }

    /**
     * Test helper, prepares some paypal order payments.
     *
     * @return oePayPalOrderPaymentList
     */
    private function createOrderPaymentList()
    {
        $orderId = '123';

        $oOrderPayment = oxNew('oePayPalOrderPayment');
        $oOrderPayment->setPaymentId(1);
        $oOrderPayment->setOrderId($orderId);
        $oOrderPayment->setAmount(55.55);
        $oOrderPayment->setAction('authorization');
        $oOrderPayment->setStatus('Voided');
        $oOrderPayment->setDate("2012-04-13 12:13:15");
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId(2);
        $oOrderPayment->setAction('capture');
        $oOrderPayment->setAmount(11.11);
        $oOrderPayment->setRefundedAmount(1.23);
        $oOrderPayment->setStatus('Completed');
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId(3);
        $oOrderPayment->setAction('capture');
        $oOrderPayment->setAmount(0.11);
        $oOrderPayment->setStatus('Completed');
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId(4);
        $oOrderPayment->setAction('capture');
        $oOrderPayment->setAmount(22.22);
        $oOrderPayment->setStatus('Pending');
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId(5);
        $oOrderPayment->setAction('refund');
        $oOrderPayment->setAmount(5.55);
        $oOrderPayment->setStatus('Refunded');
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId(6);
        $oOrderPayment->setAction('refund');
        $oOrderPayment->setAmount(15.55);
        $oOrderPayment->setStatus('Instant');
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId(7);
        $oOrderPayment->setAction('void');
        $oOrderPayment->setAmount(15.55);
        $oOrderPayment->setStatus('Instant');
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId(8);
        $oOrderPayment->setAction('refund');
        $oOrderPayment->setAmount(1.23);
        $oOrderPayment->setStatus('Refunded');
        $oOrderPayment->save();

        $oOrderPaymentList = oxNew('oePayPalOrderPaymentList');
        $oOrderPaymentList->load($orderId);

        return $oOrderPaymentList;
    }

    /**
     * Test helper, prepares some paypal order payments.
     * Voided amount comes from void action.
     *
     * @return oePayPalOrderPaymentList
     */
    private function createOrderPaymentListContainingVoidAction()
    {
        $orderId = '123';

        $oOrderPayment = oxNew('oePayPalOrderPayment');
        $oOrderPayment->setPaymentId(1);
        $oOrderPayment->setOrderId($orderId);
        $oOrderPayment->setAmount(50.00);
        $oOrderPayment->setAction('authorization');
        $oOrderPayment->setStatus('Voided');
        $oOrderPayment->setDate("2012-04-13 12:13:15");
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId(2);
        $oOrderPayment->setAction('capture');
        $oOrderPayment->setAmount(30.00);
        $oOrderPayment->setRefundedAmount(20.00);
        $oOrderPayment->setStatus('Completed');
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId(3);
        $oOrderPayment->setAction('refund');
        $oOrderPayment->setAmount(10.00);
        $oOrderPayment->setStatus('Refunded');
        $oOrderPayment->save();

        $oOrderPayment->setPaymentId(4);
        $oOrderPayment->setAction('void');
        $oOrderPayment->setAmount(11.00);
        $oOrderPayment->setStatus('Voided');
        $oOrderPayment->save();

        $oOrderPaymentList = oxNew('oePayPalOrderPaymentList');
        $oOrderPaymentList->load($orderId);

        return $oOrderPaymentList;
    }
}
