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

use OxidEsales\PayPalModule\Model\Action\Data\OrderCaptureActionData;

/**
 * Testing \OxidEsales\PayPalModule\Model\Action\Data\OrderCaptureActionData class.
 */
class OrderCaptureActionDataTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    public function providerCaptureAmount()
    {
        return [
            ['59,92', '59.92'],
            ['59.92', '59.92'],
            ['1000,33', '1000.33'],
            ['10,000,33', '10,000.33']
        ];
    }

    /**
     * Tests setting parameters from request
     *
     * @dataProvider providerCaptureAmount
     *
     * @param string $captureAmount
     */
    public function testSettingParameters_FromRequest($captureAmount, $calculatedAmount)
    {
        $type = 'Full';

        $params = array(
            'capture_amount' => $captureAmount,
            'capture_type'   => $type,
        );
        $request = $this->createPartialMock(
            \OxidEsales\PayPalModule\Core\Request::class,
            array('getPost')
        );
        $request->method('getPost')->willReturn($params);

        $order = $this->getOrder();

        $actionData = new \OxidEsales\PayPalModule\Model\Action\Data\OrderCaptureActionData($request, $order);

        $this->assertEquals($calculatedAmount, $actionData->getAmount());
        $this->assertEquals($type, $actionData->getType());
    }

    /**
     * Tests getting amount when amount is not set and no amount is passed with request. Should be taken from order
     */
    public function testGetAmount_AmountNotSet_TakenFromOrder()
    {
        $remainingOrderSum = 59.67;

        $payPalOrder = $this->createConfiguredMock(
            \OxidEsales\PayPalModule\Model\PayPalOrder::class,
            array('getRemainingOrderSum' => $remainingOrderSum)
        );
        $order = $this->createConfiguredMock(
            \OxidEsales\PayPalModule\Model\Order::class,
            array('getPayPalOrder' => $payPalOrder)
        );
        $request = $this->createConfiguredMock(
            \OxidEsales\PayPalModule\Core\Request::class,
            array('getPost' => array())
        );

        $actionData = new OrderCaptureActionData($request, $order);

        $this->assertEquals($remainingOrderSum, $actionData->getAmount());
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
        $request = $this->createConfiguredMock(
            \OxidEsales\PayPalModule\Core\Request::class,
            array('getGet' => $params)
        );

        return $request;
    }

    /**
     * Returns PayPal order object
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected function getOrder()
    {
        $order = new \OxidEsales\PayPalModule\Model\PayPalOrder();

        return $order;
    }
}
