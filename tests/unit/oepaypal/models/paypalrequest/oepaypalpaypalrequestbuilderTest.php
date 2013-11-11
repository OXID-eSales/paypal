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

require_once realpath( '.' ).'/unit/OxidTestCase.php';
require_once realpath( '.' ).'/unit/test_config.inc.php';

/**
 * Testing oePayPalPayPalRequestBuilder class.
 */
class Unit_oePayPal_Models_PayPalRequest_oePayPalPayPalRequestBuilderTest extends OxidTestCase
{

    public function testSetGetRequest()
    {
        $aData = array('DATA' => 'data');
        $oRequest = new oePayPalPayPalRequest();
        $oRequest->setData( $aData );
        $oBuilder = new oePayPalPayPalRequestBuilder();
        $oBuilder->setRequest( $oRequest );

        $this->assertEquals( $oRequest, $oBuilder->getRequest() );
    }

    public function testSetAuthorizationId()
    {
        $sAuthorizationId = 'AuthorizationId';
        $aExpected = array( 'AUTHORIZATIONID' => $sAuthorizationId );

        $oBuilder = new oePayPalPayPalRequestBuilder();
        $oBuilder->setAuthorizationId( $sAuthorizationId );

        $oRequest = $oBuilder->getRequest();

        $this->assertEquals( $aExpected, $oRequest->getData() );
    }

    public function testSetAmount()
    {
        $dAmount = 99.99;
        $sCurrency = 'EUR';
        $aExpected = array(
            'AMT' => $dAmount,
            'CURRENCYCODE' => $sCurrency
        );

        $oBuilder = new oePayPalPayPalRequestBuilder();
        $oBuilder->setAmount( $dAmount );

        $oRequest = $oBuilder->getRequest();

        $this->assertEquals( $aExpected, $oRequest->getData() );
    }

    public function testSetCompleteType()
    {
        $sCompleteType = 'Full';
        $aExpected = array( 'COMPLETETYPE' => $sCompleteType );

        $oBuilder = new oePayPalPayPalRequestBuilder();
        $oBuilder->setCompleteType( $sCompleteType );

        $oRequest = $oBuilder->getRequest();

        $this->assertEquals( $aExpected, $oRequest->getData() );
    }

    public function testSetRefundType()
    {
        $sRefundType = 'Complete';
        $aExpected = array( 'REFUNDTYPE' => $sRefundType );

        $oBuilder = new oePayPalPayPalRequestBuilder();
        $oBuilder->setRefundType( $sRefundType );

        $oRequest = $oBuilder->getRequest();

        $this->assertEquals( $aExpected, $oRequest->getData() );
    }

    public function testSetComment()
    {
        $sComment = 'Comment';
        $aExpected = array( 'NOTE' => $sComment );

        $oBuilder = new oePayPalPayPalRequestBuilder();
        $oBuilder->setComment( $sComment );

        $oRequest = $oBuilder->getRequest();

        $this->assertEquals( $aExpected, $oRequest->getData() );
    }
}