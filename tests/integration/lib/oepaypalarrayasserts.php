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

/**
 * Basket constructor
 *
 */
class oePayPalArrayAsserts extends PHPUnit_Framework_TestCase
{

    /**
     * Checks whether array length are equal and array keys and values are equal independent on keys position
     *
     * @param $aExpected
     * @param $aResult
     */
    public function assertArraysEqual( $aExpected, $aResult )
    {
        $this->assertArraysContains( $aExpected, $aResult );
        $this->assertEquals( count( $aExpected ), count( $aResult ), 'Failed asserting that expected array has equal amount of elements with result array' );
    }

    /**
     * Checks whether array array keys and values are equal independent on keys position
     *
     * @param $aExpected
     * @param $aResult
     */
    public function assertArraysContains( $aExpected, $aResult )
    {
        $aExpectedNotMatched = array();
        $aResultNotMatched = array();

        foreach ( $aExpected as $sKey => $sValue) {
            try {
                $this->assertArrayHasKey( $sKey, $aResult );
                $this->assertEquals( $sValue, $aResult[ $sKey ]);
            } catch ( Exception $oException) {
                $aExpectedNotMatched[$sKey] = $sValue;
                $aResultNotMatched[$sKey] = $aResult[ $sKey ];
            }
        }
        $this->assertEquals( $aExpectedNotMatched, $aResultNotMatched, 'Values not matched in given arrays' );
    }
}