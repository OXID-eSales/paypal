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

namespace OxidEsales\PayPalModule\Tests\Unit\Model;

/**
 * Testing oePayPalOxState class.
 */
class OrderActionManagerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Data provider for testIsActionAvailable()
     *
     * @return array
     */
    public function isActionAvailableTest_dataProvider()
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
     * @dataProvider isActionAvailableTest_dataProvider
     */
    public function testIsActionAvailable($transactionMode, $action, $total, $captured, $refunded, $voided, $isValid)
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();

        $order->setTotalOrderSum($total);
        $order->setCapturedAmount($captured);
        $order->setRefundedAmount($refunded);
        $order->setVoidedAmount($voided);
        $order->setTransactionMode($transactionMode);

        $actionManager = new \OxidEsales\PayPalModule\Model\OrderActionManager();
        $actionManager->setOrder($order);

        $this->assertEquals($isValid, $actionManager->isActionAvailable($action));
    }
}
