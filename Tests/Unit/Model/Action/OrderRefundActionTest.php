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
 * Testing \OxidEsales\PayPalModule\Model\Action\OrderRefundAction class.
 */
class OrderRefundActionTest extends \OxidEsales\TestingLibrary\UnitTestCase
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
     * Testing addition of refunded amount to order
     */
    public function testProcess_AddingRefundedAmountToOrder()
    {
        $amount = 59.67;

        $payPalResponse = $this->getPayPalResponse(array('getRefundAmount'));
        $payPalResponse->expects($this->any())
            ->method('getRefundAmount')
            ->will($this->returnValue($amount));

        $order = $this->getOrder(array('addRefundedAmount'));
        $order->expects($this->once())
            ->method('addRefundedAmount')
            ->with($this->equalTo($amount))
            ->will($this->returnValue(null));

        $action = $this->getAction($payPalResponse, $order);

        $action->process();
    }

    /**
     * Testing new payment creation with correct data after PayPal request is processed
     */
    public function testProcess_NewPaymentCreated_WithCorrectData()
    {
        $transactionId = 'transactionId';
        $correlationId = 'correlationId';
        $status = 'Completed';
        $amount = 59.67;
        $currency = 'EUR';
        $date = 'date';

        $payPalResponseMethods = array(
            'getTransactionId' => $transactionId,
            'getCorrelationId' => $correlationId,
            'getPaymentStatus' => $status,
            'getRefundAmount'  => $amount,
            'getCurrency'      => $currency,
        );
        $payPalResponse = $this->_createStub(\OxidEsales\PayPalModule\Model\Response\ResponseDoRefund::class, $payPalResponseMethods);

        $payment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $payment->setDate($date);
        $payment->setTransactionId($transactionId);
        $payment->setCorrelationId($correlationId);
        $payment->setAction('refund');
        $payment->setStatus($status);
        $payment->setAmount($amount);
        $payment->setCurrency($currency);

        $paymentList = $this->getPaymentList(array('addPayment'));
        $paymentList->expects($this->once())
            ->method('addPayment')
            ->with($payment)
            ->will($this->returnValue($this->getPayment()));

        $order = $this->getOrder(array('getPaymentList'));
        $order->expects($this->once())
            ->method('getPaymentList')
            ->will($this->returnValue($paymentList));

        $action = $this->getAction($payPalResponse, $order);

        $action->process();
    }

    /**
     * Testing saving of order after updating it
     */
    public function testProcess_ProcessingOfServiceResponse_OrderSaved()
    {
        $payPalResponse = $this->getPayPalResponse();

        $order = $this->getOrder(array('save'));
        $order->expects($this->atLeastOnce())
            ->method('save')
            ->will($this->returnValue(null));

        $action = $this->getAction($payPalResponse, $order);

        $action->process();
    }

    /**
     * Testing addition of comment after PayPal request processing
     */
    public function testProcess_ProcessingOfServiceResponse_CommentAdded()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\UtilsDate::class);
        $mockBuilder->setMethods(['getTime']);
        $utilsDate = $mockBuilder->getMock();
        $utilsDate->expects($this->any())->method('getTime')->will($this->returnValue(1410431540));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\UtilsDate::class, $utilsDate);
        $comment = 'testComment';

        $comment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $comment->setComment($comment);

        $payPalResponse = $this->getPayPalResponse();

        $payment = $this->getPayment();
        $payment->expects($this->once())
            ->method('addComment')
            ->with($this->equalTo($comment));

        $paymentList = $this->getPaymentList(array('addPayment'));
        $paymentList->expects($this->any())
            ->method('addPayment')
            ->will($this->returnValue($payment));

        $order = $this->getOrder(array('getPaymentList'));
        $order->expects($this->any())
            ->method('getPaymentList')
            ->will($this->returnValue($paymentList));

        $data = $this->getData();
        $data->expects($this->any())->method('getComment')->will($this->returnValue($comment));
        $action = $this->getAction($payPalResponse, $order, $data);

        $action->process();
    }


    /**
     * Returns payment object
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function getPayment()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\OrderPayment::class);
        $mockBuilder->setMethods(['addComment']);
        return  $mockBuilder->getMock();
    }

    /**
     * Returns payment list
     *
     * @param array $testMethods
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentList
     */
    protected function getPaymentList($testMethods = array())
    {
        $methods = array('addPayment' => $this->getPayment());
        $paymentList = $this->_createStub(\OxidEsales\PayPalModule\Model\OrderPaymentList::class, $methods, $testMethods);

        return $paymentList;
    }

    /**
     * Returns order
     *
     * @param array $testMethods
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected function getOrder($testMethods = array())
    {
        $methods = array('getPaymentList' => $this->getPaymentList());
        $order = $this->_createStub(\OxidEsales\PayPalModule\Model\PayPalOrder::class, $methods, $testMethods);

        return $order;
    }

    /**
     * Retruns basic PayPal response object
     *
     * @param array $testMethods
     *
     * @return \OxidEsales\PayPalModule\Model\Response\ResponseDoCapture
     */
    protected function getPayPalResponse($testMethods = array())
    {
        $methods = array('getRefundAmount', 'getPaymentStatus', 'getTransactionId', 'getCurrency');
        $mockedMethods = array_unique(array_merge($methods, $testMethods));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Response\ResponseDoRefund::class);
        $mockBuilder->setMethods($mockedMethods);
        return $mockBuilder->getMock();
    }

    /**
     * Returns capture action data object
     *
     * @param $methods
     *
     * @return \OxidEsales\PayPalModule\Model\Action\Data\OrderCaptureActionData
     */
    protected function getData($methods = array())
    {
        $data = $this->_createStub(\OxidEsales\PayPalModule\Model\Action\Data\OrderRefundActionData::class, $methods);

        return $data;
    }


    /**
     * Returns capture action object
     *
     * @param $payPalResponse
     * @param $order
     * @param $data
     *
     * @return \OxidEsales\PayPalModule\Model\Action\OrderCaptureAction
     */
    protected function getAction($payPalResponse, $order, $data = null)
    {
        $data = $data ? $data : $this->getData();
        $data->expects($this->any())->method('getPaymentBeingRefunded')->will($this->returnValue(new \OxidEsales\PayPalModule\Model\OrderPayment()));

        $handler = $this->_createStub('ActionHandler', array('getPayPalResponse' => $payPalResponse, 'getData' => $data));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Action\OrderRefundAction::class);
        $mockBuilder->setMethods(['getDate']);
        $mockBuilder->setConstructorArgs([$handler, $order]);
        $action = $mockBuilder->getMock();
        $action->expects($this->any())->method('getDate')->will($this->returnValue('date'));

        return $action;
    }
}