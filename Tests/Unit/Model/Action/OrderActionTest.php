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

namespace OxidEsales\PayPalModule\Tests\Unit\Model\Action;

/**
 * Testing \OxidEsales\PayPalModule\Model\Action\OrderAction class.
 */
class OrderActionTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Tests setting and getting of request object
     */
    public function testSetGetDependencies()
    {
        $order = new \stdClass();
        $handler = new \stdClass();
        $mockBuilder = $this->getMockBuilder(\OxidEsales\PayPalModule\Model\Action\OrderAction::class);
        $mockBuilder->setMethods(['process']);
        $mockBuilder->setConstructorArgs([$handler, $order]);
        $action = $mockBuilder->getMock();

        $this->assertSame($order, $action->getOrder());
        $this->assertSame($handler, $action->getHandler());
    }
}