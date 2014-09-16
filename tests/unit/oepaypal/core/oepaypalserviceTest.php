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

require_once realpath(".") . '/unit/OxidTestCase.php';

/**
 * Testing oePayPalService class.
 */
class Unit_oePayPal_core_oePayPalServiceTest extends OxidTestCase
{
    /**
     * oePayPalConfig setter getter test
     */
    public function testGetConfig_configSet_config()
    {
        $oService = new oePayPalService();
        $oService->setPayPalConfig(new oePayPalConfig());

        $this->assertTrue($oService->getPayPalConfig() instanceof oePayPalConfig);
    }

    /**
     * oePayPalConfig getter test
     */
    public function testGetConfig_notSet_config()
    {
        $oService = new oePayPalService();
        $this->assertTrue($oService->getPayPalConfig() instanceof oePayPalConfig);
    }

    /**
     * oePayPalCaller setter getter test
     */
    public function testGetCaller_callerSet_definedCaller()
    {
        $oCaller = new oePayPalCaller();
        $oCaller->setParameter('parameter', 'value');

        $oService = new oePayPalService();
        $oService->setCaller($oCaller);

        $this->assertTrue($oService->getCaller() instanceof oePayPalCaller);
        $aParameters = $oService->getCaller()->getParameters();
        $this->assertEquals('value', $aParameters['parameter']);
        $this->assertNull($aParameters['notDefinedParameter']);
    }

    /**
     * oePayPalCaller setter getter test
     */
    public function testGetCaller_defaultCaller_callerWithPreparedData()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', false);
        $this->getConfig()->setConfigParam('sOEPayPalPassword', 'pwd');
        $this->getConfig()->setConfigParam('sOEPayPalUsername', 'usr');
        $this->getConfig()->setConfigParam('sOEPayPalSignature', 'signature');

        $oService = new oePayPalService();

        $this->assertTrue($oService->getCaller() instanceof oePayPalCaller);

        $aParameters = $oService->getCaller()->getParameters();
        $this->assertEquals('84.0', $aParameters['VERSION']);
        $this->assertEquals('pwd', $aParameters['PWD']);
        $this->assertEquals('usr', $aParameters['USER']);
        $this->assertEquals('signature', $aParameters['SIGNATURE']);
        $this->assertNull($aParameters['notDefinedParameter']);

