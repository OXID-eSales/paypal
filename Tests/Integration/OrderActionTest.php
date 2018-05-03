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

namespace OxidEsales\PayPalModule\Tests\Integration;

use OxidEsales\Eshop\Application\Model\Order;

class OrderActionTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oepaypal_orderpaymentcomments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oepaypal_orderpayments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DROP TABLE IF EXISTS `oepaypal_order`');

        \OxidEsales\PayPalModule\Core\Events::addOrderPaymentsCommentsTable();
        \OxidEsales\PayPalModule\Core\Events::addOrderPaymentsTable();
        \OxidEsales\PayPalModule\Core\Events::addOrderTable();
    }

    /**
     * @covers \OxidEsales\PayPalModule\Model\Action\OrderCaptureAction::process
     * @covers \OxidEsales\PayPalModule\Model\Action\Handler\OrderCaptureActionHandler::getPayPalResponse
     * @covers \OxidEsales\PayPalModule\Model\Action\Handler\OrderCaptureActionHandler::getPayPalRequest
     * @covers \OxidEsales\PayPalModule\Model\Action\Data\OrderCaptureActionData::getAmount
     * @covers \OxidEsales\PayPalModule\Model\Action\Data\OrderCaptureActionData::getType
     */
    public function testActionCapture()
    {
        $requestParams = array(
            'capture_amount' => '200.99',
            'capture_type'   => 'Complete'
        );
        $responseParams = array(
            'TRANSACTIONID' => 'TransactionId',
            'PAYMENTSTATUS' => 'Pending',
            'AMT'           => '99.87',
            'CURRENCYCODE'  => 'EUR',
        );

        $action = $this->createAction('capture', 'testOrderId', $requestParams, $responseParams);

        $action->process();

        $order = $this->getOrder('testOrderId');
        $this->assertEquals('99.87', $order->getCapturedAmount());

        $paymentList = $order->getPaymentList()->getArray();
        $this->assertEquals(1, count($paymentList));

        $payment = array_shift($paymentList);
        $this->assertEquals('capture', $payment->getAction());
        $this->assertEquals('testOrderId', $payment->getOrderId());
        $this->assertEquals('99.87', $payment->getAmount());
        $this->assertEquals('Pending', $payment->getStatus());
        $this->assertEquals('EUR', $payment->getCurrency());

        $payment->delete();
        $order->delete();
    }

    /**
     * @covers \OxidEsales\PayPalModule\Model\Action\OrderRefundAction::process
     * @covers \OxidEsales\PayPalModule\Model\Action\Handler\OrderRefundActionHandler::getPayPalResponse
     * @covers \OxidEsales\PayPalModule\Model\Action\Handler\OrderRefundActionHandler::getPayPalRequest
     * @covers \OxidEsales\PayPalModule\Model\Action\Data\OrderRefundActionData::getAmount
     * @covers \OxidEsales\PayPalModule\Model\Action\Data\OrderRefundActionData::getType
     */
    public function testActionRefund()
    {
        $requestParams = array(
            'refund_amount'  => '10',
            'refund_type'    => 'Complete',
            'transaction_id' => 'capturedTransaction'
        );
        $responseParams = array(
            'REFUNDTRANSACTIONID' => 'TransactionId',
            'REFUNDSTATUS'        => 'Pending',
            'GROSSREFUNDAMT'      => '9.01',
            'CURRENCYCODE'        => 'EUR',
        );

        $capturedPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $capturedPayment->setOrderId('testOrderId');
        $capturedPayment->setTransactionId('capturedTransaction');
        $capturedPayment->save();

        $action = $this->createAction('refund', 'testOrderId', $requestParams, $responseParams);

        $action->process();

        $order = $this->getOrder('testOrderId');
        $this->assertEquals('9.01', $order->getRefundedAmount());

        $paymentList = $order->getPaymentList()->getArray();
        $this->assertEquals(2, count($paymentList));

        $payment = array_shift($paymentList);
        $this->assertEquals('refund', $payment->getAction());
        $this->assertEquals('testOrderId', $payment->getOrderId());
        $this->assertEquals('9.01', $payment->getAmount());
        $this->assertEquals('Pending', $payment->getStatus());
        $this->assertEquals('EUR', $payment->getCurrency());

        $capturedPayment = array_shift($paymentList);
        $this->assertEquals('9.01', $capturedPayment->getRefundedAmount());

        $payment->delete();
        $capturedPayment->delete();
        $order->delete();
    }

    /**
     * @covers oePayPalOrderReauthorizeAction::process
     * @covers \OxidEsales\PayPalModule\Model\Action\Handler\OrderReauthorizeActionHandler::getPayPalResponse
     * @covers \OxidEsales\PayPalModule\Model\Action\Handler\OrderReauthorizeActionHandler::getPayPalRequest
     */
    public function ___testActionReauthorize()
    {
        $responseParams = array(
            'AUTHORIZATIONID' => 'AuthorizationId',
            'PAYMENTSTATUS'   => 'Complete',
        );

        $action = $this->createAction('reauthorize', 'testOrderId', array(), $responseParams);
        $action->process();

        $order = $this->getOrder('testOrderId');

        $paymentList = $order->getPaymentList()->getArray();
        $this->assertEquals(1, count($paymentList));

        $payment = array_shift($paymentList);
        $this->assertEquals('re-authorization', $payment->getAction());
        $this->assertEquals('testOrderId', $payment->getOrderId());
        $this->assertEquals('0.00', $payment->getAmount());
        $this->assertEquals('Complete', $payment->getStatus());

        $payment->delete();
        $order->delete();
    }

    /**
     * @covers \OxidEsales\PayPalModule\Model\Action\OrderVoidAction::process
     * @covers \OxidEsales\PayPalModule\Model\Action\Handler\OrderVoidActionHandler::getPayPalResponse
     * @covers \OxidEsales\PayPalModule\Model\Action\Handler\OrderVoidActionHandler::getPayPalRequest
     */
    public function testActionVoid()
    {
        $responseParams = array(
            'AUTHORIZATIONID' => 'AuthorizationId',
        );

        $action = $this->createAction('void', 'testOrderId', array(), $responseParams);
        $action->process();

        $order = $this->getOrder('testOrderId');

        $paymentList = $order->getPaymentList()->getArray();
        $this->assertEquals(1, count($paymentList));

        $payment = array_shift($paymentList);
        $this->assertEquals('void', $payment->getAction());
        $this->assertEquals('testOrderId', $payment->getOrderId());
        $this->assertEquals('0.00', $payment->getAmount());
        $this->assertEquals('Voided', $payment->getStatus());

        $payment->delete();
        $order->delete();
    }

    /**
     * Returns loaded \OxidEsales\PayPalModule\Model\Action\OrderAction object
     *
     * @param string $action
     * @param string $orderId
     * @param array  $requestParams
     * @param array  $responseParams
     *
     * @return \OxidEsales\PayPalModule\Model\Action\OrderAction
     */
    protected function createAction($action, $orderId, $requestParams, $responseParams)
    {
        $order = $this->getOrder($orderId);
        $request = $this->getRequestHelper()->getRequest($requestParams);

        $actionFactory = $this->getActionFactory($request, $order);
        $action = $actionFactory->createAction($action);

        $service = $this->getPayPalCommunicationHelper()->getCaller($responseParams);
        $captureHandler = $action->getHandler();
        $captureHandler->setPayPalService($service);

        return $action;
    }

    /**
     * @return \OxidEsales\PayPalModule\Tests\Integration\Library\RequestHelper
     */
    protected function getRequestHelper()
    {
        return new \OxidEsales\PayPalModule\Tests\Integration\Library\RequestHelper();
    }

    /**
     * @return \OxidEsales\PayPalModule\Tests\Integration\Library\CommunicationHelper
     */
    protected function getPayPalCommunicationHelper()
    {
        return new \OxidEsales\PayPalModule\Tests\Integration\Library\CommunicationHelper();
    }

    /**
     * Returns loaded \OxidEsales\PayPalModule\Model\PayPalOrder object with given id
     *
     * @param string $orderId
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected function getOrder($orderId)
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $order->setOrderId($orderId);
        $order->load();

        return $order;
    }

    /**
     * @param \OxidEsales\PayPalModule\Core\Request      $request
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder $payPalOrder
     *
     * @return \OxidEsales\PayPalModule\Model\Action\OrderActionFactory
     */
    protected function getActionFactory($request, $payPalOrder)
    {
        $order = $this->_createStub(Order::class, array('getPayPalOrder' => $payPalOrder));

        $actionFactory = new \OxidEsales\PayPalModule\Model\Action\OrderActionFactory($request, $order);

        return $actionFactory;
    }
}
