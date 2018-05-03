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

namespace OxidEsales\PayPalModule\Tests\Unit\Model\Action\Data;

/**
 * Testing \OxidEsales\PayPalModule\Model\Action\Data\OrderVoidActionData class.
 */
class OrderVoidActionDataTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     * Tests getting amount when amount is not set and no amount is passed with request. Should be taken from order
     */
    public function testGetAmount_AmountNotSet_TakenFromOrder()
    {
        $remainingOrderSum = 59.67;

        $payPalOrder = $this->_createStub(\OxidEsales\PayPalModule\Model\PayPalOrder::class, array('getRemainingOrderSum' => $remainingOrderSum));
        $order = $this->_createStub(\OxidEsales\PayPalModule\Model\Order::class, array('getPayPalOrder' => $payPalOrder));
        $request = $this->_createStub(\OxidEsales\PayPalModule\Core\Request::class, array('getPost' => array()));

        $action = new \OxidEsales\PayPalModule\Model\Action\Data\OrderVoidActionData($request, $order);

        $this->assertEquals($remainingOrderSum, $action->getAmount());
    }

    /**
     *  Returns Request object with given parameters
     *
     * @param $params
     *
     * @return mixed
     */
    protected function getRequest($params)
    {
        $request = $this->_createStub(\OxidEsales\PayPalModule\Core\Request::class, array('getGet' => $params));

        return $request;
    }
}