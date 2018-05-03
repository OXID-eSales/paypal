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
 * Testing ePayPalOrderPaymentListCalculator class.
 */
class OrderPaymentListCalculatorTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     *  Setup: Prepare data - create need tables
     */
    protected function setUp()
    {
        parent::setUp();

        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpayments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_order`');
    }

    /**
     * Test case that no payment list is set.
     */
    public function testCalculateNoPaymentList()
    {
        $listCalculator = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentListCalculator::class);
        $listCalculator->calculate();

        $this->assertEquals('0.0', $listCalculator->getCapturedAmount());
        $this->assertEquals('0.0', $listCalculator->getVoidedAmount());
        $this->assertEquals('0.0', $listCalculator->getRefundedAmount());
    }

    /**
     * Test case that a payment list is set.
     */
    public function testCapturedAmountCalculateWithPaymentList()
    {
        $orderPaymentList = $this->createOrderPaymentList();

        /** @var \OxidEsales\PayPalModule\Model\OrderPaymentListCalculator $listCalculator */
        $listCalculator = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentListCalculator::class);
        $listCalculator->setPaymentList($orderPaymentList);
        $listCalculator->calculate();

        $this->assertEquals('11.22', $listCalculator->getCapturedAmount());
    }

    /**
     * Test case that a payment list is set.
     * Void data is taken from voided Authorization.
     */
    public function testVoidedAmountCalculateWithPaymentList()
    {
        $orderPaymentList = $this->createOrderPaymentList();

        $listCalculator = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentListCalculator::class);
        $listCalculator->setPaymentList($orderPaymentList);
        $listCalculator->calculate();

        $this->assertEquals('44.33', $listCalculator->getVoidedAmount());
    }

    /**
     * Test case that a payment list is set.
     */
    public function testRefundedAmountCalculateWithPaymentList()
    {
        $orderPaymentList = $this->createOrderPaymentList();

        $listCalculator = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentListCalculator::class);
        $listCalculator->setPaymentList($orderPaymentList);
        $listCalculator->calculate();

        $this->assertEquals('6.78', $listCalculator->getRefundedAmount());
    }

    /**
     * Test case that a payment list is set.
     * Voided amount comes from void action.
     */
    public function testCapturedAmountCalculateWithPaymentListAndVoidAction()
    {
        $orderPaymentList = $this->createOrderPaymentListContainingVoidAction();

        $listCalculator = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentListCalculator::class);
        $listCalculator->setPaymentList($orderPaymentList);
        $listCalculator->calculate();

        $this->assertEquals('30.00', $listCalculator->getCapturedAmount());
    }

    /**
     * Test case that a payment list is set.
     * Voided amount comes from void action.
     */
    public function testVoidedAmountCalculateWithPaymentListAndVoidAction()
    {
        $orderPaymentList = $this->createOrderPaymentListContainingVoidAction();

        $listCalculator = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentListCalculator::class);
        $listCalculator->setPaymentList($orderPaymentList);
        $listCalculator->calculate();

        $this->assertEquals('11.00', $listCalculator->getVoidedAmount());
    }

    /**
     * Test case that a payment list is set.
     * Voided amount comes from void action.
     */
    public function testRefundedAmountCalculateWithPaymentListAndVoidAction()
    {
        $orderPaymentList = $this->createOrderPaymentListContainingVoidAction();

        $listCalculator = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentListCalculator::class);
        $listCalculator->setPaymentList($orderPaymentList);
        $listCalculator->calculate();

        $this->assertEquals('10.00', $listCalculator->getRefundedAmount());
    }

    /**
     * Test helper, prepares some paypal order payments.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentList
     */
    private function createOrderPaymentList()
    {
        $orderId = '123';

        $orderPayment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $orderPayment->setPaymentId(1);
        $orderPayment->setOrderId($orderId);
        $orderPayment->setAmount(55.55);
        $orderPayment->setAction('authorization');
        $orderPayment->setStatus('Voided');
        $orderPayment->setDate("2012-04-13 12:13:15");
        $orderPayment->save();

        $orderPayment->setPaymentId(2);
        $orderPayment->setAction('capture');
        $orderPayment->setAmount(11.11);
        $orderPayment->setRefundedAmount(1.23);
        $orderPayment->setStatus('Completed');
        $orderPayment->save();

        $orderPayment->setPaymentId(3);
        $orderPayment->setAction('capture');
        $orderPayment->setAmount(0.11);
        $orderPayment->setStatus('Completed');
        $orderPayment->save();

        $orderPayment->setPaymentId(4);
        $orderPayment->setAction('capture');
        $orderPayment->setAmount(22.22);
        $orderPayment->setStatus('Pending');
        $orderPayment->save();

        $orderPayment->setPaymentId(5);
        $orderPayment->setAction('refund');
        $orderPayment->setAmount(5.55);
        $orderPayment->setStatus('Refunded');
        $orderPayment->save();

        $orderPayment->setPaymentId(6);
        $orderPayment->setAction('refund');
        $orderPayment->setAmount(15.55);
        $orderPayment->setStatus('Instant');
        $orderPayment->save();

        $orderPayment->setPaymentId(7);
        $orderPayment->setAction('void');
        $orderPayment->setAmount(15.55);
        $orderPayment->setStatus('Instant');
        $orderPayment->save();

        $orderPayment->setPaymentId(8);
        $orderPayment->setAction('refund');
        $orderPayment->setAmount(1.23);
        $orderPayment->setStatus('Refunded');
        $orderPayment->save();

        $orderPaymentList = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentList::class);
        $orderPaymentList->load($orderId);

        return $orderPaymentList;
    }

    /**
     * Test helper, prepares some paypal order payments.
     * Voided amount comes from void action.
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentList
     */
    private function createOrderPaymentListContainingVoidAction()
    {
        $orderId = '123';

        $orderPayment = oxNew(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $orderPayment->setPaymentId(1);
        $orderPayment->setOrderId($orderId);
        $orderPayment->setAmount(50.00);
        $orderPayment->setAction('authorization');
        $orderPayment->setStatus('Voided');
        $orderPayment->setDate("2012-04-13 12:13:15");
        $orderPayment->save();

        $orderPayment->setPaymentId(2);
        $orderPayment->setAction('capture');
        $orderPayment->setAmount(30.00);
        $orderPayment->setRefundedAmount(20.00);
        $orderPayment->setStatus('Completed');
        $orderPayment->save();

        $orderPayment->setPaymentId(3);
        $orderPayment->setAction('refund');
        $orderPayment->setAmount(10.00);
        $orderPayment->setStatus('Refunded');
        $orderPayment->save();

        $orderPayment->setPaymentId(4);
        $orderPayment->setAction('void');
        $orderPayment->setAmount(11.00);
        $orderPayment->setStatus('Voided');
        $orderPayment->save();

        $orderPaymentList = oxNew(\OxidEsales\PayPalModule\Model\OrderPaymentList::class);
        $orderPaymentList->load($orderId);

        return $orderPaymentList;
    }
}
