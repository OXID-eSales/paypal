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
 * PayPal order action factory class
 */
class OrderActionFactory
{
    /**
     * @var \OxidEsales\PayPalModule\Core\Request
     */
    protected $_oRequest = null;

    /**
     * @var \OxidEsales\PayPalModule\Model\Order
     */
    protected $order = null;

    /**
     * Sets dependencies
     *
     * @param \OxidEsales\PayPalModule\Core\Request $request
     * @param \OxidEsales\PayPalModule\Model\Order  $order
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
     * Returns Order object
     *
     * @return \OxidEsales\PayPalModule\Model\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Creates action object by given action name.
     *
     * @param string $action
     *
     * @return object
     *
     * @throws \OxidEsales\PayPalModule\Core\Exception\PayPalInvalidActionException
     */
    public function createAction($action)
    {
        $method = "get" . ucfirst($action) . "Action";

        if (!method_exists($this, $method)) {
            /** @var \OxidEsales\PayPalModule\Core\Exception\PayPalInvalidActionException $exception */
            $exception = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalInvalidActionException::class);
            throw $exception;
        }

        return $this->$method();
    }

    /**
     * Returns capture action object
     *
     * @return \OxidEsales\PayPalModule\Model\Action\OrderCaptureAction
     */
    public function getCaptureAction()
    {
        $order = $this->getOrder();
        $request = $this->getRequest();

        $data = oxNew(\OxidEsales\PayPalModule\Model\Action\Data\OrderCaptureActionData::class, $request, $order);
        $handler = oxNew(\OxidEsales\PayPalModule\Model\Action\Handler\OrderCaptureActionHandler::class, $data);

        $reauthorizeData = oxNew(\OxidEsales\PayPalModule\Model\Action\Data\OrderReauthorizeActionData::class, $request, $order);
        $reauthorizeHandler = oxNew(\OxidEsales\PayPalModule\Model\Action\Handler\OrderReauthorizeActionHandler::class, $reauthorizeData);

        $action = oxNew(\OxidEsales\PayPalModule\Model\Action\OrderCaptureAction::class, $handler, $order->getPayPalOrder(), $reauthorizeHandler);

        return $action;
    }

    /**
     * Returns refund action object
     *
     * @return \OxidEsales\PayPalModule\Model\Action\OrderRefundAction
     */
    public function getRefundAction()
    {
        $order = $this->getOrder();
        $data = oxNew(\OxidEsales\PayPalModule\Model\Action\Data\OrderRefundActionData::class, $this->getRequest(), $order);
        $handler = oxNew(\OxidEsales\PayPalModule\Model\Action\Handler\OrderRefundActionHandler::class, $data);

        $action = oxNew(\OxidEsales\PayPalModule\Model\Action\OrderRefundAction::class, $handler, $order->getPayPalOrder());

        return $action;
    }

    /**
     * Returns void action object
     *
     * @return \OxidEsales\PayPalModule\Model\Action\OrderVoidAction
     */
    public function getVoidAction()
    {
        $order = $this->getOrder();
        $data = oxNew(\OxidEsales\PayPalModule\Model\Action\Data\OrderVoidActionData::class, $this->getRequest(), $order);
        $handler = oxNew(\OxidEsales\PayPalModule\Model\Action\Handler\OrderVoidActionHandler::class, $data);

        $action = oxNew(\OxidEsales\PayPalModule\Model\Action\OrderVoidAction::class, $handler, $order->getPayPalOrder());

        return $action;
    }
}
