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
require_once realpath(".") . '/unit/test_config.inc.php';

/**
 * Testing oePayPalCurl class.
 */
class Unit_oePayPal_core_oePayPalCurlTest extends OxidTestCase
{
    /**
     * Checks if function returns null when nothing is set.
     */
    public function testGetHost_notSet_null()
    {
        $oCurl = new oePayPalCurl();
        $this->assertNull($oCurl->getHost(), 'Default value must be null.');
    }

    /**
     * Check if getter returns what is set in setter.
     */
    public function testGetHost_setHost_host()
    {
        $sHost = 'someHost';

        $oCurl = new oePayPalCurl();
        $oCurl->setHost($sHost);

        $this->assertEquals($sHost, $oCurl->getHost(), 'Check if getter returns what is set in setter.');
    }

    /**
     * Checks if returned header is correct when header was not set and host was set.
     */
    public function testGetHeader_headerNotSetAndHostSet_headerWithHost()
    {
        $sHost = 'someHost';
        $aExpectedHeader = array(
            'POST /cgi-bin/webscr HTTP/1.1',
            'Content-Type: application/x-www-form-urlencoded',
            'Host: ' . $sHost,
            'Connection: close'
        );
        $oCurl = new oePayPalCurl();
        $oCurl->setHost($sHost);

        $this->assertEquals($aExpectedHeader, $oCurl->getHeader(), 'Header must be formed from set host.');
    }

    /**
     * Checks if returned header is correct when header was not set and host was not set.
     */
    public function testGetHeader_headerNotSetAndHostNotSet_headerWithoutHost()
    {
        $aExpectedHeader = array(
            'POST /cgi-bin/webscr HTTP/1.1',
            'Content-Type: application/x-www-form-urlencoded',
            'Connection: close'
        );
        $oCurl = new oePayPalCurl();

        $this->assertEquals($aExpectedHeader, $oCurl->getHeader(), 'Header must be without host as host not set.');
    }

    /**
     * Checks if returned header is correct when header was set and host was set.
     */
    public function testGetHeader_headerSetAndHostSet_headerFromSet()
    {
        $sHost = 'someHost';
        $aHeader = array('Test header');
        $oCurl = new oePayPalCurl();
        $oCurl->setHost($sHost);
        $oCurl->setHeader($aHeader);

        $this->assertEquals($aHeader, $oCurl->getHeader(), 'Header must be same as set header.');
    }

    /**
     * Checks if returned header is correct when header was set and host was not set.
     */
    public function testGetHeader_headerSetAndHostNotSet_headerWithoutHost()
    {
        $aHeader = array('Test header');
        $oCurl = new oePayPalCurl();
        $oCurl->setHeader($aHeader);

        $this->assertEquals($aHeader, $oCurl->getHeader(), 'Header must be same as set header.');
    }

    /**
     * Test oePayPalCurl::setConnectionCharset()
     */
    public function testSetConnectionCharset_set_get()
    {
        $oCurl = new oePayPalCurl();
        $oCurl->setConnectionCharset('ISO-8859-1');

        $this->assertEquals('ISO-8859-1', $oCurl->getConnectionCharset());
    }

    /**
     * Test oePayPalCurl::getConnectionCharset()
     */
    public function testGetConnectionCharset_notSet_UTF()
    {
        $oCurl = new oePayPalCurl();
        $this->assertEquals('UTF-8', $oCurl->getConnectionCharset());
    }

    /**
     * Test oePayPalCurl::setDataCharset()
     */
    public function testSetDataCharset_set_get()
    {
        $oCurl = new oePayPalCurl();
        $oCurl->setDataCharset('ISO-8859-1');

        $this->assertEquals('ISO-8859-1', $oCurl->getDataCharset());
    }

    /**
     * Test oePayPalCurl::getDataCharset()
     */
    public function testGetDataCharset_notSet_UTF()
    {
        $oCurl = new oePayPalCurl();
        $this->assertEquals('UTF-8', $oCurl->getDataCharset());
    }

    /**
     * Test oePayPalCurl::setEnvironmentParameter()
     */
    public function testSetEnvironmentParameter_setParameter_addedToParameterSet()
    {
        $oCurl = new oePayPalCurl();
        $oCurl->setEnvironmentParameter('param', 'value');

        $aExpectedParameters = array(
            'CURLOPT_VERBOSE'        => 0,
            'CURLOPT_SSL_VERIFYPEER' => false,
            'CURLOPT_SSL_VERIFYHOST' => false,
            'CURLOPT_SSLVERSION'     => 3,
            'CURLOPT_RETURNTRANSFER' => 1,
            'CURLOPT_POST'           => 1,
            'CURLOPT_HTTP_VERSION'   => 2,
            'param'                  => 'value'
        );

        $this->assertEquals($aExpectedParameters, $oCurl->getEnvironmentParameters());
    }

