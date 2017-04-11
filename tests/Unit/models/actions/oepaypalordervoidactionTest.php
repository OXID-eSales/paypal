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
 * Testing oePayPalOrderVoidAction class.
 */
class Unit_oePayPal_Models_Actions_oePayPalOrderVoidActionTest extends \OxidEsales\TestingLibrary\UnitTestCase
{

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

        $oAction = $this->_getAction($oPayPalResponse, $oOrder);

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
            'getCorrelationId'   => $sCorrelationId
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

        $oAction = $this->_getAction($oPayPalResponse, $oOrder);

        $oAction->process();
    }

    /**
     * Testing saving of order after updating it
     */
    public function testProcess_CommentAdded()
    {
        $oUtilsDate = $this->getMock(\OxidEsales\Eshop\Core\UtilsDate::class, array('getTime'));
        $oUtilsDate->expects($this->any())->method('getTime')->will($this->returnValue(time()));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\UtilsDate::class, $oUtilsDate);

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

        $oData = $this->_getData();
        $oData->expects($this->any())->method('getComment')->will($this->returnValue($sComment));

        $oAction = $this->_getAction($oPayPalResponse, $oOrder, $oData);

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
        $aMethods = array('getRefundAmount', 'getPaymentStatus', 'getTransactionId', 'getCurrency');
        $aMockedMethods = array_unique(array_merge($aMethods, $aTestMethods));

        $oOrder = $this->getMock('oePayPalResponseDoVoid', $aMockedMethods);

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
        $oData = $this->_createStub('oePayPalOrderVoidActionData', $aMethods);

        return $oData;
    }

    /**
     * Returns capture action object
     *
     * @param $oPayPalResponse
     * @param $oOrder
     * @param $oData
     *
     * @return oePayPalOrderCaptureAction
     */
    protected function _getAction($oPayPalResponse, $oOrder, $oData = null)
    {
        $oData = $oData ? $oData : $this->_getData();
        $oHandler = $this->_createStub('ActionHandler', array('getPayPalResponse' => $oPayPalResponse, 'getData' => $oData));

        $oAction = $this->getMock('oePayPalOrderVoidAction', array('getDate'), array($oHandler, $oOrder));
        $oAction->expects($this->any())->method('getDate')->will($this->returnValue('date'));

        return $oAction;
    }
}