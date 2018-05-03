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

namespace OxidEsales\PayPalModule\Model\Action;

/**
 * PayPal order action class
 */
abstract class OrderAction
{
    /**
     *
     * @var \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    protected $_oOrder = null;

    /**
     * @var string
     */
    protected $orderStatus = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\Action\Handler\OrderCaptureActionHandler
     */
    protected $handler = null;

    /**
     * Sets handler and order.
     *
     * @param \OxidEsales\PayPalModule\Model\Action\Handler\OrderCaptureActionHandler $handler
     * @param \OxidEsales\PayPalModule\Model\PayPalOrder                              $order
     */
    public function __construct($handler, $order)
    {
        $this->handler = $handler;
        $this->order = $order;
    }

    /**
     * Returns \OxidEsales\PayPalModule\Model\Action\Handler\OrderCaptureActionHandler object.
     *
     * @return \OxidEsales\PayPalModule\Model\Action\Handler\OrderCaptureActionHandler
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Returns \OxidEsales\PayPalModule\Model\PayPalOrder object.
     *
     * @return \OxidEsales\PayPalModule\Model\PayPalOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Returns formatted date
     *
     * @return string
     */
    public function getDate()
    {
        $utilsDate = \OxidEsales\Eshop\Core\Registry::getUtilsDate();

        return date('Y-m-d H:i:s', $utilsDate->getTime());
    }

    /**
     * Processes PayPal action
     */
    abstract public function process();
}
