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
 * @copyright (C) OXID eSales AG 2003-2018
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
        $transId = '123456';
        $currency = 'LTU';
        $amount = 59.67;
        $type = 'Full';
        $comment = 'Comment';

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder::class);
        $mockBuilder->setMethods(['setTransactionId', 'setAmount', 'setRefundType', 'getRequest', 'setComment']);
        $builder = $mockBuilder->getMock();
        $builder->expects($this->atLeastOnce())->method('setTransactionId')->with($this->equalTo($transId));
        $builder->expects($this->atLeastOnce())->method('setAmount')->with($this->equalTo($amount), $this->equalTo($currency));
        $builder->expects($this->atLeastOnce())->method('setRefundType')->with($this->equalTo($type));
        $builder->expects($this->atLeastOnce())->method('setComment')->with($this->equalTo($comment));
        $builder->expects($this->any())->method('getRequest')->will($this->returnValue(new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest()));

        $data = $this->_createStub(
            'Data', array(
                'getTransactionId' => $transId,
                'getAmount'        => $amount,
                'getType'          => $type,
                'getComment'       => $comment,
                'getCurrency'      => $currency,
            )
        );

        $actionHandler = $this->getActionHandler($data);
        $actionHandler->setPayPalRequestBuilder($builder);

        $actionHandler->getPayPalResponse();
    }

    /**
     * Testing setting of correct request to PayPal service when creating response
     */
    public function testGetPayPalResponse_SetsCorrectRequestToService()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class);
        $payPalRequest = $mockBuilder->getMock();

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['refundTransaction']);
        $checkoutService = $mockBuilder->getMock();
        $checkoutService->expects($this->once())
            ->method('refundTransaction')
            ->with($this->equalTo($payPalRequest))
            ->will($this->returnValue(null));

        $action = $this->getActionHandler();
        $action->setPayPalService($checkoutService);
        $action->setPayPalRequest($payPalRequest);

        $action->getPayPalResponse();
    }

    /**
     * Testing returning of returning response object formed by service
     */
    public function testGetResponse_ReturnsResponseFromService()
    {
        $payPalRequest = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest();
        $payPalResponse = new \OxidEsales\PayPalModule\Model\Response\ResponseDoRefund();

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['refundTransaction']);
        $checkoutService = $mockBuilder->getMock();
        $checkoutService->expects($this->once())
            ->method('refundTransaction')
            ->will($this->returnValue($payPalResponse));

        $action = $this->getActionHandler();
        $action->setPayPalService($checkoutService);
        $action->setPayPalRequest($payPalRequest);

        $this->assertEquals($payPalResponse, $action->getPayPalResponse());
    }

    /**
     * @return \OxidEsales\PayPalModule\Core\PayPalService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getService()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        return $mockBuilder->getMock();
    }

    /**
     * Returns capture action object
     *
     * @param $data
     *
     * @return \OxidEsales\PayPalModule\Model\Action\Handler\OrderRefundActionHandler
     */
    protected function getActionHandler($data = null)
    {
        $action = new \OxidEsales\PayPalModule\Model\Action\Handler\OrderRefundActionHandler($data);

        $action->setPayPalService($this->getService());

        return $action;
    }
}