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
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId('123');
        $oOrderPayment->setTransactionId('transactionId');
        $oOrderPayment->setCorrelationId('correlationId');
        $oOrderPayment->setAmount(50);
        $oOrderPayment->setRefundedAmount(12.13);
        $oOrderPayment->setAction('capture');
        $oOrderPayment->setDate('2012-04-13 15:16:32');
        $oOrderPayment->setStatus('status');
        $oOrderPayment->setCurrency('LTL');
        $oOrderPayment->save();

        $oOrderPaymentLoaded = new oePayPalOrderPayment();
        $oOrderPaymentLoaded->load($oOrderPayment->getPaymentId());

        $this->assertEquals('123', $oOrderPaymentLoaded->getOrderId());
        $this->assertEquals('transactionId', $oOrderPaymentLoaded->getTransactionId());
        $this->assertEquals('correlationId', $oOrderPaymentLoaded->getCorrelationId());
        $this->assertEquals(50, $oOrderPaymentLoaded->getAmount());
        $this->assertEquals(12.13, $oOrderPaymentLoaded->getRefundedAmount());
        $this->assertEquals('capture', $oOrderPaymentLoaded->getAction());
        $this->assertEquals('2012-04-13 15:16:32', $oOrderPaymentLoaded->getDate());
        $this->assertEquals('status', $oOrderPaymentLoaded->getStatus());
        $this->assertEquals('LTL', $oOrderPaymentLoaded->getCurrency());
    }

    /**
     * Testing adding amount to PayPal refunded amount
     */
    public function testAddRefundedAmount_WhenEmpty()
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->addRefundedAmount(100);

        $this->assertEquals(100, $oOrderPayment->getRefundedAmount());
    }

    /**
     * Testing adding amount to PayPal refunded amount
     */
    public function testAddRefundedAmount_NotEmpty()
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setRefundedAmount(100);
        $oOrderPayment->addRefundedAmount(100);

        $this->assertEquals(200, $oOrderPayment->getRefundedAmount());
    }

    /**
     * Testing loading payment by transaction id
     */
    public function testLoadByTransactionId()
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId('orderId');
        $oOrderPayment->setTransactionId('transactionId');
        $oOrderPayment->save();

        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->loadByTransactionId('transactionId');

        $this->assertEquals('orderId', $oOrderPayment->getOrderId());
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
     * Test case for oePayPalOrderPayment::setIsValid
     * Test case for oePayPalOrderPayment::getIsValid
     *
     * @param bool $blIsValid
     *
     * @dataProvider providerSetGetIsValid
     */
    public function testSetGetIsValid($blIsValid)
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setIsValid($blIsValid);
        $this->assertEquals($blIsValid, $oOrderPayment->getIsValid(), 'Should be same value from getter as set in setter.');
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
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId('123');
        $oOrderPayment->save();

        $this->assertEquals(0, count($oOrderPayment->getCommentList()), 'No comments - default value empty array.');

        $oComment = new oePayPalOrderPaymentComment();
        $oComment->setComment('comment');

        $oOrderPayment->addComment($oComment);

        $this->assertEquals(1, count($oOrderPayment->getCommentList()));
    }

    /**
     * Test case add comment to payment
     */
    public function testAddComment_NoDateGiven()
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $oOrderPayment->setOrderId('123');
        $oOrderPayment->save();

        $this->assertEquals(0, count($oOrderPayment->getCommentList()), 'No comments - default value empty array.');

        $oComment = new oePayPalOrderPaymentComment();
        $oComment->setComment('comment');

        $oOrderPayment->addComment($oComment);

        $this->assertEquals(1, count($oOrderPayment->getCommentList()));
    }

    /**
     * Test case add comment to payment
     */
    public function testGetCommentList_noComments_instanceOfCommentList()
    {
        $oOrderPayment = new oePayPalOrderPayment();
        $this->assertTrue($oOrderPayment->getCommentList() instanceof oePayPalOrderPaymentCommentList, 'No comments - default value empty array.');
    }
}