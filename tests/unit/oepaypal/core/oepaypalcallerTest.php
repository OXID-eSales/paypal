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
 * @copyright (C) OXID eSales AG 2003-2013
 */

require_once realpath( "." ) . '/unit/OxidTestCase.php';

/**
 * Testing oePayPalCaller class.
 */
class Unit_oePayPal_core_oePayPalCallerTest extends OxidTestCase
{
    /**
     * Test case for oePayPalCallerService::setParameter()
     */
    public function testSetParameter_setParameter_addedToSetOfParameters()
    {
        $oService = new oePayPalCaller();
        $oService->setParameter( "testParam", "testValue" );
        $aParameters = array( "testParam" => "testValue" );
        $this->assertEquals( $aParameters, $oService->getParameters() );
    }

    /**
     * Test case for oePayPalCaller::setParameters()
     */
    public function testSetParameters_setOneOrMoreParameters_addedToSetOfParameters()
    {
        $oService = new oePayPalCaller();

        $oService->setParameter( "testParam", "testValue" );
        $oService->setParameters( array( "testParam" => "testValue2", "testParam3" => "testValue3", "testParam4" => "testValue4" ) );
        $oService->setParameter( "testParam4", "testValue5" );

        $aResult["testParam"] = "testValue2";
        $aResult["testParam3"] = "testValue3";
        $aResult["testParam4"] = "testValue5";

        $this->assertEquals( $aResult, $oService->getParameters() );
    }

    /**
     * Test case for oePayPalCaller::call()
     */
    public function testCall_withMethodSuccessful_returnResponseArray()
    {
        $aParams = array();
        $aParams["testParam"] = "testValue";

        $aCurlParams = $aParams;
        $aCurlParams["METHOD"] = "testMethod";

        $sUrl = 'http://url.com';
        $sCharset = 'latin';

        $aResponse = array('parameter', 'value');

        $oCaller = new oePayPalCaller();
        $oCaller->setParameters( $aParams );

        $oCaller->setCurl( $this->_prepareCurl( $aResponse, $aCurlParams, $sUrl, $sCharset ) );

        $this->assertEquals($aResponse, $oCaller->call( "testMethod" ));
    }

    /**
     * Test case for oePayPalCaller::call()
     */
    public function testCall_withoutMethod_returnResponseArray()
    {
        $aParams = array();
        $aParams["testParam"] = "testValue";

        $aCurlParams = $aParams;

        $sUrl = 'http://url.com';
        $sCharset = 'latin';

        $aResponse = array('parameter', 'value');

        $oCaller = new oePayPalCaller();
        $oCaller->setParameters( $aParams );

        $oCaller->setCurl( $this->_prepareCurl( $aResponse, $aCurlParams, $sUrl, $sCharset ) );

        $this->assertEquals($aResponse, $oCaller->call());
    }

    /**
     * Test case for oePayPalCaller::call()
     */
    public function testCall_withMethodNotSuccessful_throwException()
    {
        $this->setExpectedException( 'oePayPalResponseException' );

        $aParams = array();
        $aParams["testParam"] = "testValue";

        $aCurlParams = $aParams;

        $sUrl = 'http://url.com';
        $sCharset = 'latin';

        $aResponse = array( 'ACK' => 'Failure', 'L_LONGMESSAGE0' => 'message', 'L_ERRORCODE0' => 1 );

        $oCaller = new oePayPalCaller();
        $oCaller->setParameters( $aParams );

        $oCaller->setCurl( $this->_prepareCurl( $aResponse, $aCurlParams, $sUrl, $sCharset ) );
        $this->assertEquals($aResponse, $oCaller->call());
    }

    /**
     * Test case for oePayPalCaller::getCallBackResponse()
     */
    public function testGetCallBackResponse_setParameters_getResponse()
    {
        $aParams = array(
            'param1' => 'value1',
            'param2' => 'value2',
        );

        $oCaller = new oePayPalCaller();
        $oCaller->setParameters( $aParams );

        $aParams['METHOD'] = 'CallbackResponse';
        $sParams = http_build_query( $aParams );

        $this->assertEquals( $sParams, $oCaller->getCallBackResponse( 'CallbackResponse' ) );
    }

    /**
     * Test case for oePayPalCaller::getPayPalCurl()
     */
    public function testGetCurl_notSet_returnedNewCreated()
    {
        $oPayPalCaller = new oePayPalCaller();
        $oPayPalCurl = $oPayPalCaller->getCurl();
        $this->assertTrue( is_a( $oPayPalCurl, 'oePayPalCurl' ), 'Getter should create PayPal Curl object on request.' );
    }

