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

namespace OxidEsales\PayPalModule\Model\Action\Data;

/**
 * PayPal order action factory class
 */
class OrderActionData
{
    /**
     * Request object
     *
     * @var \OxidEsales\PayPalModule\Core\Request
     */
    protected $request = null;

    /**
     * Order object
     *
     * @var \OxidEsales\PayPalModule\Model\Order
     */
    protected $order = null;

    /**
     * Sets dependencies.
     *
     * @param \OxidEsales\PayPalModule\Core\Request      $request
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder $order
     */
    public function __construct($request, $order)
    {
        $this->request = $request;
        $this->order = $order;
    }

    /**
     * Returns Request object
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns PayPal Order object
     *
     * @return \OxidEsales\PayPalModule\Model\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * returns action amount
     *
     * @return string
     */
    public function getAuthorizationId()
    {
        return $this->getOrder()->oxorder__oxtransid->value;
    }

    /**
     * returns comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->getRequest()->getRequestParameter('action_comment');
    }

    /**
     * Returns order status
     *
     * @return string
     */
    public function getOrderStatus()
    {
        return $this->getRequest()->getRequestParameter('order_status');
    }
}
