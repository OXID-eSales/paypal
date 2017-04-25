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



/**
 * Testing \OxidEsales\PayPalModule\Model\Action\OrderRefundAction class.
 */
class OrderRefundActionTest extends \OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     *  Setup: Prepare data - create need tables
     */
    public function setUp()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpaymentcomments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_orderpayments`');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('TRUNCATE `oepaypal_order`');
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
            'getTransactionId' => $sTransactionId,
            'getCorrelationId' => $sCorrelationId,
            'getPaymentStatus' => $sStatus,
            'getRefundAmount'  => $dAmount,
            'getCurrency'      => $sCurrency,
        );
        $oPayPalResponse = $this->_createStub(\OxidEsales\PayPalModule\Model\Response\ResponseDoRefund::class, $aPayPalResponseMethods);

        $oPayment = new \OxidEsales\PayPalModule\Model\OrderPayment();
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
        $oUtilsDate = $this->getMock(\OxidEsales\Eshop\Core\UtilsDate::class, array('getTime'));
        $oUtilsDate->expects($this->any())->method('getTime')->will($this->returnValue(1410431540));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\UtilsDate::class, $oUtilsDate);
        $sComment = 'testComment';

        $oComment = new \OxidEsales\PayPalModule\Model\OrderPaymentComment();
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

        $oData = $this->_getData();
        $oData->expects($this->any())->method('getComment')->will($this->returnValue($sComment));
        $oAction = $this->_getAction($oPayPalResponse, $oOrder, $oData);

        $oAction->process();
    }


    /**
     * Returns payment object
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPayment
     */
    protected function _getPayment()
    {
        $oPayment = $this->getMock(\OxidEsales\PayPalModule\Model\OrderPayment::class, array('addComment'));

        return $oPayment;
    }

    /**
     * Returns payment list
     *
     * @param array $aTestMethods
     *
     * @return \OxidEsales\PayPalModule\Model\OrderPaymentList
     */
    protected function _getPaymentList($aTestMethods = array())
    {
        $aMethods = array('addPayment' => $this->_getPayment());
        $oPaymentList = $this->_createStub(\OxidEsales\PayPalModule\Model\OrderPaymentList::class, $aMethods, $aTestMethods);

        return $oPaymentList;
    }

    /**
     * Returns order
     *
     * @param array $aTestMethods
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected function _getOrder($aTestMethods = array())
    {
        $aMethods = array('getPaymentList' => $this->_getPaymentList());
        $oOrder = $this->_createStub(\OxidEsales\PayPalModule\Model\PayPalOrder::class, $aMethods, $aTestMethods);

        return $oOrder;
    }

    /**
     * Retruns basic PayPal response object
     *
     * @param array $aTestMethods
     *
     * @return \OxidEsales\PayPalModule\Model\Response\ResponseDoCapture
     */
    protected function _getPayPalResponse($aTestMethods = array())
    {
        $aMethods = array('getRefundAmount', 'getPaymentStatus', 'getTransactionId', 'getCurrency');
        $aMockedMethods = array_unique(array_merge($aMethods, $aTestMethods));

        $oOrder = $this->getMock(\OxidEsales\PayPalModule\Model\Response\ResponseDoRefund::class, $aMockedMethods);

        return $oOrder;
    }

    /**
     * Returns capture action data object
     *
     * @param $aMethods
     *
     * @return \OxidEsales\PayPalModule\Model\Action\Data\OrderCaptureActionData
     */
    protected function _getData($aMethods = array())
    {
        $oData = $this->_createStub(\OxidEsales\PayPalModule\Model\Action\Data\OrderRefundActionData::class, $aMethods);

        return $oData;
    }


    /**
     * Returns capture action object
     *
     * @param $oPayPalResponse
     * @param $oOrder
     * @param $oData
     *
     * @return \OxidEsales\PayPalModule\Model\Action\OrderCaptureAction
     */
    protected function _getAction($oPayPalResponse, $oOrder, $oData = null)
    {
        $oData = $oData ? $oData : $this->_getData();
        $oData->expects($this->any())->method('getPaymentBeingRefunded')->will($this->returnValue(new \OxidEsales\PayPalModule\Model\OrderPayment()));

        $oHandler = $this->_createStub('ActionHandler', array('getPayPalResponse' => $oPayPalResponse, 'getData' => $oData));

        $oAction = $this->getMock(\OxidEsales\PayPalModule\Model\Action\OrderRefundAction::class, array('getDate'), array($oHandler, $oOrder));
        $oAction->expects($this->any())->method('getDate')->will($this->returnValue('date'));

        return $oAction;
    }
}