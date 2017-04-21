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

namespace OxidEsales\PayPalModule\Tests\Integration;

require_once __DIR__ . '/lib/oepaypalcommunicationhelper.php';
require_once __DIR__ . '/lib/oepaypalrequesthelper.php';

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
        $aRequestParams = array(
            'capture_amount' => '200.99',
            'capture_type'   => 'Complete'
        );
        $aResponseParams = array(
            'TRANSACTIONID' => 'TransactionId',
            'PAYMENTSTATUS' => 'Pending',
            'AMT'           => '99.87',
            'CURRENCYCODE'  => 'EUR',
        );

        $oAction = $this->_createAction('capture', 'testOrderId', $aRequestParams, $aResponseParams);

        $oAction->process();

        $oOrder = $this->_getOrder('testOrderId');
        $this->assertEquals('99.87', $oOrder->getCapturedAmount());

        $aPaymentList = $oOrder->getPaymentList()->getArray();
        $this->assertEquals(1, count($aPaymentList));

        $oPayment = array_shift($aPaymentList);
        $this->assertEquals('capture', $oPayment->getAction());
        $this->assertEquals('testOrderId', $oPayment->getOrderId());
        $this->assertEquals('99.87', $oPayment->getAmount());
        $this->assertEquals('Pending', $oPayment->getStatus());
        $this->assertEquals('EUR', $oPayment->getCurrency());

        $oPayment->delete();
        $oOrder->delete();
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
        $aRequestParams = array(
            'refund_amount'  => '10',
            'refund_type'    => 'Complete',
            'transaction_id' => 'capturedTransaction'
        );
        $aResponseParams = array(
            'REFUNDTRANSACTIONID' => 'TransactionId',
            'REFUNDSTATUS'        => 'Pending',
            'GROSSREFUNDAMT'      => '9.01',
            'CURRENCYCODE'        => 'EUR',
        );

        $oCapturedPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
        $oCapturedPayment->setOrderId('testOrderId');
        $oCapturedPayment->setTransactionId('capturedTransaction');
        $oCapturedPayment->save();

        $oAction = $this->_createAction('refund', 'testOrderId', $aRequestParams, $aResponseParams);

        $oAction->process();

        $oOrder = $this->_getOrder('testOrderId');
        $this->assertEquals('9.01', $oOrder->getRefundedAmount());

        $aPaymentList = $oOrder->getPaymentList()->getArray();
        $this->assertEquals(2, count($aPaymentList));

        $oPayment = array_shift($aPaymentList);
        $this->assertEquals('refund', $oPayment->getAction());
        $this->assertEquals('testOrderId', $oPayment->getOrderId());
        $this->assertEquals('9.01', $oPayment->getAmount());
        $this->assertEquals('Pending', $oPayment->getStatus());
        $this->assertEquals('EUR', $oPayment->getCurrency());

        $oCapturedPayment = array_shift($aPaymentList);
        $this->assertEquals('9.01', $oCapturedPayment->getRefundedAmount());

        $oPayment->delete();
        $oCapturedPayment->delete();
        $oOrder->delete();
    }

    /**
     * @covers oePayPalOrderReauthorizeAction::process
     * @covers \OxidEsales\PayPalModule\Model\Action\Handler\OrderReauthorizeActionHandler::getPayPalResponse
     * @covers \OxidEsales\PayPalModule\Model\Action\Handler\OrderReauthorizeActionHandler::getPayPalRequest
     */
    public function ___testActionReauthorize()
    {
        $aResponseParams = array(
            'AUTHORIZATIONID' => 'AuthorizationId',
            'PAYMENTSTATUS'   => 'Complete',
        );

        $oAction = $this->_createAction('reauthorize', 'testOrderId', array(), $aResponseParams);
        $oAction->process();

        $oOrder = $this->_getOrder('testOrderId');

        $aPaymentList = $oOrder->getPaymentList()->getArray();
        $this->assertEquals(1, count($aPaymentList));

        $oPayment = array_shift($aPaymentList);
        $this->assertEquals('re-authorization', $oPayment->getAction());
        $this->assertEquals('testOrderId', $oPayment->getOrderId());
        $this->assertEquals('0.00', $oPayment->getAmount());
        $this->assertEquals('Complete', $oPayment->getStatus());

        $oPayment->delete();
        $oOrder->delete();
    }

    /**
     * @covers \OxidEsales\PayPalModule\Model\Action\OrderVoidAction::process
     * @covers \OxidEsales\PayPalModule\Model\Action\Handler\OrderVoidActionHandler::getPayPalResponse
     * @covers \OxidEsales\PayPalModule\Model\Action\Handler\OrderVoidActionHandler::getPayPalRequest
     */
    public function testActionVoid()
    {
        $aResponseParams = array(
            'AUTHORIZATIONID' => 'AuthorizationId',
        );

        $oAction = $this->_createAction('void', 'testOrderId', array(), $aResponseParams);
        $oAction->process();

        $oOrder = $this->_getOrder('testOrderId');

        $aPaymentList = $oOrder->getPaymentList()->getArray();
        $this->assertEquals(1, count($aPaymentList));

        $oPayment = array_shift($aPaymentList);
        $this->assertEquals('void', $oPayment->getAction());
        $this->assertEquals('testOrderId', $oPayment->getOrderId());
        $this->assertEquals('0.00', $oPayment->getAmount());
        $this->assertEquals('Voided', $oPayment->getStatus());

        $oPayment->delete();
        $oOrder->delete();
    }

    /**
     * Returns loaded \OxidEsales\PayPalModule\Model\Action\OrderAction object
     *
     * @param string $sAction
     * @param string $sOrderId
     * @param array  $aRequestParams
     * @param array  $aResponseParams
     *
     * @return \OxidEsales\PayPalModule\Model\Action\OrderAction
     */
    protected function _createAction($sAction, $sOrderId, $aRequestParams, $aResponseParams)
    {
        $oOrder = $this->_getOrder($sOrderId);
        $oRequest = $this->_getRequestHelper()->getRequest($aRequestParams);

        $oActionFactory = $this->_getActionFactory($oRequest, $oOrder);
        $oAction = $oActionFactory->createAction($sAction);

        $oService = $this->_getPayPalCommunicationHelper()->getCaller($aResponseParams);
        $oCaptureHandler = $oAction->getHandler();
        $oCaptureHandler->setPayPalService($oService);

        return $oAction;
    }

    /**
     * @return \oePayPalRequestHelper
     */
    protected function _getRequestHelper()
    {
        return new \oePayPalRequestHelper();
    }

    /**
     * @return \oePayPalCommunicationHelper
     */
    protected function _getPayPalCommunicationHelper()
    {
        return new \oePayPalCommunicationHelper();
    }

    /**
     * Returns loaded \OxidEsales\PayPalModule\Model\PayPalOrder object with given id
     *
     * @param string $sOrderId
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected function _getOrder($sOrderId)
    {
        $oOrder = new \OxidEsales\PayPalModule\Model\PayPalOrder();
        $oOrder->setOrderId($sOrderId);
        $oOrder->load();

        return $oOrder;
    }

    /**
     * @param \OxidEsales\PayPalModule\Core\Request      $oRequest
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder $oPayPalOrder
     *
     * @return \OxidEsales\PayPalModule\Model\Action\OrderActionFactory
     */
    protected function _getActionFactory($oRequest, $oPayPalOrder)
    {
        $oOrder = $this->_createStub(\OxidEsales\PayPalModule\Model\Order::class, array('getPayPalOrder' => $oPayPalOrder));

        $oActionFactory = new \OxidEsales\PayPalModule\Model\Action\OrderActionFactory($oRequest, $oOrder);

        return $oActionFactory;
    }
}
