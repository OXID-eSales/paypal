<?php
/**
 * #PHPHEADER_OXID_LICENSE_INFORMATION#
 *
 * @link      http://www.oxid-esales.com
 * @copyright (c) OXID eSales AG 2003-#OXID_VERSION_YEAR#
 */

require_once realpath( "." ).'/unit/OxidTestCase.php';
require_once realpath( "." ).'/unit/test_config.inc.php';

if ( ! class_exists('oePayPalDetails_parent')) {
    class oePayPalDetails_parent extends Details {}
}

class Unit_oePayPal_Controllers_oePayPalDetailsTest extends OxidTestCase
{

    protected function _createDetailsMock( $sParameter )
    {
        $oRequest = $this->getMock( 'oePayPalRequest', array( 'getGetParameter' ) );
        $oRequest->expects( $this->any() )->method( 'getGetParameter')->will(  $this->returnValue( $sParameter )  );

        $oDetails = $this->getMock( 'oePayPalDetails', array( '_getRequest' ) );
        $oDetails->expects( $this->any() )->method( '_getRequest')->will(  $this->returnValue( $oRequest )  );

        return $oDetails;
    }

    public function providerGetArticleAmount()
    {
        return array(
            // Given amount 5
            array( 'a:5:{s:2:"am";s:1:"5";s:3:"sel";N;s:9:"persparam";N;s:8:"override";b:0;s:12:"basketitemid";N;}', 5 ),
            // Not given any amount
            array( '', 1 )
        );
    }

    /**
     * Tests if returns correct amount
     * @param $sParameter
     * @param $blExpectedResult
     * @dataProvider providerGetArticleAmount
     */
    public function testGetArticleAmount( $sParameter, $blExpectedResult )
    {
        $oDetails = $this->_createDetailsMock( $sParameter );

        $this->assertEquals( $blExpectedResult, $oDetails->getArticleAmount() );
    }

    public function providerShowECSPopup()
    {
        return array(
            array( '1', true ),
            array( '0', false ),
            array( '', false ),
        );
    }

    /**
     * Tests if function gets parameter and returns correct result
     * @param $sParameter
     * @param $blExpectedResult
     * @dataProvider providerShowECSPopup
     */
    public function testShowECSPopup( $sParameter, $blExpectedResult )
    {
        $oDetails = $this->_createDetailsMock( $sParameter );

        $this->assertEquals( $blExpectedResult, $oDetails->showECSPopup() );
    }

    public function providerPersistentParam()
    {
        return array(
            array( 'a:5:{s:2:"am";s:1:"1";s:3:"sel";N;s:9:"persparam";a:1:{s:7:"details";s:2:"aa";}s:8:"override";b:0;s:12:"basketitemid";N;}', 'aa' ),
            array( 'a:5:{s:2:"am";s:1:"5";s:3:"sel";N;s:9:"persparam";N;s:8:"override";b:0;s:12:"basketitemid";N;}', null ),
        );
    }

    /**
     * Tests if function returns correct persistent param
     * @param $sParameter
     * @param $blExpectedResult
     * @dataProvider providerPersistentParam
     */
    public function testPersistentParam( $sParameter, $blExpectedResult )
    {
        $oDetails = $this->_createDetailsMock( $sParameter );

        $this->assertEquals( $blExpectedResult, $oDetails->getPersistentParam() );
    }

    public function providerGetSelection()
    {
        return array(
            array( 'a:5:{s:2:"am";s:1:"1";s:3:"sel";a:2:{i:0;s:1:"1";i:1;s:1:"0";}s:9:"persparam";N;s:8:"override";b:0;s:12:"basketitemid";N;}', array( 1, 0 ) ),
            array( 'a:5:{s:2:"am";s:1:"5";s:3:"sel";N;s:9:"persparam";N;s:8:"override";b:0;s:12:"basketitemid";N;}', null ),
        );
    }

    /**
     * Tests if returns correct selection lists values
     * @param $sParameter
     * @param $blExpectedResult
     * @dataProvider providerGetSelection
     */
    public function testGetSelection( $sParameter, $blExpectedResult )
    {
        $oDetails = $this->_createDetailsMock( $sParameter );

        $this->assertEquals( $blExpectedResult, $oDetails->getSelection() );
    }

    public function providerDisplayCartInPayPal()
    {
        return array(
            array( '1', true ),
            array( '0', false ),
            array( '', false ),
        );
    }

    /**
     * Tests if function gets parameter and returns correct result
     * @param $sParameter
     * @param $blExpectedResult
     * @dataProvider providerDisplayCartInPayPal
     */
    public function testDisplayCartInPayPal( $sParameter, $blExpectedResult )
    {
        $oDetails = $this->_createDetailsMock( $sParameter );

        $this->assertEquals( $blExpectedResult, $oDetails->displayCartInPayPal() );
    }
}