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
 * Testing oxAccessRightException class.
 */
class Unit_oePayPal_models_oePayPalOrderPaymentStatusListTest extends OxidTestCase
{


    /**
     * Data provider for testIsActionValidCapture()
     *
     * @return array
     */
    public function testAvailableStatuses_dataProvider()
    {
        return array(
            array( 'capture', array( 'completed' ) ),
            array( 'capture_partial', array( 'completed', 'pending' ) ),
            array( 'refund', array( 'completed', 'pending', 'canceled' ) ),
            array( 'refund_partial', array( 'completed', 'pending', 'canceled' ) ),
            array( 'void', array( 'completed', 'pending', 'canceled' ) ),
            array( 'test', array() ),
        );
    }

    /**
     * Testing adding amount to PayPal refunded amount
     *
     * @dataProvider testAvailableStatuses_dataProvider
     */
    public function testGetAvailableStatuses( $sAction, $aStatusList )
    {
        $oStatusList = new oePayPalOrderPaymentStatusList();

        $this->assertEquals( $aStatusList, $oStatusList->getAvailableStatuses( $sAction ) );
    }
}