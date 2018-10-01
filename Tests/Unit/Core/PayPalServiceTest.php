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

namespace OxidEsales\PayPalModule\Tests\Unit\Core;

/**
 * Testing PayPal service class.
 */
class Unit_oePayPal_core_oePayPalServiceTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * \OxidEsales\PayPalModule\Core\Config setter getter test
     */
    public function testGetConfig_configSet_config()
    {
        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $service->setPayPalConfig(new \OxidEsales\PayPalModule\Core\Config());

        $this->assertTrue($service->getPayPalConfig() instanceof \OxidEsales\PayPalModule\Core\Config);
    }

    /**
     * \OxidEsales\PayPalModule\Core\Config getter test
     */
    public function testGetConfig_notSet_config()
    {
        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $this->assertTrue($service->getPayPalConfig() instanceof \OxidEsales\PayPalModule\Core\Config);
    }

    /**
     * \OxidEsales\PayPalModule\Core\Caller setter getter test
     */
    public function testGetCaller_callerSet_definedCaller()
    {
        $caller = new \OxidEsales\PayPalModule\Core\Caller();
        $caller->setParameter('parameter', 'value');

        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $service->setCaller($caller);

        $this->assertTrue($service->getCaller() instanceof \OxidEsales\PayPalModule\Core\Caller);
        $parameters = $service->getCaller()->getParameters();
        $this->assertEquals('value', $parameters['parameter']);
        $this->assertNull($parameters['notDefinedParameter']);
    }

    /**
     * \OxidEsales\PayPalModule\Core\Caller setter getter test
     */
    public function testGetCaller_defaultCaller_callerWithPreparedData()
    {
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', false);
        $this->getConfig()->setConfigParam('sOEPayPalPassword', 'pwd');
        $this->getConfig()->setConfigParam('sOEPayPalUsername', 'usr');
        $this->getConfig()->setConfigParam('sOEPayPalSignature', 'signature');

        $service = new \OxidEsales\PayPalModule\Core\PayPalService();

        $this->assertTrue($service->getCaller() instanceof \OxidEsales\PayPalModule\Core\Caller);

        $parameters = $service->getCaller()->getParameters();
        $this->assertEquals('84.0', $parameters['VERSION']);
        $this->assertEquals('pwd', $parameters['PWD']);
        $this->assertEquals('usr', $parameters['USER']);
        $this->assertEquals('signature', $parameters['SIGNATURE']);
        $this->assertNull($parameters['notDefinedParameter']);

        $curl = $service->getCaller()->getCurl();
        $this->assertTrue($curl instanceof \OxidEsales\PayPalModule\Core\Curl);
        $this->assertEquals('api-3t.paypal.com', $curl->getHost());
        $this->assertEquals('https://api-3t.paypal.com/nvp', $curl->getUrlToCall());
    }

    /**
     * \OxidEsales\PayPalModule\Core\Caller setter getter test
     */
    public function testGetCallerWithLogger_LoggingOff_loggerNotSet()
    {
        $this->getConfig()->setConfigParam('blPayPalLoggerEnabled', false);

        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $this->assertTrue($service->getCaller() instanceof \OxidEsales\PayPalModule\Core\Caller);
        $this->assertNull($service->getCaller()->getLogger());
    }

    /**
     * \OxidEsales\PayPalModule\Core\Caller setter getter test
     */
    public function testGetCallerWithLogger_LoggingOn_loggerIsSet()
    {
        $this->getConfig()->setConfigParam('blPayPalLoggerEnabled', true);

        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $this->assertTrue($service->getCaller() instanceof \OxidEsales\PayPalModule\Core\Caller);
        $this->assertTrue($service->getCaller()->getLogger() instanceof \OxidEsales\PayPalModule\Core\Logger);
    }

    /**
     * \OxidEsales\PayPalModule\Core\Caller setter getter test
     */
    public function testSetExpressCheckout_setRequest_getResponse()
    {
        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $service->setCaller($this->prepareCallerMock($this->prepareRequest(), 'SetExpressCheckout'));

        $response = $service->setExpressCheckout($this->prepareRequest());

        $this->assertTrue($response instanceof \OxidEsales\PayPalModule\Model\Response\ResponseSetExpressCheckout);
        $this->assertEquals(array('parameter' => 'value'), $response->getData());
    }

    /**
     * Request setter getter test
     */
    public function testGetExpressCheckoutDetails_setRequest_getResponse()
    {
        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $service->setCaller($this->prepareCallerMock($this->prepareRequest(), 'GetExpressCheckoutDetails'));

        $response = $service->getExpressCheckoutDetails($this->prepareRequest());

        $this->assertTrue($response instanceof \OxidEsales\PayPalModule\Model\Response\ResponseGetExpressCheckoutDetails);
        $this->assertEquals(array('parameter' => 'value'), $response->getData());
    }

    /**
     * Request/Response setter getter test
     */
    public function testDoExpressCheckoutPayment_setRequest_getResponse()
    {
        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $service->setCaller($this->prepareCallerMock($this->prepareRequest(), 'DoExpressCheckoutPayment'));

        $response = $service->doExpressCheckoutPayment($this->prepareRequest());

        $this->assertTrue($response instanceof \OxidEsales\PayPalModule\Model\Response\ResponseDoExpressCheckoutPayment);
        $this->assertEquals(array('parameter' => 'value'), $response->getData());
    }

    /**
     * Request/Response setter getter test
     */
    public function testDoVoid_setRequest_getResponse()
    {
        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $service->setCaller($this->prepareCallerMock($this->prepareRequest(), 'DoVoid'));

        $response = $service->doVoid($this->prepareRequest());

        $this->assertTrue($response instanceof \OxidEsales\PayPalModule\Model\Response\ResponseDoVoid);
        $this->assertEquals(array('parameter' => 'value'), $response->getData());
    }

    /**
     * \OxidEsales\PayPalModule\Core\Caller setter getter test
     */
    public function testRefundTransaction_setRequest_getResponse()
    {
        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $service->setCaller($this->prepareCallerMock($this->prepareRequest(), 'RefundTransaction'));

        $response = $service->refundTransaction($this->prepareRequest());

        $this->assertTrue($response instanceof \OxidEsales\PayPalModule\Model\Response\ResponseDoRefund);
        $this->assertEquals(array('parameter' => 'value'), $response->getData());
    }

    /**
     * \OxidEsales\PayPalModule\Core\Caller setter getter test
     */
    public function testDoReAuthorization_setRequest_getResponse()
    {
        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $service->setCaller($this->prepareCallerMock($this->prepareRequest(), 'DoReauthorization'));

        $response = $service->doReAuthorization($this->prepareRequest());

        $this->assertTrue($response instanceof \OxidEsales\PayPalModule\Model\Response\ResponseDoReAuthorize);
        $this->assertEquals(array('parameter' => 'value'), $response->getData());
    }

    /**
     * setter getter test
     */
    public function testDoCapture_setRequest_getResponse()
    {
        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $service->setCaller($this->prepareCallerMock($this->prepareRequest(), 'DoCapture'));

        $response = $service->doCapture($this->prepareRequest());

        $this->assertTrue($response instanceof \OxidEsales\PayPalModule\Model\Response\ResponseDoCapture);
        $this->assertEquals(array('parameter' => 'value'), $response->getData());
    }

    /**
     * setter getter test
     */
    public function testDoVerifyWithPayPal_setRequest_getResponse()
    {
        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $service->setCaller($this->prepareCallerMock($this->prepareRequest(), null));

        $response = $service->doVerifyWithPayPal($this->prepareRequest(), 'UTF-8');
        $this->assertTrue($response instanceof \OxidEsales\PayPalModule\Model\Response\ResponseDoVerifyWithPayPal);
        $this->assertEquals(array('parameter' => 'value'), $response->getData());
    }

    /**
     * Test url and header of IPN postback call.
     */
    public function testDoVerifyWithPayPalCurl()
    {
        //switch on sandbox more
        $this->getConfig()->setConfigParam('blOEPayPalSandboxMode', true);

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Curl::class);
        $mockBuilder->setMethods(['setHost', 'setUrlToCall']);
        $mockedCurl = $mockBuilder->getMock();
        $mockedCurl->expects($this->once())
            ->method('setHost')
            ->with($this->equalTo(\OxidEsales\PayPalModule\Core\IpnConfig::OEPAYPAL_IPN_SANDBOX_HOST));
        $mockedCurl->expects($this->once())
            ->method('setUrlToCall')
            ->with($this->equalTo(\OxidEsales\PayPalModule\Core\IpnConfig::OEPAYPAL_SANDBOX_IPN_CALLBACK_URL . '&cmd=_notify-validate'));

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Caller::class);
        $mockBuilder->setMethods(['call', 'getCurl']);
        $mockedCaller = $mockBuilder->getMock();
        $mockedCaller->expects($this->once())
                     ->method('getCurl')
                     ->will($this->returnValue($mockedCurl));

        $service = new \OxidEsales\PayPalModule\Core\PayPalService();
        $service->setCaller($mockedCaller);

        $response = $service->doVerifyWithPayPal($this->prepareRequest(), 'UTF-8');
        $this->assertTrue($response instanceof \OxidEsales\PayPalModule\Model\Response\ResponseDoVerifyWithPayPal);
    }

    /**
     * data provider
     *
     * @return array
     */
    public function ipnPostbackEncodingProvider()
    {
        $data = array();

        $data['ascii_IPN'][0]['ipn'] = array(
            'payment_type'   => 'instant',
            'payment_date'   => 'Tue_May_26_2015_21:57:49_GMT_0200_(CEST)',
            'payment_status' => 'Completed',
            'payer_status'   => 'verified',
            'first_name'     => 'Bla',
            'last_name'      => 'Foo',
            'payer_email'    => 'buyer@paypalsandbox_com',
            'payer_id'       => 'TESTBUYERID01',
            'address_name'   => 'Bla_Foo',
            'address_city'   => 'Hamburg',
            'address_street' => 'meinestrasse_123',
            'charset'        => 'ISO-8859-1'
        );
        $expectedPostback = 'payment_type=instant&payment_date=Tue_May_26_2015_21%3A57%3A49_GMT_0200_%28CEST%29&' .
                            'payment_status=Completed&payer_status=verified&first_name=Bla&last_name=Foo&payer_email=' .
                            'buyer%40paypalsandbox_com&payer_id=TESTBUYERID01&address_name=Bla_Foo&address_city=' .
                            'Hamburg&address_street=meinestrasse_123&charset=ISO-8859-1';

        $data['ascii_IPN'][0]['expected_postback'] = $expectedPostback;
        $data['ascii_IPN'][0]['expected_data_encoding'] = 'ascii';

        $data['utf8_IPN'][0]['ipn'] = array(
            'payment_type'   => 'instant',
            'payment_date'   => 'Tue_May_26_2015_21:57:49_GMT_0200_(CEST)',
            'payment_status' => 'Completed',
            'payer_status'   => 'verified',
            'first_name'     => 'Bla',
            'last_name'      => 'Foo',
            'payer_email'    => 'buyer@paypalsandbox_com',
            'payer_id'       => 'TESTBUYERID01',
            'address_name'   => 'Bla_Foo',
            'address_city'   => 'Литовские',
            'address_street' => 'Blafööstraße_123',
            'charset'        => 'UTF-8'
        );
        $expectedPostback = 'payment_type=instant&payment_date=Tue_May_26_2015_21%3A57%3A49_GMT_0200_%28CEST%29&' .
                            'payment_status=Completed&payer_status=verified&first_name=Bla&last_name=Foo&payer_email=' .
                            'buyer%40paypalsandbox_com&payer_id=TESTBUYERID01&address_name=Bla_Foo&address_city=' .
                            '%D0%9B%D0%B8%D1%82%D0%BE%D0%B2%D1%81%D0%BA%D0%B8%D0%B5&address_street=' .
                            'Blaf%C3%B6%C3%B6stra%C3%9Fe_123&charset=UTF-8';
        $data['utf8_IPN'][0]['expected_postback'] = $expectedPostback;
        $data['utf8_IPN'][0]['expected_data_encoding'] = 'utf-8';

        //see http://en.wikipedia.org/wiki/Windows-1252
        $data['windows-1252_IPN'][0]['ipn'] = array(
            'payment_type'   => 'instant',
            'payment_date'   => 'Tue_May_26_2015_21:57:49_GMT_0200_(CEST)',
            'payment_status' => 'Completed',
            'payer_status'   => 'verified',
            'first_name'     => 'Bla',
            'last_name'      => 'Foo',
            'payer_email'    => 'buyer@paypalsandbox_com',
            'payer_id'       => 'TESTBUYERID01',
            'address_name'   => 'Bla_Foo',
            'address_city'   => 'Hamburg',
            'address_street' => 'Blafööstraße_123',
            'charset'        => 'windows-1252'
        );
        $expectedPostback = 'payment_type=instant&payment_date=Tue_May_26_2015_21%3A57%3A49_GMT_0200_%28CEST%29&' .
                            'payment_status=Completed&payer_status=verified&first_name=Bla&last_name=Foo&payer_email=' .
                            'buyer%40paypalsandbox_com&payer_id=TESTBUYERID01&address_name=Bla_Foo&address_city=' .
                            'Hamburg&address_street=Blaf%C3%B6%C3%B6stra%C3%9Fe_123&charset=windows-1252';
        $data['windows-1252_IPN'][0]['expected_postback'] = $expectedPostback;
        $data['windows-1252_IPN'][0]['expected_data_encoding'] = 'windows-1252';

        return $data;
    }

    /**
     * Check encoding of test data.
     * Test data will be reused later.
     *
     * @dataProvider ipnPostbackEncodingProvider
     */
    public function testTestDataEncoding($data)
    {
        foreach ($data['ipn'] as $key => $value) {
            $this->assertTrue(mb_check_encoding($value, $data['expected_data_encoding']));
        }
    }

    /**
     * Test PayPal caller service for IPN verification.
     * Verify that charsets are set as required.
     *
     * @dataProvider ipnPostbackEncodingProvider
     */
    public function testPayPalIPNConnectionCharset($data)
    {
        $curl = $this->prepareCurlDoVerifyWithPayPal($data);
        $this->assertEquals($data['charset'], $curl->getConnectionCharset());
    }

    /**
     * Test PayPal caller service for IPN verification.
     * Verify that charsets are set as required.
     *
     * @dataProvider ipnPostbackEncodingProvider
     */
    public function testPayPalIPNDataCharset($data)
    {
        $curl = $this->prepareCurlDoVerifyWithPayPal($data);
        $this->assertEquals($data['charset'], $curl->getDataCharset());
    }


    /**
     * Test PayPal caller service for IPN verification.
     * Have a look what the curl object does with parameters,
     * data should not have been changed so far
     *
     * @dataProvider ipnPostbackEncodingProvider
     */
    public function testPayPalIPNCurlParameters($data)
    {
        $curl = $this->prepareCurlDoVerifyWithPayPal($data);
        $curlParameters = $curl->getParameters();
        $this->assertEquals($data['ipn'], $curlParameters );

    }

    /**
     * Test PayPal caller service for IPN verification.
     * \OxidEsales\PayPalModule\Core\PayPalService::doVerifyWithPayPal
     *
     * @dataProvider ipnPostbackEncodingProvider
     */
    public function testPayPalIPNPostbackEncoding($data)
    {
        //Rule for PayPal IPN verification is to give them back whatever comes in and prepend
        //cmd=_notify-validate. As long as the shop does not try to reencode the original
        //request, all is well.
        //The encoding in PayPal backend should be set according to shop nevertheless.
        //see http://blog.scrobbld.com/paypal/change-encoding-in-your-paypal-account/
        //As the address data from IPN requests is not used for shop currently, wrong encoding
        // does not matter here. It might matter for PayPalExpress checkout, as that one sets the
        // delivery address that's stored at PayPal.

        $curl = $this->prepareCurlDoVerifyWithPayPal($data);

        $query = $curl->getQuery();
        $this->assertEquals($data['expected_postback'], $query);
    }

    /**
     * Prepare \OxidEsales\PayPalModule\Core\Caller stub
     *
     * @param \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest $request    request
     * @param string                                                     $methodName method name
     *
     * @return \OxidEsales\PayPalModule\Core\Caller
     */
    protected function prepareCallerMock($request, $methodName = null)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Caller::class);
        $mockBuilder->setMethods(['setRequest', 'call']);
        $caller = $mockBuilder->getMock();
        $caller->expects($this->once())->method("setRequest")->with($this->equalTo($request));
        if (!is_null($methodName)) {
            $caller->expects($this->once())->method("call")
                ->with($this->equalTo($methodName))
                ->will($this->returnValue(array('parameter' => 'value')));
        } else {
            $caller->expects($this->once())->method("call")
                ->will($this->returnValue(array('parameter' => 'value')));
        }

        return $caller;
    }

    /**
     * Prepare PayPal request
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest
     */
    protected function prepareRequest()
    {
        $request = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest();
        $request->setData(array('rParameter' => 'rValue'));

        return $request;
    }

    /**
     * Provide a mocked \OxidEsales\PayPalModule\Core\Config
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPayPalConfigMock()
    {
        $mocks = array( 'getUserEmail'                         => 'devbiz_api1.oxid-efire.com',
                        'isExpressCheckoutInMiniBasketEnabled' => '1',
                        'isStandardCheckoutEnabled'            => '1',
                        'isExpressCheckoutEnabled'             => '1',
                        'isLoggingEnabled'                     => '1',
                        'finalizeOrderOnPayPalSide'            => '1',
                        'isSandboxEnabled'                     => '1',
                        'getPassword'                          => '1382082575',
                        'getSignature'                         => 'AoRXRr2UPUu8BdpR8rbnhMMeSk9rAmMNTW2T1o9INg0KUgsqW4qcuhS5',
                        'getTransactionMode'                   => 'AUTHORIZATION',
        );

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Config::class);
        $mockBuilder->setMethods(array_keys($mocks));
        $paypalConfig = $mockBuilder->getMock();

        foreach ($mocks as $method => $returnValue) {
            $paypalConfig->expects($this->any())->method($method)->will($this->returnValue($returnValue));
        }

        return $paypalConfig;
    }

    /**
     * Test helper for preparing curl object that
     * ran IPN verification with PayPal.
     *
     * @param $data
     *
     * @return mixed
     */
    private function prepareCurlDoVerifyWithPayPal($data)
    {
        $paypalPayPalRequest = oxNew(\OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest::class);
        foreach ($data['ipn'] as $key => $value) {
            $paypalPayPalRequest->setParameter($key, $value);
        }

        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Caller::class);
        $mockBuilder->setMethods(['call']);
        $caller = $mockBuilder->getMock();
        $caller->expects($this->once())->method('call')->will($this->returnValue(array()));
        $caller->setRequest($paypalPayPalRequest);

        $curl = $caller->getCurl();
        $curl->setParameters($caller->getParameters());

        $paypalService = oxNew(\OxidEsales\PayPalModule\Core\PayPalService::class);
        $paypalService->setCaller($caller);
        $paypalService->setPayPalConfig($this->getPayPalConfigMock());
        $paypalService->doVerifyWithPayPal($paypalPayPalRequest, $data['charset']);

        return $curl;
    }
}
