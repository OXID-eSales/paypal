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
class Unit_oePayPal_models_oePayPalOrderPaymentTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     *  Setup: Prepare data - create need tables
     */
    public function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpaymentcomments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpayments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_order`');
    }

    /**
     * Test case for adding / getting PayPal Order Payment history item
     */
    public function testCreatePayPalOrderPayment()
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId('123');
        $orderPayment->setTransactionId('transactionId');
        $orderPayment->setCorrelationId('correlationId');
        $orderPayment->setAmount(50);
        $orderPayment->setRefundedAmount(12.13);
        $orderPayment->setAction('capture');
        $orderPayment->setDate('2012-04-13 15:16:32');
        $orderPayment->setStatus('status');
        $orderPayment->setCurrency('LTL');
        $orderPayment->save();

        $orderPaymentLoaded = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPaymentLoaded->load($orderPayment->getPaymentId());

        $this->assertEquals('123', $orderPaymentLoaded->getOrderId());
        $this->assertEquals('transactionId', $orderPaymentLoaded->getTransactionId());
        $this->assertEquals('correlationId', $orderPaymentLoaded->getCorrelationId());
        $this->assertEquals(50, $orderPaymentLoaded->getAmount());
        $this->assertEquals(12.13, $orderPaymentLoaded->getRefundedAmount());
        $this->assertEquals('capture', $orderPaymentLoaded->getAction());
        $this->assertEquals('2012-04-13 15:16:32', $orderPaymentLoaded->getDate());
        $this->assertEquals('status', $orderPaymentLoaded->getStatus());
        $this->assertEquals('LTL', $orderPaymentLoaded->getCurrency());
    }

    /**
     * Testing adding amount to PayPal refunded amount
     */
    public function testAddRefundedAmount_WhenEmpty()
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->addRefundedAmount(100);

        $this->assertEquals(100, $orderPayment->getRefundedAmount());
    }

    /**
     * Testing adding amount to PayPal refunded amount
     */
    public function testAddRefundedAmount_NotEmpty()
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setRefundedAmount(100);
        $orderPayment->addRefundedAmount(100);

        $this->assertEquals(200, $orderPayment->getRefundedAmount());
    }

    /**
     * Testing loading payment by transaction id
     */
    public function testLoadByTransactionId()
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId('orderId');
        $orderPayment->setTransactionId('transactionId');
        $orderPayment->save();

        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->loadByTransactionId('transactionId');

        $this->assertEquals('orderId', $orderPayment->getOrderId());
    }

    /**
     * Data provider for test testSetGetIsValid.
     */
    public function providerSetGetIsValid()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\OrderPayment::setIsValid
     * Test case for \OxidEsales\PayPalModule\Model\OrderPayment::getIsValid
     *
     * @param bool $isValid
     *
     * @dataProvider providerSetGetIsValid
     */
    public function testSetGetIsValid($isValid)
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setIsValid($isValid);
        $this->assertEquals($isValid, $orderPayment->getIsValid(), 'Should be same value from getter as set in setter.');
    }

    /**
     * Data provider for test testSetGetValidationMessage.
     */
    public function providerSetGetValidationMessage()
    {
        return array(
            array(''),
            array('zzzzz'),
            array('yyyyy'),
        );
    }

    /**
     * Test case add comment to payment
     */
    public function testAddComment_setParams_CheckInDb()
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId('123');
        $orderPayment->save();

        $this->assertEquals(0, count($orderPayment->getCommentList()), 'No comments - default value empty array.');

        $comment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $comment->setComment('comment');

        $orderPayment->addComment($comment);

        $this->assertEquals(1, count($orderPayment->getCommentList()));
    }

    /**
     * Test case add comment to payment
     */
    public function testAddComment_NoDateGiven()
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $orderPayment->setOrderId('123');
        $orderPayment->save();

        $this->assertEquals(0, count($orderPayment->getCommentList()), 'No comments - default value empty array.');

        $comment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $comment->setComment('comment');

        $orderPayment->addComment($comment);

        $this->assertEquals(1, count($orderPayment->getCommentList()));
    }

    /**
     * Test case add comment to payment
     */
    public function testGetCommentList_noComments_instanceOfCommentList()
    {
        $orderPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $this->assertTrue(
            $orderPayment->getCommentList() instanceof \OxidEsales\PayPalModule\Model\OrderPaymentCommentList,
            'No comments - default value empty array.');
    }
}