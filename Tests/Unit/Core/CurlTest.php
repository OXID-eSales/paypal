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
 * Testing curl class.
 */
class CurlTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Checks if function returns null when nothing is set.
     */
    public function testGetHost_notSet_null()
    {
        $curl = new \OxidEsales\PayPalModule\Core\Curl();
        $this->assertNull($curl->getHost(), 'Default value must be null.');
    }

    /**
     * Check if getter returns what is set in setter.
     */
    public function testGetHost_setHost_host()
    {
        $host = 'someHost';

        $curl = new \OxidEsales\PayPalModule\Core\Curl();
        $curl->setHost($host);

        $this->assertEquals($host, $curl->getHost(), 'Check if getter returns what is set in setter.');
    }

    /**
     * Checks if returned header is correct when header was not set and host was set.
     */
    public function testGetHeader_headerNotSetAndHostSet_headerWithHost()
    {
        $host = 'someHost';
        $expectedHeader = array(
            'POST /cgi-bin/webscr HTTP/1.1',
            'Content-Type: application/x-www-form-urlencoded',
            'Host: ' . $host,
            'Connection: close'
        );
        $curl = new \OxidEsales\PayPalModule\Core\Curl();
        $curl->setHost($host);

        $this->assertEquals($expectedHeader, $curl->getHeader(), 'Header must be formed from set host.');
    }

    /**
     * Checks if returned header is correct when header was not set and host was not set.
     */
    public function testGetHeader_headerNotSetAndHostNotSet_headerWithoutHost()
    {
        $expectedHeader = array(
            'POST /cgi-bin/webscr HTTP/1.1',
            'Content-Type: application/x-www-form-urlencoded',
            'Connection: close'
        );
        $curl = new \OxidEsales\PayPalModule\Core\Curl();

        $this->assertEquals($expectedHeader, $curl->getHeader(), 'Header must be without host as host not set.');
    }

    /**
     * Checks if returned header is correct when header was set and host was set.
     */
    public function testGetHeader_headerSetAndHostSet_headerFromSet()
    {
        $host = 'someHost';
        $header = array('Test header');
        $curl = new \OxidEsales\PayPalModule\Core\Curl();
        $curl->setHost($host);
        $curl->setHeader($header);

        $this->assertEquals($header, $curl->getHeader(), 'Header must be same as set header.');
    }

    /**
     * Checks if returned header is correct when header was set and host was not set.
     */
    public function testGetHeader_headerSetAndHostNotSet_headerWithoutHost()
    {
        $header = array('Test header');
        $curl = new \OxidEsales\PayPalModule\Core\Curl();
        $curl->setHeader($header);

        $this->assertEquals($header, $curl->getHeader(), 'Header must be same as set header.');
    }

    /**
     * Test Curl::setConnectionCharset()
     */
    public function testSetConnectionCharset_set_get()
    {
        $curl = new \OxidEsales\PayPalModule\Core\Curl();
        $curl->setConnectionCharset('ISO-8859-1');

        $this->assertEquals('ISO-8859-1', $curl->getConnectionCharset());
    }

    /**
     * Test Curl::getConnectionCharset()
     */
    public function testGetConnectionCharset_notSet_UTF()
    {
        $curl = new \OxidEsales\PayPalModule\Core\Curl();
        $this->assertEquals('UTF-8', $curl->getConnectionCharset());
    }

    /**
     * Test Curl::setDataCharset()
     */
    public function testSetDataCharset_set_get()
    {
        $curl = new \OxidEsales\PayPalModule\Core\Curl();
        $curl->setDataCharset('ISO-8859-1');

        $this->assertEquals('ISO-8859-1', $curl->getDataCharset());
    }

    /**
     * Test Curl::getDataCharset()
     */
    public function testGetDataCharset_notSet_UTF()
    {
        $curl = new \OxidEsales\PayPalModule\Core\Curl();
        $this->assertEquals('UTF-8', $curl->getDataCharset());
    }

    /**
     * Test Curl::setEnvironmentParameter()
     */
    public function testSetEnvironmentParameter_setParameter_addedToParameterSet()
    {
        $curl = new \OxidEsales\PayPalModule\Core\Curl();
        $curl->setEnvironmentParameter('param', 'value');

        $expectedParameters = array(
            'CURLOPT_VERBOSE'        => 0,
            'CURLOPT_SSL_VERIFYPEER' => false,
            'CURLOPT_SSL_VERIFYHOST' => false,
            'CURLOPT_SSLVERSION'     => 6,
            'CURLOPT_RETURNTRANSFER' => 1,
            'CURLOPT_POST'           => 1,
            'CURLOPT_HTTP_VERSION'   => 2,
            'param'                  => 'value'
        );

        $this->assertEquals($expectedParameters, $curl->getEnvironmentParameters());
    }

    /**
     * Test Curl::getEnvironmentParameters()
     */
    public function testGetEnvironmentParameters_default_returnDefaultSet()
    {
        $curl = new \OxidEsales\PayPalModule\Core\Curl();

        $expectedParameters = array(
            'CURLOPT_VERBOSE'        => 0,
            'CURLOPT_SSL_VERIFYPEER' => false,
            'CURLOPT_SSL_VERIFYHOST' => false,
            'CURLOPT_SSLVERSION'     => 6,
            'CURLOPT_RETURNTRANSFER' => 1,
            'CURLOPT_POST'           => 1,
            'CURLOPT_HTTP_VERSION'   => 2,
        );

        $this->assertEquals($expectedParameters, $curl->getEnvironmentParameters());
    }

    /**
     * Test Curl::getParameters()
     */
    public function testGetParameters_default_null()
    {
        $curl = new \OxidEsales\PayPalModule\Core\Curl();
        $this->assertNull($curl->getParameters());
    }

    /**
     * Test Curl::getParameters()
     */
    public function testGetParameters_set_returnSet()
    {
        $parameters = array('parameter' => 'value');

        $curl = new \OxidEsales\PayPalModule\Core\Curl();
        $curl->setParameters($parameters);
        $this->assertEquals($parameters, $curl->getParameters());
    }

    /**
     * Test Curl::setUrlToCall()
     * Test Curl::getUrlToCall()
     */
    public function testGetUrlToCall_urlSet_setReturned()
    {
        $endpointUrl = 'http://www.oxid-esales.com/index.php?anid=article';

        $payPalCurl = new \OxidEsales\PayPalModule\Core\Curl();
        $payPalCurl->setUrlToCall($endpointUrl);
        $urlToCall = $payPalCurl->getUrlToCall();

        $this->assertEquals($endpointUrl, $urlToCall, 'Url should be same as provided from config.');
    }

    /**
     * Test Curl::getUrlToCall()
     */
    public function testGetUrlToCall_notSet_null()
    {
        $payPalCurl = new \OxidEsales\PayPalModule\Core\Curl();
        $this->assertNull($payPalCurl->getUrlToCall());
    }

    /**
     * Test Curl::setUrlToCall()
     * Test Curl::getUrlToCall()
     */
    public function testGetUrlToCall_badUrlSet_Exception()
    {
        $this->expectException(\OxidEsales\PayPalModule\Core\Exception\PayPalException::class);

        $endpointUrl = 'url';
        $payPalCurl = new \OxidEsales\PayPalModule\Core\Curl();
        $payPalCurl->setUrlToCall($endpointUrl);

        $this->assertEquals($endpointUrl, $payPalCurl->getUrlToCall());
    }

    /**
     * Test Curl::setQuery()
     */
    public function testSetQuery_set_get()
    {
        $payPalCurl = new \OxidEsales\PayPalModule\Core\Curl();
        $payPalCurl->setQuery('param1=value1&param2=values2');

        $this->assertEquals('param1=value1&param2=values2', $payPalCurl->getQuery());
    }

    /**
     * Test Curl::getQuery()
     */
    public function testGetQuery_setParameter_getQueryFromParameters()
    {
        $payPalCurl = new \OxidEsales\PayPalModule\Core\Curl();
        $payPalCurl->setParameters(array('param1' => 'value1', 'param2' => 'values2'));

        $this->assertEquals('param1=value1&param2=values2', $payPalCurl->getQuery());
    }

    /**
     * Test Curl::getQuery()
     */
    public function testGetQuery_setParameterNotUtf_getQueryFromParameters()
    {
        $payPalCurl = new \OxidEsales\PayPalModule\Core\Curl();
        $payPalCurl->setDataCharset('ISO-8859-1');
        $payPalCurl->setParameters(array('param1' => 'J�ger', 'param2' => 'values2'));

        $pramsUtf = array('param1' => utf8_encode('J�ger'), 'param2' => 'values2');

        $this->assertEquals(http_build_query($pramsUtf), $payPalCurl->getQuery());
    }

    /**
     * Test Curl::execute()
     */
    public function testExecute_setParameters_getResponseArray()
    {
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Core\Curl::class);
        $mockBuilder->setMethods(['curlExecute', 'setOption', 'parseResponse', 'close']);
        $payPalCurl = $mockBuilder->getMock();

        $payPalCurl->expects($this->any())->method('setOption');
        $payPalCurl->expects($this->once())->method('curlExecute')->will($this->returnValue('rParam1=rValue1'));
        $payPalCurl->expects($this->once())->method('parseResponse')
            ->with($this->equalTo('rParam1=rValue1'))
            ->will($this->returnValue(array('rParam1' => 'rValue1')));
        $payPalCurl->expects($this->once())->method('close');

        $payPalCurl->setParameters(array('param1' => 'value1', 'param2' => 'values2'));
        $payPalCurl->setUrlToCall('http://url');

        $this->assertEquals(array('rParam1' => 'rValue1'), $payPalCurl->execute());
    }
//    public function testExecute_getParameters
}
