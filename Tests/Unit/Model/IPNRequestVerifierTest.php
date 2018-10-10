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

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

/**
 * Testing \OxidEsales\PayPalModule\Model\IPNRequestVerifier class.
 */
class IPNRequestVerifierTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testSetGetRequest()
    {
        $requestSet = new \OxidEsales\PayPalModule\Core\Request();

        $handler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $handler->setRequest($requestSet);

        $requestGet = $handler->getRequest();
        $this->assertEquals($requestSet, $requestGet, 'Getter should return what is set in setter.');
    }

    public function testSetGetShopOwner()
    {
        $shopOwner = 'some@oxid-esales.com';

        $handler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $handler->setShopOwner($shopOwner);

        $this->assertEquals($shopOwner, $handler->getShopOwner(), 'Getter should return what is set in setter.');
    }

    public function testSetGetCommunicationService()
    {
        $ipnCommunicationService = new \OxidEsales\PayPalModule\Core\PayPalService();

        $handler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $handler->setCommunicationService($ipnCommunicationService);

        $this->assertEquals($ipnCommunicationService, $handler->getCommunicationService(), 'Getter should return what is set in setter.');
    }

    public function testGetCommunicationService()
    {
        $handler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();

        $ipnCommunicationService = $handler->getCommunicationService();
        $this->assertTrue(is_a($ipnCommunicationService, \OxidEsales\PayPalModule\Core\PayPalService::class));
    }

    public function testSetGetRequestValidator()
    {
        $paymentValidator = new \OxidEsales\PayPalModule\Model\IPNRequestValidator();

        $handler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $handler->setIPNRequestValidator($paymentValidator);

        $this->assertEquals($paymentValidator, $handler->getIPNRequestValidator(), 'Getter should return what is set in setter.');
    }

    public function testGetRequestValidator()
    {
        $handler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();

        $ipnRequestValidator = $handler->getIPNRequestValidator();
        $this->assertTrue(is_a($ipnRequestValidator, \OxidEsales\PayPalModule\Model\IPNRequestValidator::class));
    }

    public function testSetGetPayPalRequest()
    {
        $payPalRequest = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest();

        $handler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $handler->setPayPalRequest($payPalRequest);

        $this->assertEquals($payPalRequest, $handler->getPayPalRequest(), 'Getter should return what is set in setter.');
    }

    public function testGetPayPalRequest()
    {
        $handler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();

        $this->assertTrue(is_a($handler->getPayPalRequest(), \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class));
    }

    public function testSetGetFailureMessage()
    {
        $failureMessage = 'some message';
        $handler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $handler->setFailureMessage($failureMessage);

        $this->assertEquals($failureMessage, $handler->getFailureMessage(), 'Getter should return what is set in setter.');
    }

    public function providerRequestCorrect()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @dataProvider providerRequestCorrect
     */
    public function testRequestCorrect($validatorSayIsValid)
    {
        $shopOwner = 'someone@oxid-esales.com';
        $payPalRequest = array('zzz' => 'yyy');
        $payPalResponse = array('aaa' => 'bbb');
        $validatorFailureMessage = 'some message';

        // Mock request to simulate PayPal request information.
        $request = $this->prepareRequest($payPalRequest);

        // Mock communication service as we do not want actually call PayPal to check if request is from there.
        // Check iff communication is done with correct request.
        $communicationService = $this->prepareCommunicationService($payPalResponse);

        // Mock Validator to check if it gets request and response with shop owner.
        // Will return if is valid from what is mocked.
        $ipnRequestValidator = $this->preparePayPalValidator($payPalRequest, $payPalResponse, $shopOwner, $validatorSayIsValid, $validatorFailureMessage);

        $handler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $handler->setShopOwner($shopOwner);
        $handler->setRequest($request);
        $handler->setCommunicationService($communicationService);
        $handler->setIPNRequestValidator($ipnRequestValidator);

        $isValidPayPalCall = $handler->requestCorrect();
        $failureMessage = $handler->getFailureMessage();
        $this->assertEquals($validatorSayIsValid, $isValidPayPalCall, 'Validator decide if call is valid.');
        if ($isValidPayPalCall) {
            $this->assertTrue(is_null($failureMessage), 'Failure message is filled only if validation fail.');
        } else {
            $this->assertEquals($validatorFailureMessage, $failureMessage, 'Validator forms validation failure message.');
        }
    }

    protected function prepareRequest($payPalRequest)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Request::class);
        $mockBuilder->setMethods(['getPost']);
        $request = $mockBuilder->getMock();
        $request->expects($this->atLeastOnce())->method('getPost')->will($this->returnValue($payPalRequest));

        return $request;
    }

    protected function prepareCommunicationService($payPalResponse)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $mockBuilder->setMethods(['doVerifyWithPayPal']);
        $communicationService = $mockBuilder->getMock();
        $communicationService->expects($this->atLeastOnce())->method('doVerifyWithPayPal')->will($this->returnValue($payPalResponse));

        return $communicationService;
    }

    protected function preparePayPalValidator($payPalRequest, $payPalResponse, $shopOwner, $validatorSayIsValid, $validatorFailureMessage)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\IPNRequestValidator::class);
        $mockBuilder->setMethods(
            ['setPayPalRequest',
             'setPayPalResponse',
             'setShopOwnerUserName',
             'isValid',
             'getValidationFailureMessage']
        );
        $requestValidator = $mockBuilder->getMock();
        $requestValidator->expects($this->atLeastOnce())->method('setPayPalRequest')->with($payPalRequest);
        $requestValidator->expects($this->atLeastOnce())->method('setPayPalResponse')->with($payPalResponse);
        $requestValidator->expects($this->atLeastOnce())->method('setShopOwnerUserName')->with($shopOwner);
        $requestValidator->expects($this->atLeastOnce())->method('isValid')->will($this->returnValue($validatorSayIsValid));
        $requestValidator->expects($this->any())->method('getValidationFailureMessage')->will($this->returnValue($validatorFailureMessage));

        return $requestValidator;
    }
}
