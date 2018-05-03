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

namespace OxidEsales\PayPalModule\Tests\Integration\Library;

class ArrayAsserts extends \OxidEsales\PayPalModule\Tests\Integration\Library\IntegrationTestHelper
{
    /**
     * Checks whether array length are equal and array keys and values are equal independent on keys position
     *
     * @param $expected
     * @param $result
     */
    public function assertArraysEqual($expected, $result)
    {
        $this->assertArraysContains($expected, $result);
        $this->assertEquals(count($expected), count($result), 'Failed asserting that expected array has equal amount of elements with result array');
    }

    /**
     * Checks whether array array keys and values are equal independent on keys position
     *
     * @param $expected
     * @param $result
     */
    public function assertArraysContains($expected, $result)
    {
        $expectedNotMatched = array();
        $resultNotMatched = array();

        foreach ($expected as $key => $value) {
            try {
                $this->assertArrayHasKey($key, $result);
                $this->assertEquals($value, $result[$key]);
            } catch (\Exception $exception) {
                $expectedNotMatched[$key] = $value;
                $resultNotMatched[$key] = $result[$key];
            }
        }
        $this->assertEquals($expectedNotMatched, $resultNotMatched, 'Values not matched in given arrays');
    }
}