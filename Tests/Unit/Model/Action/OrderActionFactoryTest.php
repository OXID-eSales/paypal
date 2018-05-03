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
 * Testing \OxidEsales\PayPalModule\Model\Action\OrderActionFactory class.
 */
class OrderActionFactoryTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Data provider for testCreateAction
     */
    public function providerCreateAction()
    {
        return array(
            array('capture', \OxidEsales\PayPalModule\Model\Action\OrderCaptureAction::class),
            array('refund', \OxidEsales\PayPalModule\Model\Action\OrderRefundAction::class),
            array('void', \OxidEsales\PayPalModule\Model\Action\OrderVoidAction::class),
        );
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\Action\OrderActionFactory::createAction
     * Testing action object creation with correct actions
     *
     * @dataProvider providerCreateAction
     */
    public function testCreateAction($action, $class)
    {
        $order = oxNew(\OxidEsales\PayPalModule\Model\Order::class);
        $request = new \OxidEsales\PayPalModule\Core\Request();
        $actionFactory = new \OxidEsales\PayPalModule\Model\Action\OrderActionFactory($request, $order);

        $this->assertTrue($actionFactory->createAction($action) instanceof $class);
    }

    /**
     * Test case for \OxidEsales\PayPalModule\Model\Action\OrderActionFactory::createAction
     * Testing action object creation with incorrect actions
     *
     * @expectedException \OxidEsales\PayPalModule\Core\Exception\PayPalInvalidActionException
     */
    public function testCreateActionWithInvalidData()
    {
        $order = oxNew(\OxidEsales\PayPalModule\Model\Order::class);
        $request = new \OxidEsales\PayPalModule\Core\Request();
        $actionFactory = new \OxidEsales\PayPalModule\Model\Action\OrderActionFactory($request, $order);

        $actionFactory->createAction('some_non_existing_action');
    }
}