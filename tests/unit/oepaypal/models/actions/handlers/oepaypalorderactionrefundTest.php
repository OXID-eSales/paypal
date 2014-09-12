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
 * @copyright (C) OXID eSales AG 2003-2013
 */

require_once realpath(".") . '/unit/OxidTestCase.php';
require_once realpath(".") . '/unit/test_config.inc.php';


/**
 * Testing oePayPalOrderRefundAction class.
 */
class Unit_oePayPal_Models_Actions_Handlers_oePayPalOrderActionRefundTest extends OxidTestCase
{

    /**
     *  Setup: Prepare data - create need tables
     */
    public function setUp()
    {
        oxDb::getDb()->execute('TRUNCATE `oepaypal_orderpaymentcomments`');
        oxDb::getDb()->execute('TRUNCATE `oepaypal_orderpayments`');
        oxDb::getDb()->execute('TRUNCATE `oepaypal_order`');
    }

    /**
     * Tests setting and getting of parameters (amount, type)
     */
    public function testSetGetParameters()
    {
        $sTransactionId = '123456';
        $dAmount = 59.92;
        $sType = 'Complete';

        $oAction = new oePayPalOrderRefundAction();

        $oAction->setTransactionId($sTransactionId);
        $oAction->setAmount($dAmount);
        $oAction->setType($sType);

        $this->assertEquals($sTransactionId, $oAction->getTransactionId());
        $this->assertEquals($dAmount, $oAction->getAmount());
        $this->assertEquals($sType, $oAction->getType());
    }

    /**
     * Testing setting and getting of PayPal request
     */
    public function testSetGetPayPalRequest()
    {
        $oPayPalRequest = new oePayPalPayPalRequest();

        $oAction = new oePayPalOrderRefundAction();
        $oAction->setPayPalRequest($oPayPalRequest);

        $this->assertEquals($oPayPalRequest, $oAction->getPayPalRequest());
    }

    /**
     * Testing building of PayPal request when request is not set
     */
    public function testGetPayPalRequest_RequestIsNotSet_BuildsRequest()
    {
        $sTransId = '123456';
        $sCurrency = 'LTU';
        $dAmount = 59.67;
        $sType = 'Full';
        $sComment = 'Comment';

        $oBuilder = $this->getMock('oePayPalPayPalRequestBuilder', array('setTransactionId', 'setAmount', 'setRefundType', 'getRequest', 'setComment'));
        $oBuilder->expects($this->atLeastOnce())->method('setTransactionId')->with($this->equalTo($sTransId));
        $oBuilder->expects($this->atLeastOnce())->method('setAmount')->with($this->equalTo($dAmount), $this->equalTo($sCurrency));
        $oBuilder->expects($this->atLeastOnce())->method('setRefundType')->with($this->equalTo($sType));
        $oBuilder->expects($this->atLeastOnce())->method('setComment')->with($this->equalTo($sComment));
        $oBuilder->expects($this->any())->method('getRequest')->will($this->returnValue(new oePayPalPayPalRequest()));

        $oOrder = $this->_createStub('oePayPalPayPalOrder', array('getCurrency' => $sCurrency));

        $oAction = $this->_getAction();

        $oAction->setTransactionId($sTransId);
        $oAction->setAmount($dAmount);
        $oAction->setType($sType);
        $oAction->setComment($sComment);

        $oAction->setOrder($oOrder);
        $oAction->setPayPalRequestBuilder($oBuilder);

        $this->assertTrue($oAction->getPayPalRequest() instanceof oePayPalPayPalRequest);
    }

    /**
     * Testing setting of correct request to PayPal service when creating response
     */
    public function testGetPayPalResponse_SetsCorrectRequestToService()
    {
        $oPayPalRequest = $this->getMock('oePayPalPayPalRequest');

        $oCheckoutService = $this->getMock('oePayPalService', array('refundTransaction'));
        $oCheckoutService->expects($this->once())
            ->method('refundTransaction')
            ->with($this->equalTo($oPayPalRequest))
            ->will($this->returnValue(null));

        $oAction = new oePayPalOrderRefundAction();
        $oAction->setPayPalService($oCheckoutService);
        $oAction->setPayPalRequest($oPayPalRequest);

        $oAction->getPayPalResponse();
    }

