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
require_once realpath(".") . '/unit/test_config.inc.php';

if (!class_exists('oePayPalOxOrder_parent')) {
    class oePayPalOxOrder_parent extends oxOrder
    {
    }
}

/**
 * Testing oePayPalOxState class.
 */
class Unit_oePayPal_models_oePayPalOrderActionManagerTest extends OxidTestCase
{

    /**
     * Data provider for testIsActionAvailable()
     *
     * @return array
     */
    public function testIsActionAvailable_dataProvider()
    {
        return array(
            array('Sale', 'capture', 1000, 0, 0, 0, false),
            array('Sale', 'reauthorize', 1000, 0, 0, 0, false),
            array('Sale', 'void', 1000, 0, 0, 0, false),
            array('Sale', 'refund', 1000, 0, 0, 0, false),
            array('Authorization', 'capture', 1000, 999.99, 0, 0, true),
            array('Authorization', 'capture', 1000, 900, 1200, 0, true),
            array('Authorization', 'capture', 1000, 1000, 0, 0, false),
            array('Authorization', 'capture', 1000, 1100, 0, 0, false),
            array('Authorization', 'capture', 1000, 0, 0, 1000, false),
            array('Authorization', 'reauthorize', 1000, 999.99, 0, 0, true),
            array('Authorization', 'reauthorize', 1000, 900, 1200, 0, true),
            array('Authorization', 'reauthorize', 1000, 1000, 0, 0, false),
            array('Authorization', 'reauthorize', 1000, 1100, 0, 0, false),
            array('Authorization', 'reauthorize', 1000, 0, 0, 1000, false),
            array('Authorization', 'void', 1000, 999.99, 0, 0, true),
            array('Authorization', 'void', 1000, 900, 1200, 0, true),
            array('Authorization', 'void', 1000, 1000, 0, 0, false),
            array('Authorization', 'void', 1000, 1100, 0, 0, false),
            array('Authorization', 'void', 1000, 0, 0, 1000, false),
            array('Authorization', 'refund', 1000, 0, 0, 0, false),
            array('TestMode', 'testAction', -50, -5, 9999, 9999, false),
        );
    }

    /**
     * Tests isPayPalActionValid
     *
     * @dataProvider testIsActionAvailable_dataProvider
     */
    public function testIsActionAvailable($sTransactionMode, $sAction, $dTotal, $dCaptured, $dRefunded, $dVoided, $dIsValid)
    {
        $oOrder = new oePayPalPayPalOrder();

        $oOrder->setTotalOrderSum($dTotal);
        $oOrder->setCapturedAmount($dCaptured);
        $oOrder->setRefundedAmount($dRefunded);
        $oOrder->setVoidedAmount($dVoided);
        $oOrder->setTransactionMode($sTransactionMode);

        $oActionManager = new oePayPalOrderActionManager();
        $oActionManager->setOrder($oOrder);

        $this->assertEquals($dIsValid, $oActionManager->isActionAvailable($sAction));
    }
}
