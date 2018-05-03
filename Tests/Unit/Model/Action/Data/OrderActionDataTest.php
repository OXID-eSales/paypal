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

use OxidEsales\Eshop\Application\Model\Order;

/**
 * Testing \OxidEsales\PayPalModule\Model\Action\Data\OrderActionData class.
 */
class OrderActionDataTest extends \OxidEsales\TestingLibrary\UnitTestCase
{
    /**
     */
    public function testGetAuthorizationId()
    {
        $request = $this->getRequest(array());
        $order = $this->getOrder();
        $order->oxorder__oxtransid = new \OxidEsales\Eshop\Core\Field('authorizationId');

        $actionData = new \OxidEsales\PayPalModule\Model\Action\Data\OrderActionData($request, $order);

        $this->assertEquals('authorizationId', $actionData->getAuthorizationId());
    }

    /**
     */
    public function testGetAmount()
    {
        $request = $this->getRequest(array('action_comment' => 'comment'));
        $order = $this->getOrder();

        $actionData = new \OxidEsales\PayPalModule\Model\Action\Data\OrderActionData($request, $order);

        $this->assertEquals('comment', $actionData->getComment());
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

    /**
     *
     */
    protected function getOrder()
    {
        $order = oxNew(Order::class);

        return $order;
    }
}