    /**
     * Test case for oePayPalCaller::getCurl()
     */
    public function testSetCurl_setCurl_returnedSet()
    {
        $oCurl = new oePayPalCurl();
        $oCurl->setConnectionCharset( 'latin' );

        $oCaller = new oePayPalCaller();
        $oCaller->setCurl( $oCurl );
        $oCurl = $oCaller->getCurl();
        $this->assertTrue( $oCurl instanceof oePayPalCurl );
        $this->assertEquals( 'latin', $oCurl->getConnectionCharset() );
    }

    /**
     * Test case for oePayPalCaller::getPayPalCurl()
     */
    public function testSetRequest_RequestDataSetAsParameters()
    {
        $oRequest = new oePayPalPayPalRequest();
        $oRequest->setData( array('param'=>'data') );

        $oCaller = new oePayPalCaller();

        $oCaller->setRequest( $oRequest );
        $this->assertEquals( array('param'=>'data'), $oCaller->getParameters() );
    }

    /**
     * Test case for oePayPalCaller::getCharset()
     */
    public function testGetLogger_notSet_null()
    {
        $oCaller = new oePayPalCaller();
        $this->assertNull( $oCaller->getLogger() );
    }

    /**
     * Test case for oePayPalCaller::getCharset()
     */
    public function testGetLogger_setLogger_Logger()
    {
        $oCaller = new oePayPalCaller();
        $oCaller->setLogger( new oePayPalLogger() );
        $this->assertTrue( $oCaller->getLogger() instanceof oePayPalLogger );
    }

    /**
     * Test case for oePayPalCaller::log()
     */
    public function testLog_notSetLogger_LoggerNotUsed()
    {
        $oCaller = $this->getMock( 'oePayPalCaller', array( 'getLogger' ) );
        $oCaller->expects( $this->once() )->method( 'getLogger' )->will( $this->returnValue( null ) );
        $oCaller->log( 'logMassage' );
    }

    /**
     * Test case for oePayPalCaller::log()
     */
    public function testLog_setLogger_LoggerUsed()
    {
        $oLogger = $oCurl = $this->getMock( 'oePayPalLogger', array( 'log' ) );
        $oLogger->expects( $this->once() )->method( 'log' )->with( $this->equalTo( 'logMassage' ) );

        $oCaller = new oePayPalCaller();
        $oCaller->setLogger( $oLogger );
        $oCaller->log( 'logMassage' );
    }

    /**
     * Test case for oePayPalCaller::log() usage in oePayPalCaller::call()
     */
    public function testLogUsage_onCallMethod_atLeastOnce()
    {
        $oLogger = $oCurl = $this->getMock( 'oePayPalLogger', array( 'log' ) );
        $oLogger->expects( $this->atLeastOnce() )->method( 'log' );

        $oCaller = new oePayPalCaller();
        $oCaller->setLogger( $oLogger );
        $oCaller->setParameters( array('k'=>'val') );
        $oCaller->setCurl($this->_prepareCurl( array(), array('k'=>'val'), 'http://url.com', 'utf8' ));

        $oCaller->call();
    }

    /**
     * Test case for oePayPalCaller::log() usage in oePayPalCaller::getCallBackResponse()
     */
    public function testLogUsage_onGetCallBackResponseMethod_atLeastOnce()
    {
        $oLogger = $this->getMock( 'oePayPalLogger', array( 'log' ) );
        $oLogger->expects( $this->atLeastOnce() )->method( 'log' );

        $oCaller = new oePayPalCaller();
        $oCaller->setLogger( $oLogger );
        $oCaller->setParameters( array('k'=>'val') );
        $oCaller->setCurl($this->_prepareCurl( array(), array('k'=>'val'), 'http://url.com', 'utf8' ));
        $oCaller->call();
    }

    /**
     * Prepare oePayPalCurl stub
     *
     * @param array $aResponse response
     * @param array $aParamsCurl params
     * @param string $sUrl url
     * @param string $sCharset charset
     *
     * @return oePayPalCurl
     */
    protected function _prepareCurl( $aResponse, $aParamsCurl, $sUrl, $sCharset )
    {
        $oCurl = $this->getMock( 'oePayPalCurl', array( "execute", 'setUrlToCall', 'setParameters', 'setDataCharset' ) );
        $oCurl->expects( $this->once() )->method( "execute" )->will( $this->returnValue( $aResponse ) );
        $oCurl->setDataCharset( $sCharset );
        $oCurl->setParameters( $aParamsCurl );
        $oCurl->setUrlToCall( $sUrl );

        return $oCurl;
    }
}

