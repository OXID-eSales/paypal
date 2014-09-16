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

require_once realpath('.') . '/unit/OxidTestCase.php';
require_once realpath('.') . '/unit/test_config.inc.php';

/**
 * Testing oePayPalGetExpressCheckoutDetailsRequestBuilder class.
 */
class Unit_oePayPal_Models_PayPalRequest_oePayPalGetExpressCheckoutDetailsRequestBuilderTest extends OxidTestCase
{

    /**
     * Test building PayPal request object
     */
    public function testBuildRequest()
    {
        $aExpectedParams = array(
            'TOKEN' => '111',
        );
        $oSession = new oxSession();
        $oSession->setVariable("oepaypal-token", "111");

        $oBuilder = $this->_getPayPalRequestBuilder();
        $oBuilder->setSession($oSession);
        $oBuilder->buildRequest();

        $this->_assertArraysEqual($aExpectedParams, $oBuilder->getPayPalRequest()->getData());
    }

    /**
     *
     *
     * @return oePayPalCheckoutRequestBuilder
     */
    protected function _getPayPalRequestBuilder()
    {
        $oBuilder = new oePayPalGetExpressCheckoutDetailsRequestBuilder();

        return $oBuilder;
    }

    /**
     * Checks whether array length are equal and array keys and values are equal independent on keys position
     *
     * @param $aExpected
     * @param $aResult
     */
    protected function _assertArraysEqual($aExpected, $aResult)
    {
        $this->_assertArraysContains($aExpected, $aResult);
        $this->assertEquals(count($aExpected), count($aResult));
    }

    /**
     * Checks whether array array keys and values are equal independent on keys position
     *
     * @param $aExpected
     * @param $aResult
     */
    protected function _assertArraysContains($aExpected, $aResult)
    {
        foreach ($aExpected as $sKey => $sValue) {
            $this->assertArrayHasKey($sKey, $aResult, "Key not found: $sKey");
            $this->assertEquals($sValue, $aResult[$sKey], "Key '$sKey' value is not equal to '$sValue'");
        }
    }
}