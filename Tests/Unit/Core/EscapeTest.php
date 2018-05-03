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

namespace OxidEsales\PayPalModule\Tests\Unit\Core;

/**
 * Testing \OxidEsales\PayPalModule\Core\Escape class.
 */
class EscapeTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Testing input processor. Checking 3 cases - passing object, array, string.
     */
    public function testCheckParamSpecialChars()
    {
        $object = new \stdClass();
        $object->xxx = 'yyy';
        $array = array('&\\o<x>i"\'d' . chr(0));
        $string = '&\\o<x>i"\'d' . chr(0);
        $payPalRequest = new \OxidEsales\PayPalModule\Core\Request();

        // object must came back the same
        $this->assertEquals($object, $payPalRequest->escapeSpecialChars($object));

        // array items comes fixed
        $this->assertEquals(array('&amp;&#092;o&lt;x&gt;i&quot;&#039;d'), $payPalRequest->escapeSpecialChars($array));

        // string comes fixed
        $this->assertEquals('&amp;&#092;o&lt;x&gt;i&quot;&#039;d', $payPalRequest->escapeSpecialChars($string));
    }

    /**
     * Data provider for testCheckParamSpecialCharsAlsoFixesArrayKeys()
     *
     * @return array
     */
    public function providerCheckParamSpecialCharsAlsoFixesArrayKeys()
    {
        return array(
            array(
                array('asd&' => 'a%&'),
                array('asd&amp;' => 'a%&amp;'),
            ),
            array(
                'asd&',
                'asd&amp;',
            )
        );
    }

    /**
     * Test if checkParamSpecialChars also can fix arrays
     *
     * @dataProvider providerCheckParamSpecialCharsAlsoFixesArrayKeys
     */
    public function testCheckParamSpecialCharsAlsoFixesArrayKeys($checkData, $checkExpectedResult)
    {
        $payPalRequest = new \OxidEsales\PayPalModule\Core\Request();
        $this->assertEquals($checkExpectedResult, $payPalRequest->escapeSpecialChars($checkData));
    }
}
