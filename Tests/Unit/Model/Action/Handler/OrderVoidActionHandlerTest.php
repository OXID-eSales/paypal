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
 * Testing \OxidEsales\PayPalModule\Model\Action\Handler\OrderVoidActionHandler class.
 */
class OrderVoidActionHandlerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Testing building of PayPal request when request is not set
     */
    public function testGetPayPalRequest_RequestIsNotSet_BuildsRequest()
    {
        $authId = '123456';
        $currency = 'LTU';
        $amount = 59.67;
        $comment = "Comment";

        $data = $this->_createStub(
            'Data', array(
                'getAuthorizationId' => $authId,
                'getAmount'          => $amount,
                'getComment'         => $comment,
                'getCurrency'        => $currency,
            )
        );
        $actionHandler = $this->getActionHandler($data);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequestBuilder::class);
        $mockBuilder->setMethods(['setAuthorizationId', 'setAmount', 'setCompleteType', 'getRequest', 'setComment']);
        $builder = $mockBuilder->getMock();
        $builder->expects($this->once())->method('setAuthorizationId')->with($this->equalTo($authId));
        $builder->expects($this->once())->method('setAmount')->with($this->equalTo($amount), $this->equalTo($currency));
        $builder->expects($this->once())->method('setComment')->with($this->equalTo($comment));
        $builder->expects($this->once())->method('getRequest')->will($this->returnValue(new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest()));

        $actionHandler->setPayPalRequestBuilder($builder);

        $actionHandler->getPayPalRequest();
    }

    /**
     * Testing setting of correct request to PayPal service when creating response
     */
    public function testGetPayPalResponse_SetsCorrectRequestToService()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class);
        $payPalRequest = $mockBuilder->getMock();

        $actionHandler = $this->getActionHandler();
        $actionHandler->setPayPalRequest($payPalRequest);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['doVoid']);
        $checkoutService = $mockBuilder->getMock();
        $checkoutService->expects($this->once())
            ->method('doVoid')
            ->with($this->equalTo($payPalRequest))
            ->will($this->returnValue(null));

        $actionHandler->setPayPalService($checkoutService);

        $actionHandler->getPayPalResponse();
    }

    /**
     * @return \OxidEsales\PayPalModule\Core\PayPalService|PHPUnit_Framework_MockObject_MockObject
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
     * @return \OxidEsales\PayPalModule\Model\Action\OrderCaptureAction
     */
    protected function getActionHandler($data = null)
    {
        $action = new \OxidEsales\PayPalModule\Model\Action\Handler\OrderVoidActionHandler($data);
        $action->setPayPalService($this->getService());

        return $action;
    }
}