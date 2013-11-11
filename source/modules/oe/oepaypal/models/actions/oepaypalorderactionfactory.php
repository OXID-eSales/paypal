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
 * @copyright (C) OXID eSales AG 2003-2013
 */

/**
 * PayPal order action factory class
 */
class oePayPalOrderActionFactory
{

    /**
     * @var oePayPalRequest
     */
    protected $_oRequest = null;

    /**
     * @var oePayPalOxOrder
     */
    protected $_oOrder = null;

    /**
     * Sets dependencies
     *
     * @param oePayPalRequest $oRequest
     * @param oePayPalOxOrder $oOrder
     */
    public function __construct( $oRequest, $oOrder )
    {
        $this->_oRequest = $oRequest;
        $this->_oOrder = $oOrder;
    }

    /**
     * Returns Request object
     *
     * @return oePayPalRequest
     */
    public function getRequest()
    {
        return $this->_oRequest;
    }

    /**
     * Returns Order object
     *
     * @return oePayPalOxOrder
     */
    public function getOrder()
    {
        return $this->_oOrder;
    }

    /**
     * Creates action object by given action name
     */
    public function createAction( $sAction )
    {
        $sMethod = "get".ucfirst( $sAction )."Action";

        if ( !method_exists( $this, $sMethod ) ) {
            throw oxNew( 'oePayPalInvalidActionException' );
        }

        return $this->$sMethod();
    }

    /**
     * Returns capture action object
     *
     * @return oePayPalOrderCaptureAction
     */
    public function getCaptureAction()
    {
        $oOrder = $this->getOrder();
        $oRequest = $this->getRequest();

        $oData = oxNew( 'oePayPalOrderCaptureActionData', $oRequest, $oOrder );
        $oHandler = oxNew( 'oePayPalOrderCaptureActionHandler', $oData );

        $oReauthorizeData = oxNew( 'oePayPalOrderReauthorizeActionData', $oRequest, $oOrder );
        $oReauthorizeHandler = oxNew( 'oePayPalOrderReauthorizeActionHandler', $oReauthorizeData );

        $oAction = oxNew( 'oePayPalOrderCaptureAction', $oHandler, $oOrder->getPayPalOrder(), $oReauthorizeHandler );

        return $oAction;
    }

    /**
     * Returns refund action object
     *
     * @return oePayPalOrderRefundAction
     */
    public function getRefundAction()
    {
        $oOrder = $this->getOrder();
        $oData = oxNew( 'oePayPalOrderRefundActionData', $this->getRequest(), $oOrder );
        $oHandler = oxNew( 'oePayPalOrderRefundActionHandler', $oData );

        $oAction = oxNew( 'oePayPalOrderRefundAction', $oHandler, $oOrder->getPayPalOrder() );

        return $oAction;
    }

    /**
     * Returns void action object
     *
     * @return oePayPalOrderVoidAction
     */
    public function getVoidAction()
    {
        $oOrder = $this->getOrder();
        $oData = oxNew( 'oePayPalOrderVoidActionData', $this->getRequest(), $oOrder );
        $oHandler = oxNew( 'oePayPalOrderVoidActionHandler', $oData );

        $oAction = oxNew( 'oePayPalOrderVoidAction', $oHandler, $oOrder->getPayPalOrder() );

        return $oAction;
    }

}