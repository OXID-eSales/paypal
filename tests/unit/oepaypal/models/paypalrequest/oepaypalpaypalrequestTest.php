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

require_once realpath('.') . '/unit/OxidTestCase.php';
require_once realpath('.') . '/unit/test_config.inc.php';

/**
 * Testing oePayPalPayPalRequest class.
 */
class Unit_oePayPal_Models_PayPalRequest_oePayPalPayPalRequestTest extends OxidTestCase
{

    public function testSetGetData()
    {
        $aData = array(
            'AUTHORIZATIONID' => 'AuthorizationId'
        );

        $oRequest = new oePayPalPayPalRequest();
        $oRequest->setData($aData);
        $this->assertEquals($aData, $oRequest->getData());
    }

    public function testGetData_NoDataSet()
    {
        $oRequest = new oePayPalPayPalRequest();
        $this->assertEquals(array(), $oRequest->getData());
    }

    public function testSetGetParameter()
    {
        $oRequest = new oePayPalPayPalRequest();
        $oRequest->setParameter('AUTHORIZATIONID', 'AuthorizationId');

        $this->assertEquals('AuthorizationId', $oRequest->getParameter('AUTHORIZATIONID'));
    }

    public function testSetGetParameter_OverwritingOfSetData()
    {
        $aData = array(
            'AUTHORIZATIONID' => 'AuthorizationId',
            'TRANSACTIONID'   => 'TransactionId',
        );
        $sNewId = 'NewAuthorizationId';

        $oRequest = new oePayPalPayPalRequest();
        $oRequest->setData($aData);
        $oRequest->setParameter('AUTHORIZATIONID', $sNewId);

        $aData['AUTHORIZATIONID'] = $sNewId;
        $this->assertEquals($aData, $oRequest->getData());
    }
}