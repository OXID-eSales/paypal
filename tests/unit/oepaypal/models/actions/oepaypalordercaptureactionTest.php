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
 * Testing oePayPalOrderCaptureAction class.
 */
class Unit_oePayPal_Models_Actions_oePayPalOrderCaptureActionTest extends OxidTestCase
{

    /**
     * Testing addition of captured amount to order
     */
    public function testProcess_CapturedAmountAddedToOrder()
    {
        $dAmount = 59.67;

        $oPayPalResponse = $this->_getPayPalResponse(array('getCapturedAmount'));
        $oPayPalResponse->expects($this->any())->method('getCapturedAmount')->will($this->returnValue($dAmount));

        $oOrder = $this->_getOrder(array('addCapturedAmount'));
        $oOrder->expects($this->once())->method('addCapturedAmount')->with($this->equalTo($dAmount));

        $oAction = $this->_getAction($oPayPalResponse, $oOrder);

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
            'getTransactionId'  => $sTransactionId,
            'getCorrelationId'  => $sCorrelationId,
            'getPaymentStatus'  => $sStatus,
            'getCapturedAmount' => $dAmount,
            'getCurrency'       => $sCurrency,
        );
        $oPayPalResponse = $this->_createStub('oePayPalResponseDoCapture', $aPayPalResponseMethods);

        $oPayment = new oePayPalOrderPayment();
        $oPayment->setDate($sDate);
        $oPayment->setTransactionId($sTransactionId);
        $oPayment->setCorrelationId($sCorrelationId);
        $oPayment->setAction('capture');
        $oPayment->setStatus($sStatus);
        $oPayment->setAmount($dAmount);
        $oPayment->setCurrency($sCurrency);

        $oPaymentList = $this->_getPaymentList(array('addPayment'));
        $oPaymentList->expects($this->once())
            ->method('addPayment')
            ->with($oPayment)
            ->will($this->returnValue($oPayment));

        $oOrder = $this->_getOrder(array('getPaymentList'));
        $oOrder->expects($this->once())
            ->method('getPaymentList')
            ->will($this->returnValue($oPaymentList));

        $oAction = $this->_getAction($oPayPalResponse, $oOrder);

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

        $oAction = $this->_getAction($oPayPalResponse, $oOrder);

        $oAction->process();
    }

    /**
     * Testing addition of comment after PayPal request processing
     */
    public function testProcess_ProcessingOfServiceResponse_CommentAdded()
    {
        $oUtilsDate = $this->getMock('oxUtilsDate', array('getTime'));
        $oUtilsDate->expects($this->any())->method('getTime')->will($this->returnValue(1410431540));
        oxRegistry::set('oxUtilsDate', $oUtilsDate);

        $sComment = 'testComment';
        $oComment = new oePayPalOrderPaymentComment();
        $oComment->setComment($sComment);

        $oPayPalResponse = $this->_getPayPalResponse();

        $oPayment = $this->_getPayment();
        $oPayment->expects($this->once())->method('addComment')->with($this->equalTo($oComment));

        $oPaymentList = $this->_getPaymentList(array('addPayment'));
        $oPaymentList->expects($this->any())->method('addPayment')->will($this->returnValue($oPayment));

        $oOrder = $this->_getOrder(array('getPaymentList'));
        $oOrder->expects($this->any())->method('getPaymentList')->will($this->returnValue($oPaymentList));

        $oData = $this->_getData();
        $oData->expects($this->any())->method('getComment')->will($this->returnValue($sComment));

        $oAction = $this->_getAction($oPayPalResponse, $oOrder, $oData);

        $oAction->process();
    }

    /**
     * Testing reauthorizing
     */
    public function testProcess_Reauthorizing_FirstCapture()
    {
        $oPayPalResponse = $this->_getPayPalResponse();

        $oOrder = $this->_getOrder(array('getCapturedAmount'));
        $oOrder->expects($this->any())->method('getCapturedAmount')->will($this->returnValue(0));

        $oReauthorizePayPalResponse = $this->_getPayPalResponse();

        $oReauthorizeHandler = $this->getMock('ReauthorizeActionHandler', array('getPayPalResponse'));
        $oReauthorizeHandler->expects($this->never())->method('getPayPalResponse')->will($this->returnValue($oReauthorizePayPalResponse));

        $oAction = $this->_getAction($oPayPalResponse, $oOrder, null, $oReauthorizeHandler);

        $oAction->process();
    }

    /**
     * Testing reauthorizing
     */
    public function testProcess_Reauthorizing_AlreadyCaptured()
    {
        $oPayPalResponse = $this->_getPayPalResponse();

        $oOrder = $this->_getOrder(array('getCapturedAmount'));
        $oOrder->expects($this->any())->method('getCapturedAmount')->will($this->returnValue(0.01));

        $oReauthorizePayPalResponse = $this->_getPayPalResponse();

        $oReauthorizeHandler = $this->getMock('ReauthorizeActionHandler', array('getPayPalResponse'));
        $oReauthorizeHandler->expects($this->once())->method('getPayPalResponse')->will($this->returnValue($oReauthorizePayPalResponse));

        $oAction = $this->_getAction($oPayPalResponse, $oOrder, null, $oReauthorizeHandler);

        $oAction->process();
    }

    /**
     * Testing addition of comment after PayPal request processing
     */
    public function testProcess_Reauthorizing_ExceptionThrown()
    {
        $oPayPalResponse = $this->_getPayPalResponse();

        $oOrder = $this->_getOrder(array('getCapturedAmount'));
        $oOrder->expects($this->any())->method('getCapturedAmount')->will($this->returnValue(0.01));

        $oReauthorizeHandler = $this->getMock('ReauthorizeActionHandler', array('getPayPalResponse'));
        $oReauthorizeHandler->expects($this->once())->method('getPayPalResponse')->will($this->throwException(new oePayPalResponseException()));

        $oAction = $this->_getAction($oPayPalResponse, $oOrder, null, $oReauthorizeHandler);

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
        $aMethods = array('getPaymentList' => $this->_getPaymentList(), 'save' => true);
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
        $aMethods = array('getCapturedAmount', 'getPaymentStatus', 'getAuthorizationId', 'getCurrency');
        $aMockedMethods = array_unique(array_merge($aMethods, $aTestMethods));

        $oOrder = $this->getMock('oePayPalResponseDoCapture', $aMockedMethods);

        return $oOrder;
    }

    /**
     * Returns capture action data object
     *
     * @param $aMethods
     *
     * @return oePayPalOrderCaptureActionData
     */
    protected function _getData($aMethods = array())
    {
        $oData = $this->_createStub('oePayPalOrderCaptureActionData', $aMethods);

        return $oData;
    }

    /**
     * Returns capture action object
     *
     * @param $oPayPalResponse
     * @param $oOrder
     * @param $oData
     * @param $oReauthorizeHandler
     *
     * @return oePayPalOrderCaptureAction
     */
    protected function _getAction($oPayPalResponse, $oOrder, $oData = null, $oReauthorizeHandler = null)
    {
        $oData = $oData ? $oData : $this->_getData();
        $oHandler = $this->_createStub('CaptureActionHandler', array('getPayPalResponse' => $oPayPalResponse, 'getData' => $oData));
        $oReauthorizeHandler = $oReauthorizeHandler ? $oReauthorizeHandler : $this->_createStub('ReauthorizeActionHandler', array());

        $oAction = $this->getMock('oePayPalOrderCaptureAction', array('getDate'), array($oHandler, $oOrder, $oReauthorizeHandler));

        $oAction->expects($this->any())->method('getDate')->will($this->returnValue('date'));

        return $oAction;
    }
}