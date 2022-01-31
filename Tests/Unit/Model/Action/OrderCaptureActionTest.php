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

use stdClass;

/**
 * Testing \OxidEsales\PayPalModule\Model\Action\OrderCaptureAction class.
 */
class OrderCaptureActionTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Testing addition of captured amount to order
     */
    public function testProcess_CapturedAmountAddedToOrder()
    {
        $amount = 59.67;

        $payPalResponse = $this->getPayPalResponse(array('getCapturedAmount'));
        $payPalResponse->expects($this->any())->method('getCapturedAmount')->will($this->returnValue($amount));

        $order = $this->getOrder(array('addCapturedAmount'));
        $order->expects($this->once())->method('addCapturedAmount')->with($this->equalTo($amount));

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
            'getTransactionId'  => $transactionId,
            'getCorrelationId'  => $correlationId,
            'getPaymentStatus'  => $status,
            'getCapturedAmount' => $amount,
            'getCurrency'       => $currency,
        );
        $payPalResponse = $this->createStub(\OxidEsales\PayPalModule\Model\Response\ResponseDoCapture::class, $payPalResponseMethods);

        $payment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $payment->setDate($date);
        $payment->setTransactionId($transactionId);
        $payment->setCorrelationId($correlationId);
        $payment->setAction('capture');
        $payment->setStatus($status);
        $payment->setAmount($amount);
        $payment->setCurrency($currency);

        $paymentList = $this->getPaymentList(array('addPayment'));
        $paymentList->expects($this->once())
            ->method('addPayment')
            ->with($payment)
            ->will($this->returnValue($payment));

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
        $mockBuilder->onlyMethods(['getTime']);
        $utilsDate = $mockBuilder->getMock();
        $utilsDate->expects($this->any())->method('getTime')->will($this->returnValue(1410431540));
        
        //todo ??
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\UtilsDate::class, $utilsDate);

        $commentContent = 'testComment';
        $comment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
        $comment->setComment($commentContent);

        $payPalResponse = $this->getPayPalResponse();

        $payment = $this->getPayment();
        $payment->expects($this->once())->method('addComment')->with($this->equalTo($comment));

        $paymentList = $this->createPartialMock(\OxidEsales\PayPalModule\Model\OrderPaymentList::class, ['addPayment']);
        $paymentList->method('addPayment')->willReturn($payment);

        $order = $this->createPartialMock(\OxidEsales\PayPalModule\Model\PayPalOrder::class, ['getPaymentList']);
        $order->method('getPaymentList')->willReturn($paymentList);

        $data = $this->createStub(\OxidEsales\PayPalModule\Model\Action\Data\OrderCaptureActionData::class);
        $data->expects($this->once())->method('getComment')->will($this->returnValue($commentContent));

        $action = $this->getAction($payPalResponse, $order, $data);

        $action->process();
    }

    /**
     * Testing reauthorizing
     */
    public function testProcess_Reauthorizing_FirstCapture()
    {
        $payPalResponse = $this->getPayPalResponse();

        $order = $this->getOrder(array('getCapturedAmount'));
        $order->expects($this->any())->method('getCapturedAmount')->will($this->returnValue(0));

        $reauthorizePayPalResponse = $this->createPartialMock(
            \OxidEsales\PayPalModule\Model\Response\ResponseDoReAuthorize::class,
            ['getPaymentStatus', 'getAuthorizationId']
        );

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Action\Handler\OrderReauthorizeActionHandler::class);
        $mockBuilder->setMethods(['getPayPalResponse']);
        $mockBuilder->setConstructorArgs([new stdClass()]);
        $reauthorizeHandler = $mockBuilder->getMock();
        $reauthorizeHandler->expects($this->never())->method('getPayPalResponse')->will($this->returnValue($reauthorizePayPalResponse));

        $action = $this->getAction($payPalResponse, $order, null, $reauthorizeHandler);

        $action->process();
    }

    /**
     * Testing reauthorizing
     */
    public function testProcess_Reauthorizing_AlreadyCaptured()
    {
        $payPalResponse = $this->getPayPalResponse();

        $order = $this->getOrder(array('getCapturedAmount'));
        $order->expects($this->any())->method('getCapturedAmount')->will($this->returnValue(0.01));

        $reauthorizePayPalResponse = $this->createPartialMock(
            \OxidEsales\PayPalModule\Model\Response\ResponseDoReAuthorize::class,
            ['getPaymentStatus', 'getAuthorizationId']
        );

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Action\Handler\OrderReauthorizeActionHandler::class);
        $mockBuilder->setMethods(['getPayPalResponse']);
        $mockBuilder->setConstructorArgs([new stdClass()]);
        $reauthorizeHandler = $mockBuilder->getMock();
        $reauthorizeHandler->expects($this->once())->method('getPayPalResponse')->will($this->returnValue($reauthorizePayPalResponse));

        $action = $this->getAction($payPalResponse, $order, null, $reauthorizeHandler);

        $action->process();
    }

    /**
     * Testing addition of comment after PayPal request processing
     */
    public function testProcess_Reauthorizing_ExceptionThrown()
    {
        $payPalResponse = $this->getPayPalResponse();

        $order = $this->getOrder(array('getCapturedAmount'));
        $order->expects($this->any())->method('getCapturedAmount')->will($this->returnValue(0.01));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Action\Handler\OrderReauthorizeActionHandler::class);
        $mockBuilder->setMethods(['getPayPalResponse']);
        $mockBuilder->setConstructorArgs([new stdClass()]);
        $reauthorizeHandler = $mockBuilder->getMock();
        $reauthorizeHandler->expects($this->once())->method('getPayPalResponse')->will($this->throwException(new \OxidEsales\PayPalModule\Core\Exception\PayPalResponseException()));

        $action = $this->getAction($payPalResponse, $order, null, $reauthorizeHandler);

        $action->process();
    }

    /**
     * Returns payment object
     */
    protected function getPayment()
    {
        return $this->createPartialMock(\OxidEsales\PayPalModule\Model\OrderPayment::class, ['addComment']);
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
        $paymentList = $this->createStub(\OxidEsales\PayPalModule\Model\OrderPaymentList::class, $methods, $testMethods);

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
        $methods = array('getPaymentList' => $this->getPaymentList(), 'save' => true);
        // $order = $this->createStub(\OxidEsales\PayPalModule\Model\PayPalOrder::class, $methods, $testMethods);

//todo merge other methods names into arr
        $partialMethods = [
            'getPaymentList',
            'save'
        ];

        $mockedMethods = array_unique(array_merge($partialMethods, $testMethods));

        /** @var MockObject $order */
        $order = $this->createPartialMock(\OxidEsales\PayPalModule\Model\PayPalOrder::class, $mockedMethods);
        $order->method('getPaymentList')->willReturn($this->getPaymentList());
        $order->method('save')->willReturn(true);

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
        $methods = array('getCapturedAmount', 'getPaymentStatus', 'getCurrency');
        $mockedMethods = array_unique(array_merge($methods, $testMethods));

        return $this->createPartialMock(\OxidEsales\PayPalModule\Model\Response\ResponseDoCapture::class, $mockedMethods);
    }

    /**
     * Returns capture action object
     *
     * @param $payPalResponse
     * @param $order
     * @param $data
     * @param $reauthorizeHandler
     *
     * @return \OxidEsales\PayPalModule\Model\Action\OrderCaptureAction
     */
    protected function getAction($payPalResponse, $order, $data = null, $reauthorizeHandler = null)
    {
        $data = $data ? $data : $this->createStub(\OxidEsales\PayPalModule\Model\Action\Data\OrderCaptureActionData::class);
        $handler = $this->createConfiguredMock(\OxidEsales\PayPalModule\Model\Action\Handler\OrderCaptureActionHandler::class, array('getPayPalResponse' => $payPalResponse, 'getData' => $data));
        $reauthorizeHandler = $reauthorizeHandler ? $reauthorizeHandler : $this->createStub(\OxidEsales\PayPalModule\Model\Action\Handler\OrderReauthorizeActionHandler::class);


        // var_dump($handler->getPayPalResponse());
        // die(1);
        //todo touch
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Action\OrderCaptureAction::class);
        $mockBuilder->onlyMethods(['getDate']);
        $mockBuilder->setConstructorArgs([$handler, $order, $reauthorizeHandler]);
        $action = $mockBuilder->getMock();

        $action->expects($this->any())->method('getDate')->will($this->returnValue('date'));

        return $action;
    }
}
