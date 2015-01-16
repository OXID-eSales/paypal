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
 * Testing oePayPalEscape class.
 */
class Unit_oePayPal_core_oePayPalEscapeTest extends OxidTestCase
{
    /**
     * Testing input processor. Checking 3 cases - passing object, array, string.
     */
    public function testCheckParamSpecialChars()
    {
        $oVar = new stdClass();
        $oVar->xxx = 'yyy';
        $aVar = array('&\\o<x>i"\'d' . chr(0));
        $sVar = '&\\o<x>i"\'d' . chr(0);
        $oPayPalRequest = new oePayPalRequest();
        // object must came back the same
        $this->assertEquals($oVar, $oPayPalRequest->escapeSpecialChars($oVar));

        // array items comes fixed
        $this->assertEquals(array('&amp;&#092;o&lt;x&gt;i&quot;&#039;d'), $oPayPalRequest->escapeSpecialChars($aVar));

        // string comes fixed
        $this->assertEquals('&amp;&#092;o&lt;x&gt;i&quot;&#039;d', $oPayPalRequest->escapeSpecialChars($sVar));
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
        $oPayPalRequest = new oePayPalRequest();
        $this->assertEquals($checkExpectedResult, $oPayPalRequest->escapeSpecialChars($checkData));
    }
}