    /**
     * Testing returning of returning response object formed by service
     */
    public function testGetResponse_ReturnsResponseFromService()
    {
        $oPayPalRequest = new oePayPalPayPalRequest();
        $oPayPalResponse = new oePayPalResponseDoRefund();

        $oCheckoutService = $this->getMock('oePayPalService', array('refundTransaction'));
        $oCheckoutService->expects($this->once())
            ->method('refundTransaction')
            ->will($this->returnValue($oPayPalResponse));

        $oAction = new oePayPalOrderRefundAction();
        $oAction->setPayPalService($oCheckoutService);
        $oAction->setPayPalRequest($oPayPalRequest);

        $this->assertEquals($oPayPalResponse, $oAction->getPayPalResponse());
    }


    /**
     * Testing addition of refunded amount to order
     */
    public function testProcess_AddingRefundedAmountToOrder()
    {
        $dAmount = 59.67;

        $oPayPalResponse = $this->_getPayPalResponse(array('getRefundAmount'));
        $oPayPalResponse->expects($this->any())
            ->method('getRefundAmount')
            ->will($this->returnValue($dAmount));

        $oOrder = $this->_getOrder(array('addRefundedAmount'));
        $oOrder->expects($this->once())
            ->method('addRefundedAmount')
            ->with($this->equalTo($dAmount))
            ->will($this->returnValue(null));

        $oAction = $this->_getAction();
        $oAction->setPayPalResponse($oPayPalResponse);
        $oAction->setOrder($oOrder);

        $oAction->process();
    }

    /**
     * Testing new payment creation with correct data after PayPal request is processed
     */
    public function testProcess_NewPaymentCreated_WithCorrectData()
    {
        $sTransactionId = 'transactionId';
        $sCorrelationId = 'correlationId';
        $sStatus = 'Completed';
        $dAmount = 59.67;
        $sCurrency = 'EUR';
        $sDate = 'date';

        $aPayPalResponseMethods = array(
            'getTransactionId' => $sTransactionId,
            'getCorrelationId' => $sCorrelationId,
            'getPaymentStatus' => $sStatus,
            'getRefundAmount' => $dAmount,
            'getCurrency' => $sCurrency,
        );
        $oPayPalResponse = $this->_createStub('oePayPalResponseDoRefund', $aPayPalResponseMethods);

        $oPayment = new oePayPalOrderPayment();
        $oPayment->setDate($sDate);
        $oPayment->setTransactionId($sTransactionId);
        $oPayment->setCorrelationId($sCorrelationId);
        $oPayment->setAction('refund');
        $oPayment->setStatus($sStatus);
        $oPayment->setAmount($dAmount);
        $oPayment->setCurrency($sCurrency);

        $oPaymentList = $this->_getPaymentList(array('addPayment'));
        $oPaymentList->expects($this->once())
            ->method('addPayment')
            ->with($oPayment)
            ->will($this->returnValue($this->_getPayment()));

        $oOrder = $this->_getOrder(array('getPaymentList'));
        $oOrder->expects($this->once())
            ->method('getPaymentList')
            ->will($this->returnValue($oPaymentList));

        $oAction = $this->_getAction();
        $oAction->setPayPalResponse($oPayPalResponse);
        $oAction->setOrder($oOrder);

        $oAction->process();
    }

    /**
     * Testing saving of order after updating it
     */
    public function testProcess_ProcessingOfServiceResponse_OrderSaved()
    {
        $oPayPalResponse = $this->_getPayPalResponse();

        $oOrder = $this->_getOrder(array('save'));
        $oOrder->expects($this->atLeastOnce())
            ->method('save')
            ->will($this->returnValue(null));

        $oAction = $this->_getAction();
        $oAction->setPayPalResponse($oPayPalResponse);
        $oAction->setOrder($oOrder);

        $oAction->process();
    }

