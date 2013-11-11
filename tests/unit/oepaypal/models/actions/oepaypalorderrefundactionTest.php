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

require_once realpath( "." ).'/unit/OxidTestCase.php';
require_once realpath( "." ).'/unit/test_config.inc.php';


/**
 * Testing oePayPalOrderRefundAction class.
 */
class Unit_oePayPal_Models_Actions_oePayPalOrderRefundActionTest extends OxidTestCase
{

    /**
     *  Setup: Prepare data - create need tables
     */
    public function setUp()
    {
        oxDb::getDb()->execute( 'TRUNCATE `oepaypal_orderpaymentcomments`' );
        oxDb::getDb()->execute( 'TRUNCATE `oepaypal_orderpayments`' );
        oxDb::getDb()->execute( 'TRUNCATE `oepaypal_order`' );
    }

    /**
     * Testing addition of refunded amount to order
     */
    public function testProcess_AddingRefundedAmountToOrder()
    {
        $dAmount = 59.67;

        $oPayPalResponse = $this->_getPayPalResponse( array( 'getRefundAmount' ) );
        $oPayPalResponse->expects( $this->any() )
            ->method( 'getRefundAmount' )
            ->will( $this->returnValue( $dAmount ) );

        $oOrder = $this->_getOrder( array( 'addRefundedAmount' ) );
        $oOrder->expects( $this->once() )
            ->method( 'addRefundedAmount' )
            ->with( $this->equalTo( $dAmount ) )
            ->will( $this->returnValue( null ) );

        $oAction = $this->_getAction( $oPayPalResponse, $oOrder );

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
        $oPayPalResponse = $this->_createStub( 'oePayPalResponseDoRefund', $aPayPalResponseMethods );

        $oPayment = new oePayPalOrderPayment();
        $oPayment->setDate( $sDate );
        $oPayment->setTransactionId( $sTransactionId );
        $oPayment->setCorrelationId( $sCorrelationId );
        $oPayment->setAction( 'refund' );
        $oPayment->setStatus( $sStatus );
        $oPayment->setAmount( $dAmount );
        $oPayment->setCurrency( $sCurrency );

        $oPaymentList = $this->_getPaymentList( array( 'addPayment' ) );
        $oPaymentList->expects( $this->once() )
            ->method( 'addPayment' )
            ->with( $oPayment )
            ->will( $this->returnValue( $this->_getPayment() ) );

        $oOrder = $this->_getOrder( array( 'getPaymentList' ) );
        $oOrder->expects( $this->once() )
            ->method( 'getPaymentList' )
            ->will( $this->returnValue( $oPaymentList ) );

        $oAction = $this->_getAction( $oPayPalResponse, $oOrder );

        $oAction->process();
    }

    /**
     * Testing saving of order after updating it
     */
    public function testProcess_ProcessingOfServiceResponse_OrderSaved()
    {
        $oPayPalResponse = $this->_getPayPalResponse();

        $oOrder = $this->_getOrder( array( 'save' ) );
        $oOrder->expects( $this->atLeastOnce() )
            ->method( 'save' )
            ->will( $this->returnValue( null ) );

        $oAction = $this->_getAction( $oPayPalResponse, $oOrder );

        $oAction->process();
    }

    /**
     * Testing addition of comment after PayPal request processing
     */
    public function testProcess_ProcessingOfServiceResponse_CommentAdded()
    {
        $sComment = 'testComment';
        $oComment = new oePayPalOrderPaymentComment();
        $oComment->setComment( $sComment );

        $oPayPalResponse = $this->_getPayPalResponse();

        $oPayment = $this->_getPayment();
        $oPayment->expects( $this->once() )
            ->method( 'addComment' )
            ->with( $this->equalTo( $oComment ) );

        $oPaymentList = $this->_getPaymentList( array( 'addPayment' ) );
        $oPaymentList->expects( $this->any() )
            ->method( 'addPayment' )
            ->will( $this->returnValue( $oPayment ) );

        $oOrder = $this->_getOrder( array( 'getPaymentList' ) );
        $oOrder->expects( $this->any() )
            ->method( 'getPaymentList' )
            ->will( $this->returnValue( $oPaymentList ) );

        $oData = $this->_getData();
        $oData->expects($this->any())->method('getComment')->will($this->returnValue( $sComment ) );
        $oAction = $this->_getAction( $oPayPalResponse, $oOrder, $oData );

        $oAction->process();
    }


    /**
     * Returns payment object
     *
     * @return oePayPalOrderPayment
     */
    protected function _getPayment()
    {
        $oPayment = $this->getMock( 'oePayPalOrderPayment', array( 'addComment' ));
        return $oPayment;
    }

    /**
     * Returns payment list
     *
     * @param array $aTestMethods
     * @return oePayPalOrderPaymentList
     */
    protected function _getPaymentList( $aTestMethods = array() )
    {
        $aMethods = array( 'addPayment' => $this->_getPayment() );
        $oPaymentList = $this->_createStub( 'oePayPalOrderPaymentList', $aMethods, $aTestMethods );

        return $oPaymentList;
    }

    /**
     * Returns order
     *
     * @param array $aTestMethods
     * @return oePayPalPayPalOrder
     */
    protected function _getOrder( $aTestMethods = array() )
    {
        $aMethods = array( 'getPaymentList' => $this->_getPaymentList() );
        $oOrder = $this->_createStub( 'oePayPalPayPalOrder', $aMethods, $aTestMethods );
        return $oOrder;
    }

    /**
     * Retruns basic PayPal response object
     *
     * @param array $aTestMethods
     * @return oePayPalResponseDoCapture
     */
    protected function _getPayPalResponse( $aTestMethods = array() )
    {
        $aMethods = array( 'getRefundAmount', 'getPaymentStatus', 'getTransactionId', 'getCurrency'  );
        $aMockedMethods = array_unique( array_merge( $aMethods, $aTestMethods ) );

        $oOrder = $this->getMock( 'oePayPalResponseDoRefund', $aMockedMethods );
        return $oOrder;
    }

    /**
     * Returns capture action data object
     *
     * @param $aMethods
     * @return oePayPalOrderCaptureActionData
     */
    protected function _getData( $aMethods = array() )
    {
        $oData = $this->_createStub( 'oePayPalOrderRefundActionData', $aMethods );
        return $oData;
    }


    /**
     * Returns capture action object
     *
     * @param $oPayPalResponse
     * @param $oOrder
     * @param $oData
     * @return oePayPalOrderCaptureAction
     */
    protected function _getAction( $oPayPalResponse, $oOrder, $oData = null )
    {
        $oData = $oData? $oData : $this->_getData();
        $oData->expects($this->any())->method('getPaymentBeingRefunded')->will($this->returnValue( new oePayPalOrderPayment() ) );

        $oHandler = $this->_createStub( 'ActionHandler', array( 'getPayPalResponse' => $oPayPalResponse, 'getData' => $oData ) );

        $oAction = $this->getMock( 'oePayPalOrderRefundAction', array( 'getDate' ), array( $oHandler, $oOrder) );
        $oAction->expects($this->any())->method('getDate')->will($this->returnValue('date'));

        return $oAction;
    }
}