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
 * Testing oePayPalOrderReauthorizeAction class.
 */
class Unit_oePayPal_Models_Actions_Handlers_oePayPalOrderReauthorizeActionTest extends OxidTestCase
{

    /**
     * Tests setting and getting of parameters (amount)
     */
    public function testSetGetParameters()
    {
        $sAmount = 59.92;
        $oAction = $this->_getAction();
        $oAction->setAmount($sAmount);
        $this->assertEquals($sAmount, $oAction->getAmount());
    }

    /**
     * Tests setting and getting PayPal request
     */
    public function testSetGetPayPalRequest()
    {
        $oPayPalRequest = new oePayPalPayPalRequest();

        $oAction = $this->_getAction();
        $oAction->setPayPalRequest($oPayPalRequest);

        $this->assertEquals($oPayPalRequest, $oAction->getPayPalRequest());
    }

    /**
     * Tests getting amount when amount is not set and no amount is passed with request. Should be taken from order
     */
    public function testGetAmount_AmountNotSet_TakenFromOrder()
    {
        $sRemainingOrderSum = 59.67;

        $oOrder = $this->_createStub('oePayPalPayPalOrder', array('getRemainingOrderSum' => $sRemainingOrderSum));
        $oRequest = $this->_createStub('oePayPalRequest', array('getPost' => array()));

        $oAction = $this->_getAction();
        $oAction->setOrder($oOrder);
        $oAction->setRequest($oRequest);

        $this->assertEquals($sRemainingOrderSum, $oAction->getAmount());
    }

    /**
     * Testing building of PayPal request when request is not set
     */
    public function testGetPayPalRequest_RequestIsNotSet_BuildsRequest()
    {
        $sAuthId = '123456';
        $sCurrency = 'LTU';
        $dAmount = 59.67;

        $oBuilder = $this->getMock('oePayPalPayPalRequestBuilder', array('setAuthorizationId', 'setAmount', 'setCompleteType'));
        $oBuilder->expects($this->once())->method('setAuthorizationId')->with($this->equalTo($sAuthId));
        $oBuilder->expects($this->once())->method('setAmount')->with($this->equalTo($dAmount), $this->equalTo($sCurrency));

        $oOrder = $this->_createStub('oePayPalPayPalOrder', array('getCurrency' => $sCurrency));

        $oAction = $this->_getAction();
        $oAction->setAuthorizationId($sAuthId);
        $oAction->setAmount($dAmount);

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

        $oCheckoutService = $this->getMock('oePayPalService', array('doReAuthorization'));
        $oCheckoutService->expects($this->once())->method('doReAuthorization')->with($this->equalTo($oPayPalRequest));

        $oAction = new oePayPalOrderReauthorizeAction();
        $oAction->setPayPalService($oCheckoutService);
        $oAction->setPayPalRequest($oPayPalRequest);

        $oAction->getPayPalResponse();
    }

    /**
     * Testing new payment creation with correct data after PayPal request is processed
     */
    public function testProcess_NewPaymentCreated_WithCorrectData()
    {
        $sAuthId = 'authorizationId';
        $sCorrelationId = 'correlationId';
        $sPaymentStatus = 'PStatus';
        $sDate = 'date';

        $aMethods = array(
            'getAuthorizationId' => $sAuthId,
            'getCorrelationId'   => $sCorrelationId,
            'getPaymentStatus'   => $sPaymentStatus,
        );
        $oPayPalResponse = $this->_createStub('oePayPalOrderReauthorizeAction', $aMethods);

        $oPayment = new oePayPalOrderPayment();
        $oPayment->setDate($sDate);
        $oPayment->setTransactionId($sAuthId);
        $oPayment->setCorrelationId($sCorrelationId);
        $oPayment->setAction('re-authorization');
        $oPayment->setStatus($sPaymentStatus);

        $oPaymentList = $this->_getPaymentList(array('addPayment'));
        $oPaymentList->expects($this->once())->method('addPayment')
            ->with($oPayment)
            ->will($this->returnValue($this->_getPayment()));

        $oOrder = $this->_getOrder(array('getPaymentList'));
        $oOrder->expects($this->once())->method('getPaymentList')->will($this->returnValue($oPaymentList));

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
        $oOrder = $this->_getOrder(array('save'));
        $oOrder->expects($this->atLeastOnce())->method('save')->will($this->returnValue(null));

        $oAction = $this->_getAction();
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

        $oPayment = $this->_getPayment();
        $oPayment->expects($this->once())->method('addComment')->with($this->equalTo($oComment));

        $oPaymentList = $this->_getPaymentList(array('addPayment'));
        $oPaymentList->expects($this->any())->method('addPayment')->will($this->returnValue($oPayment));

        $oOrder = $this->_getOrder(array('getPaymentList'));
        $oOrder->expects($this->any())->method('getPaymentList')->will($this->returnValue($oPaymentList));

        $oAction = $this->_getAction();
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
     *
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
     *
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
     *
     * @return oePayPalResponseDoCapture
     */
    protected function _getPayPalResponse($aTestMethods = array())
    {
        $aMethods = array('getAuthorizationId', 'getPaymentStatus');
        $aMockedMethods = array_unique(array_merge($aMethods, $aTestMethods));

        $oOrder = $this->getMock('oePayPalResponseDoRefund', $aMockedMethods);

        return $oOrder;
    }

    /**
     * Returns reauthorize action object
     *
     * @return oePayPalOrderReauthorizeAction
     */
    protected function _getAction()
    {
        $oAction = $this->_createStub('oePayPalOrderReauthorizeAction', array('getDate' => 'date'));
        $oAction->setComment('');
        $oAction->setAuthorizationId("authId");
        $oAction->setPayPalResponse($this->_getPayPalResponse());
        $oAction->setOrderStatus('Completed');

        return $oAction;
    }
}