        $oCurl = $oService->getCaller()->getCurl();
        $this->assertTrue($oCurl instanceof oePayPalCurl);
        $this->assertEquals('api-3t.paypal.com', $oCurl->getHost());
        $this->assertEquals('https://api-3t.paypal.com/nvp', $oCurl->getUrlToCall());
    }

    /**
     * oePayPalCaller setter getter test
     */
    public function testGetCallerWithLogger_LoggingOff_loggerNotSet()
    {
        $this->getConfig()->setConfigParam('blPayPalLoggerEnabled', false);

        $oService = new oePayPalService();
        $this->assertTrue($oService->getCaller() instanceof oePayPalCaller);
        $this->assertNull($oService->getCaller()->getLogger());
    }

    /**
     * oePayPalCaller setter getter test
     */
    public function testGetCallerWithLogger_LoggingOn_loggerIsSet()
    {
        $this->getConfig()->setConfigParam('blPayPalLoggerEnabled', true);

        $oService = new oePayPalService();
        $this->assertTrue($oService->getCaller() instanceof oePayPalCaller);
        $this->assertTrue($oService->getCaller()->getLogger() instanceof oePayPalLogger);
    }

    /**
     * oePayPalCaller setter getter test
     */
    public function testSetExpressCheckout_setRequest_getResponse()
    {
        $oService = new oePayPalService();
        $oService->setCaller($this->_prepareCallerMock($this->_prepareRequest(), 'SetExpressCheckout'));

        $oResponse = $oService->setExpressCheckout($this->_prepareRequest());

        $this->assertTrue($oResponse instanceof oePayPalResponseSetExpressCheckout);
        $this->assertEquals(array('parameter' => 'value'), $oResponse->getData());
    }

    /**
     * oePayPalCaller setter getter test
     */
    public function testGetExpressCheckoutDetails_setRequest_getResponse()
    {
        $oService = new oePayPalService();
        $oService->setCaller($this->_prepareCallerMock($this->_prepareRequest(), 'GetExpressCheckoutDetails'));

        $oResponse = $oService->getExpressCheckoutDetails($this->_prepareRequest());

        $this->assertTrue($oResponse instanceof oePayPalResponseGetExpressCheckoutDetails);
        $this->assertEquals(array('parameter' => 'value'), $oResponse->getData());
    }

    /**
     * oePayPalCaller setter getter test
     */
    public function testDoExpressCheckoutPayment_setRequest_getResponse()
    {
        $oService = new oePayPalService();
        $oService->setCaller($this->_prepareCallerMock($this->_prepareRequest(), 'DoExpressCheckoutPayment'));

        $oResponse = $oService->doExpressCheckoutPayment($this->_prepareRequest());

        $this->assertTrue($oResponse instanceof oePayPalResponseDoExpressCheckoutPayment);
        $this->assertEquals(array('parameter' => 'value'), $oResponse->getData());
    }

    /**
     * oePayPalCaller setter getter test
     */
    public function testDoVoid_setRequest_getResponse()
    {
        $oService = new oePayPalService();
        $oService->setCaller($this->_prepareCallerMock($this->_prepareRequest(), 'DoVoid'));

        $oResponse = $oService->doVoid($this->_prepareRequest());

        $this->assertTrue($oResponse instanceof oePayPalResponseDoVoid);
        $this->assertEquals(array('parameter' => 'value'), $oResponse->getData());
    }

    /**
     * oePayPalCaller setter getter test
     */
    public function testRefundTransaction_setRequest_getResponse()
    {
        $oService = new oePayPalService();
        $oService->setCaller($this->_prepareCallerMock($this->_prepareRequest(), 'RefundTransaction'));

        $oResponse = $oService->refundTransaction($this->_prepareRequest());

        $this->assertTrue($oResponse instanceof oePayPalResponseDoRefund);
        $this->assertEquals(array('parameter' => 'value'), $oResponse->getData());
    }

    /**
     * oePayPalCaller setter getter test
     */
    public function testDoReAuthorization_setRequest_getResponse()
    {
        $oService = new oePayPalService();
        $oService->setCaller($this->_prepareCallerMock($this->_prepareRequest(), 'DoReauthorization'));

        $oResponse = $oService->doReAuthorization($this->_prepareRequest());

        $this->assertTrue($oResponse instanceof oePayPalResponseDoReAuthorize);
        $this->assertEquals(array('parameter' => 'value'), $oResponse->getData());
    }

    /**
     * oePayPalCaller setter getter test
     */
    public function testDoCapture_setRequest_getResponse()
    {
        $oService = new oePayPalService();
        $oService->setCaller($this->_prepareCallerMock($this->_prepareRequest(), 'DoCapture'));

        $oResponse = $oService->doCapture($this->_prepareRequest());

        $this->assertTrue($oResponse instanceof oePayPalResponseDoCapture);
        $this->assertEquals(array('parameter' => 'value'), $oResponse->getData());
    }

    /**
     * oePayPalCaller setter getter test
     */
    public function testDoVerifyWithPayPal_setRequest_getResponse()
    {
        $oService = new oePayPalService();
        $oService->setCaller($this->_prepareCallerMock($this->_prepareRequest(), null));

        $oResponse = $oService->doVerifyWithPayPal($this->_prepareRequest(), 'UTF-8');
        $this->assertTrue($oResponse instanceof oePayPalResponseDoVerifyWithPayPal);
        $this->assertEquals(array('parameter' => 'value'), $oResponse->getData());
    }

    /**
     * Prepare oePayPalCaller stub
     *
     * @param oePayPalPayPalRequest $oRequest    request
     * @param string                $sMethodName method name
     *
     * @return oePayPalCaller
     */
    protected function _prepareCallerMock($oRequest, $sMethodName = null)
    {
        $oCaller = $this->getMock("oePayPalCaller", array("setRequest", 'call'));
        $oCaller->expects($this->once())->method("setRequest")->with($this->equalTo($oRequest));
        if (!is_null($sMethodName)) {
            $oCaller->expects($this->once())->method("call")
                ->with($this->equalTo($sMethodName))
                ->will($this->returnValue(array('parameter' => 'value')));
        } else {
            $oCaller->expects($this->once())->method("call")
                ->will($this->returnValue(array('parameter' => 'value')));
        }

        return $oCaller;
    }

    /**
     * Prepare PayPal request
     *
     * @return oePayPalPayPalRequest
     */
    protected function _prepareRequest()
    {
        $oRequest = new oePayPalPayPalRequest();
        $oRequest->setData(array('rParameter' => 'rValue'));

        return $oRequest;
    }
}