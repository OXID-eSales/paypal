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

require_once realpath(".") . '/unit/OxidTestCase.php';

/**
 * Testing oePayPalCheckValidator class.
 */
class Unit_oePayPal_core_oePayPalCheckValidatorTest extends OxidTestCase
{

    /**
     * Data provider for testIsPayPalCheckValid()
     *
     * @return array
     */
    public function isIsPayPalCheckValid_dataProvider()
    {
        return array(
            array(200, 220, true),   //if new value is less
            array(50, 50, true),     //if new and old values are the same
            array(620, 600, false),   //if new value is bigger
            array(26.55, '26.55', true),   //if old value is string
            array(600, 620, true),   //if new value is smaller
        );
    }

    /**
     * Test oePayPalCheckValidator::isPayPalCheckValid()
     *
     * @dataProvider isIsPayPalCheckValid_dataProvider
     */
    public function testIsPayPalCheckValid($dNewAmount, $dOldAmount, $blResult)
    {
        $oCheckValidator = new oePayPalCheckValidator();
        $oCheckValidator->setNewBasketAmount($dNewAmount);
        $oCheckValidator->setOldBasketAmount($dOldAmount);
        $this->assertEquals($blResult, $oCheckValidator->isPayPalCheckValid());
    }

    /**
     * Test case for oePayPalCheckValidator::getOldBasketAmount()
     */
    public function testSetGetOldBasketAmount()
    {
        $oValidator = new oePayPalCheckValidator();
        $oValidator->setOldBasketAmount('3.5');

        $this->assertEquals(3.5, $oValidator->getOldBasketAmount());
    }

    /**
     * Test case for oePayPalCheckValidator::getNewBasketAmount()
     */
    public function testSetGetNewBasketAmount()
    {
        $oValidator = new oePayPalCheckValidator();
        $oValidator->setNewBasketAmount('3.5');

        $this->assertEquals(3.5, $oValidator->getNewBasketAmount());
    }

}

