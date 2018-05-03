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
 * Testing \OxidEsales\PayPalModule\Core\PayPalCheckValidator class.
 */
class PayPalCheckValidatorTest extends \OxidEsales\TestingLibrary\UnitTestCase
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
     * Test \OxidEsales\PayPalModule\Core\PayPalCheckValidator::isPayPalCheckValid()
     *
     * @dataProvider isIsPayPalCheckValid_dataProvider
     */
    public function testIsPayPalCheckValid($newAmount, $oldAmount, $result)
    {
        $checkValidator = new \OxidEsales\PayPalModule\Core\PayPalCheckValidator();
        $checkValidator->setNewBasketAmount($newAmount);
        $checkValidator->setOldBasketAmount($oldAmount);
        $this->assertEquals($result, $checkValidator->isPayPalCheckValid());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\PayPalCheckValidator::getOldBasketAmount()
     */
    public function testSetGetOldBasketAmount()
    {
        $validator = new \OxidEsales\PayPalModule\Core\PayPalCheckValidator();
        $validator->setOldBasketAmount('3.5');

        $this->assertEquals(3.5, $validator->getOldBasketAmount());
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Core\PayPalCheckValidator::getNewBasketAmount()
     */
    public function testSetGetNewBasketAmount()
    {
        $validator = new \OxidEsales\PayPalModule\Core\PayPalCheckValidator();
        $validator->setNewBasketAmount('3.5');

        $this->assertEquals(3.5, $validator->getNewBasketAmount());
    }
}

