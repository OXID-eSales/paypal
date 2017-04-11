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


/**
 * Testing oxAccessRightException class.
 */
class Unit_oePayPal_models_responses_oePayPalResponseDoVoidTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Returns response data
     *
     * @return array
     */
    protected function _getResponseData()
    {
        $aData = array(
            'AUTHORIZATIONID' => 'authorizationId',
            'CORRELATIONID'   => 'correlationId',
            'PAYMENTSTATUS'   => 'completed'
        );

        return $aData;
    }

    /**
     * Test get authorization id
     */
    public function testGetAuthorizationId()
    {
        $oResponse = new oePayPalResponseDoVoid();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('authorizationId', $oResponse->getAuthorizationId());
    }

    /**
     * Test get correlation id
     */
    public function testGetCorrelationId()
    {
        $oResponse = new oePayPalResponseDoCapture();
        $oResponse->setData($this->_getResponseData());
        $this->assertEquals('correlationId', $oResponse->getCorrelationId());
    }
}
