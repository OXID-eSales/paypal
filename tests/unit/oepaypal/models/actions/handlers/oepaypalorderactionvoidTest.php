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
 * Testing oePayPalOrderVoidAction class.
 */
class Unit_oePayPal_Models_Actions_Handlers_oePayPalOrderVoidActionTest extends OxidTestCase
{

    /**
     * Tests setting and getting of parameters (amount, type)
     */
    public function testSetGetParameters()
    {
        $sAmount = 59.92;
        $oAction = new oePayPalOrderVoidAction();
        $oAction->setAmount($sAmount);
        $this->assertEquals($sAmount, $oAction->getAmount());
    }

    /**
     * Tests getting amount when amount is not set and no amount is passed with request. Should be taken from order
     */
    public function testGetAmount_AmountNotSet_TakenFromOrder()
    {
        $sRemainingOrderSum = 59.67;

        $oOrder = $this->getMock('oePayPalPayPalOrder', array('getRemainingOrderSum'));
        $oOrder->expects($this->any())
            ->method('getRemainingOrderSum')
            ->will($this->returnValue($sRemainingOrderSum));

        $oAction = new oePayPalOrderVoidAction();
        $oAction->setOrder($oOrder);

        $this->assertEquals($sRemainingOrderSum, $oAction->getAmount());
    }

    /**
     * Testing setting and getting of PayPal request
     */
    public function testSetGetPayPalRequest()
    {
        $oPayPalRequest = new oePayPalPayPalRequest();

        $oAction = new oePayPalOrderVoidAction();
        $oAction->setPayPalRequest($oPayPalRequest);

        $this->assertEquals($oPayPalRequest, $oAction->getPayPalRequest());
    }

    /**
     * Testing building of PayPal request when request is not set
     */
    public function testGetPayPalRequest_RequestIsNotSet_BuildsRequest()
    {
        $sAuthId = '123456';
        $sCurrency = 'LTU';
        $dAmount = 59.67;
        $sComment = "Comment";

        $oBuilder = $this->getMock('oePayPalPayPalRequestBuilder', array('setAuthorizationId', 'setAmount', 'setCompleteType', 'getRequest', 'setComment'));
        $oBuilder->expects($this->once())->method('setAuthorizationId')->with($this->equalTo($sAuthId));
        $oBuilder->expects($this->once())->method('setAmount')->with($this->equalTo($dAmount), $this->equalTo($sCurrency));
        $oBuilder->expects($this->once())->method('setComment')->with($this->equalTo($sComment));
        $oBuilder->expects($this->once())->method('getRequest')->will($this->returnValue(new oePayPalPayPalRequest()));

        $oOrder = $this->_createStub('oePayPalPayPalOrder', array('getCurrency' => $sCurrency));

        $oAction = new oePayPalOrderVoidAction();

        $oAction->setAuthorizationId($sAuthId);
        $oAction->setAmount($dAmount);
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
        $oPayPalRequest = $this->getMock('oePayPalPayPalRequest', array('getAmount'));
        $oPayPalRequest->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue(9.99));

        $oCheckoutService = $this->getMock('oePayPalService', array('doVoid'));
        $oCheckoutService->expects($this->once())
            ->method('doVoid')
            ->with($this->equalTo($oPayPalRequest))
            ->will($this->returnValue(null));

        $oAction = new oePayPalOrderVoidAction();
        $oAction->setPayPalService($oCheckoutService);
        $oAction->setPayPalRequest($oPayPalRequest);

        $oAction->getPayPalResponse();
    }

    /**
     * Testing addition of voided amount to order
     */
    public function testProcess_VoidedAmountAddedToOrder()
    {
        $oPayPalResponse = new oePayPalResponseDoVoid();
        $dAmount = 5.19;

        $oOrder = $this->_getOrder(array('getRemainingOrderSum', 'setVoidedAmount'));
        $oOrder->expects($this->once())
            ->method('getRemainingOrderSum')
            ->will($this->returnValue($dAmount));
        $oOrder->expects($this->once())
            ->method('setVoidedAmount')
            ->with($this->equalTo($dAmount));

        $oAction = $this->_getAction();
        $oAction->setPayPalResponse($oPayPalResponse);
        $oAction->setOrderStatus('Completed');
        $oAction->setOrder($oOrder);

        $oAction->process();
    }

    /**
     * Testing new payment creation with correct data after PayPal request is processed
     */
    public function testProcess_NewPaymentCreated_WithCorrectData()
    {
        $sAuthentificationId = 'authentificationId';
        $sCorrelationId = 'correlationId';
        $dAmount = 2.99;
        $sDate = 'date';

        $aPayPalResponseMethods = array(
            'getAuthorizationId' => $sAuthentificationId,
            'getCorrelationId' => $sCorrelationId
        );
        $oPayPalResponse = $this->_createStub('oePayPalResponseDoVoid', $aPayPalResponseMethods);

        $oPayment = new oePayPalOrderPayment();
        $oPayment->setDate($sDate);
        $oPayment->setTransactionId($sAuthentificationId);
        $oPayment->setCorrelationId($sCorrelationId);
        $oPayment->setAction('void');
        $oPayment->setStatus('Voided');
        $oPayment->setAmount($dAmount);

        $oPaymentList = $this->_getPaymentList(array('addPayment'));
        $oPaymentList->expects($this->once())->method('addPayment')
            ->with($oPayment)
            ->will($this->returnValue($this->_getPayment()));

        $oOrder = $this->_getOrder(array('getPaymentList', 'getRemainingOrderSum'));
        $oOrder->expects($this->once())->method('getRemainingOrderSum')->will($this->returnValue($dAmount));
        $oOrder->expects($this->once())->method('getPaymentList')->will($this->returnValue($oPaymentList));

        $oAction = $this->_getAction();
        $oAction->setPayPalResponse($oPayPalResponse);
        $oAction->setOrder($oOrder);

        $oAction->process();
    }

    /**
     * Testing saving of order after updating it
     */
    public function testProcess_CommentAdded()
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
        $oPaymentList->expects($this->any())->method('addPayment')->will($this->returnValue($oPayment));

        $oOrder = $this->_getOrder(array('getPaymentList', 'getRemainingOrderSum'));
        $oOrder->expects($this->once())->method('getPaymentList')->will($this->returnValue($oPaymentList));

        $oAction = $this->_getAction();
        $oAction->setPayPalResponse($oPayPalResponse);
        $oAction->setOrder($oOrder);
        $oAction->setComment($sComment);

        $oAction->process();
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

        $oOrder = $this->getMock('oePayPalResponseDoVoid', $aMockedMethods);
        return $oOrder;
    }

    /**
     * Returns capture action object
     *
     * @return oePayPalOrderCaptureAction
     */
    protected function _getAction()
    {
        $oAction = $this->_createStub('oePayPalOrderVoidAction', array('getDate' => 'date'));
        $oAction->setComment('');
        $oAction->setOrderStatus('Completed');

        return $oAction;
    }
}