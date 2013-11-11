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
require_once realpath( "." ).'/integration/lib/oepaypalcommunicationhelper.php';
require_once realpath( "." ).'/integration/lib/oepaypalrequesthelper.php';

class Integration_oePayPal_oePayPalOrderActionTest extends OxidTestCase
{
    public function setUp()
    {
        oePayPalEvents::addOrderPaymentsCommentsTable();
        oePayPalEvents::addOrderPaymentsTable();
        oePayPalEvents::addOrderTableFields();
    }

    public function tearDown()
    {
        oxDb::getDb()->execute( 'DROP TABLE `oepaypal_orderpaymentcomments`' );
        oxDb::getDb()->execute( 'DROP TABLE `oepaypal_orderpayments`' );
        oxDb::getDb()->execute( 'DROP TABLE `oepaypal_order`' );
    }

    /**
     * @covers oePayPalOrderActionCapture::process
     * @covers oePayPalOrderActionCapture::getPayPalResponse
     * @covers oePayPalOrderActionCapture::getPayPalRequest
     * @covers oePayPalOrderActionCapture::getAmount
     * @covers oePayPalOrderActionCapture::getType
     */
    public function testActionCapture()
    {
        $aRequestParams = array(
            'capture_amount' => '200.99',
            'capture_type' => 'Complete'
        );
        $aResponseParams = array(
            'TRANSACTIONID' => 'TransactionId',
            'PAYMENTSTATUS' => 'Pending',
            'AMT' => '99.87',
            'CURRENCYCODE'  => 'EUR',
        );

        $oAction = $this->_createAction( 'capture', 'testOrderId', $aRequestParams, $aResponseParams );

        $oAction->process();

        $oOrder = $this->_getOrder( 'testOrderId' );
        $this->assertEquals( '99.87', $oOrder->getCapturedAmount() );

        $aPaymentList = $oOrder->getPaymentList()->getArray();
        $this->assertEquals( 1, count( $aPaymentList ) );

        $oPayment = array_shift( $aPaymentList );
        $this->assertEquals( 'capture', $oPayment->getAction() );
        $this->assertEquals( 'testOrderId', $oPayment->getOrderId() );
        $this->assertEquals( '99.87', $oPayment->getAmount() );
        $this->assertEquals( 'Pending', $oPayment->getStatus() );
        $this->assertEquals( 'EUR', $oPayment->getCurrency() );

        $oPayment->delete();
        $oOrder->delete();
    }

    /**
     * @covers oePayPalOrderActionRefund::process
     * @covers oePayPalOrderActionRefund::getPayPalResponse
     * @covers oePayPalOrderActionRefund::getPayPalRequest
     * @covers oePayPalOrderActionRefund::getAmount
     * @covers oePayPalOrderActionRefund::getType
     */
    public function testActionRefund()
    {
        $aRequestParams = array(
            'refund_amount' => '10',
            'refund_type' => 'Complete',
            'transaction_id' => 'capturedTransaction'
        );
        $aResponseParams = array(
            'REFUNDTRANSACTIONID' => 'TransactionId',
            'REFUNDSTATUS' => 'Pending',
            'GROSSREFUNDAMT' => '9.01',
            'CURRENCYCODE'  => 'EUR',
        );

        $oCapturedPayment = new oePayPalOrderPayment();
        $oCapturedPayment->setOrderId( 'testOrderId' );
        $oCapturedPayment->setTransactionId( 'capturedTransaction' );
        $oCapturedPayment->save();

        $oAction = $this->_createAction( 'refund', 'testOrderId', $aRequestParams, $aResponseParams );

        $oAction->process();

        $oOrder = $this->_getOrder( 'testOrderId' );
        $this->assertEquals( '9.01', $oOrder->getRefundedAmount() );

        $aPaymentList = $oOrder->getPaymentList()->getArray();
        $this->assertEquals( 2, count( $aPaymentList ) );

        $oPayment = array_shift( $aPaymentList );
        $this->assertEquals( 'refund', $oPayment->getAction() );
        $this->assertEquals( 'testOrderId', $oPayment->getOrderId() );
        $this->assertEquals( '9.01', $oPayment->getAmount() );
        $this->assertEquals( 'Pending', $oPayment->getStatus() );
        $this->assertEquals( 'EUR', $oPayment->getCurrency() );

        $oCapturedPayment = array_shift( $aPaymentList );
        $this->assertEquals( '9.01', $oCapturedPayment->getRefundedAmount() );

        $oPayment->delete();
        $oCapturedPayment->delete();
        $oOrder->delete();
    }

