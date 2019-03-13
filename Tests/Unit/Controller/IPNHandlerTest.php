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
namespace OxidEsales\PayPalModule\Tests\Unit\Controller;


/**
 * Testing \OxidEsales\PayPalModule\Controller\IPNHandler class.
 */
class IPNHandlerTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function testGetPayPalRequest()
    {
        $payPalHandler = new \OxidEsales\PayPalModule\Controller\IPNHandler();
        $payPalRequest = $payPalHandler->getPayPalRequest();
        $this->assertTrue(is_a($payPalRequest, \OxidEsales\PayPalModule\Core\Request::class));
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Controller\IPNHandler::handleRequest()
     * Handler should return false if called without PayPal request data.
     */
    public function testHandleRequest_emptyData_false()
    {
        $payPalIPNHandler = new \OxidEsales\PayPalModule\Controller\IPNHandler();
        $payPalIPNHandler->handleRequest();

        $logHelper = new \OxidEsales\PayPalModule\Tests\Acceptance\PayPalLogHelper();
        $logData = $logHelper->getLogData();
        $lastLogItem = end($logData);
        $requestHandled = $lastLogItem->data['Result'] == 'true';

        $this->assertEquals(false, $requestHandled);
    }
}
