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
 * Testing oxAccessRightException class.
 */
class OrderPaymentStatusListTest extends \OxidEsales\TestingLibrary\UnitTestCase
{

    /**
     * Data provider for testIsActionValidCapture()
     *
     * @return array
     */
    public function availableStatusesTest_dataProvider()
    {
        return array(
            array('capture', array('completed')),
            array('capture_partial', array('completed', 'pending')),
            array('refund', array('completed', 'pending', 'canceled')),
            array('refund_partial', array('completed', 'pending', 'canceled')),
            array('void', array('completed', 'pending', 'canceled')),
            array('test', array()),
        );
    }

    /**
     * Testing adding amount to PayPal refunded amount
     *
     * @dataProvider availableStatusesTest_dataProvider
     */
    public function testGetAvailableStatuses($action, $statusList)
    {
        $statusListProvider = new \OxidEsales\PayPalModule\Model\OrderPaymentStatusList();

        $this->assertEquals($statusList, $statusListProvider->getAvailableStatuses($action));
    }
}
