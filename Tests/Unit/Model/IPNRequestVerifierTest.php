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

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

/**
 * Testing \OxidEsales\PayPalModule\Model\IPNRequestVerifier class.
 */
class IPNRequestVerifierTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testSetGetRequest()
    {
        $oRequestSet = new \OxidEsales\PayPalModule\Core\Request();

        $oHandler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $oHandler->setRequest($oRequestSet);

        $oRequestGet = $oHandler->getRequest();
        $this->assertEquals($oRequestSet, $oRequestGet, 'Getter should return what is set in setter.');
    }

    public function testSetGetShopOwner()
    {
        $sShopOwner = 'some@oxid-esales.com';

        $oHandler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $oHandler->setShopOwner($sShopOwner);

        $this->assertEquals($sShopOwner, $oHandler->getShopOwner(), 'Getter should return what is set in setter.');
    }

    public function testSetGetCommunicationService()
    {
        $oIPNCommunicationService = new \OxidEsales\PayPalModule\Core\PayPalService();

        $oHandler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $oHandler->setCommunicationService($oIPNCommunicationService);

        $this->assertEquals($oIPNCommunicationService, $oHandler->getCommunicationService(), 'Getter should return what is set in setter.');
    }

    public function testGetCommunicationService()
    {
        $oHandler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();

        $oIPNCommunicationService = $oHandler->getCommunicationService();
        $this->assertTrue(is_a($oIPNCommunicationService, \OxidEsales\PayPalModule\Core\PayPalService::class));
    }

    public function testSetGetRequestValidator()
    {
        $oPaymentValidator = new \OxidEsales\PayPalModule\Model\IPNRequestValidator();

        $oHandler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $oHandler->setIPNRequestValidator($oPaymentValidator);

        $this->assertEquals($oPaymentValidator, $oHandler->getIPNRequestValidator(), 'Getter should return what is set in setter.');
    }

    public function testGetRequestValidator()
    {
        $oHandler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();

        $oIPNRequestValidator = $oHandler->getIPNRequestValidator();
        $this->assertTrue(is_a($oIPNRequestValidator, \OxidEsales\PayPalModule\Model\IPNRequestValidator::class));
    }

    public function testSetGetPayPalRequest()
    {
        $oPayPalRequest = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest();

        $oHandler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $oHandler->setPayPalRequest($oPayPalRequest);

        $this->assertEquals($oPayPalRequest, $oHandler->getPayPalRequest(), 'Getter should return what is set in setter.');
    }

    public function testGetPayPalRequest()
    {
        $oHandler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();

        $this->assertTrue(is_a($oHandler->getPayPalRequest(), \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class));
    }

    public function testSetGetFailureMessage()
    {
        $sFailureMessage = 'some message';
        $oHandler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $oHandler->setFailureMessage($sFailureMessage);

        $this->assertEquals($sFailureMessage, $oHandler->getFailureMessage(), 'Getter should return what is set in setter.');
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
    public function testRequestCorrect($blValidatorSayIsValid)
    {
        $sShopOwner = 'someone@oxid-esales.com';
        $aPayPalRequest = array('zzz' => 'yyy');
        $aPayPalResponse = array('aaa' => 'bbb');
        $sValidatorFailureMessage = 'some message';

        // Mock request to simulate PayPal request information.
        $oRequest = $this->_prepareRequest($aPayPalRequest);

        // Mock communication service as we do not want actually call PayPal to check if request is from there.
        // Check iff communication is done with correct request.
        $oCommunicationService = $this->_prepareCommunicationService($aPayPalResponse);

        // Mock Validator to check if it gets request and response with shop owner.
        // Will return if is valid from what is mocked.
        $oIPNRequestValidator = $this->_preparePayPalValidator($aPayPalRequest, $aPayPalResponse, $sShopOwner, $blValidatorSayIsValid, $sValidatorFailureMessage);

        $oHandler = new \OxidEsales\PayPalModule\Model\IPNRequestVerifier();
        $oHandler->setShopOwner($sShopOwner);
        $oHandler->setRequest($oRequest);
        $oHandler->setCommunicationService($oCommunicationService);
        $oHandler->setIPNRequestValidator($oIPNRequestValidator);

        $blIsValidPayPalCall = $oHandler->requestCorrect();
        $sFailureMessage = $oHandler->getFailureMessage();
        $this->assertEquals($blValidatorSayIsValid, $blIsValidPayPalCall, 'Validator decide if call is valid.');
        if ($blIsValidPayPalCall) {
            $this->assertTrue(is_null($sFailureMessage), 'Failure message is filled only if validation fail.');
        } else {
            $this->assertEquals($sValidatorFailureMessage, $sFailureMessage, 'Validator forms validation failure message.');
        }
    }

    protected function _prepareRequest($aPayPalRequest)
    {
        $oRequest = $this->getMock(\OxidEsales\PayPalModule\Core\Request::class, array('getPost'));
        $oRequest->expects($this->atLeastOnce())->method('getPost')->will($this->returnValue($aPayPalRequest));

        return $oRequest;
    }

    protected function _prepareCommunicationService($aPayPalResponse)
    {
        $oCommunicationService = $this->getMock(\OxidEsales\PayPalModule\Core\PayPalService::class, array('doVerifyWithPayPal'));
        $oCommunicationService->expects($this->atLeastOnce())->method('doVerifyWithPayPal')->will($this->returnValue($aPayPalResponse));

        return $oCommunicationService;
    }

    protected function _preparePayPalValidator($aPayPalRequest, $aPayPalResponse, $sShopOwner, $blValidatorSayIsValid, $sValidatorFailureMessage)
    {
        $oRequestValidator = $this->getMock(\OxidEsales\PayPalModule\Model\IPNRequestValidator::class, array('setPayPalRequest', 'setPayPalResponse', 'setShopOwnerUserName', 'isValid', 'getValidationFailureMessage'));
        $oRequestValidator->expects($this->atLeastOnce())->method('setPayPalRequest')->with($aPayPalRequest);
        $oRequestValidator->expects($this->atLeastOnce())->method('setPayPalResponse')->with($aPayPalResponse);
        $oRequestValidator->expects($this->atLeastOnce())->method('setShopOwnerUserName')->with($sShopOwner);
        $oRequestValidator->expects($this->atLeastOnce())->method('isValid')->will($this->returnValue($blValidatorSayIsValid));
        $oRequestValidator->expects($this->any())->method('getValidationFailureMessage')->will($this->returnValue($sValidatorFailureMessage));

        return $oRequestValidator;
    }
}