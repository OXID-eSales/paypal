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
 * Testing PayPal caller class.
 */
class CallerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::setParameter()
     */
    public function testSetParameter_setParameter_addedToSetOfParameters()
    {
        $service = new \OxidEsales\PayPalModule\Core\Caller();
        $service->setParameter("testParam", "testValue");
        $parameters = array("testParam" => "testValue");
        $this->assertEquals($parameters, $service->getParameters());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::setParameters()
     */
    public function testSetParameters_setOneOrMoreParameters_addedToSetOfParameters()
    {
        $service = new \OxidEsales\PayPalModule\Core\Caller();

        $service->setParameter("testParam", "testValue");
        $service->setParameters(array("testParam" => "testValue2", "testParam3" => "testValue3", "testParam4" => "testValue4"));
        $service->setParameter("testParam4", "testValue5");

        $result["testParam"] = "testValue2";
        $result["testParam3"] = "testValue3";
        $result["testParam4"] = "testValue5";

        $this->assertEquals($result, $service->getParameters());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::call()
     */
    public function testCall_withMethodSuccessful_returnResponseArray()
    {
        $params = array();
        $params["testParam"] = "testValue";

        $curlParams = $params;
        $curlParams["METHOD"] = "testMethod";

        $url = 'http://url.com';
        $charset = 'latin';

        $response = array('parameter', 'value');

        $caller = new \OxidEsales\PayPalModule\Core\Caller();
        $caller->setParameters($params);

        $caller->setCurl($this->prepareCurl($response, $curlParams, $url, $charset));

        $this->assertEquals($response, $caller->call("testMethod"));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::call()
     */
    public function testCall_withoutMethod_returnResponseArray()
    {
        $params = array();
        $params["testParam"] = "testValue";

        $curlParams = $params;

        $url = 'http://url.com';
        $charset = 'latin';

        $response = array('parameter', 'value');

        $caller = new \OxidEsales\PayPalModule\Core\Caller();
        $caller->setParameters($params);

        $caller->setCurl($this->prepareCurl($response, $curlParams, $url, $charset));

        $this->assertEquals($response, $caller->call());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::call()
     */
    public function testCall_withMethodNotSuccessful_throwException()
    {
        $this->expectException(\OxidEsales\PayPalModule\Core\Exception\PayPalResponseException::class);

        $params = array();
        $params["testParam"] = "testValue";

        $curlParams = $params;

        $url = 'http://url.com';
        $charset = 'latin';

        $response = array('ACK' => 'Failure', 'L_LONGMESSAGE0' => 'message', 'L_ERRORCODE0' => 1);

        $caller = new \OxidEsales\PayPalModule\Core\Caller();
        $caller->setParameters($params);

        $caller->setCurl($this->prepareCurl($response, $curlParams, $url, $charset));
        $this->assertEquals($response, $caller->call());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::getCallBackResponse()
     */
    public function testGetCallBackResponse_setParameters_getResponse()
    {
        $params = array(
            'param1' => 'value1',
            'param2' => 'value2',
        );

        $caller = new \OxidEsales\PayPalModule\Core\Caller();
        $caller->setParameters($params);

        $params['METHOD'] = 'CallbackResponse';
        $params = http_build_query($params);

        $this->assertEquals($params, $caller->getCallBackResponse('CallbackResponse'));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::getPayPalCurl()
     */
    public function testGetCurl_notSet_returnedNewCreated()
    {
        $payPalCaller = new \OxidEsales\PayPalModule\Core\Caller();
        $payPalCurl = $payPalCaller->getCurl();
        $this->assertTrue(is_a($payPalCurl, \OxidEsales\PayPalModule\Core\Curl::class), 'Getter should create PayPal Curl object on request.');
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::getCurl()
     */
    public function testSetCurl_setCurl_returnedSet()
    {
        $curl = new \OxidEsales\PayPalModule\Core\Curl();
        $curl->setConnectionCharset('latin');

        $caller = new \OxidEsales\PayPalModule\Core\Caller();
        $caller->setCurl($curl);
        $curl = $caller->getCurl();
        $this->assertTrue($curl instanceof \OxidEsales\PayPalModule\Core\Curl);
        $this->assertEquals('latin', $curl->getConnectionCharset());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::getPayPalCurl()
     */
    public function testSetRequest_RequestDataSetAsParameters()
    {
        $request = new \OxidEsales\PayPalModule\Model\PayPalRequest\PayPalRequest();
        $request->setData(array('param' => 'data'));

        $caller = new \OxidEsales\PayPalModule\Core\Caller();

        $caller->setRequest($request);
        $this->assertEquals(array('param' => 'data'), $caller->getParameters());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::getCharset()
     */
    public function testGetLogger_notSet_null()
    {
        $caller = new \OxidEsales\PayPalModule\Core\Caller();
        $this->assertNull($caller->getLogger());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::getCharset()
     */
    public function testGetLogger_setLogger_Logger()
    {
        $caller = new \OxidEsales\PayPalModule\Core\Caller();
        $caller->setLogger(new \OxidEsales\PayPalModule\Core\Logger());
        $this->assertTrue($caller->getLogger() instanceof \OxidEsales\PayPalModule\Core\Logger);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::log()
     */
    public function testLog_notSetLogger_LoggerNotUsed()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Caller::class);
        $mockBuilder->setMethods(['getLogger']);
        $caller = $mockBuilder->getMock();
        $caller->expects($this->once())->method('getLogger')->will($this->returnValue(null));
        $caller->log('logMassage');
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::log()
     */
    public function testLog_setLogger_LoggerUsed()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Logger::class);
        $mockBuilder->setMethods(['log']);
        $logger = $mockBuilder->getMock();
        $logger->expects($this->once())->method('log')->with($this->equalTo('logMassage'));

        $caller = new \OxidEsales\PayPalModule\Core\Caller();
        $caller->setLogger($logger);
        $caller->log('logMassage');
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::log() usage in oePayPalCaller::call()
     */
    public function testLogUsage_onCallMethod_atLeastOnce()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Logger::class);
        $mockBuilder->setMethods(['log']);
        $logger = $mockBuilder->getMock();
        $logger->expects($this->atLeastOnce())->method('log');

        $caller = new \OxidEsales\PayPalModule\Core\Caller();
        $caller->setLogger($logger);
        $caller->setParameters(array('k' => 'val'));
        $caller->setCurl($this->prepareCurl(array(), array('k' => 'val'), 'http://url.com', 'utf8'));

        $caller->call();
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\Caller::log() usage in oePayPalCaller::getCallBackResponse()
     */
    public function testLogUsage_onGetCallBackResponseMethod_atLeastOnce()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Logger::class);
        $mockBuilder->setMethods(['log']);
        $logger = $mockBuilder->getMock();
        $logger->expects($this->atLeastOnce())->method('log');

        $caller = new \OxidEsales\PayPalModule\Core\Caller();
        $caller->setLogger($logger);
        $caller->setParameters(array('k' => 'val'));
        $caller->setCurl($this->prepareCurl(array(), array('k' => 'val'), 'http://url.com', 'utf8'));
        $caller->call();
    }

    /**
     * Prepare curl stub
     *
     * @param array  $response   response
     * @param array  $paramsCurl params
     * @param string $url        url
     * @param string $charset    charset
     *
     * @return \OxidEsales\PayPalModule\Core\Curl
     */
    protected function prepareCurl($response, $paramsCurl, $url, $charset)
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Curl::class);
        $mockBuilder->setMethods(['execute', 'setUrlToCall', 'setParameters', 'setDataCharset']);
        $curl = $mockBuilder->getMock();
        $curl->expects($this->once())->method("execute")->will($this->returnValue($response));
        $curl->setDataCharset($charset);
        $curl->setParameters($paramsCurl);
        $curl->setUrlToCall($url);

        return $curl;
    }
}

