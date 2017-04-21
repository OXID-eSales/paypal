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

namespace OxidEsales\PayPalModule\Tests\Unit\Model\Action\Handler;

/**
 * Testing \OxidEsales\PayPalModule\Model\Action\Handler\OrderRefundActionHandler class.
 */
class OrderRefundActionHandlerTest extends \OxidEsales\TestingLibrary\UnitTestCase
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
     * Testing building of PayPal request when request is not set
     */
    public function testGetPayPalRequest_RequestIsNotSet_BuildsRequest()
    {
        $sTransId = '123456';
        $sCurrency = 'LTU';
        $dAmount = 59.67;
        $sType = 'Full';
        $sComment = 'Comment';

        $oBuilder = $this->getMock(
            \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder::class,
            array('setTransactionId', 'setAmount', 'setRefundType', 'getRequest', 'setComment')
        );
        $oBuilder->expects($this->atLeastOnce())->method('setTransactionId')->with($this->equalTo($sTransId));
        $oBuilder->expects($this->atLeastOnce())->method('setAmount')->with($this->equalTo($dAmount), $this->equalTo($sCurrency));
        $oBuilder->expects($this->atLeastOnce())->method('setRefundType')->with($this->equalTo($sType));
        $oBuilder->expects($this->atLeastOnce())->method('setComment')->with($this->equalTo($sComment));
        $oBuilder->expects($this->any())->method('getRequest')->will($this->returnValue(new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest()));

        $oData = $this->_createStub(
            'Data', array(
                'getTransactionId' => $sTransId,
                'getAmount'        => $dAmount,
                'getType'          => $sType,
                'getComment'       => $sComment,
                'getCurrency'      => $sCurrency,
            )
        );

        $oActionHandler = $this->_getActionHandler($oData);
        $oActionHandler->setPayPalRequestBuilder($oBuilder);

        $oActionHandler->getPayPalResponse();
    }

    /**
     * Testing setting of correct request to PayPal service when creating response
     */
    public function testGetPayPalResponse_SetsCorrectRequestToService()
    {
        $oPayPalRequest = $this->getMock(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class);

        $oCheckoutService = $this->getMock(\OxidEsales\PayPalModule\Core\PayPalService::class, array('refundTransaction'));
        $oCheckoutService->expects($this->once())
            ->method('refundTransaction')
            ->with($this->equalTo($oPayPalRequest))
            ->will($this->returnValue(null));

        $oAction = $this->_getActionHandler();
        $oAction->setPayPalService($oCheckoutService);
        $oAction->setPayPalRequest($oPayPalRequest);

        $oAction->getPayPalResponse();
    }

    /**
     * Testing returning of returning response object formed by service
     */
    public function testGetResponse_ReturnsResponseFromService()
    {
        $oPayPalRequest = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest();
        $oPayPalResponse = new \OxidEsales\PayPalModule\Model\Response\ResponseDoRefund();

        $oCheckoutService = $this->getMock(\OxidEsales\PayPalModule\Core\PayPalService::class, array('refundTransaction'));
        $oCheckoutService->expects($this->once())
            ->method('refundTransaction')
            ->will($this->returnValue($oPayPalResponse));

        $oAction = $this->_getActionHandler();
        $oAction->setPayPalService($oCheckoutService);
        $oAction->setPayPalRequest($oPayPalRequest);

        $this->assertEquals($oPayPalResponse, $oAction->getPayPalResponse());
    }

    /**
     * @return \OxidEsales\PayPalModule\Core\PayPalService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getService()
    {
        return $this->getMock(\OxidEsales\PayPalModule\Core\PayPalService::class);
    }

    /**
     * Returns capture action object
     *
     * @param $oData
     *
     * @return \OxidEsales\PayPalModule\Model\Action\Handler\OrderRefundActionHandler
     */
    protected function _getActionHandler($oData = null)
    {
        $oAction = new \OxidEsales\PayPalModule\Model\Action\Handler\OrderRefundActionHandler($oData);

        $oAction->setPayPalService($this->_getService());

        return $oAction;
    }
}