    /**
     * Test oePayPalCurl::getEnvironmentParameters()
     */
    public function testGetEnvironmentParameters_default_returnDefaultSet()
    {
        $oCurl = new oePayPalCurl();

        $aExpectedParameters = array(
            'CURLOPT_VERBOSE'        => 0,
            'CURLOPT_SSL_VERIFYPEER' => false,
            'CURLOPT_SSL_VERIFYHOST' => false,
            'CURLOPT_SSLVERSION'     => 3,
            'CURLOPT_RETURNTRANSFER' => 1,
            'CURLOPT_POST'           => 1,
            'CURLOPT_HTTP_VERSION'   => 2,
        );

        $this->assertEquals($aExpectedParameters, $oCurl->getEnvironmentParameters());
    }

    /**
     * Test oePayPalCurl::getParameters()
     */
    public function testGetParameters_default_null()
    {
        $oCurl = new oePayPalCurl();
        $this->assertNull($oCurl->getParameters());
    }

    /**
     * Test oePayPalCurl::getParameters()
     */
    public function testGetParameters_set_returnSet()
    {
        $aParameters = array('parameter' => 'value');

        $oCurl = new oePayPalCurl();
        $oCurl->setParameters($aParameters);
        $this->assertEquals($aParameters, $oCurl->getParameters());
    }

    /**
     * Test oePayPalCurl::setUrlToCall()
     * Test oePayPalCurl::getUrlToCall()
     */
    public function testGetUrlToCall_urlSet_setReturned()
    {
        $sEndpointUrl = 'http://www.oxid-esales.com/index.php?anid=article';

        $oPayPalCurl = new oePayPalCurl();
        $oPayPalCurl->setUrlToCall($sEndpointUrl);
        $sUrlToCall = $oPayPalCurl->getUrlToCall();

        $this->assertEquals($sEndpointUrl, $sUrlToCall, 'Url should be same as provided from config.');
    }

    /**
     * Test oePayPalCurl::getUrlToCall()
     */
    public function testGetUrlToCall_notSet_null()
    {
        $oPayPalCurl = new oePayPalCurl();
        $this->assertNull($oPayPalCurl->getUrlToCall());
    }

    /**
     * Test oePayPalCurl::setUrlToCall()
     * Test oePayPalCurl::getUrlToCall()
     */
    public function testGetUrlToCall_badUrlSet_Exception()
    {
        $this->setExpectedException('oePayPalException');

        $sEndpointUrl = 'url';
        $oPayPalCurl = new oePayPalCurl();
        $oPayPalCurl->setUrlToCall($sEndpointUrl);

        $this->assertEquals($sEndpointUrl, $oPayPalCurl->getUrlToCall());
    }

    /**
     * Test oePayPalCurl::setQuery()
     */
    public function testSetQuery_set_get()
    {
        $oPayPalCurl = new oePayPalCurl();
        $oPayPalCurl->setQuery('param1=value1&param2=values2');

        $this->assertEquals('param1=value1&param2=values2', $oPayPalCurl->getQuery());
    }

    /**
     * Test oePayPalCurl::getQuery()
     */
    public function testGetQuery_setParameter_getQueryFromParameters()
    {
        $oPayPalCurl = new oePayPalCurl();
        $oPayPalCurl->setParameters(array('param1' => 'value1', 'param2' => 'values2'));

        $this->assertEquals('param1=value1&param2=values2', $oPayPalCurl->getQuery());
    }

    /**
     * Test oePayPalCurl::getQuery()
     */
    public function testGetQuery_setParameterNotUtf_getQueryFromParameters()
    {
        $oPayPalCurl = new oePayPalCurl();
        $oPayPalCurl->setDataCharset('ISO-8859-1');
        $oPayPalCurl->setParameters(array('param1' => 'Jäger', 'param2' => 'values2'));

        $aPramsUtf = array('param1' => utf8_encode('Jäger'), 'param2' => 'values2');

        $this->assertEquals(http_build_query($aPramsUtf), $oPayPalCurl->getQuery());
    }

    /**
     * Test oePayPalCurl::execute()
     */
    public function testExecute_setParameters_getResponseArray()
    {
        $oPayPalCurl = $this->getMock('oePayPalCurl', array("_execute", '_setOption', '_parseResponse', '_close'));

        $oPayPalCurl->expects($this->any())->method('_setOption');
        $oPayPalCurl->expects($this->once())->method('_execute')->will($this->returnValue('rParam1=rValue1'));
        $oPayPalCurl->expects($this->once())->method('_parseResponse')
            ->with($this->equalTo('rParam1=rValue1'))
            ->will($this->returnValue(array('rParam1' => 'rValue1')));
        $oPayPalCurl->expects($this->once())->method('_close');

        $oPayPalCurl->setParameters(array('param1' => 'value1', 'param2' => 'values2'));
        $oPayPalCurl->setUrlToCall('http://url');

        $this->assertEquals(array('rParam1' => 'rValue1'), $oPayPalCurl->execute());
    }
//    public function testExecute_getParameters
}