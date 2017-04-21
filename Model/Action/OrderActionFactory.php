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
 * @copyright (C) OXID eSales AG 2003-2017
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
    protected $_oOrder = null;

    /**
     * Sets dependencies
     *
     * @param \OxidEsales\PayPalModule\Core\Request $oRequest
     * @param \OxidEsales\PayPalModule\Model\Order  $oOrder
     */
    public function __construct($oRequest, $oOrder)
    {
        $this->_oRequest = $oRequest;
        $this->_oOrder = $oOrder;
    }

    /**
     * Returns Request object
     *
     * @return \OxidEsales\PayPalModule\Core\Request
     */
    public function getRequest()
    {
        return $this->_oRequest;
    }

    /**
     * Returns Order object
     *
     * @return \OxidEsales\PayPalModule\Model\Order
     */
    public function getOrder()
    {
        return $this->_oOrder;
    }

    /**
     * Creates action object by given action name.
     *
     * @param string $sAction
     *
     * @return object
     *
     * @throws \OxidEsales\PayPalModule\Core\Exception\PayPalInvalidActionException
     */
    public function createAction($sAction)
    {
        $sMethod = "get" . ucfirst($sAction) . "Action";

        if (!method_exists($this, $sMethod)) {
            /** @var \OxidEsales\PayPalModule\Core\Exception\PayPalInvalidActionException $oException */
            $oException = oxNew(\OxidEsales\PayPalModule\Core\Exception\PayPalInvalidActionException::class);
            throw $oException;
        }

        return $this->$sMethod();
    }

    /**
     * Returns capture action object
     *
     * @return \OxidEsales\PayPalModule\Model\Action\OrderCaptureAction
     */
    public function getCaptureAction()
    {
        $oOrder = $this->getOrder();
        $oRequest = $this->getRequest();

        $oData = oxNew(\OxidEsales\PayPalModule\Model\Action\Data\OrderCaptureActionData::class, $oRequest, $oOrder);
        $oHandler = oxNew(\OxidEsales\PayPalModule\Model\Action\Handler\OrderCaptureActionHandler::class, $oData);

        $oReauthorizeData = oxNew(\OxidEsales\PayPalModule\Model\Action\Data\OrderReauthorizeActionData::class, $oRequest, $oOrder);
        $oReauthorizeHandler = oxNew(\OxidEsales\PayPalModule\Model\Action\Handler\OrderReauthorizeActionHandler::class, $oReauthorizeData);

        $oAction = oxNew(\OxidEsales\PayPalModule\Model\Action\OrderCaptureAction::class, $oHandler, $oOrder->getPayPalOrder(), $oReauthorizeHandler);

        return $oAction;
    }

    /**
     * Returns refund action object
     *
     * @return \OxidEsales\PayPalModule\Model\Action\OrderRefundAction
     */
    public function getRefundAction()
    {
        $oOrder = $this->getOrder();
        $oData = oxNew(\OxidEsales\PayPalModule\Model\Action\Data\OrderRefundActionData::class, $this->getRequest(), $oOrder);
        $oHandler = oxNew(\OxidEsales\PayPalModule\Model\Action\Handler\OrderRefundActionHandler::class, $oData);

        $oAction = oxNew(\OxidEsales\PayPalModule\Model\Action\OrderRefundAction::class, $oHandler, $oOrder->getPayPalOrder());

        return $oAction;
    }

    /**
     * Returns void action object
     *
     * @return \OxidEsales\PayPalModule\Model\Action\OrderVoidAction
     */
    public function getVoidAction()
    {
        $oOrder = $this->getOrder();
        $oData = oxNew(\OxidEsales\PayPalModule\Model\Action\Data\OrderVoidActionData::class, $this->getRequest(), $oOrder);
        $oHandler = oxNew(\OxidEsales\PayPalModule\Model\Action\Handler\OrderVoidActionHandler::class, $oData);

        $oAction = oxNew(\OxidEsales\PayPalModule\Model\Action\OrderVoidAction::class, $oHandler, $oOrder->getPayPalOrder());

        return $oAction;
    }
}