    /**
     * @covers oePayPalOrderActionReauthorize::process
     * @covers oePayPalOrderActionReauthorize::getPayPalResponse
     * @covers oePayPalOrderActionReauthorize::getPayPalRequest
     */
    public function ___testActionReauthorize()
    {
        $aResponseParams = array(
            'AUTHORIZATIONID' => 'AuthorizationId',
            'PAYMENTSTATUS' => 'Complete',
        );

        $oAction = $this->_createAction( 'reauthorize', 'testOrderId', array(), $aResponseParams );
        $oAction->process();

        $oOrder = $this->_getOrder( 'testOrderId' );

        $aPaymentList = $oOrder->getPaymentList()->getArray();
        $this->assertEquals( 1, count( $aPaymentList ) );

        $oPayment = array_shift( $aPaymentList );
        $this->assertEquals( 're-authorization', $oPayment->getAction() );
        $this->assertEquals( 'testOrderId', $oPayment->getOrderId() );
        $this->assertEquals( '0.00', $oPayment->getAmount() );
        $this->assertEquals( 'Complete', $oPayment->getStatus() );

        $oPayment->delete();
        $oOrder->delete();
    }

    /**
     * @covers oePayPalOrderActionVoid::process
     * @covers oePayPalOrderActionVoid::getPayPalResponse
     * @covers oePayPalOrderActionVoid::getPayPalRequest
     */
    public function testActionVoid()
    {
        $aResponseParams = array(
            'AUTHORIZATIONID' => 'AuthorizationId',
        );

        $oAction = $this->_createAction( 'void', 'testOrderId', array(), $aResponseParams );
        $oAction->process();

        $oOrder = $this->_getOrder( 'testOrderId' );

        $aPaymentList = $oOrder->getPaymentList()->getArray();
        $this->assertEquals( 1, count( $aPaymentList ) );

        $oPayment = array_shift( $aPaymentList );
        $this->assertEquals( 'void', $oPayment->getAction() );
        $this->assertEquals( 'testOrderId', $oPayment->getOrderId() );
        $this->assertEquals( '0.00', $oPayment->getAmount() );
        $this->assertEquals( 'Voided', $oPayment->getStatus() );

        $oPayment->delete();
        $oOrder->delete();
    }

    /**
     * Returns loaded oePayPalOrderAction object
     *
     * @param string $sAction
     * @param string $sOrderId
     * @param array $aRequestParams
     * @param array $aResponseParams
     *
     * @return oePayPalOrderAction
     */
    protected function _createAction( $sAction, $sOrderId, $aRequestParams, $aResponseParams )
    {
        $oOrder = $this->_getOrder( $sOrderId );
        $oRequest = $this->_getRequestHelper()->getRequest( $aRequestParams );

        $oActionFactory = $this->_getActionFactory( $oRequest, $oOrder );
        $oAction = $oActionFactory->createAction( $sAction );

        $oService = $this->_getPayPalCommunicationHelper()->getCaller( $aResponseParams );
        $oCaptureHandler = $oAction->getHandler();
        $oCaptureHandler->setPayPalService( $oService );

        return $oAction;
    }

    protected function _getRequestHelper()
    {
        return new oePayPalRequestHelper();
    }

    protected function _getPayPalCommunicationHelper()
    {
        return new oePayPalCommunicationHelper();
    }

    /**
     * Returns loaded oePayPalPayPalOrder object with given id
     *
     * @param string $sOrderId
     *
     * @return oePayPalPayPalOrder
     */
    protected function _getOrder( $sOrderId )
    {
        $oOrder = new oePayPalPayPalOrder();
        $oOrder->setOrderId( $sOrderId );
        $oOrder->load();

        return $oOrder;
    }

    protected function _getActionFactory( $oRequest, $oPayPalOrder )
    {
        $oOrder = $this->_createStub( 'oePayPalOxOrder', array( 'getPayPalOrder' => $oPayPalOrder ) );

        $oActionFactory = new oePayPalOrderActionFactory( $oRequest, $oOrder );

        return $oActionFactory;
    }
}