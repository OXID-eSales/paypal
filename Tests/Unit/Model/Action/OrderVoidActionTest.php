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

namespace OxidEsales\PayPalModule\Tests\Unit\Model\Action;

use OxidEsales\PayPalModule\Model\Action\Handler\OrderVoidActionHandler;

/**
 * Testing \OxidEsales\PayPalModule\Model\Action\OrderVoidAction class.
 */
class OrderVoidActionTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Testing addition of voided amount to order
     */
    public function testProcess_VoidedAmountAddedToOrder()
    {
        $payPalResponse = new \OxidEsales\PayPalModule\Model\Response\ResponseDoVoid();
        $amount = 5.19;

        $order = $this->getOrder(array('getRemainingOrderSum', 'setVoidedAmount'));
        $order->expects($this->once())
            ->method('getRemainingOrderSum')
            ->willReturn($amount);
        $order->expects($this->once())
            ->method('setVoidedAmount')
            ->with($this->equalTo($amount));

        $action = $this->getAction($payPalResponse, $order);

        $action->process();
    }

    /**
     * Testing new payment creation with correct data after PayPal request is processed
     */
    public function testProcess_NewPaymentCreated_WithCorrectData()
    {
        $authentificationId = 'authentificationId';
        $correlationId = 'correlationId';
        $amount = 2.99;
        $date = 'date';

        $payPalResponseMethods = array(
            'getAuthorizationId' => $authentificationId,
            'getCorrelationId'   => $correlationId
        );
        $payPalResponse = $this->createConfiguredMock(\OxidEsales\PayPalModule\Model\Response\ResponseDoVoid::class, $payPalResponseMethods);

        $payment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $payment->setDate($date);
        $payment->setTransactionId($authentificationId);
        $payment->setCorrelationId($correlationId);
        $payment->setAction('void');
        $payment->setStatus('Voided');
        $payment->setAmount($amount);

        $paymentList = $this->createPartialMock(\OxidEsales\PayPalModule\Model\OrderPaymentList::class, ['addPayment']);
        $paymentList->expects($this->once())
            ->method('addPayment')
            ->willReturn($payment);

        $partialMethods = [
            'getPaymentList',
            'save',
            'getRemainingOrderSum'
        ];
    
        /** @var MockObject $order */
        $order = $this->createPartialMock(\OxidEsales\PayPalModule\Model\PayPalOrder::class, $partialMethods);
        $order->method('save')->willReturn(true);
        $order->expects($this->once())
            ->method('getPaymentList')
            ->willReturn($paymentList);
        $order->expects($this->once())
            ->method('getRemainingOrderSum')
            ->willReturn($amount);

        $action = $this->getAction($payPalResponse, $order);

        $action->process();
    }

    /**
     * Testing saving of order after updating it
     */
    public function testProcess_CommentAdded()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\Eshop\Core\UtilsDate::class);
        $mockBuilder->onlyMethods(['getTime']);
        $utilsDate = $mockBuilder->getMock();
        $utilsDate->expects($this->any())->method('getTime')->will($this->returnValue(time()));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\UtilsDate::class, $utilsDate);

        $comment = 'testComment';
        $comment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $comment->setComment($comment);

        $payPalResponseMethods = array(
            'getAuthorizationId',
            'getCorrelationId'
        );
        $payPalResponse = $this->createPartialMock(\OxidEsales\PayPalModule\Model\Response\ResponseDoVoid::class, $payPalResponseMethods);

        $payment = $this->getPayment();
        $payment->expects($this->once())
            ->method('addComment')
            ->with($this->equalTo($comment));

        $paymentList = $this->createPartialMock(\OxidEsales\PayPalModule\Model\OrderPaymentList::class, ['addPayment']);
        $paymentList->expects($this->once())
            ->method('addPayment')
            ->willReturn($payment);

        $partialMethods = [
            'getPaymentList',
            'save'
        ];
    
        /** @var MockObject $order */
        $order = $this->createPartialMock(\OxidEsales\PayPalModule\Model\PayPalOrder::class, $partialMethods);
        $order->method('save')->willReturn(true);
        $order->expects($this->once())
            ->method('getPaymentList')
            ->willReturn($paymentList);

        $data = $this->getData();
        $data->method('getComment')->willReturn($comment);
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
        $mockBuilder->onlyMethods(['addComment']);
        return $mockBuilder->getMock();
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
        $methods = array('addPayment');
        $mockedMethods = array_unique(array_merge($methods, $testMethods));
        $paymentList = $this->createPartialMock(
            \OxidEsales\PayPalModule\Model\OrderPaymentList::class,
            $mockedMethods
        );
        $paymentList->method('addPayment')->willReturn($this->getPayment());

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
        $methods = array('getPaymentList');
        $mockedMethods = array_unique(array_merge($methods, $testMethods));
        $order = $this->createPartialMock(
            \OxidEsales\PayPalModule\Model\PayPalOrder::class,
            $mockedMethods
        );
        $order->method('getPaymentList')->willReturn($this->getPaymentList());

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
        $mockBuilder->onlyMethods($mockedMethods);
        return $mockBuilder->getMock();
    }

    /**
     * Returns capture action data object
     *
     * @param $methods
     *
     * @return \OxidEsales\PayPalModule\Model\Action\Data\OrderCaptureActionData
     */
    protected function getData()
    {
        $data = $this->createStub(\OxidEsales\PayPalModule\Model\Action\Data\OrderVoidActionData::class, $methods);

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
        $handler = $this->createPartialMock(
            OrderVoidActionHandler::class,
            array('getPayPalResponse', 'getData')
        );

        $handler->method('getPayPalResponse')->willReturn($payPalResponse);
        $handler->method('getData')->willReturn($data);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Action\OrderVoidAction::class);
        $mockBuilder->onlyMethods(['getDate']);
        $mockBuilder->setConstructorArgs([$handler, $order]);
        $action = $mockBuilder->getMock();
        $action->expects($this->any())->method('getDate')->willReturn('date');

        return $action;
    }
}
