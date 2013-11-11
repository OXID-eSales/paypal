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
 * Testing oePayPalOrderActionFactory class.
 */
class Unit_oePayPal_Models_Actions_Data_oePayPalOrderActionDataTest extends OxidTestCase
{

    /**
     */
    public function testGetAuthorizationId()
    {
        $oRequest = $this->_getRequest( array() );
        $oOrder = $this->_getOrder();
        $oOrder->oxorder__oxtransid = new oxField( 'authorizationId' );

        $oActionData = new oePayPalOrderActionData( $oRequest, $oOrder );

        $this->assertEquals( 'authorizationId', $oActionData->getAuthorizationId() );
    }

    /**
     */
    public function testGetAmount()
    {
        $oRequest = $this->_getRequest( array( 'action_comment' => 'comment' ) );
        $oOrder = $this->_getOrder();

        $oActionData = new oePayPalOrderActionData( $oRequest, $oOrder );

        $this->assertEquals( 'comment', $oActionData->getComment() );
    }

    /**
     *  Returns Request object with given parameters
     *
     * @param $aParams
     * @return mixed
     */
    protected function _getRequest( $aParams )
    {
        $oRequest = $this->_createStub( 'oePayPalRequest', array( 'getGet' => $aParams ) );

        return $oRequest;
    }

    /**
     *
     */
    protected function _getOrder()
    {
        $oOrder = new oePayPalOxOrder();

        return $oOrder;
    }

}