    /**
     * Testing addition of comment after PayPal request processing
     */
    public function testProcess_ProcessingOfServiceResponse_CommentAdded()
    {
        $sComment = 'testComment';
        $oComment = new oePayPalOrderPaymentComment();
        $oComment->setComment($sComment);

        $oPayPalResponse = $this->_getPayPalResponse();

        $oPayment = $this->_getPayment();
        $oPayment->expects($this->once())
            ->method('addComment')
            ->with($this->equalTo($oComment));

        $oPaymentList = $this->_getPaymentList(array('addPayment'));
        $oPaymentList->expects($this->any())
            ->method('addPayment')
            ->will($this->returnValue($oPayment));

        $oOrder = $this->_getOrder(array('getPaymentList'));
        $oOrder->expects($this->any())
            ->method('getPaymentList')
            ->will($this->returnValue($oPaymentList));

        $oAction = $this->_getAction();
        $oAction->setPayPalResponse($oPayPalResponse);
        $oAction->setOrder($oOrder);
        $oAction->setComment('testComment');

        $oAction->process();
    }

    /**
     * Testing setting and getting of payment that is being refunded (usually capture payment which is being refunded by this transaction)
     */
    public function testSetGetPaymentBeingRefunded()
    {
        $oPayment = $this->_getPayment();
        $oAction = $this->_getAction();
        $oAction->setPaymentBeingRefunded($oPayment);

        $this->assertEquals($oPayment, $oAction->getPaymentBeingRefunded());
    }

    /**
     * Test loading of payment by transaction id
     */
    public function testGetPaymentBeingRefunded_LoadedByTransactionId_TransactionIdSet()
    {
        $oPayment = new oePayPalOrderPayment();
        $oPayment->setTransactionId('transId');
        $oPayment->setOrderId('_testOrderId');
        $oPayment->save();

        $oAction = $this->_getAction();
        $oAction->setTransactionId('transId');

        $oPayment = $oAction->getPaymentBeingRefunded();

        $this->assertEquals('transId', $oPayment->getTransactionId());
    }

    /**
     * Returns payment object
     *
     * @return oePayPalOrderPayment
     */
    protected function _getPayment()
    {
        $oPayment = $this->getMock('oePayPalOrderPayment', array('addComment'));
        return $oPayment;
    }

    /**
     * Returns payment list
     *
     * @param array $aTestMethods
     * @return oePayPalOrderPaymentList
     */
    protected function _getPaymentList($aTestMethods = array())
    {
        $aMethods = array('addPayment' => $this->_getPayment());
        $oPaymentList = $this->_createStub('oePayPalOrderPaymentList', $aMethods, $aTestMethods);

        return $oPaymentList;
    }

    /**
     * Returns order
     *
     * @param array $aTestMethods
     * @return oePayPalPayPalOrder
     */
    protected function _getOrder($aTestMethods = array())
    {
        $aMethods = array('getPaymentList' => $this->_getPaymentList());
        $oOrder = $this->_createStub('oePayPalPayPalOrder', $aMethods, $aTestMethods);
        return $oOrder;
    }

    /**
     * Retruns basic PayPal response object
     *
     * @param array $aTestMethods
     * @return oePayPalResponseDoCapture
     */
    protected function _getPayPalResponse($aTestMethods = array())
    {
        $aMethods = array('getRefundAmount', 'getPaymentStatus', 'getTransactionId', 'getCurrency');
        $aMockedMethods = array_unique(array_merge($aMethods, $aTestMethods));

        $oOrder = $this->getMock('oePayPalResponseDoRefund', $aMockedMethods);
        return $oOrder;
    }

    /**
     * Returns capture action object
     *
     * @return oePayPalOrderCaptureAction
     */
    protected function _getAction()
    {
        $oAction = $this->_createStub('oePayPalOrderRefundAction', array('getDate' => 'date'));
        $oAction->setComment('');
        $oAction->setTransactionId("authId");
        $oAction->setOrderStatus('Completed');

        return $oAction;
    }
}